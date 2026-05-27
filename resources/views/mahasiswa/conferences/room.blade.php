<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
     (function(){
      var t=localStorage.getItem('theme')||'system';
      var prefersDark=window.matchMedia('(prefers-color-scheme: dark)').matches;
      if(t==='dark'||(t==='system'&&prefersDark))document.documentElement.classList.add('dark');
     })();
    </script>
    <title>{{ $conference->title }} - Live Class</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap" onload="this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" onload="this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"></noscript>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        #room-wrapper { display: flex; flex-direction: column; height: 100vh; }
        .room-header { padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; background: var(--surface-container-lowest, #ffffff); border-bottom: 1px solid var(--outline-variant, #c3c6d7); flex-shrink: 0; gap: 16px; }
        .room-header h1 { font-family: 'Manrope', sans-serif; font-weight: 800; color: var(--primary, #004ac6); margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px; }
        .live-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--surface-container-low, #f3f3fe); border: 1px solid var(--outline-variant, #c3c6d7); font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--secondary, #006c49); }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--secondary, #006c49); animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 50% { opacity: 0.4; } }
        .leave-btn { padding: 10px 18px; background: var(--surface-container-high, #e7e7f3); color: var(--on-surface, #191b23); border: 1px solid var(--outline-variant, #c3c6d7); border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        .leave-btn:hover { background: var(--surface-variant, #e1e2ed); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        #jitsi-mount { flex: 1; min-height: 0; background: #0f111a; }
    </style>
</head>
<body class="bg-surface font-body text-on-surface">
<div id="room-wrapper">
    <header class="room-header">
        <div style="display:flex; align-items:center; gap:12px; min-width:0;">
            <h1>
                <span class="material-symbols-outlined">videocam</span>
                <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $conference->title }}</span>
            </h1>
            <span class="live-badge"><span class="live-dot"></span> Live</span>
        </div>
        <button type="button" data-leave-conference class="leave-btn">
            <span class="material-symbols-outlined">logout</span> Keluar
        </button>
    </header>
    <div id="jitsi-mount"></div>
</div>

<script>
    window.JITSI_DOMAIN       = @json(config('services.jitsi.domain'));
    window.JITSI_ROOM_NAME    = @json($conference->room_name);
    window.JITSI_JWT          = @json($jwt);
    window.JITSI_DISPLAY_NAME = @json(auth()->user()->name);
    window.JITSI_IS_MODERATOR = false;
    window.JITSI_RETURN_URL   = @json(route('mahasiswa.courses.show', $conference->course));
</script>
@vite('resources/js/conference-jitsi.js')

</body>
</html>
