<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $conference->title }} - Live Class</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/conference-room.js', 'resources/js/conference-meet-enhancements.js'])
    
    <style>
        :root {
            --primary: #004ac6;
            --on-primary: #ffffff;
            --surface: #191b23;
            --on-surface: #faf8ff;
            --surface-container: #2e3039;
            --outline-variant: #434655;
            --secondary: #006c49;
            --error: #ba1a1a;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: var(--surface); color: var(--on-surface); font-family: 'Inter', sans-serif; overflow: hidden; }
        h1, h2, h3, h4 { font-family: 'Manrope', sans-serif; }

        /* ---- Layout ---- */
        #room-wrapper { display: flex; flex-direction: column; height: 100vh; }
        
        /* Top Navigation */
        #topbar { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 16px 32px; 
            flex-shrink: 0; 
            background: rgba(25, 27, 35, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 50;
        }

        #main-area { flex: 1; display: flex; overflow: hidden; position: relative; padding: 16px; gap: 16px; min-height: 0; }
        
        /* Bottom Control Bar */
        #bottombar { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 20px 32px; 
            flex-shrink: 0; 
            background: rgba(25, 27, 35, 0.9);
            backdrop-filter: blur(24px);
            border-top: 1px solid rgba(255, 255, 255, 0.08); 
            z-index: 50;
        }

        /* ---- Topbar Elements ---- */
        .meeting-info { display: flex; flex-direction: column; gap: 2px; }
        .meeting-title { font-size: 14px; font-weight: 800; color: var(--on-surface); text-transform: uppercase; letter-spacing: 0.05em; font-style: ; }
        .meeting-meta { font-size: 11px; font-weight: 600; color: var(--outline-variant); text-transform: uppercase; letter-spacing: 0.1em; ; }

        .status-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; border-radius: 12px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; ; }
        .status-pill.screensharing { background: rgba(0, 74, 198, 0.2); border: 1px solid rgba(0, 74, 198, 0.3); color: #8ab4f8; }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; }
        .status-dot.active { background: #4edea3; box-shadow: 0 0 12px #4edea3; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.2); } 100% { opacity: 1; transform: scale(1); } }

        /* ---- Video Grid ---- */
        #participants-sidebar { flex: 1; display: flex; flex-direction: column; gap: 12px; min-width: 0; overflow: hidden; }
        #participants-grid { flex: 1; display: grid; grid-template-columns: repeat(1, 1fr); gap: 12px; align-content: center; justify-content: center; min-height: 0; }

        /* Participant Tile */
        .participant-tile { 
            position: relative; 
            background: var(--surface-container); 
            border-radius: 24px; 
            overflow: hidden; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .participant-tile.speaking { border: 2px solid var(--primary); box-shadow: 0 0 0 4px rgba(0, 74, 198, 0.2); }
        
        .tile-name { 
            position: absolute; 
            bottom: 12px; 
            left: 12px; 
            background: rgba(0, 0, 0, 0.4); 
            backdrop-filter: blur(12px); 
            padding: 4px 14px; 
            border-radius: 12px; 
            font-size: 11px; 
            font-weight: 800; 
            color: #fff; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            ;
        }

        .avatar-circle { 
            width: 80px; 
            height: 80px; 
            border-radius: 32px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 28px; 
            font-weight: 900; 
            color: #fff; 
            font-family: 'Manrope';
            font-style: ;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        /* ---- Local PiP ---- */
        #local-pip-wrapper { flex-shrink: 0; display: flex; flex-direction: column; width: 240px; }
        #local-pip { 
            position: relative; 
            background: var(--surface-container); 
            border-radius: 24px; 
            overflow: hidden; 
            aspect-ratio: 16/9; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            width: 100%;
            border: 1px solid rgba(255,255,255,0.1);
        }
        #local-pip video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }

        /* ---- Controls ---- */
        .ctrl-btn { 
            width: 56px; 
            height: 56px; 
            border-radius: 20px; 
            border: none; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: rgba(255, 255, 255, 0.05);
            color: var(--on-surface);
        }
        .ctrl-btn:hover { background: rgba(255, 255, 255, 0.1); transform: translateY(-4px); }
        .ctrl-btn.ctrl-off { background: var(--error); color: #fff; }
        .ctrl-btn.ctrl-active { background: var(--primary); color: #fff; }
        
        .ctrl-end { 
            padding: 0 28px; 
            height: 56px; 
            border-radius: 20px; 
            background: var(--error); 
            color: #fff; 
            border: none; 
            cursor: pointer; 
            font-size: 12px; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: 0.15em;
            ;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .ctrl-end:hover { background: #d01d1d; transform: translateY(-4px); box-shadow: 0 12px 24px rgba(186, 26, 26, 0.3); }

        /* ---- Side Panel ---- */
        #side-panel-wrapper { 
            width: 380px; 
            flex-shrink: 0; 
            background: rgba(25, 27, 35, 0.6); 
            backdrop-filter: blur(40px);
            border-left: 1px solid rgba(255,255,255,0.05); 
            border-radius: 32px 0 0 32px;
            margin: -16px -16px -16px 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideInRight 0.5s cubic-bezier(0.22, 1, 0.36, 1);
        }
        #side-panel-wrapper.panel-hidden { display: none !important; }
        @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }

        .panel-header { padding: 32px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: space-between; }
        .panel-header span { font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; ; }

        .chat-input-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; padding: 8px 16px; margin: 16px; display: flex; gap: 8px; align-items: center; }
        #chat-input { background: transparent; border: none; color: #fff; font-size: 12px; flex: 1; outline: none; ; padding: 8px 0; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.1); }

        .ctrl-btn:active, .ctrl-end:active { transform: scale(0.95) !important; }

        .mic-test-container { display: flex; gap: 4px; height: 24px; align-items: center; justify-content: center; margin-top: 12px; }
        .mic-bar { width: 4px; height: 4px; background: var(--outline-variant); border-radius: 2px; transition: height 0.1s ease, background 0.1s ease; }
        .mic-bar.active { background: #4edea3; }
        .mic-bar.warning { background: #fbbc04; }
        .mic-bar.danger { background: var(--error); }
        .mic-test-status { text-align: center; font-size: 11px; margin-top: 8px; color: var(--outline-variant); font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }
    </style>
</head>
<body>
<div id="room-wrapper">

    <!-- Top Bar -->
    <header id="topbar">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white ">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">videocam</span>
                </div>
                <div class="meeting-info">
                    <h2 class="meeting-title ">{{ $conference->title }}</h2>
                    <p class="meeting-meta ">{{ $conference->course->nama_matkul }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div id="screenshare-status" class="status-pill screensharing hidden ">
                <span class="status-dot active"></span>
                ACTIVE BROADCAST
            </div>
            
            <div class="flex flex-col items-end">
                <div id="elapsed-timer" class="font-mono text-lg font-black tracking-tighter text-primary">00:00</div>
                <div id="participant-count" class="text-[9px] font-black uppercase tracking-widest text-outline-variant ">1 Peserta Terkoneksi</div>
            </div>
            
            <div id="conn-status" class="px-3 py-1 bg-surface-container rounded-lg text-[9px] font-black uppercase tracking-widest text-[#fbbc04] ">Menghubungkan...</div>
        </div>
    </header>

    <!-- Main Live Area -->
    <main id="main-area">
        <div id="video-section" style="flex:1;display:flex;overflow:hidden;min-width:0;gap:16px;">
            <div id="screenshare-view" class="hidden">
                <div id="screenshare-video"></div>
            </div>

            <div id="participants-sidebar" class="solo-mode">
                <div id="participants-grid"></div>
                <div id="local-pip-wrapper">
                    <div id="local-pip">
                        <div id="local-avatar">
                            <div class="avatar-circle" style="background:{{ '#' . substr(md5(auth()->user()->name), 0, 6) }}">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="local-name ">{{ auth()->user()->name }} (Anda)</div>
                        <div class="local-hand" id="local-hand-indicator">✋</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Panels (Chat / Participants) -->
        <aside id="side-panel-wrapper" class="panel-hidden">
            <!-- Chat Panel -->
            <div id="panel-chat" class="panel-hidden h-full flex flex-col">
                <div class="panel-header">
                    <span class="">Live Discussion</span>
                    <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/5 transition-colors" onclick="ConferenceUI.closePanel()">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div id="chat-messages" class="flex-1 overflow-y-auto p-8 space-y-6">
                    <p class="text-[10px] font-bold text-center text-outline-variant uppercase tracking-widest bg-white/5 py-4 rounded-2xl">Pesan hanya terlihat oleh peserta di ruangan ini</p>
                </div>
                <div class="chat-input-box">
                    <textarea id="chat-input" rows="1" placeholder="Type a message..."></textarea>
                    <button class="w-8 h-8 flex items-center justify-center text-outline-variant hover:text-white transition-colors" id="btn-emoji" onclick="document.getElementById('emoji-picker').classList.toggle('hidden')">😊</button>
                    <button class="w-8 h-8 bg-primary rounded-xl flex items-center justify-center text-white disabled:opacity-50 transition-all" id="btn-chat-send" disabled>
                        <span class="material-symbols-outlined text-sm">send</span>
                    </button>
                    <div id="emoji-picker" class="hidden absolute bottom-16 right-4 bg-surface-container p-4 rounded-2xl grid grid-cols-5 gap-2 border border-white/5 shadow-2xl">
                        @foreach(['👍','❤️','😂','😮','👏','🎉','🔥','💯','✅','🙏'] as $emoji)
                            <button class="w-8 h-8 flex items-center justify-center hover:bg-white/5 rounded-lg text-lg" onclick="ChatHelper.addEmoji('{{ $emoji }}')">{{ $emoji }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Participants Panel -->
            <div id="panel-participants" class="panel-hidden h-full flex flex-col">
                <div class="panel-header">
                    <span class="">Peserta Aktif (<span id="panel-participant-total">1</span>)</span>
                    <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/5 transition-colors" onclick="ConferenceUI.closePanel()">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="p-6">
                    <div class="bg-white/5 rounded-2xl flex items-center gap-3 px-4 py-3 border border-white/5">
                        <span class="material-symbols-outlined text-outline-variant text-[20px]">search</span>
                        <input type="text" id="participant-search" class="bg-transparent border-none text-xs text-white outline-none flex-1 " placeholder="Cari teman..." oninput="ConferenceUI.renderParticipants()">
                    </div>
                </div>
                <div id="participants-list" class="flex-1 overflow-y-auto px-6 pb-6"></div>
            </div>
        </aside>
    </main>

    <!-- Bottom Controls -->
    <footer id="bottombar">
        <div class="flex items-center gap-6 min-w-[240px]">
            <div id="elapsed-timer-sm" class="hidden font-mono text-lg font-black tracking-tighter text-primary">00:00</div>
        </div>

        <div class="flex items-center gap-3">
            <button id="btn-mic" class="ctrl-btn ctrl-active" onclick="ConferenceRoom.toggleMic()" title="Toggle Mikrofon">
                <span class="material-symbols-outlined" id="icon-mic">mic</span>
            </button>
            <button id="btn-cam" class="ctrl-btn ctrl-active" onclick="ConferenceRoom.toggleCamera()" title="Toggle Kamera">
                <span class="material-symbols-outlined" id="icon-cam">videocam</span>
            </button>
            <button id="btn-screen" class="ctrl-btn" onclick="ConferenceRoom.toggleScreenShare()" title="Bagikan Layar">
                <span class="material-symbols-outlined">present_to_all</span>
            </button>
            <button id="btn-hand" class="ctrl-btn" onclick="ConferenceUI.toggleHand()" title="Raise Hand">
                <span class="material-symbols-outlined">front_hand</span>
            </button>
            
            <div class="relative">
                <button class="ctrl-btn" onclick="document.getElementById('reactions-popup').classList.toggle('hidden')" title="Reactions">
                    <span class="material-symbols-outlined">add_reaction</span>
                </button>
                <div id="reactions-popup" class="hidden absolute bottom-20 left-1/2 -translate-x-1/2 bg-surface-container p-3 rounded-2xl flex gap-2 border border-white/5 shadow-2xl">
                    @foreach(['👍','❤️','😂','😮','👏','🎉'] as $emoji)
                        <button class="w-10 h-10 flex items-center justify-center hover:bg-white/10 rounded-xl text-xl transition-transform hover:scale-125" onclick="ConferenceUI.sendReaction('{{ $emoji }}');document.getElementById('reactions-popup').classList.add('hidden');">{{ $emoji }}</button>
                    @endforeach
                </div>
            </div>

            <div class="w-px h-10 bg-white/10 mx-2"></div>

            <button class="ctrl-end" onclick="ConferenceRoom.disconnect('{{ route('mahasiswa.courses.show', $conference->course) }}')">
                <span class="material-symbols-outlined text-[20px]">call_end</span>
                Keluar Sesi
            </button>
        </div>

        <div class="flex items-center justify-end gap-3 min-w-[240px]">
             <button id="btn-chat" class="ctrl-btn" onclick="ConferenceUI.togglePanel('chat')" title="Chat">
                <span class="material-symbols-outlined">chat_bubble</span>
                <span id="chat-unread-badge" class="hidden absolute top-3 right-3 w-3 h-3 bg-error rounded-full border-2 border-surface"></span>
            </button>
            <button id="btn-participants" class="ctrl-btn" onclick="ConferenceUI.togglePanel('participants')" title="Participants">
                <span class="material-symbols-outlined">group</span>
                <span id="participants-count-badge" class="absolute top-3 right-3 w-4 h-4 bg-primary rounded-full text-[8px] font-black flex items-center justify-center border-2 border-surface">1</span>
            </button>
            <button class="ctrl-btn" onclick="Settings.open()" title="Settings">
                <span class="material-symbols-outlined">settings</span>
            </button>
        </div>
    </footer>
</div>

<!-- LiveKit Core Logic Interface -->
<script>
    const ChatHelper = {
        addEmoji(emoji) {
            const input = document.getElementById('chat-input');
            if (input) {
                input.value += emoji;
                input.dispatchEvent(new Event('input'));
            }
            document.getElementById('emoji-picker').classList.add('hidden');
            input?.focus();
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('btn-chat-send');

        chatInput?.addEventListener('input', function() {
            if (sendBtn) sendBtn.disabled = !this.value.trim();
        });

        chatInput?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim()) {
                    ConferenceUI.sendMessage(this.value);
                    this.value = '';
                    if (sendBtn) sendBtn.disabled = true;
                }
            }
        });

        sendBtn?.addEventListener('click', function() {
            const val = chatInput?.value;
            if (val?.trim()) {
                ConferenceUI.sendMessage(val);
                chatInput.value = '';
                sendBtn.disabled = true;
            }
        });

        // Close popups on click outside
        document.addEventListener('click', function(e) {
            ['reactions-popup', 'emoji-picker'].forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.classList.contains('hidden') && !e.target.closest(`#${id}`) && !e.target.closest(`[onclick*="${id}"]`)) {
                    el.classList.add('hidden');
                }
            });
        });

        // Initialize LiveKit
        ConferenceRoom.init(
            '{{ route('mahasiswa.conferences.token', $conference) }}',
            '{{ config('services.livekit.url') }}',
            '{{ auth()->user()->name }}',
            false
        );
    });
</script>

{{-- Settings Modal (Glassmorphism Overhaul) --}}
<div id="settings-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-xl">
    <div class="bg-surface-container-lowest w-[520px] max-w-[95vw] rounded-[3rem] border border-white/5 overflow-hidden shadow-2xl flex flex-col ">
        <div class="p-8 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-xl font-black text-white uppercase tracking-tighter">Konfigurasi Perangkat</h2>
            <button onclick="Settings.close()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/5 transition-colors">
                <span class="material-symbols-outlined text-outline-variant">close</span>
            </button>
        </div>
        
        <div class="flex border-b border-white/5">
            <button id="tab-audio" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 border-primary text-primary" onclick="Settings.switchTab('audio')">AUDIO ENGINE</button>
            <button id="tab-video" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 border-transparent text-outline-variant" onclick="Settings.switchTab('video')">VIDEO ENGINE</button>
        </div>

        <div class="p-10 space-y-8 flex-1 min-h-[300px]">
            <!-- Audio Panel -->
            <div id="panel-audio" class="space-y-6">
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-outline-variant opacity-60">Input Mikrofon</label>
                    <div class="flex gap-2">
                        <select id="select-mic" class="flex-1 bg-white/5 border border-white/5 rounded-2xl p-4 text-sm text-white outline-none focus:border-primary transition-all appearance-none" onchange="Settings.applyMic()"></select>
                        <button id="btn-mic-test" onclick="MicTest.start()" class="px-6 bg-white/5 hover:bg-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white border border-white/5 transition-colors whitespace-nowrap active:scale-95">Test Mikrofon</button>
                    </div>
                    <div class="mic-test-container">
                        @for($i=0; $i<20; $i++) <div class="mic-bar"></div> @endfor
                    </div>
                    <div id="mic-test-status" class="mic-test-status">Klik "Test Mikrofon" untuk mulai.</div>
                </div>
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-outline-variant opacity-60">Output Speaker</label>
                    <select id="select-speaker" class="w-full bg-white/5 border border-white/5 rounded-2xl p-4 text-sm text-white outline-none focus:border-primary transition-all appearance-none" onchange="Settings.applySpeaker()"></select>
                </div>
            </div>
            
            <!-- Video Panel -->
            <div id="panel-video" class="hidden space-y-6">
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-outline-variant opacity-60">Sensor Kamera</label>
                    <select id="select-cam" class="w-full bg-white/5 border border-white/5 rounded-2xl p-4 text-sm text-white outline-none focus:border-primary transition-all appearance-none" onchange="Settings.applyCam()"></select>
                    
                    <div class="aspect-video w-full bg-black/50 rounded-2xl overflow-hidden shadow-inner border border-white/5 flex items-center justify-center relative mt-4">
                        <video id="cam-test-preview" autoplay playsinline muted class="w-full h-full object-cover transform scale-x-[-1]"></video>
                        <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/80 to-transparent flex justify-center">
                            <span class="text-[10px] font-black tracking-widest text-white/50 uppercase">Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8 border-t border-white/5 flex justify-end gap-4">
            <button class="px-8 py-3 bg-white/5 text-white rounded-xl text-[10px] font-black uppercase tracking-widest " onclick="Settings.close()">BATAL</button>
            <button class="px-8 py-3 bg-primary text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/20" onclick="Settings.applyAll()">SIMPAN PERUBAHAN</button>
        </div>
    </div>
</div>
</body>
</html>
