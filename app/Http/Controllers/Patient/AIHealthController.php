<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\HealthConversation;
use App\Models\HealthMessage;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AIHealthController extends Controller
{
    public function __construct(private readonly GeminiService $gemini)
    {
    }

    public function index(Request $request): View
    {
        $conversations = HealthConversation::forUser($request->user()->id)
            ->with(['messages' => fn ($query) => $query->oldest('id')])
            ->latest()
            ->limit(8)
            ->get();

        $activeConversation = $conversations->firstWhere('status', 'active');

        return view('patient.ai-health-chat', compact('conversations', 'activeConversation'));
    }

    public function start(Request $request): JsonResponse
    {
        $conversation = HealthConversation::firstOrCreate(
            ['user_id' => $request->user()->id, 'status' => 'active'],
            ['urgency_level' => 'low']
        );

        if ($conversation->messages()->doesntExist()) {
            $conversation->messages()->create([
                'sender_type' => 'assistant',
                'message' => 'What symptoms are you experiencing today?',
                'created_at' => now(),
            ]);
        }

        return response()->json($this->conversationResource($conversation->load('messages')));
    }

    public function send(Request $request, HealthConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        if ($conversation->status !== 'active') {
            return response()->json(['message' => 'This assessment is already completed. Please start a new conversation.'], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $localEmergency = $this->detectEmergency($validated['message']);

        $conversation->messages()->create([
            'sender_type' => 'patient',
            'message' => $validated['message'],
            'created_at' => now(),
        ]);

        if ($localEmergency) {
            $reply = [
                'reply' => 'Your symptoms may need urgent medical attention. Please contact local emergency services or go to the nearest emergency department now. Are you currently safe and able to get help?',
                'urgency_level' => 'emergency',
                'should_end' => false,
                'summary' => 'Patient reported possible emergency symptoms during AI preliminary assessment.',
            ];
        } else {
            try {
                $reply = $this->gemini->generateReply($conversation->fresh('messages'));
            } catch (\Throwable $exception) {
                return $this->geminiFailureResponse($exception);
            }
        }

        $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message' => $reply['reply'],
            'created_at' => now(),
        ]);

        $conversation->update([
            'urgency_level' => $this->highestUrgency($conversation->urgency_level, $reply['urgency_level']),
            'status' => $reply['should_end'] ? 'completed' : 'active',
            'summary' => $reply['summary'] ?: $conversation->summary,
        ]);

        return response()->json($this->conversationResource($conversation->fresh('messages')));
    }

    public function complete(Request $request, HealthConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        if ($conversation->messages()->where('sender_type', 'patient')->doesntExist()) {
            return response()->json(['message' => 'Please share your symptoms before ending the assessment.'], 422);
        }

        try {
            $summary = $this->gemini->generateSummary($conversation->load('messages'));
        } catch (\Throwable $exception) {
            return $this->geminiFailureResponse($exception);
        }

        $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message' => $summary['reply'],
            'created_at' => now(),
        ]);

        $conversation->update([
            'status' => 'completed',
            'summary' => $summary['summary'],
            'urgency_level' => $this->highestUrgency($conversation->urgency_level, $summary['urgency_level']),
        ]);

        return response()->json($this->conversationResource($conversation->fresh('messages')));
    }

    public function restart(Request $request): JsonResponse
    {
        HealthConversation::forUser($request->user()->id)
            ->where('status', 'active')
            ->update(['status' => 'completed']);

        $conversation = HealthConversation::create([
            'user_id' => $request->user()->id,
            'status' => 'active',
            'urgency_level' => 'low',
        ]);

        $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message' => 'What symptoms are you experiencing today?',
            'created_at' => now(),
        ]);

        return response()->json($this->conversationResource($conversation->load('messages')));
    }

    private function authorizeConversation(Request $request, HealthConversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
    }

    private function detectEmergency(string $message): bool
    {
        return str($message)->lower()->contains([
            'chest pain',
            'can not breathe',
            "can't breathe",
            'difficulty breathing',
            'shortness of breath',
            'stroke',
            'face drooping',
            'slurred speech',
            'severe bleeding',
            'unconscious',
            'fainted',
            'seizure',
            'suicidal',
            'suicide',
            'anaphylaxis',
            'swollen throat',
            'stiff neck',
            'confusion',
        ]);
    }

    private function highestUrgency(string $current, string $incoming): string
    {
        $rank = ['low' => 1, 'medium' => 2, 'high' => 3, 'emergency' => 4];

        return ($rank[$incoming] ?? 1) > ($rank[$current] ?? 1) ? $incoming : $current;
    }

    private function geminiFailureResponse(\Throwable $exception): JsonResponse
    {
        return response()->json([
            'message' => 'Gemini connection failed, so the AI assistant could not generate a response.',
            'details' => config('app.debug') ? $exception->getMessage() : null,
        ], 503);
    }

    private function conversationResource(HealthConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'status' => $conversation->status,
            'summary' => $conversation->summary,
            'urgency_level' => $conversation->urgency_level,
            'created_at' => $conversation->created_at?->format('d M Y h:i A'),
            'messages' => $conversation->messages->sortBy('id')->values()->map(fn (HealthMessage $message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'message' => $message->message,
                'created_at' => $message->created_at?->format('h:i A'),
            ])->all(),
        ];
    }
}
