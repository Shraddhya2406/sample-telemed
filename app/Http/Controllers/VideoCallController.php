<?php

namespace App\Http\Controllers;

use App\Events\VideoCallSignal;
use App\Models\Appointment;
use App\Models\User;
use App\Models\VideoCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoCallController extends Controller
{
    public function start(Request $request, User $patient): View
    {
        $doctor = $request->user();

        abort_unless($doctor->role?->name === 'doctor', 403);
        abort_unless($patient->role?->name === 'patient', 403);
        $this->authorizeDoctorCanCallPatient($doctor->id, $patient->id);

        $videoCall = VideoCall::create([
            'caller_id' => $doctor->id,
            'receiver_id' => $patient->id,
            'status' => 'initiated',
        ])->load(['caller.role', 'receiver.role']);

        broadcast(new VideoCallSignal($videoCall, $doctor, $patient->id, 'incoming-call'));

        return view('video-call', [
            'videoCall' => $videoCall,
            'otherUser' => $patient,
            'isCaller' => true,
        ]);
    }

    public function show(Request $request, VideoCall $videoCall): View
    {
        $videoCall->load(['caller.role', 'receiver.role']);
        abort_unless($videoCall->hasParticipant($request->user()), 403);

        return view('video-call', [
            'videoCall' => $videoCall,
            'otherUser' => $videoCall->otherParticipant($request->user()),
            'isCaller' => $request->user()->id === $videoCall->caller_id,
        ]);
    }

    public function accept(Request $request): JsonResponse
    {
        $videoCall = $this->callForReceiver($request);

        if ($videoCall->status !== 'initiated') {
            return response()->json(['message' => 'This call is no longer available.'], 422);
        }

        $videoCall->update([
            'status' => 'accepted',
            'started_at' => now(),
        ]);

        broadcast(new VideoCallSignal($videoCall->fresh(['caller.role', 'receiver.role']), $request->user(), $videoCall->caller_id, 'call-accepted'));

        return response()->json([
            'message' => 'Call accepted.',
            'call_url' => route('call.show', $videoCall),
        ]);
    }

    public function reject(Request $request): JsonResponse
    {
        $videoCall = $this->callForReceiver($request);

        if ($videoCall->status === 'ended') {
            return response()->json(['message' => 'This call has already ended.'], 422);
        }

        $videoCall->update([
            'status' => 'rejected',
            'ended_at' => now(),
        ]);

        broadcast(new VideoCallSignal($videoCall->fresh(['caller.role', 'receiver.role']), $request->user(), $videoCall->caller_id, 'call-rejected'));

        return response()->json(['message' => 'Call rejected.']);
    }

    public function end(Request $request): JsonResponse|RedirectResponse
    {
        $videoCall = $this->participantCall($request);

        if (! in_array($videoCall->status, ['rejected', 'ended'], true)) {
            $videoCall->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);

            $videoCall = $videoCall->fresh(['caller.role', 'receiver.role']);
            $otherUser = $videoCall->otherParticipant($request->user());

            if ($otherUser) {
                broadcast(new VideoCallSignal($videoCall, $request->user(), $otherUser->id, 'call-ended'));
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Call ended.']);
        }

        return back()->with('success', 'Call ended.');
    }

    public function signal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video_call_id' => ['required', 'integer', 'exists:video_calls,id'],
            'type' => ['required', 'string', 'in:offer,answer,ice-candidate'],
            'payload' => ['required', 'array'],
        ]);

        $videoCall = VideoCall::with(['caller.role', 'receiver.role'])->findOrFail($validated['video_call_id']);
        abort_unless($videoCall->hasParticipant($request->user()), 403);
        abort_if(in_array($videoCall->status, ['rejected', 'ended'], true), 422, 'This call is not active.');

        $otherUser = $videoCall->otherParticipant($request->user());
        abort_unless($otherUser, 403);

        broadcast(new VideoCallSignal($videoCall, $request->user(), $otherUser->id, $validated['type'], $validated['payload']));

        return response()->json(['message' => 'Signal sent.']);
    }

    private function callForReceiver(Request $request): VideoCall
    {
        $validated = $request->validate([
            'video_call_id' => ['required', 'integer', 'exists:video_calls,id'],
        ]);

        return VideoCall::with(['caller.role', 'receiver.role'])
            ->whereKey($validated['video_call_id'])
            ->where('receiver_id', $request->user()->id)
            ->firstOrFail();
    }

    private function participantCall(Request $request): VideoCall
    {
        $validated = $request->validate([
            'video_call_id' => ['required', 'integer', 'exists:video_calls,id'],
        ]);

        $videoCall = VideoCall::with(['caller.role', 'receiver.role'])->findOrFail($validated['video_call_id']);
        abort_unless($videoCall->hasParticipant($request->user()), 403);

        return $videoCall;
    }

    private function authorizeDoctorCanCallPatient(int $doctorId, int $patientId): void
    {
        $hasAppointment = Appointment::where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where('status', 'approved')
            ->exists();

        abort_unless($hasAppointment, 403, 'Only patients with an approved appointment can be called.');
    }
}
