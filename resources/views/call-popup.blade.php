@auth
@php
    $broadcastConnection = config('broadcasting.default');
    $echoConnection = in_array($broadcastConnection, ['pusher', 'reverb'], true)
        ? $broadcastConnection
        : 'pusher';
    $broadcastConfig = config("broadcasting.connections.{$echoConnection}", []);
    $broadcastOptions = $broadcastConfig['options'] ?? [];
    $echoKey = $broadcastConfig['key'];
    $echoCluster = $broadcastOptions['cluster'] ?? config('broadcasting.connections.pusher.options.cluster', 'mt1');
    $echoHost = $echoConnection === 'reverb'
        ? ($broadcastOptions['host'] ?? null)
        : (env('PUSHER_HOST') ?: null);
    $echoPort = $broadcastOptions['port'] ?? 80;
    $echoScheme = $broadcastOptions['scheme'] ?? 'http';
@endphp
<div id="incoming-call-popup" class="telemed-call-popup" hidden>
    <div class="telemed-call-panel" role="dialog" aria-live="polite" aria-labelledby="incoming-call-title">
        <div class="telemed-call-avatar" data-call-initial>?</div>
        <div class="telemed-call-copy">
            <div id="incoming-call-title" class="telemed-call-title">Incoming video call</div>
            <div class="telemed-call-meta"><span data-call-caller>Name</span> &middot; <span data-call-role>Role</span></div>
        </div>
        <div class="telemed-call-actions">
            <button type="button" class="telemed-call-btn telemed-call-accept" data-call-accept>Accept</button>
            <button type="button" class="telemed-call-btn telemed-call-reject" data-call-reject>Reject</button>
        </div>
    </div>
</div>

<style>
    .telemed-call-popup {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        z-index: 9999;
        max-width: min(26rem, calc(100vw - 2rem));
    }
    .telemed-call-panel {
        display: grid;
        grid-template-columns: 3rem minmax(0, 1fr);
        gap: .85rem;
        align-items: center;
        padding: 1rem;
        color: #0f172a;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: .5rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .2);
    }
    .telemed-call-avatar {
        display: inline-flex;
        width: 3rem;
        height: 3rem;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        color: #fff;
        background: #0f766e;
        font-weight: 800;
    }
    .telemed-call-copy { min-width: 0; }
    .telemed-call-title { font-weight: 800; }
    .telemed-call-meta { overflow-wrap: anywhere; color: #64748b; font-size: .9rem; }
    .telemed-call-actions {
        grid-column: 1 / -1;
        display: flex;
        gap: .5rem;
    }
    .telemed-call-btn {
        flex: 1;
        min-height: 2.35rem;
        border: 0;
        border-radius: .5rem;
        font-weight: 700;
        cursor: pointer;
    }
    .telemed-call-accept { color: #fff; background: #16a34a; }
    .telemed-call-reject { color: #fff; background: #dc2626; }
    .dark .telemed-call-panel {
        color: #e2e8f0;
        background: #0f172a;
        border-color: #334155;
    }
    .dark .telemed-call-meta { color: #94a3b8; }
</style>

<script>
    window.telemedCallConfig = Object.assign(window.telemedCallConfig || {}, {
        userId: @json(auth()->id()),
        csrfToken: @json(csrf_token()),
        echo: {
            key: @json($echoKey),
            cluster: @json($echoCluster),
            wsHost: @json($echoHost),
            wsPort: @json($echoPort),
            wssPort: @json($echoPort ?: 443),
            forceTLS: @json($echoScheme === 'https'),
        },
        routes: {
            accept: @json(route('call.accept')),
            reject: @json(route('call.reject')),
            broadcastAuth: @json(url('/broadcasting/auth')),
        }
    });

    window.loadTelemedScript = window.loadTelemedScript || function (src) {
        return new Promise(function (resolve, reject) {
            const existing = document.querySelector('script[src="' + src + '"]');
            if (existing) {
                existing.addEventListener('load', resolve, { once: true });
                if (existing.dataset.loaded === '1') resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = function () { script.dataset.loaded = '1'; resolve(); };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    };

    window.ensureTelemedEcho = window.ensureTelemedEcho || function () {
        if (window.Echo && typeof window.Echo.private === 'function') return Promise.resolve(window.Echo);
        const config = window.telemedCallConfig.echo || {};
        if (!config.key) return Promise.reject(new Error('Broadcasting key is not configured.'));

        return window.loadTelemedScript('https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js')
            .then(function () {
                const pusherOptions = {
                    cluster: config.cluster,
                    forceTLS: Boolean(config.forceTLS),
                    enabledTransports: ['ws', 'wss'],
                    channelAuthorization: {
                        endpoint: window.telemedCallConfig.routes.broadcastAuth,
                        headers: { 'X-CSRF-TOKEN': window.telemedCallConfig.csrfToken },
                    },
                };

                if (config.wsHost && !String(config.wsHost).startsWith('api-')) {
                    pusherOptions.wsHost = config.wsHost;
                    pusherOptions.wsPort = Number(config.wsPort || 80);
                    pusherOptions.wssPort = Number(config.wssPort || 443);
                }

                const pusher = new Pusher(config.key, pusherOptions);

                window.Echo = {
                    pusher: pusher,
                    private: function (name) {
                        const channel = pusher.subscribe('private-' + name);

                        return {
                            subscribed: function (callback) {
                                channel.bind('pusher:subscription_succeeded', callback);
                                return this;
                            },
                            error: function (callback) {
                                channel.bind('pusher:subscription_error', callback);
                                return this;
                            },
                            listen: function (eventName, callback) {
                                channel.bind(String(eventName).replace(/^\./, ''), callback);
                                return this;
                            },
                        };
                    },
                };

                return window.Echo;
            });
    };

    (function () {
        const popup = document.getElementById('incoming-call-popup');
        if (!popup || window.telemedIncomingCallLoaded) return;
        window.telemedIncomingCallLoaded = true;

        let activeCall = null;
        const caller = popup.querySelector('[data-call-caller]');
        const role = popup.querySelector('[data-call-role]');
        const initial = popup.querySelector('[data-call-initial]');
        const accept = popup.querySelector('[data-call-accept]');
        const reject = popup.querySelector('[data-call-reject]');

        function post(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.telemedCallConfig.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.message || 'Call action failed.');
                    return data;
                });
            });
        }

        function showIncoming(event) {
            activeCall = event;
            const name = event.caller?.name || event.from?.name || 'Caller';
            caller.textContent = name;
            role.textContent = event.caller?.role || event.from?.role || 'doctor';
            initial.textContent = name.charAt(0).toUpperCase();
            popup.hidden = false;
        }

        accept?.addEventListener('click', function () {
            if (!activeCall) return;
            accept.disabled = true;
            post(window.telemedCallConfig.routes.accept, { video_call_id: activeCall.video_call_id })
                .then(function (data) { window.location.href = data.call_url; })
                .catch(function (error) { alert(error.message); })
                .finally(function () { accept.disabled = false; });
        });

        reject?.addEventListener('click', function () {
            if (!activeCall) return;
            reject.disabled = true;
            post(window.telemedCallConfig.routes.reject, { video_call_id: activeCall.video_call_id })
                .then(function () { popup.hidden = true; activeCall = null; })
                .catch(function (error) { alert(error.message); })
                .finally(function () { reject.disabled = false; });
        });

        window.ensureTelemedEcho()
            .then(function (echo) {
                echo.private('users.' + window.telemedCallConfig.userId)
                    .listen('.video-call.signal', function (event) {
                        if (event.type === 'incoming-call') showIncoming(event);
                    });
            })
            .catch(function (error) {
                console.warn('Video call notifications are offline:', error.message);
            });
    })();
</script>
@endauth
