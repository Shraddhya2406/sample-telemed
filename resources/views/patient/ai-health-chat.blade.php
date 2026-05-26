@extends('layouts.patient')

@section('title', 'AI Health Assistant')
@section('page_title', 'AI Health Assistant')
@section('eyebrow', 'Preliminary symptom assessment')

@section('content')
@php
    $aiAssistantTimezone = 'Asia/Kolkata';
    $formatAssistantDate = fn ($date) => $date ? $date->copy()->timezone($aiAssistantTimezone)->format('d M Y h:i A').' IST' : null;
    $formatAssistantTime = fn ($date) => $date ? $date->copy()->timezone($aiAssistantTimezone)->format('h:i A').' IST' : null;
    $initialConversation = $activeConversation ?: $conversations->first();
    $historyPayload = $conversations->map(fn ($conversation) => [
        'id' => $conversation->id,
        'status' => $conversation->status,
        'summary' => $conversation->summary,
        'urgency_level' => $conversation->urgency_level,
        'medicine_suggestions' => $conversation->medicine_suggestions ?? [],
        'medicine_suggestion_message' => $conversation->status === 'completed' && empty($conversation->medicine_suggestions ?? [])
            ? (in_array($conversation->urgency_level, ['high', 'emergency'], true)
                ? 'No medicine suggestions are shown because your assessment may need prompt medical review.'
                : 'No suitable in-stock medicine matched your assessment from the current pharmacy inventory. Please book a doctor consultation or check with a pharmacist for safe guidance.')
            : null,
        'created_at' => $formatAssistantDate($conversation->created_at),
        'messages' => $conversation->messages->sortBy('id')->values()->map(fn ($message) => [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'message' => $message->message,
            'created_at' => $formatAssistantTime($message->created_at),
        ])->all(),
    ])->values();
@endphp

<div class="grid min-h-[calc(100svh-8rem)] gap-4 pb-24 sm:gap-5 lg:min-h-[calc(100vh-9rem)] lg:grid-cols-[minmax(0,1fr)_22rem] lg:pb-0">
    <section class="flex h-[calc(100svh-10rem)] min-h-[32rem] flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:h-[calc(100svh-11rem)] lg:h-auto lg:min-h-[38rem]">
        <div class="shrink-0 border-b border-slate-200 bg-slate-50 px-3 py-3 dark:border-slate-800 dark:bg-slate-950 sm:px-5 sm:py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white sm:h-10 sm:w-10">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5a7 7 0 0 0-7 7v1a5 5 0 0 0 5 5h1v-6H7v-1a5 5 0 0 1 10 0v1h-4v6h1a5 5 0 0 0 5-5v-1a7 7 0 0 0-7-7Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-slate-950 dark:text-white sm:text-lg">Clinical AI Assistant</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">One question at a time, saved for doctor review.</p>
                        </div>
                    </div>
                </div>
                <button type="button" id="new-assessment" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm font-bold text-blue-700 transition hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-900 dark:text-blue-300 dark:hover:bg-slate-800 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                    </svg>
                    New Assessment
                </button>
            </div>
            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm leading-6 text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-100 sm:mt-4 sm:px-4 sm:py-3">
                This AI assistant provides preliminary guidance only and is not a replacement for professional medical advice. For severe symptoms, seek immediate medical attention.
            </div>
        </div>

        <div id="chat-messages" class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50 px-3 py-4 dark:bg-slate-950 sm:space-y-4 sm:px-5 sm:py-5" aria-live="polite"></div>

        <div id="typing-indicator" class="hidden shrink-0 border-t border-slate-200 bg-white px-3 py-3 dark:border-slate-800 dark:bg-slate-900 sm:px-5">
            <div class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                <span class="h-2 w-2 animate-pulse rounded-full bg-blue-600"></span>
                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500 [animation-delay:120ms]"></span>
                <span class="h-2 w-2 animate-pulse rounded-full bg-blue-600 [animation-delay:240ms]"></span>
                Thinking
            </div>
        </div>

        <form id="chat-form" class="shrink-0 border-t border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900 sm:p-5">
            <div class="flex gap-2">
                <label for="message-input" class="sr-only">Message</label>
                <textarea id="message-input" class="min-h-12 min-w-0 flex-1 resize-none rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm leading-5 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-blue-950" rows="1" placeholder="Describe your symptoms" required></textarea>
                <button type="submit" class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60" aria-label="Send message">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m22 2-7 20-4-9-9-4 20-7Z" />
                    </svg>
                </button>
            </div>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p id="chat-error" class="hidden min-w-0 text-sm font-semibold text-rose-600"></p>
                <button type="button" id="complete-assessment" class="inline-flex w-full items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300 sm:ml-auto sm:w-auto">Finish and Save Summary</button>
            </div>
        </form>
    </section>

    <aside class="space-y-4 lg:space-y-5">
        <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Assessment Status</h3>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500">Urgency</p>
                    <p id="urgency-label" class="mt-1 text-lg font-bold text-slate-950 dark:text-white">Low</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500">Status</p>
                    <p id="status-label" class="mt-1 text-lg font-bold text-slate-950 dark:text-white">Active</p>
                </div>
            </div>
            <div id="summary-panel" class="mt-4 hidden rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm leading-6 text-emerald-900 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-100"></div>
            <div id="medicine-panel" class="mt-4 hidden">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h4 class="text-sm font-bold text-slate-950 dark:text-white">Available Medicine Suggestions</h4>
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">In stock</span>
                </div>
                <div id="medicine-list" class="space-y-1.5"></div>
                <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">These are not prescriptions. Please confirm with a doctor or pharmacist before use.</p>
            </div>
            <a id="book-doctor-review" href="{{ route('patient.appointments.create') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700">Book Doctor Appointment</a>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">History</h3>
                <span id="history-count" class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">{{ $conversations->count() }}</span>
            </div>
            <div id="history-list" class="mt-3 max-h-72 space-y-2 overflow-y-auto pr-1 lg:max-h-[28rem]"></div>
        </section>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const routes = {
            start: @json(route('patient.ai-health.start')),
            restart: @json(route('patient.ai-health.restart')),
            sendTemplate: @json(route('patient.ai-health.send', ['conversation' => '__ID__'])),
            completeTemplate: @json(route('patient.ai-health.complete', ['conversation' => '__ID__'])),
        };
        const conversations = @json($historyPayload);
        const addToCartUrl = @json(route('patient.cart.add'));
        let current = @json($initialConversation ? $historyPayload->firstWhere('id', $initialConversation->id) : null);
        let busy = false;

        const messagesEl = document.getElementById('chat-messages');
        const form = document.getElementById('chat-form');
        const input = document.getElementById('message-input');
        const errorEl = document.getElementById('chat-error');
        const typingEl = document.getElementById('typing-indicator');
        const urgencyEl = document.getElementById('urgency-label');
        const statusEl = document.getElementById('status-label');
        const summaryEl = document.getElementById('summary-panel');
        const medicinePanel = document.getElementById('medicine-panel');
        const medicineList = document.getElementById('medicine-list');
        const bookDoctorReview = document.getElementById('book-doctor-review');
        const historyEl = document.getElementById('history-list');
        const historyCountEl = document.getElementById('history-count');
        const completeButton = document.getElementById('complete-assessment');
        const newButton = document.getElementById('new-assessment');

        function url(template) {
            return template.replace('__ID__', current.id);
        }

        function setBusy(value) {
            busy = value;
            form.querySelector('button[type="submit"]').disabled = value;
            completeButton.disabled = value || !current || current.status !== 'active';
            typingEl.classList.toggle('hidden', !value);
        }

        function badgeClass(urgency) {
            return {
                emergency: 'text-rose-700 dark:text-rose-300',
                high: 'text-orange-700 dark:text-orange-300',
                medium: 'text-amber-700 dark:text-amber-300',
                low: 'text-emerald-700 dark:text-emerald-300',
            }[urgency] || 'text-slate-950 dark:text-white';
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value || '';
            return div.innerHTML;
        }

        function renderMessages() {
            const messages = current?.messages || [];
            messagesEl.innerHTML = messages.map((message) => {
                const isPatient = message.sender_type === 'patient';
                return `
                    <div class="flex ${isPatient ? 'justify-end' : 'justify-start'}">
                        <div class="max-w-[92%] rounded-lg px-3 py-2.5 shadow-sm sm:max-w-[84%] sm:px-4 sm:py-3 ${isPatient ? 'bg-blue-600 text-white' : 'border border-slate-200 bg-white text-slate-800 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100'}">
                            <p class="whitespace-pre-wrap break-words text-sm leading-6">${escapeHtml(message.message)}</p>
                            <p class="mt-1 text-[11px] font-semibold ${isPatient ? 'text-blue-100' : 'text-slate-400'}">${isPatient ? 'You' : 'Assistant'} · ${escapeHtml(message.created_at || '')}</p>
                        </div>
                    </div>
                `;
            }).join('');
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function renderStatus() {
            const urgency = current?.urgency_level || 'low';
            urgencyEl.textContent = urgency.charAt(0).toUpperCase() + urgency.slice(1);
            urgencyEl.className = 'mt-1 text-lg font-bold ' + badgeClass(urgency);
            statusEl.textContent = current?.status ? current.status.charAt(0).toUpperCase() + current.status.slice(1) : 'Active';
            summaryEl.classList.toggle('hidden', !current?.summary);
            summaryEl.textContent = current?.summary || '';
            renderMedicineSuggestions();
            updateBookDoctorReviewLink();
            input.disabled = current?.status === 'completed';
            input.placeholder = current?.status === 'completed' ? 'This assessment is completed' : 'Describe your symptoms';
            completeButton.disabled = !current || current.status !== 'active' || busy;
        }

        function updateBookDoctorReviewLink() {
            const baseUrl = @json(route('patient.appointments.create'));
            if (!current?.id) {
                bookDoctorReview.href = baseUrl;
                return;
            }

            const url = new URL(baseUrl, window.location.origin);
            url.searchParams.set('ai_conversation', current.id);
            bookDoctorReview.href = url.toString();
        }

        function renderMedicineSuggestions() {
            const suggestions = current?.medicine_suggestions || [];
            const message = current?.medicine_suggestion_message || '';
            medicinePanel.classList.toggle('hidden', !suggestions.length && !message);
            medicineList.innerHTML = suggestions.length ? suggestions.map((suggestion) => `
                <article class="rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex gap-2">
                        <img src="${escapeHtml(suggestion.image_url || '')}" alt="" class="h-10 w-10 shrink-0 rounded-md border border-slate-100 object-cover dark:border-slate-800">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-1 min-[380px]:flex-row min-[380px]:items-start min-[380px]:justify-between min-[380px]:gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-slate-950 dark:text-white">${escapeHtml(suggestion.name)}</p>
                                    <p class="truncate text-[11px] font-semibold text-slate-500">${escapeHtml(suggestion.category || 'Medicine')}</p>
                                </div>
                                <p class="shrink-0 text-[11px] font-bold text-slate-700 dark:text-slate-200">Rs. ${Number(suggestion.price || 0).toFixed(2)}</p>
                            </div>
                            <p class="mt-1 line-clamp-2 text-[11px] leading-4 text-slate-600 dark:text-slate-300">${escapeHtml(suggestion.reason)}</p>
                            <p class="mt-0.5 line-clamp-2 text-[11px] leading-4 text-amber-700 dark:text-amber-300">${escapeHtml(suggestion.caution)}</p>
                            <div class="mt-1.5 flex flex-col gap-2 min-[380px]:flex-row min-[380px]:items-center min-[380px]:justify-between">
                                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">${Number(suggestion.stock_quantity || 0)} left</span>
                                <div class="grid grid-cols-2 gap-1 min-[380px]:flex">
                                    <a href="${escapeHtml(suggestion.url)}" class="rounded-md border border-slate-200 px-2 py-1 text-center text-[11px] font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200">View</a>
                                    <button type="button" data-add-medicine-id="${Number(suggestion.medicine_id || 0)}" class="rounded-md bg-blue-600 px-2 py-1 text-[11px] font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            `).join('') : `
                <div class="rounded-lg border border-slate-200 bg-white p-3 text-sm leading-5 text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300">
                    ${escapeHtml(message || 'No suitable medicine suggestions were found from current inventory.')}
                </div>
            `;
        }

        medicineList.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-add-medicine-id]');
            if (!button || button.disabled) return;

            const medicineId = button.dataset.addMedicineId;
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Adding';

            try {
                const form = new FormData();
                form.append('medicine_id', medicineId);
                form.append('quantity', '1');

                const response = await fetch(addToCartUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: form,
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Could not add medicine to cart.');
                }

                window.showPatientToast?.(data.message || 'Medicine added to cart.', 'success');
                if (typeof data.cart_count !== 'undefined') {
                    const badge = document.getElementById('cart-count-badge');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                    }
                }
                button.textContent = 'Added';
            } catch (error) {
                window.showPatientToast?.(error.message || 'Could not add medicine to cart.', 'error');
                button.textContent = originalText;
                button.disabled = false;
            }
        });

        function renderHistory() {
            historyCountEl.textContent = conversations.length;
            historyEl.innerHTML = conversations.length ? conversations.map((conversation) => `
                <button type="button" data-conversation-id="${conversation.id}" class="block w-full rounded-lg border px-3 py-2 text-left transition ${current?.id === conversation.id ? 'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950/40' : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800'}">
                    <span class="block truncate text-sm font-bold text-slate-900 dark:text-white">${escapeHtml(conversation.created_at || 'Assessment')}</span>
                    <span class="mt-1 block text-xs font-semibold text-slate-500">${escapeHtml(conversation.status)} · ${escapeHtml(conversation.urgency_level)}</span>
                    <span class="mt-1 block truncate text-xs text-slate-500">${escapeHtml(conversation.summary || conversation.messages?.at(-1)?.message || 'Symptom assessment')}</span>
                </button>
            `).join('') : '<p class="rounded-lg bg-slate-50 px-3 py-4 text-sm text-slate-500 dark:bg-slate-950">No assessments yet.</p>';
        }

        function syncConversation(conversation) {
            current = conversation;
            const index = conversations.findIndex((item) => item.id === conversation.id);
            if (index >= 0) {
                conversations[index] = conversation;
            } else {
                conversations.unshift(conversation);
            }
            renderMessages();
            renderStatus();
            renderHistory();
        }

        async function postJson(endpoint, payload = {}) {
            setBusy(true);
            errorEl.classList.add('hidden');
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok) {
                    const error = new Error(data.message || 'Request failed.');
                    error.details = data.details || '';
                    error.debugSteps = Array.isArray(data.debug_steps) ? data.debug_steps : [];
                    throw error;
                }
                syncConversation(data);
            } catch (error) {
                const details = error.details ? `<p class="mt-2 text-xs text-rose-700">${escapeHtml(error.details)}</p>` : '';
                const steps = error.debugSteps?.length
                    ? `<ul class="mt-2 list-disc space-y-1 pl-5">${error.debugSteps.map((step) => `<li>${escapeHtml(step)}</li>`).join('')}</ul>`
                    : '';
                errorEl.innerHTML = `${escapeHtml(error.message || 'Network error.')}${details}${steps}`;
                errorEl.classList.remove('hidden');
            } finally {
                setBusy(false);
            }
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = input.value.trim();
            if (!message || busy) return;
            if (!current) {
                await postJson(routes.start);
            }
            input.value = '';
            await postJson(url(routes.sendTemplate), { message });
        });

        completeButton.addEventListener('click', () => {
            if (current && current.status === 'active') {
                postJson(url(routes.completeTemplate));
            }
        });

        newButton.addEventListener('click', () => postJson(routes.restart));

        historyEl.addEventListener('click', (event) => {
            const button = event.target.closest('[data-conversation-id]');
            if (!button) return;
            const found = conversations.find((conversation) => String(conversation.id) === button.dataset.conversationId);
            if (found) syncConversation(found);
        });

        if (current) {
            renderMessages();
            renderStatus();
            renderHistory();
        } else {
            postJson(routes.start);
        }
    })();
</script>
@endpush
