@extends('doctor.layout')

@section('title', 'Patient History')
@section('page-title', 'Patient Details')

@section('content')
@php
    $latestAppointment = $patient->patientAppointments->first();
    $latestPrescription = $patient->patientPrescriptions->first();
    $latestQuiz = $patient->quizAttempts->sortByDesc('created_at')->first();
    $latestAIConversation = $patient->healthConversations->sortByDesc('created_at')->first();
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_24rem]">
    <section class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-base font-bold text-blue-700">
                        {{ str($patient->name)->substr(0, 1)->upper() }}
                    </span>
                    <div class="min-w-0">
                        <h2 class="truncate text-base font-bold text-slate-950">{{ $patient->name }}</h2>
                        <p class="truncate text-xs text-slate-500">{{ $patient->email }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-700">
                            {{ $patient->patientAppointments->count() }} appointments · {{ $patient->patientPrescriptions->count() }} prescriptions
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($latestAppointment)
                        <a href="{{ route('doctor.appointments.show', $latestAppointment) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Latest Appointment
                        </a>
                    @endif
                    <a href="{{ route('doctor.patients.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                        Back to Patients
                    </a>
                </div>
            </div>

            <dl class="grid gap-x-8 gap-y-2 px-4 py-3 text-sm sm:grid-cols-2">
                <div class="flex items-baseline justify-between gap-4 border-b border-slate-100 py-1.5">
                    <dt class="text-slate-500">Last Visit</dt>
                    <dd class="shrink-0 text-right font-semibold text-slate-900">{{ $latestAppointment?->appointment_date?->format('d M Y') ?: 'N/A' }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-b border-slate-100 py-1.5">
                    <dt class="shrink-0 text-slate-500">Last Diagnosis</dt>
                    <dd class="min-w-0 truncate text-right font-semibold text-slate-900">{{ $latestAppointment?->diagnosis ?: 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Appointments</h2>
                    <p class="text-xs text-slate-500">Consultation timeline with this patient.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">{{ $patient->patientAppointments->count() }}</span>
            </div>

            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Diagnosis</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($patient->patientAppointments as $appointment)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-3 py-2 font-semibold text-slate-900">{{ $appointment->appointment_date?->format('d M Y') }}</td>
                                <td class="whitespace-nowrap px-3 py-2"><x-doctor.status-badge :status="$appointment->status" /></td>
                                <td class="max-w-xs px-3 py-2 text-slate-600">{{ str($appointment->diagnosis ?: $appointment->symptoms ?: 'Not recorded')->limit(70) }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right">
                                    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4">
                                    <x-doctor.empty-state title="No appointments" message="Appointments with this patient will appear here." icon="calendar" class="py-6" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Prescriptions</h2>
                    <p class="text-xs text-slate-500">Medication history created by you.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">{{ $patient->patientPrescriptions->count() }}</span>
            </div>

            <div class="mt-3 space-y-2">
                @forelse($patient->patientPrescriptions as $prescription)
                    <article class="rounded-xl border border-slate-200 px-3 py-2.5 transition hover:border-blue-200 hover:bg-slate-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-950">{{ $prescription->created_at->format('d M Y') }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ str($prescription->diagnosis ?: $prescription->notes ?: 'No diagnosis recorded')->limit(90) }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $prescription->items->pluck('medicine.name')->filter()->join(', ') ?: 'No medicines listed' }}</p>
                            </div>
                            <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">Open</a>
                        </div>
                    </article>
                @empty
                    <x-doctor.empty-state title="No prescriptions" message="Prescriptions created for this patient will appear here." icon="clipboard-list" class="py-6" />
                @endforelse
            </div>
        </div>
    </section>

    <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
        <section class="rounded-2xl border border-slate-200 bg-white p-4 text-xs shadow-sm">
            <h2 class="text-sm font-semibold text-slate-950">Patient Snapshot</h2>
            <dl class="mt-3 space-y-2">
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Patient ID</dt>
                    <dd class="font-semibold text-slate-900">#{{ $patient->id }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Appointments</dt>
                    <dd class="font-semibold text-slate-900">{{ $patient->patientAppointments->count() }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Prescriptions</dt>
                    <dd class="font-semibold text-slate-900">{{ $patient->patientPrescriptions->count() }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Latest AI Urgency</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ $latestAIConversation ? str($latestAIConversation->urgency_level)->headline() : ($latestQuiz?->result_category ?: 'N/A') }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">AI Assessments</h2>
                    <p class="text-xs text-slate-500">Preliminary conversations saved for review.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">{{ $patient->healthConversations->count() }}</span>
            </div>

            <div class="mt-3 max-h-[34rem] space-y-3 overflow-y-auto pr-1">
                @forelse($patient->healthConversations->sortByDesc('created_at') as $conversation)
                    <article class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">{{ $conversation->created_at->format('d M Y h:i A') }}</p>
                                <p class="text-xs text-slate-500">{{ str($conversation->status)->headline() }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ str($conversation->urgency_level)->headline() }}</span>
                        </div>
                        @if($conversation->summary)
                            <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600">{{ $conversation->summary }}</p>
                        @endif
                        @if(! empty($conversation->medicine_suggestions))
                            <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                                <p class="text-xs font-semibold text-blue-900">AI medicine suggestions from available stock</p>
                                <div class="mt-2 space-y-2">
                                    @foreach($conversation->medicine_suggestions as $suggestion)
                                        <div class="text-xs leading-5 text-blue-950">
                                            <span class="font-semibold">{{ $suggestion['name'] ?? 'Medicine' }}</span>
                                            <span class="text-blue-700">- {{ $suggestion['reason'] ?? 'Suggested from symptom context.' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-blue-700">Conversation transcript</summary>
                            <div class="mt-2 space-y-2">
                                @foreach($conversation->messages->sortBy('id') as $message)
                                    <div class="rounded-lg {{ $message->sender_type === 'patient' ? 'bg-blue-50 text-blue-950' : 'bg-slate-50 text-slate-700' }} px-3 py-2 text-xs leading-5">
                                        <span class="font-semibold">{{ $message->sender_type === 'patient' ? 'Patient' : 'AI Assistant' }}:</span>
                                        {{ $message->message }}
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    </article>
                @empty
                    <x-doctor.empty-state title="No AI assessments" message="AI health assessments will appear when the patient uses the assistant." icon="bot" class="py-6" />
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Quiz Results</h2>
                    <p class="text-xs text-slate-500">Patient self-assessment history.</p>
                </div>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">{{ $patient->quizAttempts->count() }}</span>
            </div>

            <div class="mt-3 max-h-[34rem] space-y-3 overflow-y-auto pr-1">
                @forelse($patient->quizAttempts as $attempt)
                    <article class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">{{ $attempt->result_category }}</p>
                                <p class="text-xs text-slate-500">{{ $attempt->created_at->format('d M Y h:i A') }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Score {{ $attempt->total_score }}</span>
                        </div>
                        <div class="mt-3 space-y-2">
                            @foreach($attempt->quizAnswers as $answer)
                                <div class="text-xs leading-5 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $answer->healthQuestion?->question ?? 'Question unavailable' }}</p>
                                    <p>{{ $answer->healthOption?->option_text ?? 'Answer unavailable' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <x-doctor.empty-state title="No quiz history" message="Quiz results will appear when the patient completes an assessment." icon="clipboard-check" class="py-6" />
                @endforelse
            </div>
        </section>
    </aside>
</div>
@endsection
