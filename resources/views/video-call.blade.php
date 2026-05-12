@php
    $currentUser = auth()->user();
    $currentRole = $currentUser?->role?->name;
    $layout = $currentRole === 'doctor' ? 'doctor.layout' : 'layouts.patient';
@endphp

@extends($layout)

@section('title', 'Video Consultation')
@section('page-title', 'Video Consultation')
@section('content')
<div class="telemed-room"
    data-video-call-id="{{ $videoCall->id }}"
    data-is-caller="{{ $isCaller ? '1' : '0' }}"
    data-status="{{ $videoCall->status }}"
    data-current-user-id="{{ $currentUser->id }}"
    data-signal-url="{{ route('call.signal') }}"
    data-accept-url="{{ route('call.accept') }}"
    data-reject-url="{{ route('call.reject') }}"
    data-end-url="{{ route('call.end') }}"
>
    <div class="telemed-room-header">
        <div>
            <p class="telemed-room-kicker">Secure one-to-one consultation</p>
            <h2 class="telemed-room-title">{{ $isCaller ? 'Calling' : 'Consulting with' }} {{ $otherUser?->role?->name === 'doctor' ? 'Dr. ' : '' }}{{ $otherUser?->name }}</h2>
            <p class="telemed-room-meta">
                {{ ucfirst($currentRole ?? 'user') }} to {{ ucfirst($otherUser?->role?->name ?? 'user') }} ·
                <span data-call-status>{{ ucfirst($videoCall->status) }}</span>
            </p>
        </div>
        <div class="telemed-room-actions">
            @if(! $isCaller && $videoCall->status === 'initiated')
                <button type="button" class="telemed-control telemed-control-accept" data-accept-call>Accept</button>
                <button type="button" class="telemed-control telemed-control-end" data-reject-call>Reject</button>
            @endif
            <button type="button" class="telemed-control" data-start-media>Start Camera</button>
            <button type="button" class="telemed-control" data-toggle-mic disabled>Mute Mic</button>
            <button type="button" class="telemed-control telemed-control-end" data-end-call>End Call</button>
        </div>
    </div>

    <div class="telemed-video-grid">
        <section class="telemed-video-stage telemed-video-remote">
            <video data-remote-video autoplay playsinline></video>
            <div class="telemed-video-label">{{ $otherUser?->name }} · {{ ucfirst($otherUser?->role?->name ?? 'User') }}</div>
            <div class="telemed-video-placeholder" data-remote-placeholder>Waiting for remote video</div>
        </section>
        <section class="telemed-video-stage telemed-video-local">
            <video data-local-video autoplay muted playsinline></video>
            <div class="telemed-video-label">You · {{ ucfirst($currentRole ?? 'User') }}</div>
            <div class="telemed-video-placeholder" data-local-placeholder>Camera is off</div>
        </section>
    </div>

    <div class="telemed-room-note" data-call-message>
        Allow camera and microphone access, then keep this page open while the consultation is active.
    </div>
</div>

<style>
    .telemed-room {
        display: grid;
        gap: 1rem;
        max-width: 1180px;
        margin: 0 auto;
        color: #0f172a;
    }
    .telemed-room-header {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #fff;
        border: 1px solid #dbe7ee;
        border-radius: .5rem;
    }
    .telemed-room-kicker {
        margin: 0 0 .2rem;
        color: #0f766e;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .telemed-room-title {
        margin: 0;
        font-size: clamp(1.25rem, 3vw, 1.85rem);
        font-weight: 800;
    }
    .telemed-room-meta {
        margin: .25rem 0 0;
        color: #64748b;
    }
    .telemed-room-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }
    .telemed-control {
        min-height: 2.5rem;
        padding: .55rem .9rem;
        border: 1px solid #cbd5e1;
        border-radius: .5rem;
        color: #0f172a;
        background: #fff;
        font-weight: 800;
        cursor: pointer;
    }
    .telemed-control:disabled {
        cursor: not-allowed;
        opacity: .55;
    }
    .telemed-control-accept {
        color: #fff;
        background: #16a34a;
        border-color: #16a34a;
    }
    .telemed-control-end {
        color: #fff;
        background: #dc2626;
        border-color: #dc2626;
    }
    .telemed-video-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(17rem, 26rem);
        gap: 1rem;
        align-items: stretch;
    }
    .telemed-video-stage {
        position: relative;
        min-height: 20rem;
        overflow: hidden;
        background: #020617;
        border-radius: .5rem;
    }
    .telemed-video-stage video {
        display: block;
        width: 100%;
        height: 100%;
        min-height: inherit;
        object-fit: cover;
        background: #020617;
    }
    .telemed-video-local {
        min-height: 16rem;
    }
    .telemed-video-label {
        position: absolute;
        left: .75rem;
        bottom: .75rem;
        z-index: 2;
        max-width: calc(100% - 1.5rem);
        padding: .35rem .55rem;
        overflow-wrap: anywhere;
        color: #fff;
        background: rgba(15, 23, 42, .74);
        border-radius: .5rem;
        font-size: .85rem;
        font-weight: 800;
    }
    .telemed-video-placeholder {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 1rem;
        color: #cbd5e1;
        text-align: center;
        pointer-events: none;
    }
    .telemed-video-stage.has-stream .telemed-video-placeholder {
        display: none;
    }
    .telemed-room-note {
        padding: .85rem 1rem;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
    }
    .dark .telemed-room,
    .dark .telemed-control {
        color: #e2e8f0;
    }
    .dark .telemed-room-header,
    .dark .telemed-control {
        background: #0f172a;
        border-color: #334155;
    }
    .dark .telemed-room-meta,
    .dark .telemed-room-note {
        color: #94a3b8;
    }
    .dark .telemed-room-note {
        background: #020617;
        border-color: #1e293b;
    }
    @media (max-width: 900px) {
        .telemed-video-grid {
            grid-template-columns: 1fr;
        }
        .telemed-video-stage,
        .telemed-video-local {
            min-height: 18rem;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    (function () {
        const room = document.querySelector('.telemed-room[data-video-call-id]');
        if (!room) return;

        const csrf = window.telemedCallConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const state = {
            callId: Number(room.dataset.videoCallId),
            isCaller: room.dataset.isCaller === '1',
            status: room.dataset.status,
            localStream: null,
            peer: null,
            remoteDescriptionReady: false,
            pendingCandidates: [],
            offerStarted: false,
        };

        const localVideo = room.querySelector('[data-local-video]');
        const remoteVideo = room.querySelector('[data-remote-video]');
        const localStage = localVideo.closest('.telemed-video-stage');
        const remoteStage = remoteVideo.closest('.telemed-video-stage');
        const statusEl = room.querySelector('[data-call-status]');
        const messageEl = room.querySelector('[data-call-message]');
        const startButton = room.querySelector('[data-start-media]');
        const micButton = room.querySelector('[data-toggle-mic]');
        const endButton = room.querySelector('[data-end-call]');
        const acceptButton = room.querySelector('[data-accept-call]');
        const rejectButton = room.querySelector('[data-reject-call]');

        function setMessage(text, isError) {
            messageEl.textContent = text;
            messageEl.style.borderColor = isError ? '#fecaca' : '';
            messageEl.style.color = isError ? '#b91c1c' : '';
        }

        function setStatus(status) {
            state.status = status;
            statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }

        function post(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.message || 'Request failed.');
                    return data;
                });
            });
        }

        function sendSignal(type, payload) {
            return post(room.dataset.signalUrl, {
                video_call_id: state.callId,
                type: type,
                payload: payload,
            }).catch(function (error) {
                console.warn('Signal failed:', error.message);
            });
        }

        function ensurePeer() {
            if (state.peer) return state.peer;

            state.peer = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }],
            });

            state.peer.onicecandidate = function (event) {
                if (event.candidate) sendSignal('ice-candidate', { candidate: event.candidate });
            };

            state.peer.ontrack = function (event) {
                if (remoteVideo.srcObject !== event.streams[0]) {
                    remoteVideo.srcObject = event.streams[0];
                    remoteStage.classList.add('has-stream');
                }
            };

            state.peer.onconnectionstatechange = function () {
                if (['failed', 'disconnected'].includes(state.peer.connectionState)) {
                    setMessage('The video connection was interrupted.', true);
                }
            };

            if (state.localStream) {
                state.localStream.getTracks().forEach(function (track) {
                    state.peer.addTrack(track, state.localStream);
                });
            }

            return state.peer;
        }

        function startMedia() {
            if (state.localStream) return Promise.resolve(state.localStream);

            return navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(function (stream) {
                    state.localStream = stream;
                    localVideo.srcObject = stream;
                    localStage.classList.add('has-stream');
                    micButton.disabled = false;
                    startButton.disabled = true;
                    ensurePeer();
                    setMessage('Camera and microphone are ready.');
                    return stream;
                })
                .catch(function (error) {
                    setMessage('Camera or microphone permission was denied.', true);
                    throw error;
                });
        }

        function maybeStartOffer() {
            if (!state.isCaller || state.offerStarted || state.status !== 'accepted') return;
            state.offerStarted = true;
            startMedia()
                .then(function () {
                    const peer = ensurePeer();
                    return peer.createOffer()
                        .then(function (offer) { return peer.setLocalDescription(offer); })
                        .then(function () { return sendSignal('offer', { description: peer.localDescription }); });
                })
                .then(function () { setMessage('Calling. Waiting for remote video.'); })
                .catch(function (error) {
                    state.offerStarted = false;
                    setMessage(error.message || 'Could not start the call.', true);
                });
        }

        function flushCandidates() {
            if (!state.remoteDescriptionReady || !state.peer) return;
            const candidates = state.pendingCandidates.splice(0);
            candidates.forEach(function (candidate) {
                state.peer.addIceCandidate(new RTCIceCandidate(candidate)).catch(function (error) {
                    console.warn('ICE candidate failed:', error.message);
                });
            });
        }

        function handleOffer(description) {
            startMedia()
                .then(function () {
                    const peer = ensurePeer();
                    return peer.setRemoteDescription(new RTCSessionDescription(description))
                        .then(function () {
                            state.remoteDescriptionReady = true;
                            flushCandidates();
                            return peer.createAnswer();
                        })
                        .then(function (answer) { return peer.setLocalDescription(answer); })
                        .then(function () { return sendSignal('answer', { description: peer.localDescription }); });
                })
                .then(function () { setMessage('Connected.'); })
                .catch(function (error) { setMessage(error.message || 'Could not answer the call.', true); });
        }

        function handleAnswer(description) {
            ensurePeer().setRemoteDescription(new RTCSessionDescription(description))
                .then(function () {
                    state.remoteDescriptionReady = true;
                    flushCandidates();
                    setMessage('Connected.');
                })
                .catch(function (error) { setMessage(error.message || 'Could not connect the call.', true); });
        }

        function handleCandidate(candidate) {
            if (!candidate) return;
            if (!state.remoteDescriptionReady || !state.peer) {
                state.pendingCandidates.push(candidate);
                return;
            }
            state.peer.addIceCandidate(new RTCIceCandidate(candidate)).catch(function (error) {
                console.warn('ICE candidate failed:', error.message);
            });
        }

        function cleanup() {
            state.localStream?.getTracks().forEach(function (track) { track.stop(); });
            state.peer?.close();
            state.localStream = null;
            state.peer = null;
            localVideo.srcObject = null;
            remoteVideo.srcObject = null;
            localStage.classList.remove('has-stream');
            remoteStage.classList.remove('has-stream');
        }

        startButton?.addEventListener('click', function () {
            startMedia().then(maybeStartOffer).catch(function () {});
        });

        micButton?.addEventListener('click', function () {
            const audioTrack = state.localStream?.getAudioTracks()[0];
            if (!audioTrack) return;
            audioTrack.enabled = !audioTrack.enabled;
            micButton.textContent = audioTrack.enabled ? 'Mute Mic' : 'Unmute Mic';
        });

        acceptButton?.addEventListener('click', function () {
            acceptButton.disabled = true;
            post(room.dataset.acceptUrl, { video_call_id: state.callId })
                .then(function () {
                    setStatus('accepted');
                    acceptButton.remove();
                    rejectButton?.remove();
                    return startMedia();
                })
                .catch(function (error) { setMessage(error.message, true); })
                .finally(function () { acceptButton.disabled = false; });
        });

        rejectButton?.addEventListener('click', function () {
            rejectButton.disabled = true;
            post(room.dataset.rejectUrl, { video_call_id: state.callId })
                .then(function () {
                    setStatus('rejected');
                    cleanup();
                    setMessage('Call rejected.');
                })
                .catch(function (error) { setMessage(error.message, true); })
                .finally(function () { rejectButton.disabled = false; });
        });

        endButton?.addEventListener('click', function () {
            endButton.disabled = true;
            post(room.dataset.endUrl, { video_call_id: state.callId })
                .then(function () {
                    setStatus('ended');
                    cleanup();
                    setMessage('Call ended.');
                })
                .catch(function (error) { setMessage(error.message, true); })
                .finally(function () { endButton.disabled = false; });
        });

        window.ensureTelemedEcho()
            .then(function (echo) {
                echo.private('users.' + window.telemedCallConfig.userId)
                    .listen('.video-call.signal', function (event) {
                        if (Number(event.video_call_id) !== state.callId) return;

                        if (event.type === 'call-accepted') {
                            setStatus('accepted');
                            maybeStartOffer();
                        } else if (event.type === 'call-rejected') {
                            setStatus('rejected');
                            cleanup();
                            setMessage('The call was rejected.');
                        } else if (event.type === 'call-ended') {
                            setStatus('ended');
                            cleanup();
                            setMessage('The other participant ended the call.');
                        } else if (event.type === 'offer') {
                            handleOffer(event.payload.description);
                        } else if (event.type === 'answer') {
                            handleAnswer(event.payload.description);
                        } else if (event.type === 'ice-candidate') {
                            handleCandidate(event.payload.candidate);
                        }
                    });

                if (state.status === 'accepted') maybeStartOffer();
            })
            .catch(function (error) {
                setMessage('Real-time signaling is offline: ' + error.message, true);
            });
    })();
</script>
@endpush
