const safeEscape = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

function patchPinning(ConferenceUI, ConferenceRoom) {
    ConferenceUI.togglePinByEncoded = function togglePinByEncoded(encodedIdentity) {
        ConferenceUI.togglePin(decodeURIComponent(encodedIdentity));
    };

    ConferenceUI.togglePin = function togglePin(identity) {
        if (!identity || identity === window.__myIdentity) return;

        ConferenceUI.pinnedId = ConferenceUI.pinnedId === identity ? null : identity;
        if (ConferenceUI.pinnedId) {
            ConferenceUI.viewMode = 'spotlight';
        }

        const viewBtn = document.getElementById('btn-viewmode');
        viewBtn?.classList.toggle('ctrl-active', ConferenceUI.viewMode === 'spotlight');

        ConferenceRoom.reflow();
        if (ConferenceUI.sidePanel === 'participants') {
            ConferenceUI.renderParticipants();
        }
    };
}

function patchControls(ConferenceUI) {
    ConferenceUI.toggleFullscreen = function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
            return;
        }
        document.exitFullscreen?.();
    };

    const originalCopyLink = ConferenceUI.copyLink?.bind(ConferenceUI);
    ConferenceUI.copyLink = function copyLink() {
        const labels = document.querySelectorAll('[id="copy-link-text"]');
        const showCopiedState = () => {
            labels.forEach((el) => {
                el.textContent = 'Tersalin!';
                el.classList.add('text-green-400');
            });
            setTimeout(() => {
                labels.forEach((el) => {
                    el.textContent = 'Salin Tautan';
                    el.classList.remove('text-green-400');
                });
            }, 2000);
        };

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(window.location.href).then(showCopiedState).catch(() => {
                originalCopyLink?.();
            });
            return;
        }

        originalCopyLink?.();
    };
}

function patchParticipantsPanel(ConferenceUI) {
    ConferenceUI.renderParticipants = function renderParticipants() {
        const list = document.getElementById('participants-list');
        if (!list) return;

        const all = Object.entries(ConferenceUI.participants).map(([identity, participant]) => ({
            identity,
            ...participant,
        }));

        const searchVal = (document.getElementById('participant-search')?.value || '').toLowerCase();

        const ordered = all
            .sort((a, b) => {
                if (a.isHost !== b.isHost) return a.isHost ? -1 : 1;
                if (a.isHandRaised !== b.isHandRaised) return a.isHandRaised ? -1 : 1;
                if (a.isSpeaking !== b.isSpeaking) return a.isSpeaking ? -1 : 1;
                return (a.name || '').localeCompare((b.name || ''), 'id');
            })
            .filter((p) => (searchVal ? (p.name || '').toLowerCase().includes(searchVal) : true));

        const hosts = ordered.filter((p) => p.isHost);
        const guests = ordered.filter((p) => !p.isHost);

        const row = (p) => {
            const speakingRing = p.isSpeaking ? `box-shadow:0 0 0 2px ${p.color}80;` : '';
            const speakingDot = p.isSpeaking
                ? '<span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-green-500 border-2 border-[#202124]"></span>'
                : '';
            const pinButton = !p.isMe
                ? `<button type="button" data-pin-id="${encodeURIComponent(p.identity)}" class="meet-pin-btn text-[11px] px-2 py-1 rounded-md border border-outline-variant/50 text-on-surface-variant hover:bg-surface-container transition">${ConferenceUI.pinnedId === p.identity ? 'Lepas Sematkan' : 'Sematkan'}</button>`
                : '';

            return `
                <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-surface-container transition-colors">
                    <div class="relative shrink-0">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-semibold" style="background:${p.color};${speakingRing}">${safeEscape((p.name || '').split(' ').slice(0, 2).map((n) => n[0]?.toUpperCase() || '').join(''))}</div>
                        ${speakingDot}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            ${p.isHost ? '<svg class="w-3 h-3 text-yellow-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm2.7-2h8.6l.9-4.8-2.9 2.9L12 8.4l-2.3 3.7-2.9-2.9.9 4.8z"/></svg>' : ''}
                            <span class="text-on-surface text-sm truncate max-w-[120px] lg:max-w-[180px]">${safeEscape(p.name || '')}${p.isMe ? '<span class="text-on-surface-variant ml-1">(Anda)</span>' : ''}</span>
                        </div>
                        ${p.isHandRaised ? '<p class="text-xs text-yellow-600">✋ Tangan terangkat</p>' : ''}
                    </div>
                    ${pinButton}
                </div>`;
        };

        let html = '';
        if (hosts.length) {
            html += `<p class="text-xs text-on-surface-variant px-2 py-1 uppercase tracking-wider">Host (${hosts.length})</p>`;
            hosts.forEach((p) => { html += row(p); });
        }
        if (guests.length) {
            html += `<p class="text-xs text-on-surface-variant px-2 py-1 uppercase tracking-wider mt-2">Peserta (${guests.length})</p>`;
            guests.forEach((p) => { html += row(p); });
        }
        if (!ordered.length) {
            html = '<div class="text-center text-on-surface-variant text-sm py-8">Tidak ada peserta ditemukan</div>';
        }

        list.innerHTML = html;

        list.querySelectorAll('.meet-pin-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                ConferenceUI.togglePinByEncoded(btn.dataset.pinId || '');
            });
        });

        ConferenceUI.renderParticipantsFooter(all);
    };
}

function patchRoomInit(ConferenceUI, ConferenceRoom) {
    const originalInit = ConferenceRoom.init.bind(ConferenceRoom);
    ConferenceRoom.init = async function initWithShortcuts(...args) {
        await originalInit(...args);
        ConferenceRoom.registerShortcuts?.();
    };

    ConferenceRoom.registerShortcuts = function registerShortcuts() {
        if (window.__conferenceShortcutsBound) return;
        window.__conferenceShortcutsBound = true;

        document.addEventListener('keydown', (event) => {
            const tag = event.target?.tagName?.toLowerCase();
            if (tag === 'input' || tag === 'textarea') return;

            if (event.ctrlKey && event.key.toLowerCase() === 'd') {
                event.preventDefault();
                ConferenceRoom.toggleMic();
                return;
            }
            if (event.ctrlKey && event.key.toLowerCase() === 'e') {
                event.preventDefault();
                ConferenceRoom.toggleCamera();
                return;
            }
            if (event.key.toLowerCase() === 'c') {
                event.preventDefault();
                ConferenceUI.togglePanel('chat');
                return;
            }
            if (event.key.toLowerCase() === 'f') {
                event.preventDefault();
                ConferenceUI.toggleFullscreen();
            }
        });
    };
}

function patchTilesAndLayout(ConferenceUI, ConferenceRoom) {
    const originalAddTile = ConferenceRoom.addTile.bind(ConferenceRoom);
    ConferenceRoom.addTile = function addTileWithPin(identity, displayName, avatarColor) {
        originalAddTile(identity, displayName, avatarColor);

        const tile = document.getElementById(`tile-${ConferenceRoom.safeId(identity)}`);
        if (!tile || tile.dataset.meetEnhanced === '1') return;

        tile.dataset.meetEnhanced = '1';
        tile.addEventListener('dblclick', () => ConferenceUI.togglePin(identity));

        const indicators = tile.querySelector('.tile-indicators');
        if (indicators && !tile.querySelector('.meet-pin-indicator')) {
            const pinBtn = document.createElement('button');
            pinBtn.type = 'button';
            pinBtn.className = 'indicator meet-pin-indicator';
            pinBtn.title = 'Sematkan';
            pinBtn.style.background = 'rgba(0,0,0,0.45)';
            pinBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 9V4l1-1V2H7v1l1 1v5l-2 2v1h5v7l1-1 1-6h5v-1z"/></svg>';
            pinBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                ConferenceUI.togglePin(identity);
            });
            indicators.prepend(pinBtn);
        }
    };

    const originalRemoveTile = ConferenceRoom.removeTile.bind(ConferenceRoom);
    ConferenceRoom.removeTile = function removeTileWithPinCleanup(identity) {
        if (ConferenceUI.pinnedId === identity) {
            ConferenceUI.pinnedId = null;
        }
        originalRemoveTile(identity);
    };

    const originalReflow = ConferenceRoom.reflow.bind(ConferenceRoom);
    ConferenceRoom.reflow = function reflowWithMeetRules() {
        const grid = document.getElementById('participants-grid');
        if (!grid) return;

        const videoSection = document.getElementById('video-section');
        const mainArea = document.getElementById('main-area');
        if (videoSection?.classList.contains('screenshare-active')) {
            mainArea?.classList.add('screenshare-active');
        } else {
            mainArea?.classList.remove('screenshare-active');
        }

        originalReflow();
    };

    const originalUpdateParticipantCount = ConferenceRoom.updateParticipantCount.bind(ConferenceRoom);
    ConferenceRoom.updateParticipantCount = function updateParticipantCountWithPanel() {
        originalUpdateParticipantCount();

        const badge = document.getElementById('participants-count-badge');
        const panelTotal = document.getElementById('panel-participant-total');
        if (badge && panelTotal) {
            panelTotal.textContent = badge.textContent || '1';
        }
    };
}

function applyMeetEnhancements() {
    const ConferenceUI = window.ConferenceUI;
    const ConferenceRoom = window.ConferenceRoom;
    if (!ConferenceUI || !ConferenceRoom) return;

    patchPinning(ConferenceUI, ConferenceRoom);
    patchControls(ConferenceUI);
    patchParticipantsPanel(ConferenceUI);
    patchRoomInit(ConferenceUI, ConferenceRoom);
    patchTilesAndLayout(ConferenceUI, ConferenceRoom);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyMeetEnhancements);
} else {
    applyMeetEnhancements();
}
