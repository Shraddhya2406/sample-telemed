@extends('doctor.layout')

@section('title', 'Appointment')
@section('page-title', 'Appointment Details')

@section('content')
@php
    $notesLocked = in_array($appointment->status, ['rejected', 'completed'], true);
    $aiConversations = $appointment->patient->healthConversations->sortByDesc('created_at')->take(3);
    $linkedAIConversation = $appointment->healthConversation;
@endphp

<div class="grid gap-4 lg:grid-cols-3">
    <section class="space-y-4 lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-base font-bold text-slate-950">{{ $appointment->patient->name }}</h2>
                        <x-doctor.status-badge :status="$appointment->status" />
                    </div>
                    <p class="truncate text-xs text-slate-500">{{ $appointment->patient->email }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-700">{{ $appointment->appointment_date?->format('d M Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($appointment->status === 'approved')
                        <a href="{{ route('doctor.call.start', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                            <i data-lucide="video" class="h-4 w-4"></i>
                            Start Call
                        </a>
                    @endif
                    @if($appointment->status === 'completed' && $appointment->prescription)
                        <a href="{{ route('doctor.prescriptions.show', $appointment->prescription) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                            Prescription
                        </a>
                    @endif
                    <a href="{{ route('doctor.patients.show', $appointment->patient) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                        History
                    </a>
                </div>
            </div>

            @if($appointment->status === 'pending')
                <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}" class="flex flex-wrap gap-2 border-b border-slate-200 px-4 py-3">
                    @csrf
                    @method('PATCH')
                    <button name="status" value="approved" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Accept
                    </button>
                    <button name="status" value="rejected" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Reject
                    </button>
                </form>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Consultation Notes</h2>
                    <p class="text-xs text-slate-500">Diagnosis and advice for this appointment.</p>
                </div>
                @if($notesLocked)
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                        <i data-lucide="lock" class="h-3.5 w-3.5"></i>
                        Locked
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('doctor.appointments.notes', $appointment) }}" class="mt-3 space-y-3">
                @csrf
                @method('PATCH')

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Symptoms</p>
                        <p class="mt-1 max-h-16 overflow-y-auto rounded-lg bg-slate-50 px-3 py-2 text-sm leading-5 text-slate-600">{{ $appointment->symptoms ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Patient Notes</p>
                        <p class="mt-1 max-h-16 overflow-y-auto rounded-lg bg-slate-50 px-3 py-2 text-sm leading-5 text-slate-600">{{ $appointment->notes ?: 'Not provided' }}</p>
                    </div>
                </div>

                <div class="grid gap-3 lg:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Diagnosis</label>
                        <textarea name="diagnosis" class="min-h-32 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required @readonly($notesLocked)>{{ old('diagnosis', $appointment->diagnosis) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Advice</label>
                        <textarea name="advice" class="min-h-32 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required @readonly($notesLocked)>{{ old('advice', $appointment->advice) }}</textarea>
                    </div>
                </div>

                @unless($notesLocked)
                    <div class="flex flex-wrap gap-2">
                        <button class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Save Notes
                        </button>
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50" name="next" value="prescription">
                            <i data-lucide="clipboard-plus" class="h-4 w-4"></i>
                            Create Prescription
                        </button>
                    </div>
                @endunless
            </form>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Linked AI Conversation</h2>
                    <p class="text-xs text-slate-500">Conversation used when this appointment was booked.</p>
                </div>
                @if($linkedAIConversation)
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $linkedAIConversation->urgency_level === 'emergency' ? 'bg-red-50 text-red-700 ring-1 ring-red-100' : ($linkedAIConversation->urgency_level === 'high' ? 'bg-orange-50 text-orange-700 ring-1 ring-orange-100' : ($linkedAIConversation->urgency_level === 'medium' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-100' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100')) }}">
                        {{ str($linkedAIConversation->urgency_level)->headline() }}
                    </span>
                @endif
            </div>

            @if($linkedAIConversation)
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">AI Summary</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $linkedAIConversation->summary ?: 'No summary available.' }}</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Medicine Suggestions</p>
                        <div class="mt-2 space-y-2">
                            @forelse($linkedAIConversation->medicine_suggestions ?? [] as $suggestion)
                                <div class="text-sm leading-5 text-blue-950">
                                    <span class="font-semibold">{{ $suggestion['name'] ?? 'Medicine' }}</span>
                                    <span class="text-blue-700">- {{ $suggestion['reason'] ?? 'Suggested from symptom context.' }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-blue-800">No medicine suggestions were generated.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-3 max-h-[28rem] space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                    @foreach($linkedAIConversation->messages->sortBy('id') as $message)
                        <div class="flex {{ $message->sender_type === 'patient' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[86%] rounded-lg px-3 py-2 text-sm leading-5 {{ $message->sender_type === 'patient' ? 'bg-blue-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200' }}">
                                <p class="text-[11px] font-semibold {{ $message->sender_type === 'patient' ? 'text-blue-100' : 'text-slate-500' }}">{{ $message->sender_type === 'patient' ? 'Patient' : 'AI Assistant' }} · {{ $message->created_at?->format('d M Y h:i A') }}</p>
                                <p class="mt-1">{{ $message->message }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-doctor.empty-state title="No linked AI conversation" message="This appointment was not booked from an AI assessment." icon="bot" class="py-6" />
            @endif
        </section>
    </section>

    <aside class="space-y-4 lg:col-span-1 lg:sticky lg:top-24 lg:self-start">
        <section id="messages" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Chat</h2>
                    <p class="text-xs text-slate-500">Appointment messages</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                    <i data-lucide="message-circle" class="h-3.5 w-3.5"></i>
                    Live
                </span>
            </div>

            <div
                class="mt-3"
                data-chat-messages
                data-chat-variant="doctor"
                data-fetch-url="{{ rtrim(request()->getBaseUrl(), '/') }}/doctor/appointments/{{ $appointment->id }}/messages"
                data-appointment-id="{{ $appointment->id }}"
                data-current-user-id="{{ auth()->id() }}"
                data-last-id="{{ $appointment->messages->max('id') ?? 0 }}"
            >
                @forelse($appointment->messages->sortBy('id') as $message)
                    <div class="chat-message {{ $message->sender_id === auth()->id() ? 'chat-message-own' : 'chat-message-other' }}" data-message-id="{{ $message->id }}">
                        <div class="chat-bubble">
                            <div class="chat-meta">{{ $message->sender_id === auth()->id() ? 'You' : $message->sender->name }} &middot; {{ $message->created_at->format('d M Y h:i A') }}</div>
                            <div class="chat-body">{{ $message->message }}</div>
                        </div>
                    </div>
                @empty
                    <x-doctor.empty-state title="No messages yet" message="Appointment messages will appear here." icon="message-circle" data-chat-empty class="py-6" />
                @endforelse
            </div>

            <form method="POST" action="{{ route('doctor.appointments.messages.store', $appointment) }}" class="mt-3" data-chat-form>
                @csrf
                <label class="sr-only" for="appointment-message">Write a message</label>
                <textarea id="appointment-message" name="message" class="min-h-20 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Write a message"></textarea>
                <div class="mt-2 hidden text-sm font-medium text-red-600" data-chat-error></div>
                <button class="mt-2 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                    <i data-lucide="send" class="h-4 w-4"></i>
                    Send
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 text-xs shadow-sm">
            <h2 class="text-sm font-semibold text-slate-950">Payment Details</h2>
            <dl class="mt-3 space-y-2">
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Fee</dt>
                    <dd class="text-right font-semibold text-slate-900">Rs. {{ number_format((float) $appointment->consultation_fee, 2) }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-right font-semibold {{ $appointment->payment_status === 'paid' ? 'text-emerald-600' : 'text-slate-900' }}">{{ str($appointment->payment_status ?? 'unpaid')->headline() }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Method</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ str($appointment->payment_method ?: 'N/A')->headline() }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Paid At</dt>
                    <dd class="text-right font-semibold text-slate-900">{{ $appointment->paid_at?->format('d M Y h:i A') ?: 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Payment ID</dt>
                    <dd class="mt-1 break-words font-semibold text-slate-900">{{ $appointment->payment_id ?: 'N/A' }}</dd>
                </div>
            </dl>
        </section>

        {{--
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-slate-950">Quiz History</h2>
            @forelse($appointment->patient->quizAttempts as $attempt)
                <div class="mt-3 rounded-xl border border-slate-200 p-3">
                    <div class="font-semibold text-slate-950">{{ $attempt->result_category }}</div>
                    <div class="mb-2 text-sm text-slate-500">{{ $attempt->created_at->format('d M Y') }}</div>
                    @foreach($attempt->quizAnswers as $answer)
                        <div class="text-sm text-slate-600">
                            <strong>{{ $answer->healthQuestion?->question ?? 'Question unavailable' }}</strong><br>
                            {{ $answer->healthOption?->option_text ?? 'Answer unavailable' }}
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="mt-3 text-sm text-slate-500">No quiz attempts found.</div>
            @endforelse
        </section>
        --}}
    </aside>
</div>

@include('appointments.chat-script')
@endsection
