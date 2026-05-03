document.addEventListener('DOMContentLoaded', () => {
    if (typeof JitsiMeetExternalAPI === 'undefined') {
        console.error('JitsiMeetExternalAPI not loaded');
        return;
    }

    const mount = document.getElementById('jitsi-mount');
    if (!mount) return;

    const domain = window.JITSI_DOMAIN || '8x8.vc';
    const isModerator = !!window.JITSI_IS_MODERATOR;
    const appId = window.JITSI_APP_ID;
    const fullRoomName = appId ? `${appId}/${window.JITSI_ROOM_NAME}` : window.JITSI_ROOM_NAME;

    const api = new JitsiMeetExternalAPI(domain, {
        roomName: fullRoomName,
        jwt: window.JITSI_JWT,
        parentNode: mount,
        width: '100%',
        height: '100%',
        userInfo: { displayName: window.JITSI_DISPLAY_NAME },
        configOverwrite: {
            prejoinPageEnabled: false,
            startWithAudioMuted: !isModerator,
            startWithVideoMuted: !isModerator,
            disableDeepLinking: true,
        },
        interfaceConfigOverwrite: {
            MOBILE_APP_PROMO: false,
            SHOW_JITSI_WATERMARK: false,
            SHOW_BRAND_WATERMARK: false,
        },
    });

    window.__jitsiApi = api;

    api.addListener('readyToClose', async () => {
        if (isModerator && window.JITSI_END_URL) {
            try {
                await fetch(window.JITSI_END_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.JITSI_CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
            } catch (e) { /* best effort */ }
        }
        if (window.JITSI_RETURN_URL) {
            window.location.href = window.JITSI_RETURN_URL;
        }
    });

    document.querySelectorAll('[data-end-conference]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!confirm('Akhiri sesi live ini untuk semua mahasiswa?')) return;
            api.executeCommand('hangup');
        });
    });

    document.querySelectorAll('[data-leave-conference]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            api.executeCommand('hangup');
        });
    });

    window.addEventListener('beforeunload', () => api.dispose());
});
