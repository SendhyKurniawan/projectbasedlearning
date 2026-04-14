// Conference Room JS - LiveKit Client Integration
import { Room, RoomEvent, Track, VideoPresets } from 'livekit-client';

let room = null;
let camEnabled = true;
let micEnabled = true;
let screenShareEnabled = false;
let elapsedSeconds = 0;
let elapsedInterval = null;

// Avatar color palette matched to template
const AVATAR_COLORS = [
    '#1a73e8', '#0f9d58', '#f4b400', '#db4437', '#9c27b0',
    '#00bcd4', '#ff5722', '#795548', '#607d8b', '#e91e63',
];

function getAvatarColor(identity) {
    let hash = 0;
    for (let i = 0; i < identity.length; i++) {
        hash = identity.charCodeAt(i) + ((hash << 5) - hash);
    }
    return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
}

function getInitials(name) {
    return name.split(' ').slice(0, 2).map(p => p[0]?.toUpperCase() || '').join('');
}

function formatDuration(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    if (h > 0) {
        return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

// ---------------------------------------------------------------------------
// ConferenceUI — manages new UI state on top of ConferenceRoom
// ---------------------------------------------------------------------------
const ConferenceUI = {
    sidePanel: null,    // null | 'chat' | 'participants'
    viewMode: 'grid',  // 'grid' | 'spotlight'
    pinnedId: null,
    messages: [],
    unreadCount: 0,
    handRaised: false,
    participants: {},   // identity -> { name, color, isMuted, isVideoOff, isHandRaised, isSpeaking }
    reactionCounter: 0,

    // ---- Panel management ----
    togglePanel(panel) {
        if (ConferenceUI.sidePanel === panel) {
            ConferenceUI.closePanel();
        } else {
            ConferenceUI.sidePanel = panel;
            document.getElementById('panel-chat')?.classList.add('panel-hidden');
            document.getElementById('panel-participants')?.classList.add('panel-hidden');
            const target = document.getElementById('panel-' + panel);
            target?.classList.remove('panel-hidden');
            document.getElementById('side-panel-wrapper')?.classList.remove('panel-hidden');
            if (panel === 'chat') {
                ConferenceUI.unreadCount = 0;
                ConferenceUI.updateUnreadBadge();
                setTimeout(() => {
                    const msgs = document.getElementById('chat-messages');
                    if (msgs) msgs.scrollTop = msgs.scrollHeight;
                    document.getElementById('chat-input')?.focus();
                }, 50);
            }
            if (panel === 'participants') {
                ConferenceUI.renderParticipants();
            }
        }
    },

    closePanel() {
        ConferenceUI.sidePanel = null;
        document.getElementById('side-panel-wrapper')?.classList.add('panel-hidden');
    },

    // ---- Chat ----
    sendMessage(text) {
        if (!text?.trim()) return;
        const msg = {
            id: 'msg-' + Date.now(),
            senderName: window.__myName || 'Anda',
            senderColor: window.__myColor || '#1a73e8',
            message: text.trim(),
            timestamp: new Date(),
            isMe: true,
        };
        ConferenceUI.messages.push(msg);
        ConferenceUI.appendMessage(msg);

        // Broadcast via LiveKit data channel
        if (room) {
            const payload = JSON.stringify({ type: 'chat', name: msg.senderName, message: msg.message });
            const encoded = new TextEncoder().encode(payload);
            room.localParticipant.publishData(encoded, { reliable: true });
        }
    },

    receiveMessage(senderIdentity, senderName, message) {
        const color = getAvatarColor(senderIdentity);
        const msg = {
            id: 'msg-' + Date.now() + '-' + Math.random(),
            senderName,
            senderColor: color,
            message,
            timestamp: new Date(),
            isMe: false,
        };
        ConferenceUI.messages.push(msg);
        ConferenceUI.appendMessage(msg);
        if (ConferenceUI.sidePanel !== 'chat') {
            ConferenceUI.unreadCount++;
            ConferenceUI.updateUnreadBadge();
        }
    },

    appendMessage(msg) {
        const container = document.getElementById('chat-messages');
        if (!container) return;
        const time = msg.timestamp.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const initials = getInitials(msg.senderName);
        const div = document.createElement('div');
        div.className = `flex gap-2 mb-3 ${msg.isMe ? 'flex-row-reverse' : 'flex-row'}`;
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold shrink-0 self-end"
                 style="background:${msg.senderColor}">${initials}</div>
            <div class="max-w-[75%] flex flex-col gap-0.5 ${msg.isMe ? 'items-end' : 'items-start'}">
                ${!msg.isMe ? `<span class="text-xs text-gray-400 px-1">${msg.senderName}</span>` : ''}
                <div class="px-3 py-2 rounded-2xl text-sm text-white ${msg.isMe ? 'rounded-br-sm bg-blue-600' : 'rounded-bl-sm bg-[#3c4043]'}">${msg.message}</div>
                <span class="text-xs text-gray-500 px-1">${time}</span>
            </div>`;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    },

    updateUnreadBadge() {
        const badge = document.getElementById('chat-unread-badge');
        if (!badge) return;
        if (ConferenceUI.unreadCount > 0 && ConferenceUI.sidePanel !== 'chat') {
            badge.textContent = ConferenceUI.unreadCount > 9 ? '9+' : ConferenceUI.unreadCount;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    },

    // ---- Participants panel ----
    renderParticipants() {
        const list = document.getElementById('participants-list');
        if (!list) return;

        const all = Object.values(ConferenceUI.participants);
        const searchVal = (document.getElementById('participant-search')?.value || '').toLowerCase();
        const filtered = searchVal ? all.filter(p => p.name.toLowerCase().includes(searchVal)) : all;

        const hosts = filtered.filter(p => p.isHost);
        const guests = filtered.filter(p => !p.isHost);

        let html = '';
        if (hosts.length > 0) {
            html += `<p class="text-xs text-gray-500 px-2 py-1 uppercase tracking-wider">Host (${hosts.length})</p>`;
            hosts.forEach(p => { html += ConferenceUI.participantItemHtml(p); });
        }
        if (guests.length > 0) {
            html += `<p class="text-xs text-gray-500 px-2 py-1 uppercase tracking-wider mt-2">Peserta (${guests.length})</p>`;
            guests.forEach(p => { html += ConferenceUI.participantItemHtml(p); });
        }
        if (filtered.length === 0) {
            html = '<div class="text-center text-gray-500 text-sm py-8">Tidak ada peserta ditemukan</div>';
        }
        list.innerHTML = html;

        // Footer stats
        const total = all.length;
        const micActive = all.filter(p => !p.isMuted).length;
        const vidActive = all.filter(p => !p.isVideoOff).length;
        const hands = all.filter(p => p.isHandRaised).length;
        const footer = document.getElementById('participants-footer');
        if (footer) {
            footer.innerHTML = `
                <div class="text-center"><p class="text-on-surface font-bold">${total}</p><p class="text-on-surface-variant text-xs">Peserta</p></div>
                <div class="text-center"><p class="text-on-surface font-bold">${micActive}</p><p class="text-on-surface-variant text-xs">Mik aktif</p></div>
                <div class="text-center"><p class="text-on-surface font-bold">${vidActive}</p><p class="text-on-surface-variant text-xs">Video aktif</p></div>
                <div class="text-center"><p class="text-on-surface font-bold">${hands}</p><p class="text-on-surface-variant text-xs">Tangan</p></div>`;
        }
    },

    participantItemHtml(p) {
        const initials = getInitials(p.name);
        const speakingRing = p.isSpeaking ? `box-shadow:0 0 0 2px ${p.color}80;` : '';
        const speakingDot = p.isSpeaking ? '<span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-green-500 border-2 border-[#202124]"></span>' : '';
        const micIcon = p.isMuted
            ? `<svg class="w-3.5 h-3.5 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3 3 4.27l6.01 6.01V11c0 1.66 1.34 3 3 3 .23 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>`
            : `<svg class="w-3.5 h-3.5 ${p.isSpeaking ? 'text-green-400' : 'text-gray-400'}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>`;
        const vidIcon = p.isVideoOff
            ? `<svg class="w-3.5 h-3.5 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M21 6.5l-4-4-9.96 9.96-2.54-2.5L3 11.46 7.04 15.5 3 19.5l1.5 1.5 4-4 10.5 10.5 1.5-1.5L10.46 16l.54-.54L15 19.5l6-5.96V6.5zM15 9.34V15h-3.34L15 9.34zm-8 5.16V9h5.66L7 14.5z"/></svg>`
            : `<svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M15 8v8H5V8h10m1-2H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4V7c0-.55-.45-1-1-1z"/></svg>`;
        const hostIcon = p.isHost ? `<svg class="w-3 h-3 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm2.7-2h8.6l.9-4.8-2.9 2.9L12 8.4l-2.3 3.7-2.9-2.9.9 4.8z"/></svg>` : '';
        const handIcon = p.isHandRaised ? '<span class="text-xs">✋</span>' : '';
        return `
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-surface-container transition-colors">
                <div class="relative shrink-0">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-semibold"
                         style="background:${p.color};${speakingRing}">${initials}</div>
                    ${speakingDot}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        ${hostIcon}
                        <span class="text-on-surface text-sm truncate inline-block max-w-[120px] lg:max-w-[180px]">${p.name}${p.isMe ? '<span class="text-on-surface-variant ml-1">(Anda)</span>' : ''}</span>
                    </div>
                    ${p.isHandRaised ? '<p class="text-xs text-yellow-400">✋ Tangan terangkat</p>' : ''}
                </div>
                <div class="flex items-center gap-1 shrink-0">${micIcon}${vidIcon}</div>
            </div>`;
    },

    // ---- Raise Hand ----
    toggleHand() {
        ConferenceUI.handRaised = !ConferenceUI.handRaised;
        const btn = document.getElementById('btn-hand');
        if (ConferenceUI.handRaised) {
            btn?.classList.add('ctrl-raised');
            btn?.classList.remove('ctrl-action');
        } else {
            btn?.classList.remove('ctrl-raised');
            btn?.classList.add('ctrl-action');
        }
        // Update own tile indicator
        const handEl = document.getElementById('local-hand-indicator');
        if (handEl) handEl.style.display = ConferenceUI.handRaised ? 'flex' : 'none';

        // Broadcast to others
        if (room) {
            const payload = JSON.stringify({ type: 'hand', raised: ConferenceUI.handRaised });
            room.localParticipant.publishData(new TextEncoder().encode(payload), { reliable: true });
        }

        // Update own participant record
        const myId = room?.localParticipant?.identity;
        if (myId && ConferenceUI.participants[myId]) {
            ConferenceUI.participants[myId].isHandRaised = ConferenceUI.handRaised;
        }
        if (ConferenceUI.sidePanel === 'participants') ConferenceUI.renderParticipants();
    },

    // ---- Reactions ----
    sendReaction(emoji) {
        ConferenceUI.showFloatingReaction(emoji);
        if (room) {
            const payload = JSON.stringify({ type: 'reaction', emoji });
            room.localParticipant.publishData(new TextEncoder().encode(payload), { reliable: false });
        }
    },

    showFloatingReaction(emoji) {
        ConferenceUI.reactionCounter++;
        const id = 'reaction-' + ConferenceUI.reactionCounter;
        const el = document.createElement('div');
        el.id = id;
        el.className = 'reaction-float';
        el.textContent = emoji;
        document.getElementById('room-wrapper')?.appendChild(el);
        setTimeout(() => el.remove(), 2800);
    },

    // ---- View mode ----
    toggleViewMode() {
        ConferenceUI.viewMode = ConferenceUI.viewMode === 'grid' ? 'spotlight' : 'grid';
        const btn = document.getElementById('btn-viewmode');
        const label = document.getElementById('viewmode-label');
        if (ConferenceUI.viewMode === 'spotlight') {
            btn?.classList.add('ctrl-active');
            if (label) label.textContent = 'Grid';
        } else {
            btn?.classList.remove('ctrl-active');
            if (label) label.textContent = 'Spotlight';
        }
        ConferenceRoom.reflow();
    },

    // ---- Copy room link ----
    copyLink(roomId) {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const el = document.getElementById('copy-link-text');
            if (el) {
                el.textContent = 'Tersalin!';
                el.classList.add('text-green-400');
                setTimeout(() => {
                    el.textContent = 'Salin Tautan';
                    el.classList.remove('text-green-400');
                }, 2000);
            }
        });
    },

    // ---- Data received from other participants ----
    handleDataReceived(data, participant) {
        try {
            const decoded = new TextDecoder().decode(data);
            const msg = JSON.parse(decoded);
            if (msg.type === 'chat') {
                ConferenceUI.receiveMessage(participant.identity, msg.name || participant.identity, msg.message);
            } else if (msg.type === 'hand') {
                const p = ConferenceUI.participants[participant.identity];
                if (p) {
                    p.isHandRaised = msg.raised;
                    const handEl = document.getElementById('hand-ind-' + ConferenceRoom.safeId(participant.identity));
                    if (handEl) handEl.style.display = msg.raised ? 'flex' : 'none';
                }
                if (ConferenceUI.sidePanel === 'participants') ConferenceUI.renderParticipants();
            } else if (msg.type === 'reaction') {
                ConferenceUI.showFloatingReaction(msg.emoji);
            }
        } catch (e) {
            // Not a JSON data message
        }
    },
};

window.ConferenceUI = ConferenceUI;

// ---------------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------------
const Settings = {
    async open() {
        const modal = document.getElementById('settings-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        await Settings.loadDevices();
    },
    close() {
        document.getElementById('settings-modal')?.classList.add('hidden');
        if (typeof MicTest !== 'undefined' && MicTest.active) MicTest.stop();
        if (typeof CamTest !== 'undefined' && CamTest.active) CamTest.stop();
    },
    async loadDevices() {
        try {
            await navigator.mediaDevices.getUserMedia({ audio: true, video: true }).catch(() => {});
            const devices = await navigator.mediaDevices.enumerateDevices();
            Settings.populate('select-mic', devices.filter(d => d.kind === 'audioinput'));
            Settings.populate('select-speaker', devices.filter(d => d.kind === 'audiooutput'));
            Settings.populate('select-cam', devices.filter(d => d.kind === 'videoinput'));
        } catch (e) {
            console.warn('Device enumeration failed:', e);
        }
    },
    populate(selectId, devices) {
        const el = document.getElementById(selectId);
        if (!el) return;
        const current = el.value;
        el.innerHTML = devices.map(d =>
            `<option value="${d.deviceId}" ${d.deviceId === current ? 'selected' : ''}>${d.label || d.kind}</option>`
        ).join('');
    },
    switchTab(tab) {
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('tab-' + tab)?.classList.add('active');
        document.getElementById('panel-' + tab)?.classList.remove('hidden');
        if (tab === 'video' && typeof CamTest !== 'undefined') CamTest.start();
        else if (typeof CamTest !== 'undefined') CamTest.stop();
    },
    async applyMic() {
        const id = document.getElementById('select-mic')?.value;
        if (id && room) await room.switchActiveDevice('audioinput', id);
        if (typeof MicTest !== 'undefined' && MicTest.active) {
            MicTest.stop();
            MicTest.start();
        }
    },
    async applySpeaker() {
        const id = document.getElementById('select-speaker')?.value;
        if (id && room) await room.switchActiveDevice('audiooutput', id);
    },
    async applyCam() {
        const id = document.getElementById('select-cam')?.value;
        if (id && room) await room.switchActiveDevice('videoinput', id);
        if (typeof CamTest !== 'undefined' && CamTest.active) {
            CamTest.stop();
            CamTest.start();
        }
    },
    async applyAll() {
        await Promise.all([Settings.applyMic(), Settings.applySpeaker(), Settings.applyCam()]);
        Settings.close();
    },
};

window.Settings = Settings;

// ---------------------------------------------------------------------------
// MicTest — Web Audio API VU meter
// ---------------------------------------------------------------------------
const MicTest = {
    stream: null, audioCtx: null, analyser: null, animationId: null, active: false,

    async start() {
        if (MicTest.active) { MicTest.stop(); return; }
        const deviceId = document.getElementById('select-mic')?.value;
        try {
            MicTest.stream = await navigator.mediaDevices.getUserMedia({
                audio: deviceId ? { deviceId: { exact: deviceId } } : true,
                video: false,
            });
        } catch (e) {
            MicTest.setStatus('error', 'Tidak dapat mengakses mikrofon.'); return;
        }
        MicTest.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        MicTest.analyser = MicTest.audioCtx.createAnalyser();
        MicTest.analyser.fftSize = 256;
        MicTest.analyser.smoothingTimeConstant = 0.6;
        MicTest.audioCtx.createMediaStreamSource(MicTest.stream).connect(MicTest.analyser);
        MicTest.active = true;
        MicTest.setStatus('active', 'Mikrofon aktif — bicara untuk melihat level...');
        const btn = document.getElementById('btn-mic-test');
        if (btn) { btn.textContent = 'Hentikan Test'; btn.classList.add('btn-mic-test-active'); }
        MicTest.drawLoop();
    },

    stop() {
        MicTest.active = false;
        cancelAnimationFrame(MicTest.animationId);
        MicTest.stream?.getTracks().forEach(t => t.stop());
        MicTest.stream = null;
        MicTest.audioCtx?.close();
        MicTest.audioCtx = null; MicTest.analyser = null;
        MicTest.clearMeter();
        MicTest.setStatus('idle', 'Klik "Test Mikrofon" untuk mulai.');
        const btn = document.getElementById('btn-mic-test');
        if (btn) { btn.textContent = 'Test Mikrofon'; btn.classList.remove('btn-mic-test-active'); }
    },

    drawLoop() {
        if (!MicTest.active || !MicTest.analyser) return;
        const data = new Uint8Array(MicTest.analyser.frequencyBinCount);
        MicTest.analyser.getByteFrequencyData(data);
        MicTest.drawMeter(Math.min(100, (data.slice(0, 32).reduce((s, v) => s + v, 0) / 32 / 255) * 100 * 3.5));
        MicTest.animationId = requestAnimationFrame(MicTest.drawLoop);
    },

    drawMeter(pct) {
        const bars = document.querySelectorAll('.mic-bar');
        const total = bars.length;
        bars.forEach((bar, i) => {
            const threshold = ((i + 1) / total) * 100;
            bar.classList.toggle('active', pct >= threshold);
            if (pct >= threshold) {
                bar.style.background = pct > 80 ? '#ea4335' : pct > 50 ? '#fbbc04' : '#34a853';
            } else {
                bar.style.background = '';
            }
        });
    },

    clearMeter() {
        document.querySelectorAll('.mic-bar').forEach(b => { b.classList.remove('active'); b.style.background = ''; });
    },

    setStatus(type, msg) {
        const el = document.getElementById('mic-test-status');
        if (!el) return;
        el.textContent = msg;
        el.className = 'mic-test-status' + (type === 'active' ? ' status-active' : type === 'error' ? ' status-error' : '');
    },
};

window.MicTest = MicTest;

// ---------------------------------------------------------------------------
// CamTest — MediaDevices Video API Test
// ---------------------------------------------------------------------------
const CamTest = {
    stream: null,
    active: false,
    async start() {
        if (CamTest.active) { CamTest.stop(); return; }
        const deviceId = document.getElementById('select-cam')?.value;
        const videoEl = document.getElementById('cam-test-preview');
        if (!videoEl) return;
        try {
            CamTest.stream = await navigator.mediaDevices.getUserMedia({
                video: deviceId ? { deviceId: { exact: deviceId } } : true,
                audio: false,
            });
            videoEl.srcObject = CamTest.stream;
            CamTest.active = true;
        } catch (e) {
            console.warn('Cannot access camera for test:', e);
        }
    },
    stop() {
        CamTest.active = false;
        const videoEl = document.getElementById('cam-test-preview');
        if (videoEl) videoEl.srcObject = null;
        CamTest.stream?.getTracks().forEach(t => t.stop());
        CamTest.stream = null;
    }
};

window.CamTest = CamTest;

// ---------------------------------------------------------------------------
// ConferenceRoom — core LiveKit integration
// ---------------------------------------------------------------------------
window.ConferenceRoom = {
    async init(tokenUrl, livekitUrl, myName, isHost) {
        window.__myName = myName;
        window.__myColor = getAvatarColor(myName);
        window.__isHost = isHost;

        // Add self to participants record
        ConferenceUI.participants['__local__'] = {
            name: myName,
            color: window.__myColor,
            isMuted: false,
            isVideoOff: false,
            isHandRaised: false,
            isSpeaking: false,
            isHost: isHost,
            isMe: true,
        };

        // Start elapsed timer
        elapsedInterval = setInterval(() => {
            elapsedSeconds++;
            const el = document.getElementById('elapsed-timer');
            if (el) el.textContent = formatDuration(elapsedSeconds);
        }, 1000);

        try {
            const tokenRes = await fetch(tokenUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!tokenRes.ok) { ConferenceRoom.showError('Gagal mendapatkan token akses.'); return; }
            const { token } = await tokenRes.json();

            room = new Room({
                adaptiveStream: true,
                dynacast: true,
                videoCaptureDefaults: { resolution: VideoPresets.h720.resolution },
            });

            // Store identity reference
            window.__myIdentity = '__local__';

            ConferenceRoom.setupRoomEvents();

            await room.connect(livekitUrl, token);

            // Update local identity key after connection
            const identity = room.localParticipant.identity;
            window.__myIdentity = identity;
            if (ConferenceUI.participants['__local__']) {
                ConferenceUI.participants[identity] = { ...ConferenceUI.participants['__local__'] };
                delete ConferenceUI.participants['__local__'];
            }

            ConferenceRoom.setStatus('connected');
            await room.localParticipant.setCameraEnabled(true);
            await room.localParticipant.setMicrophoneEnabled(true);
            ConferenceRoom.updateParticipantCount();

        } catch (e) {
            ConferenceRoom.showError(e.message);
        }
    },

    setupRoomEvents() {
        room.on(RoomEvent.LocalTrackPublished, (publication) => {
            if (publication.track?.kind === Track.Kind.Video) {
                if (publication.source === Track.Source.ScreenShare) {
                    ConferenceRoom.showScreenShare(publication.track, true);
                } else {
                    ConferenceRoom.renderLocalVideo(publication.track);
                }
            }
        });

        room.on(RoomEvent.LocalTrackUnpublished, (publication) => {
            if (publication.source === Track.Source.ScreenShare) {
                ConferenceRoom.hideScreenShare();
                screenShareEnabled = false;
                const btn = document.getElementById('btn-screen');
                btn?.classList.remove('ctrl-active');
                btn?.classList.add('ctrl-action');
                document.getElementById('screenshare-status')?.classList.add('hidden');
            }
        });

        room.on(RoomEvent.ParticipantConnected, (participant) => {
            const color = getAvatarColor(participant.identity);
            ConferenceUI.participants[participant.identity] = {
                name: participant.identity,
                color,
                isMuted: false,
                isVideoOff: false,
                isHandRaised: false,
                isSpeaking: false,
                isHost: false,
                isMe: false,
            };
            ConferenceRoom.addTile(participant.identity, participant.identity, color);
            ConferenceRoom.updateParticipantCount();
            participant.on(RoomEvent.TrackSubscribed, (track) => {
                ConferenceRoom.attachRemoteTrack(track, participant.identity);
            });
        });

        room.on(RoomEvent.ParticipantDisconnected, (participant) => {
            delete ConferenceUI.participants[participant.identity];
            ConferenceRoom.removeTile(participant.identity);
            ConferenceRoom.updateParticipantCount();
            if (ConferenceUI.sidePanel === 'participants') ConferenceUI.renderParticipants();
        });

        room.on(RoomEvent.TrackSubscribed, (track, pub, participant) => {
            ConferenceRoom.attachRemoteTrack(track, participant.identity);
        });

        room.on(RoomEvent.TrackUnsubscribed, (track, pub, participant) => {
            track.detach().forEach(el => el.remove());
            const id = ConferenceRoom.safeId(participant.identity);
            if (pub.source === Track.Source.Camera) {
                const avatarEl = document.getElementById('avatar-' + id);
                avatarEl?.classList.remove('hidden');
                if (ConferenceUI.participants[participant.identity]) {
                    ConferenceUI.participants[participant.identity].isVideoOff = true;
                }
            }
        });

        room.on(RoomEvent.ActiveSpeakersChanged, (speakers) => {
            // Reset all
            Object.keys(ConferenceUI.participants).forEach(key => {
                ConferenceUI.participants[key].isSpeaking = false;
            });
            document.querySelectorAll('.participant-tile').forEach(t => t.classList.remove('speaking'));
            document.getElementById('local-pip')?.classList.remove('speaking');
            document.querySelectorAll('.participant-tile').forEach(t => t.classList.remove('speaker-focus'));

            if (speakers.length > 0) {
                const dominant = speakers[0];
                speakers.forEach(speaker => {
                    if (ConferenceUI.participants[speaker.identity]) {
                        ConferenceUI.participants[speaker.identity].isSpeaking = true;
                    }
                    if (speaker.isLocal) {
                        document.getElementById('local-pip')?.classList.add('speaking');
                    } else {
                        const tile = document.getElementById('tile-' + ConferenceRoom.safeId(speaker.identity));
                        tile?.classList.add('speaking');
                    }
                });

                // Dominant speaker gets focus
                if (!dominant.isLocal && ConferenceUI.viewMode === 'spotlight') {
                    const tile = document.getElementById('tile-' + ConferenceRoom.safeId(dominant.identity));
                    tile?.classList.add('speaker-focus');
                    ConferenceRoom.reflow();
                }
            }
            if (ConferenceUI.sidePanel === 'participants') ConferenceUI.renderParticipants();
        });

        room.on(RoomEvent.TrackMuted, (pub, participant) => {
            if (pub.kind === Track.Kind.Audio && ConferenceUI.participants[participant.identity]) {
                ConferenceUI.participants[participant.identity].isMuted = true;
                const id = ConferenceRoom.safeId(participant.identity);
                const ind = document.getElementById('mic-ind-' + id);
                if (ind) {
                    ind.classList.remove('mic-on');
                    ind.classList.add('mic-off');
                }
            }
        });

        room.on(RoomEvent.TrackUnmuted, (pub, participant) => {
            if (pub.kind === Track.Kind.Audio && ConferenceUI.participants[participant.identity]) {
                ConferenceUI.participants[participant.identity].isMuted = false;
                const id = ConferenceRoom.safeId(participant.identity);
                const ind = document.getElementById('mic-ind-' + id);
                if (ind) {
                    ind.classList.remove('mic-off');
                    ind.classList.add('mic-on');
                }
            }
        });

        room.on(RoomEvent.DataReceived, (data, participant) => {
            ConferenceUI.handleDataReceived(data, participant);
        });

        room.on(RoomEvent.Disconnected, () => {
            ConferenceRoom.setStatus('disconnected');
        });

        // Existing remote participants
        room.remoteParticipants.forEach((participant) => {
            const color = getAvatarColor(participant.identity);
            ConferenceUI.participants[participant.identity] = {
                name: participant.identity,
                color,
                isMuted: false,
                isVideoOff: false,
                isHandRaised: false,
                isSpeaking: false,
                isHost: false,
                isMe: false,
            };
            ConferenceRoom.addTile(participant.identity, participant.identity, color);
            participant.trackPublications.forEach(pub => {
                if (pub.track) ConferenceRoom.attachRemoteTrack(pub.track, participant.identity);
            });
            ConferenceRoom.updateParticipantCount();
        });
    },

    renderLocalVideo(track) {
        const pip = document.getElementById('local-pip');
        if (!pip) return;
        const existing = pip.querySelector('video');
        if (existing) existing.remove();
        const el = track.attach();
        el.style.cssText = 'width:100%;height:100%;object-fit:cover;transform:scaleX(-1);border-radius:12px;position:absolute;inset:0;';
        pip.appendChild(el);
        document.getElementById('local-avatar')?.classList.add('hidden');
    },

    addTile(identity, displayName, avatarColor) {
        const grid = document.getElementById('participants-grid');
        if (!grid || document.getElementById('tile-' + ConferenceRoom.safeId(identity))) return;

        const id = ConferenceRoom.safeId(identity);
        const color = avatarColor || getAvatarColor(identity);
        const initials = getInitials(displayName);

        const tile = document.createElement('div');
        tile.id = 'tile-' + id;
        tile.className = 'participant-tile';
        tile.style.cssText = `--tile-color:${color};`;
        tile.innerHTML = `
            <div class="tile-inner">
                <div id="video-${id}" class="tile-video"></div>
                <div class="tile-avatar" id="avatar-${id}" style="background:linear-gradient(145deg,${color}bb 0%,${color}44 100%)">
                    <div class="avatar-circle" style="background:${color}">${initials}</div>
                </div>
            </div>
            <div class="tile-name">${displayName}</div>
            <div class="tile-indicators">
                <div class="indicator mic-on" id="mic-ind-${id}">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                </div>
                <div class="hand-indicator hidden" id="hand-ind-${id}" style="display:none;align-items:center;justify-content:center;width:28px;height:28px;">✋</div>
            </div>`;
        grid.appendChild(tile);
        ConferenceRoom.reflow();
    },

    removeTile(identity) {
        document.getElementById('tile-' + ConferenceRoom.safeId(identity))?.remove();
        ConferenceRoom.reflow();
    },

    attachRemoteTrack(track, identity) {
        const id = ConferenceRoom.safeId(identity);

        if (track.source === Track.Source.ScreenShare) {
            ConferenceRoom.showScreenShare(track, false, identity);
            return;
        }

        if (track.kind === Track.Kind.Audio) {
            const el = track.attach();
            el.style.display = 'none';
            document.body.appendChild(el);
            return;
        }

        if (!document.getElementById('video-' + id)) {
            ConferenceRoom.addTile(identity, identity, getAvatarColor(identity));
        }
        const target = document.getElementById('video-' + id);
        if (!target) return;
        const el = track.attach();
        el.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:0;';
        target.appendChild(el);
        document.getElementById('avatar-' + id)?.classList.add('hidden');

        if (ConferenceUI.participants[identity]) {
            ConferenceUI.participants[identity].isVideoOff = false;
        }
    },

    showScreenShare(track, isLocal, sharerIdentity) {
        const view = document.getElementById('screenshare-view');
        const video = document.getElementById('screenshare-video');
        const label = document.getElementById('screenshare-label');
        if (!view || !video) return;
        video.innerHTML = '';
        const el = track.attach();
        el.style.cssText = 'width:100%;height:100%;object-fit:contain;border-radius:8px;';
        video.appendChild(el);
        if (label) label.textContent = isLocal ? 'Anda sedang berbagi layar' : ((sharerIdentity || '') + ' sedang berbagi layar');
        view.classList.remove('hidden');
        document.getElementById('video-section')?.classList.add('screenshare-active');
        document.getElementById('screenshare-status')?.classList.remove('hidden');
    },

    hideScreenShare() {
        const view = document.getElementById('screenshare-view');
        const video = document.getElementById('screenshare-video');
        if (video) {
            video.querySelectorAll('video').forEach(v => {
                v.srcObject?.getTracks?.().forEach(t => t.stop());
                v.remove();
            });
        }
        view?.classList.add('hidden');
        document.getElementById('video-section')?.classList.remove('screenshare-active');
        document.getElementById('screenshare-status')?.classList.add('hidden');
    },

    async toggleCamera() {
        if (!room) return;
        camEnabled = !camEnabled;
        await room.localParticipant.setCameraEnabled(camEnabled);

        const btn = document.getElementById('btn-cam');
        const icon = document.getElementById('icon-cam');
        const localAvatar = document.getElementById('local-avatar');

        if (camEnabled) {
            btn?.classList.remove('ctrl-off');
            btn?.classList.add('ctrl-active');
            if (icon) icon.textContent = ConferenceRoom.icons.cam;
            localAvatar?.classList.add('hidden');
            if (ConferenceUI.participants[window.__myIdentity]) {
                ConferenceUI.participants[window.__myIdentity].isVideoOff = false;
            }
        } else {
            btn?.classList.remove('ctrl-active');
            btn?.classList.add('ctrl-off');
            if (icon) icon.textContent = ConferenceRoom.icons.camOff;
            localAvatar?.classList.remove('hidden');
            if (ConferenceUI.participants[window.__myIdentity]) {
                ConferenceUI.participants[window.__myIdentity].isVideoOff = true;
            }
        }
    },

    async toggleMic() {
        if (!room) return;
        micEnabled = !micEnabled;
        await room.localParticipant.setMicrophoneEnabled(micEnabled);

        const btn = document.getElementById('btn-mic');
        const icon = document.getElementById('icon-mic');

        if (micEnabled) {
            btn?.classList.remove('ctrl-off');
            btn?.classList.add('ctrl-active');
            if (icon) icon.textContent = ConferenceRoom.icons.mic;
            if (ConferenceUI.participants[window.__myIdentity]) {
                ConferenceUI.participants[window.__myIdentity].isMuted = false;
            }
        } else {
            btn?.classList.remove('ctrl-active');
            btn?.classList.add('ctrl-off');
            if (icon) icon.textContent = ConferenceRoom.icons.micOff;
            if (ConferenceUI.participants[window.__myIdentity]) {
                ConferenceUI.participants[window.__myIdentity].isMuted = true;
            }
        }
    },

    async toggleScreenShare() {
        if (!room) return;
        try {
            screenShareEnabled = !screenShareEnabled;
            await room.localParticipant.setScreenShareEnabled(screenShareEnabled);
            const btn = document.getElementById('btn-screen');
            if (screenShareEnabled) {
                btn?.classList.add('ctrl-active');
                btn?.classList.remove('ctrl-action');
            } else {
                btn?.classList.remove('ctrl-active');
                btn?.classList.add('ctrl-action');
                ConferenceRoom.hideScreenShare();
            }
        } catch (e) {
            screenShareEnabled = false;
            console.warn('Screen share gagal:', e);
        }
    },

    async disconnect(redirectUrl) {
        if (elapsedInterval) clearInterval(elapsedInterval);
        if (room) { await room.disconnect(); room = null; }
        if (redirectUrl) window.location.href = redirectUrl;
    },

    reflow() {
        const grid = document.getElementById('participants-grid');
        const sidebar = document.getElementById('participants-sidebar');
        if (!grid) return;
        const count = grid.children.length;

        if (sidebar) {
            sidebar.classList.toggle('solo-mode', count === 0);
        }

        if (count === 0) return;

        const isScreenshare = document.getElementById('main-area')?.classList.contains('screenshare-active');
        const tiles = grid.querySelectorAll('.participant-tile');

        if (isScreenshare) {
            grid.style.gridTemplateColumns = '1fr';
            grid.style.gridTemplateRows = 'auto';
            tiles.forEach(t => { t.style.aspectRatio = '16/9'; t.style.height = 'auto'; });
            return;
        }

        // Spotlight mode: only show 1 tile prominently
        if (ConferenceUI.viewMode === 'spotlight' && count > 1) {
            const focusTile = ConferenceUI.pinnedId
                ? document.getElementById('tile-' + ConferenceRoom.safeId(ConferenceUI.pinnedId))
                : grid.querySelector('.speaker-focus') || grid.firstElementChild;

            tiles.forEach(t => {
                t.style.aspectRatio = 'unset';
                t.style.height = '100%';
                t.classList.remove('spotlight-featured', 'spotlight-secondary');
            });

            if (focusTile) {
                grid.style.gridTemplateColumns = 'repeat(4, 1fr)';
                grid.style.gridTemplateRows = 'repeat(4, 1fr)';
                focusTile.style.gridColumn = 'span 3';
                focusTile.style.gridRow = 'span 4';
            }
            return;
        }

        // Grid mode
        let cols = 1, rows = 1;
        const hasFocus = grid.querySelector('.speaker-focus') !== null;

        if (hasFocus) {
            if (count <= 3) { cols = 3; rows = 2; }
            else if (count <= 8) { cols = 4; rows = 3; }
            else { cols = 5; rows = 4; }
        } else {
            if (count === 1) { cols = 1; rows = 1; }
            else if (count === 2) { cols = 2; rows = 1; }
            else if (count <= 4) { cols = 2; rows = 2; }
            else if (count <= 6) { cols = 3; rows = 2; }
            else if (count <= 9) { cols = 3; rows = 3; }
            else if (count <= 12) { cols = 4; rows = 3; }
            else if (count <= 16) { cols = 4; rows = 4; }
            else { cols = 5; rows = Math.ceil(count / 5); }
        }

        grid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        grid.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
        tiles.forEach(t => { t.style.aspectRatio = 'unset'; t.style.height = '100%'; t.style.gridColumn = ''; t.style.gridRow = ''; });
    },

    updateParticipantCount() {
        const grid = document.getElementById('participants-grid');
        const count = (grid?.children.length || 0) + 1;
        const el = document.getElementById('participant-count');
        if (el) el.textContent = count + ' peserta';
        const badge = document.getElementById('participants-count-badge');
        if (badge) badge.textContent = count;
    },

    setStatus(status) {
        const el = document.getElementById('conn-status');
        if (!el) return;
        if (status === 'connected') {
            el.textContent = 'Terhubung';
            el.style.color = '#34d399';
        } else if (status === 'disconnected') {
            el.textContent = 'Terputus';
            el.style.color = '#f87171';
        }
    },

    showError(msg) {
        const el = document.getElementById('conn-status');
        if (el) { el.textContent = 'Gagal: ' + msg; el.style.color = '#f87171'; }
    },

    safeId(str) {
        return str.replace(/[^a-zA-Z0-9_-]/g, '_');
    },

    confirmEnd(e) {
        if (!confirm('Akhiri sesi untuk semua peserta?')) {
            e.preventDefault();
            return false;
        }
        // Before letting the form submit, clean up LiveKit connection
        if (room) {
            room.disconnect();
            room = null;
        }
        return true;
    },

    icons: {
        mic: 'mic',
        micOff: 'mic_off',
        cam: 'videocam',
        camOff: 'videocam_off',
    }
};
