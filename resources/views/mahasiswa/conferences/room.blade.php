<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $conference->title }} - Live Class</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/conference-room.js', 'resources/js/conference-meet-enhancements.js'])
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        /* Layout */
        #room-wrapper { display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        #main-area { flex: 1; display: flex; overflow: hidden; position: relative; gap: 16px; padding: 16px; min-height: 0; }
        
        /* Video Area */
        #video-section { flex: 1; display: flex; flex-direction: column; min-width: 0; background: var(--surface-container-lowest, #ffffff); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--outline-variant, #c3c6d7); overflow: hidden; position: relative;}
        
        #participants-sidebar { flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 16px; }
        
        /* Solo Mode */
        #participants-sidebar.solo-mode { align-items: center; justify-content: center; }
        #participants-sidebar.solo-mode #participants-grid { display: none; }
        #participants-sidebar.solo-mode #local-pip-wrapper { width: 100%; max-width: 960px; height: auto; max-height: calc(100vh - 180px); flex-shrink: 0; margin: 0; display: flex; align-items: center; justify-content: center; aspect-ratio: 16/9; }
        #participants-sidebar.solo-mode #local-pip { height: 100%; width: 100%; aspect-ratio: 16/9; }
        
        /* Grid */
        #participants-grid { flex: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; align-content: center; justify-content: center; min-height: 0; }
        
        /* Tile */
        .participant-tile, #local-pip { position: relative; background: var(--surface-container-high, #e7e7f3); border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; outline: 3px solid transparent; transition: all 0.2s; aspect-ratio: 16/9; }
        .participant-tile.speaking, #local-pip.speaking { outline: 3px solid var(--secondary, #006c49); box-shadow: 0 0 0 6px rgba(0, 108, 73, 0.15); }
        .participant-tile.speaker-focus { grid-column: span 2; grid-row: span 2; z-index: 2; }
        
        .tile-video, #local-pip video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        #local-pip video { transform: scaleX(-1); }
        
        .avatar-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: #fff; font-family: 'Manrope', sans-serif;}
        
        .tile-name, .local-name { position: absolute; bottom: 12px; left: 12px; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); padding: 4px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #fff; max-width: calc(100% - 70px); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .tile-indicators { position: absolute; bottom: 12px; right: 12px; display: flex; gap: 6px; }
        .indicator { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); color: #fff; }
        .mic-off { background: var(--error, #ba1a1a); }
        .hand-indicator { color: var(--tertiary, #3e3fcc); background: #fff; }
        
        /* Screenshare */
        #screenshare-view { display: flex; flex-direction: column; flex: 1; min-width: 0; }
        #screenshare-view.hidden { display: none; }
        #screenshare-video { flex: 1; background: #0f111a; display: flex; align-items: center; justify-content: center; position: relative; }
        #screenshare-video video { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        #video-section.screenshare-active #screenshare-view { flex: 1; min-width: 0; }
        #video-section.screenshare-active #participants-sidebar { border-top: 1px solid var(--outline-variant); flex: 0 0 200px; height: 200px; overflow-x: auto; overflow-y: hidden; flex-direction: row; align-items: center; justify-content: flex-start; }
        #video-section.screenshare-active #participants-grid { display: flex; gap: 12px; }
        #video-section.screenshare-active .participant-tile, #video-section.screenshare-active #local-pip-wrapper { width: 240px; height: 135px; flex-shrink: 0; }
        
        /* Side Panel */
        #side-panel-wrapper { width: 340px; flex-shrink: 0; display: flex; flex-direction: column; background: var(--surface-container-lowest, #ffffff); border: 1px solid var(--outline-variant, #c3c6d7); border-radius: 16px; overflow: hidden; transition: margin border 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        #side-panel-wrapper.panel-hidden { display: none !important; }
        
        .panel-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--outline-variant, #c3c6d7); }
        
        #chat-messages, #participants-list { flex: 1; overflow-y: auto; padding: 16px; }
        .chat-input-box { background: var(--surface-container-low, #f3f3fe); border-radius: 12px; padding: 12px; display: flex; gap: 8px; position: relative; border: 1px solid var(--outline-variant, #c3c6d7); }
        #chat-input { flex: 1; background: transparent; border: none; outline: none; resize: none; font-size: 14px; max-height: 80px; }
        
        /* Controls */
        .glass-panel { background: rgba(250, 248, 255, 0.85); backdrop-filter: blur(16px); }
        .ctrl-btn { width: 48px; height: 48px; border-radius: 50%; border: 1px solid transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); background: var(--surface-container-high, #e7e7f3); color: var(--on-surface, #191b23); }
        .ctrl-btn:hover { background: var(--surface-variant, #e1e2ed); transform: scale(1.05); }
        .ctrl-btn:active { transform: scale(0.95); opacity: 0.9; }
        .ctrl-btn.ctrl-off { background: var(--error, #ba1a1a); color: var(--on-error, #ffffff); }
        .ctrl-btn.ctrl-off:hover { background: #a41717; }
        .ctrl-btn.ctrl-active { background: var(--primary, #004ac6); color: var(--on-primary, #ffffff); }
        
        /* Mic Test */
        .mic-test-container { display: flex; align-items: center; gap: 4px; margin-top: 12px; }
        .mic-bar { flex: 1; height: 6px; border-radius: 3px; background: var(--surface-variant); transition: background 0.1s; border: 1px solid rgba(0,0,0,0.1); }
        .mic-bar.active { border-color: transparent; }
        .mic-test-status { font-size: 12px; font-weight: 600; color: var(--on-surface-variant); margin-top: 8px; }
        .mic-test-status.status-active { color: var(--primary); }
        .mic-test-status.status-error { color: var(--error); }
        
        /* Floating Emojis */
        .reaction-float { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%); font-size: 40px; pointer-events: none; z-index: 50; animation: floatUp 2.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes floatUp { 0% { opacity:0; transform:translate(-50%, 0) scale(0.5); } 10% { opacity:1; transform:translate(-50%, 0) scale(1.3); } 100% { opacity:0; transform:translate(-50%, -200px) scale(0.8); } }
        
        /* Tooltips & Popups */
        #reactions-popup, #more-menu { position: absolute; bottom: calc(100% + 12px); left: 50%; transform: translateX(-50%); background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid var(--outline-variant, #c3c6d7); padding: 8px; z-index: 100; display: flex; gap: 4px; }
        #reactions-popup.hidden, #more-menu.hidden { display: none; }
        #reactions-popup button { font-size: 24px; padding: 10px; border-radius: 12px; transition: transform 0.1s, background 0.1s; }
        #reactions-popup button:hover { transform: scale(1.2); background: var(--surface-container, #ededf9); }
    </style>
</head>
<body class="bg-surface font-body text-on-surface">

<div id="room-wrapper">

    <!-- Top Navigation Bar -->
    <header class="bg-surface-container-lowest/80 backdrop-blur-md w-full z-50 border-b border-outline-variant/20 px-6 py-3 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-6">
            <h1 class="text-xl font-extrabold tracking-tighter text-primary font-headline flex items-center gap-2">
                <span class="material-symbols-outlined text-[24px]">architecture</span>
                Architectural Scholar
            </h1>
            <div class="hidden md:flex items-center gap-2 bg-surface-container-low border border-outline-variant/30 px-3 py-1.5 rounded-full shadow-inner">
                <span class="w-2 h-2 bg-secondary rounded-full animate-pulse shadow-[0_0_8px_rgba(0,108,73,0.6)]"></span>
                <span class="text-xs font-bold text-secondary uppercase tracking-widest">{{ $conference->title }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div id="conn-status" class="text-xs font-bold text-tertiary uppercase tracking-widest">Memulai koneksi...</div>
            <div id="elapsed-timer" class="font-bold text-sm bg-surface-container px-3 py-1 rounded-lg border border-outline-variant/20 font-mono">00:00</div>
        </div>
    </header>

    <!-- Main Workspace -->
    <div id="main-area">
        
        <!-- Video Stage -->
        <div id="video-section">
            
            <div id="screenshare-view" class="hidden">
                <div id="screenshare-video"></div>
                <div id="screenshare-label" class="p-3 bg-surface-container-lowest border-t border-outline-variant/20 text-xs text-center font-bold text-on-surface-variant"></div>
            </div>

            <div id="participants-sidebar" class="solo-mode">
                <div id="participants-grid"></div>
                <!-- Local PiP -->
                <div id="local-pip-wrapper">
                    <div id="local-pip">
                        <div id="local-avatar" class="absolute inset-0 bg-surface-container flex items-center justify-center">
                            <div class="avatar-circle" style="background:{{ '#' . substr(md5(auth()->user()->name), 0, 6) }}">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="local-name">{{ auth()->user()->name }} (Anda)</div>
                        <div class="local-hand" id="local-hand-indicator" style="position: absolute; bottom: 12px; right: 12px; display: none; font-size: 24px;">✋</div>
                    </div>
                </div>
            </div>

            <!-- Floating Control Bar -->
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-40">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center gap-3 shadow-xl border border-outline-variant/30">
                    
                    <button id="btn-mic" class="ctrl-btn ctrl-active" onclick="ConferenceRoom.toggleMic()" title="Mikrofon">
                        <span id="icon-mic" class="material-symbols-outlined text-[20px]">mic</span>
                    </button>

                    <button id="btn-cam" class="ctrl-btn ctrl-active" onclick="ConferenceRoom.toggleCamera()" title="Kamera">
                        <span id="icon-cam" class="material-symbols-outlined text-[20px]">videocam</span>
                    </button>

                    <button id="btn-screen" class="ctrl-btn" onclick="ConferenceRoom.toggleScreenShare()" title="Bagikan Layar">
                        <span class="material-symbols-outlined text-[20px]">screen_share</span>
                    </button>

                    <div class="w-px h-8 bg-outline-variant/40 mx-1"></div>

                    <div style="position: relative;">
                        <button class="ctrl-btn" onclick="document.getElementById('reactions-popup').classList.toggle('hidden'); document.getElementById('more-menu').classList.add('hidden');" title="Kirim Reaksi">
                            <span class="material-symbols-outlined text-[20px]">mood</span>
                        </button>
                        <div id="reactions-popup" class="hidden">
                            @foreach(['👍','❤️','😂','😮','👏','🎉'] as $emoji)
                                <button onclick="ConferenceUI.sendReaction('{{ $emoji }}'); document.getElementById('reactions-popup').classList.add('hidden');">{{ $emoji }}</button>
                            @endforeach
                        </div>
                    </div>

                    <button id="btn-hand" class="ctrl-btn" onclick="ConferenceUI.toggleHand()" title="Angkat Tangan">
                        <span class="material-symbols-outlined text-[20px]">back_hand</span>
                    </button>

                    <div style="position: relative;">
                        <button class="ctrl-btn" onclick="document.getElementById('more-menu').classList.toggle('hidden'); document.getElementById('reactions-popup').classList.add('hidden');" title="Menu Lainnya">
                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                        </button>
                        <div id="more-menu" class="hidden flex-col items-stretch p-2" style="min-width: 180px;">
                            <button onclick="Settings.open(); document.getElementById('more-menu').classList.add('hidden');" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-container rounded-lg text-sm font-bold text-on-surface w-full text-left transition-colors">
                                <span class="material-symbols-outlined text-[18px]">settings</span> Pengaturan UI
                            </button>
                            <button onclick="ConferenceUI.copyLink(); document.getElementById('more-menu').classList.add('hidden');" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-container rounded-lg text-sm font-bold text-on-surface w-full text-left transition-colors">
                                <span class="material-symbols-outlined text-[18px]">content_copy</span> Salin Info Sesi
                            </button>
                        </div>
                    </div>

                    <div class="w-px h-8 bg-outline-variant/40 mx-1"></div>
                    
                    <button class="w-12 h-12 rounded-full border border-transparent flex items-center justify-center cursor-pointer transition-all bg-surface-container-high text-on-surface hover:bg-surface-variant relative" onclick="ConferenceUI.togglePanel('chat')" title="Chat" id="btn-chat">
                        <span class="material-symbols-outlined text-[20px]">chat</span>
                        <span id="chat-unread-badge" class="hidden absolute -top-1 -right-1 bg-error text-white text-[10px] items-center justify-center font-bold w-4 h-4 rounded-full border-2 border-white flex"></span>
                    </button>

                    <button class="w-12 h-12 rounded-full border border-transparent flex items-center justify-center cursor-pointer transition-all bg-surface-container-high text-on-surface hover:bg-surface-variant relative" onclick="ConferenceUI.togglePanel('participants')" title="Daftar Peserta" id="btn-participants">
                        <span class="material-symbols-outlined text-[20px]">group</span>
                        <span id="participants-count-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] items-center justify-center font-bold w-4 h-4 rounded-full border-2 border-white flex">1</span>
                    </button>

                    <div class="w-px h-8 bg-outline-variant/40 mx-1"></div>

                    {{-- Mahasiswa hanya bisa "Keluar", tidak bisa "Akhiri Sesi" --}}
                    <button onclick="ConferenceRoom.disconnect('{{ route('mahasiswa.courses.show', $conference->course) }}')" class="px-6 py-3 rounded-xl bg-surface-container-high text-on-surface font-bold hover:bg-surface-variant transition-all hover:-translate-y-0.5 active:scale-95 shadow border border-outline-variant/20 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">logout</span> Keluar
                    </button>

                </div>
            </div>
        </div>

        <!-- Side Panel -->
        <div id="side-panel-wrapper" class="panel-hidden">
            <!-- Participants Panel -->
            <div id="panel-participants" class="panel-hidden flex-col flex-1 min-h-[50%] border-b border-outline-variant/10">
                <div class="panel-header bg-surface-container-lowest">
                    <h3 class="font-headline font-bold text-on-surface text-lg">Peserta (<span id="panel-participant-total">1</span>)</h3>
                    <button onclick="ConferenceUI.closePanel('participants')" class="text-on-surface-variant hover:text-error transition-colors p-1 rounded-md hover:bg-surface-container">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-4 bg-surface-container-lowest border-b border-outline-variant/10">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                        <input type="text" id="participant-search" placeholder="Cari mahasiswa..." class="w-full bg-surface-container border-none rounded-xl pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary" oninput="ConferenceUI.renderParticipants()">
                    </div>
                </div>
                <div id="participants-list" class="flex-1 overflow-y-auto p-2 bg-surface"></div>
                <div id="participants-footer" class="p-4 bg-surface-container-lowest border-t border-outline-variant/10">
                    <button onclick="ConferenceUI.copyLink()" class="w-full py-2.5 bg-surface-container hover:bg-surface-variant rounded-xl text-sm font-bold flex justify-center items-center gap-2 border border-outline-variant/20 transition-all text-on-surface">
                        <span class="material-symbols-outlined text-[18px]">link</span> <span id="copy-link-text">Salin Undangan Rapat</span>
                    </button>
                </div>
            </div>

            <!-- Chat Panel -->
            <div id="panel-chat" class="panel-hidden flex-col flex-1 min-h-[50%]">
                <div class="panel-header bg-surface-container-lowest border-t border-outline-variant/10">
                    <h3 class="font-headline font-bold text-on-surface text-lg">Live Discussion</h3>
                    <button onclick="ConferenceUI.closePanel('chat')" class="text-on-surface-variant hover:text-error transition-colors p-1 rounded-md hover:bg-surface-container">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-surface/50">
                    <p class="text-center text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/60 my-4">Diskusi dimulai</p>
                </div>
                <div class="p-4 bg-surface-container-lowest border-t border-outline-variant/20">
                    <div class="chat-input-box">
                        <textarea id="chat-input" rows="1" placeholder="Type a message..."></textarea>
                        <div class="flex items-end gap-1 pb-1">
                            <button id="btn-emoji" onclick="document.getElementById('emoji-picker').classList.toggle('hidden')" class="p-1.5 text-on-surface-variant hover:bg-surface-variant rounded-md transition-colors"><span class="material-symbols-outlined text-[18px]">mood</span></button>
                            <button id="btn-chat-send" disabled class="p-1.5 bg-primary text-on-primary rounded-md disabled:opacity-30 disabled:bg-surface-container disabled:text-on-surface-variant transition-colors"><span class="material-symbols-outlined text-[18px]">send</span></button>
                        </div>
                        <div id="emoji-picker" class="hidden absolute bottom-[calc(100%+8px)] right-0 bg-surface-container-lowest shadow-lg border border-outline-variant/20 rounded-xl p-2 grid grid-cols-5 gap-1">
                            @foreach(['👍','❤️','😂','😮','👏','🎉','🔥','💯','✅','🙏'] as $emoji)
                                <button onclick="ChatHelper.addEmoji('{{ $emoji }}')" class="p-2 hover:bg-surface-container rounded-lg text-lg">{{ $emoji }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="settings-modal" class="hidden fixed inset-0 z-[9999] bg-on-surface/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/20 flex flex-col">
        <div class="flex justify-between items-center p-6 border-b border-outline-variant/10">
            <h2 class="font-headline font-bold text-xl text-on-surface">Device Settings</h2>
            <button onclick="Settings.close()" class="p-2 hover:bg-surface-container rounded-full transition-colors"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="flex border-b border-outline-variant/10 px-6 gap-6">
            <button id="tab-audio" class="py-4 text-sm font-bold text-primary border-b-2 border-primary" onclick="Settings.switchTab('audio')">Audio</button>
            <button id="tab-video" class="py-4 text-sm font-bold text-on-surface-variant hover:text-on-surface" onclick="Settings.switchTab('video')">Video</button>
        </div>
        <div class="p-6 flex-1 min-h-[300px] bg-surface">
            <div id="panel-audio" class="settings-panel space-y-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Microphone</label>
                    <div class="flex gap-2 mb-2">
                        <select id="select-mic" class="flex-1 bg-surface-container border-none rounded-xl p-3 focus:ring-2 focus:ring-primary shadow-inner text-sm font-medium" onchange="Settings.applyMic()"></select>
                        <button id="btn-mic-test" onclick="MicTest.start()" class="px-4 bg-surface-container hover:bg-surface-variant rounded-xl text-sm font-bold border border-outline-variant/20 transition-colors text-on-surface whitespace-nowrap active:scale-95">Test Mikrofon</button>
                    </div>
                    <div class="mic-test-container">
                        @for($i=0; $i<20; $i++) <div class="mic-bar"></div> @endfor
                    </div>
                    <div id="mic-test-status" class="mic-test-status">Klik "Test Mikrofon" untuk mulai.</div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Speaker</label>
                    <select id="select-speaker" class="w-full bg-surface-container border-none rounded-xl p-3 focus:ring-2 focus:ring-primary shadow-inner text-sm font-medium" onchange="Settings.applySpeaker()"></select>
                </div>
            </div>
            <div id="panel-video" class="settings-panel hidden space-y-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Camera</label>
                    <select id="select-cam" class="w-full bg-surface-container border-none rounded-xl p-3 focus:ring-2 focus:ring-primary shadow-inner text-sm font-medium mb-4" onchange="Settings.applyCam()"></select>
                    
                    <div class="aspect-video w-full bg-neutral-900 rounded-xl overflow-hidden shadow-inner border border-outline-variant/20 flex items-center justify-center relative">
                        <video id="cam-test-preview" autoplay playsinline muted class="w-full h-full object-cover transform scale-x-[-1]"></video>
                        <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/60 to-transparent flex justify-center">
                            <span class="text-xs font-bold tracking-widest text-white/80 uppercase">Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-outline-variant/10 flex justify-end gap-3 bg-surface-container-lowest">
            <button onclick="Settings.close()" class="px-6 py-2.5 rounded-xl font-bold text-on-surface-variant hover:bg-surface-container transition-colors">Batal</button>
            <button onclick="Settings.applyAll()" class="px-6 py-2.5 rounded-xl font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-sm">Simpan</button>
        </div>
    </div>
</div>

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
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 80) + 'px';
        });

        chatInput?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim()) {
                    ConferenceUI.sendMessage(this.value);
                    this.value = '';
                    this.style.height = 'auto';
                    if (sendBtn) sendBtn.disabled = true;
                }
            }
        });

        sendBtn?.addEventListener('click', function() {
            const val = chatInput?.value;
            if (val?.trim()) {
                ConferenceUI.sendMessage(val);
                chatInput.value = '';
                chatInput.style.height = 'auto';
                sendBtn.disabled = true;
            }
        });

        // Outside click handlers
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#reactions-popup') && !e.target.closest('button[onclick*="reactions-popup"]')) {
                document.getElementById('reactions-popup')?.classList.add('hidden');
            }
            if (!e.target.closest('#more-menu') && !e.target.closest('button[onclick*="more-menu"]')) {
                document.getElementById('more-menu')?.classList.add('hidden');
            }
            if (!e.target.closest('#emoji-picker') && !e.target.closest('#btn-emoji')) {
                document.getElementById('emoji-picker')?.classList.add('hidden');
            }
        });

        ConferenceRoom.init(
            '{{ route('mahasiswa.conferences.token', $conference) }}',
            '{{ config('services.livekit.url') }}',
            '{{ auth()->user()->name }}',
            false
        );
    });
</script>
</body>
</html>
