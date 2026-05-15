// Jitsi (JaaS) embed client.
// Driven by window.JITSI_* globals injected by room blade views.

const REQUIRED = ['JITSI_DOMAIN', 'JITSI_APP_ID', 'JITSI_ROOM_NAME', 'JITSI_JWT', 'JITSI_RETURN_URL'];

function fail(message) {
    const mount = document.getElementById('jitsi-mount');
    if (mount) {
        mount.innerHTML = `<div style="color:#fff;padding:32px;font-family:system-ui;text-align:center;max-width:560px;margin:auto;">
            <h2 style="margin:0 0 12px;font-size:18px;">Tidak dapat memuat ruang konferensi</h2>
            <p style="opacity:.75;font-size:14px;line-height:1.5;">${message}</p>
        </div>`;
    }
    console.error('[jitsi]', message);
}

function ensureGlobals() {
    const missing = REQUIRED.filter((k) => !window[k]);
    if (missing.length) {
        fail(`Konfigurasi hilang: ${missing.join(', ')}`);
        return false;
    }
    if (typeof window.JitsiMeetExternalAPI !== 'function') {
        fail('Library JaaS gagal dimuat. Periksa koneksi internet atau JITSI_DOMAIN/JITSI_APP_ID.');
        return false;
    }
    return true;
}

function init() {
    if (!ensureGlobals()) return;

    const mount = document.getElementById('jitsi-mount');
    if (!mount) {
        fail('Element #jitsi-mount tidak ditemukan.');
        return;
    }

    const isModerator = Boolean(window.JITSI_IS_MODERATOR);
    const tenantRoom = `${window.JITSI_APP_ID}/${window.JITSI_ROOM_NAME}`;

    const api = new window.JitsiMeetExternalAPI(window.JITSI_DOMAIN, {
        roomName: tenantRoom,
        parentNode: mount,
        jwt: window.JITSI_JWT,
        userInfo: {
            displayName: window.JITSI_DISPLAY_NAME || 'User',
        },
        configOverwrite: {
            prejoinPageEnabled: false,
            disableDeepLinking: true,
            enableWelcomePage: false,
            enableClosePage: false,
            startWithAudioMuted: !isModerator,
            startWithVideoMuted: !isModerator,
        },
        interfaceConfigOverwrite: {
            MOBILE_APP_PROMO: false,
            SHOW_JITSI_WATERMARK: false,
            SHOW_WATERMARK_FOR_GUESTS: false,
        },
    });

    let leaving = false;
    const goBack = () => {
        if (leaving) return;
        leaving = true;
        window.location.href = window.JITSI_RETURN_URL;
    };

    api.addListener('readyToClose', goBack);
    api.addListener('videoConferenceLeft', goBack);

    // Mahasiswa "Keluar" button.
    const leaveBtn = document.querySelector('[data-leave-conference]');
    if (leaveBtn) {
        leaveBtn.addEventListener('click', () => {
            try { api.executeCommand('hangup'); } catch (_) { /* noop */ }
            setTimeout(goBack, 600);
        });
    }

    // Dosen / Admin "Akhiri Sesi" button. POSTs to JITSI_END_URL, then hangs up.
    const endBtn = document.querySelector('[data-end-conference]');
    if (endBtn && window.JITSI_END_URL) {
        endBtn.addEventListener('click', async () => {
            if (!window.confirm('Akhiri sesi ini untuk semua peserta?')) return;
            endBtn.disabled = true;
            try {
                await fetch(window.JITSI_END_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.JITSI_CSRF || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
            } catch (e) {
                console.error('[jitsi] end request failed', e);
            }
            try { api.executeCommand('endConference'); } catch (_) { /* noop */ }
            try { api.executeCommand('hangup'); } catch (_) { /* noop */ }
            setTimeout(goBack, 800);
        });
    }

    window.__jitsiApi = api;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
