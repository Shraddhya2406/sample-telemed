<style>
    [data-chat-messages] {
        max-height: 420px;
        overflow-y: auto;
        padding: .75rem;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: .75rem;
    }

    .chat-message {
        display: flex;
        margin-bottom: .75rem;
    }

    .chat-message:last-child {
        margin-bottom: 0;
    }

    .chat-message-own {
        justify-content: flex-end;
    }

    .chat-message-other {
        justify-content: flex-start;
    }

    .chat-bubble {
        max-width: min(78%, 34rem);
        padding: .65rem .85rem;
        border-radius: 1rem;
        border: 1px solid #dbe7ee;
        background: #fff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
    }

    .chat-message-own .chat-bubble {
        color: #fff;
        background: #0f766e;
        border-color: #0f766e;
        border-bottom-right-radius: .3rem;
    }

    .chat-message-other .chat-bubble {
        color: #1f2937;
        background: #fff;
        border-bottom-left-radius: .3rem;
    }

    .chat-meta {
        margin-bottom: .2rem;
        font-size: .76rem;
        color: #6b7280;
    }

    .chat-message-own .chat-meta {
        color: rgba(255, 255, 255, .78);
        text-align: right;
    }

    .chat-body {
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        line-height: 1.45;
    }
</style>

<script>
    (function () {
        if (window.appointmentChatLoaded) {
            return;
        }

        window.appointmentChatLoaded = true;

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value || '';
            return div.innerHTML;
        }

        function messageHtml(message, variant) {
            const ownerClass = message.is_own ? 'chat-message-own' : 'chat-message-other';
            const displayName = message.is_own ? 'You' : message.sender_name;
            const meta = escapeHtml(displayName) + ' &middot; ' + escapeHtml(message.created_at);
            const body = escapeHtml(message.message);

            return '<div class="chat-message ' + ownerClass + '" data-message-id="' + message.id + '">' +
                '<div class="chat-bubble">' +
                '<div class="chat-meta">' + meta + '</div>' +
                '<div class="chat-body">' + body + '</div>' +
                '</div>' +
                '</div>';
        }

        function setError(form, text) {
            const error = form.querySelector('[data-chat-error]');

            if (!error) {
                return;
            }

            error.textContent = text || '';
            error.classList.toggle('hidden', !text);
            error.classList.toggle('d-none', !text);
        }

        function appendMessages(container, messages) {
            const variant = container.dataset.chatVariant || 'patient';
            const empty = container.querySelector('[data-chat-empty]');

            messages.forEach(function (message) {
                if (container.querySelector('[data-message-id="' + message.id + '"]')) {
                    return;
                }

                if (empty) {
                    empty.remove();
                }

                container.insertAdjacentHTML('beforeend', messageHtml(message, variant));
                container.dataset.lastId = Math.max(Number(container.dataset.lastId || 0), Number(message.id));
            });

            if (messages.length > 0) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function fetchMessages(container) {
            const url = new URL(container.dataset.fetchUrl, window.location.origin);
            url.searchParams.set('after_id', container.dataset.lastId || 0);

            return fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Unable to load messages.');
                    }

                    return response.json();
                })
                .then(function (data) {
                    appendMessages(container, data.messages || []);
                });
        }

        function initChat() {
            const container = document.querySelector('[data-chat-messages]');
            const form = document.querySelector('[data-chat-form]');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            if (!container || !form) {
                return;
            }

            container.scrollTop = container.scrollHeight;

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                setError(form, '');

                const textarea = form.querySelector('textarea[name="message"]');
                const button = form.querySelector('button[type="submit"], button:not([type])');
                const body = new FormData(form);

                if (button) {
                    button.disabled = true;
                }

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body,
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                const errors = data.errors ? Object.values(data.errors).flat().join(' ') : null;
                                throw new Error(errors || data.message || 'Message could not be sent.');
                            }

                            return data;
                        });
                    })
                    .then(function (data) {
                        if (data.chat_message) {
                            appendMessages(container, [data.chat_message]);
                        }

                        if (textarea) {
                            textarea.value = '';
                            textarea.focus();
                        }
                    })
                    .catch(function (error) {
                        setError(form, error.message || 'Message could not be sent.');
                    })
                    .finally(function () {
                        if (button) {
                            button.disabled = false;
                        }
                    });
            });

            fetchMessages(container).catch(function () {});
            window.setInterval(function () {
                fetchMessages(container).catch(function () {});
            }, 3000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChat);
        } else {
            initChat();
        }
    })();
</script>
