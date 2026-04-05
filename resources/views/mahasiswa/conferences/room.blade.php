<!DOCTYPE html>
<html lang="id">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>{{ $conference->title }}</title>
 @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/conference-room.js', 'resources/js/conference-meet-enhancements.js'])
 <style>
 *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 html, body { height: 100%; background: #202124; color: #fff; font-family: 'Google Sans', 'Inter', ui-sans-serif, sans-serif; overflow: hidden; }

 /* ---- Layout ---- */
 #room-wrapper { display: flex; flex-direction: column; height: 100vh; }
 #topbar { display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; flex-shrink: 0; border-bottom: 1px solid rgba(255,255,255,0.06); background: #202124; }
 #main-area { flex: 1; display: flex; overflow: hidden; position: relative; padding: 8px; gap: 8px; min-height: 0; }
 #bottombar { display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; flex-shrink: 0; gap: 10px; position: relative; border-top: 1px solid rgba(255,255,255,0.08); background: #202124; }

 /* ---- Topbar ---- */
 .app-logo { display: flex; align-items: center; gap: 8px; }
 .logo-bars { display: flex; gap: 3px; }
 .logo-bar { width: 4px; height: 16px; border-radius: 2px; }
 .logo-bar:nth-child(1) { background: #4285f4; }
 .logo-bar:nth-child(2) { background: #0f9d58; }
 .logo-bar:nth-child(3) { background: #f4b400; }
 .logo-bar:nth-child(4) { background: #db4437; }
 .logo-text { font-size: 14px; font-weight: 600; color: #fff; }
 .logo-sub { font-size: 11px; color: #5f6368; }
 .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 500; }
 .status-pill.screensharing { background: rgba(52,168,83,0.15); border: 1px solid rgba(52,168,83,0.35); color: #81c995; }
 .status-dot { width: 7px; height: 7px; border-radius: 50%; animation: pulse-dot 1.2s infinite; }
 .status-dot.green { background: #34a853; }
 @keyframes pulse-dot { 0%,100%{ opacity:1;transform:scale(1); } 50%{ opacity:.55;transform:scale(1.25); } }
 #elapsed-timer { font-size: 14px; color: #bdc1c6; font-weight: 500; }
 #conn-status { font-size: 11px; color: #fbbc04; font-weight: 500; }
 .meeting-title { font-size: 14px; font-weight: 500; color: #e8eaed; }
 .meeting-meta { font-size: 12px; color: #9aa0a6; margin-top: 2px; }

 /* ---- Main video area ---- */
 #participants-sidebar { flex: 1; display: flex; flex-direction: column; gap: 8px; min-width: 0; overflow: hidden; }
 
 /* Solo mode: only self-view, centered, 16:9 landscape */
 #participants-sidebar.solo-mode { align-items: center; justify-content: center; }
 #participants-sidebar.solo-mode #participants-grid { display: none; }
 #participants-sidebar.solo-mode #local-pip-wrapper { width: 100%; max-width: 960px; height: auto; max-height: calc(100vh - 120px); flex-shrink: 0; margin: 0; display: flex; align-items: center; justify-content: center; aspect-ratio: 16/9; }
 #participants-sidebar.solo-mode #local-pip { height: 100%; width: 100%; aspect-ratio: 16/9; }

 /* ---- Grid ---- */
 #participants-grid { flex: 1; display: grid; grid-template-columns: repeat(1, 1fr); gap: 8px; align-content: center; justify-content: center; min-height: 0; }

 /* ---- Participant Tile ---- */
 .participant-tile { position: relative; background: #3c4043; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; transition: outline 0.15s; outline: 3px solid transparent; min-height: 80px; }
 .participant-tile.speaking { outline: 3px solid var(--tile-color, #1a73e8); box-shadow: 0 0 0 5px color-mix(in srgb,var(--tile-color,#1a73e8) 30%,transparent); }
 .participant-tile.speaker-focus { grid-column: span 2; grid-row: span 2; z-index: 2; }
 .tile-inner { position: absolute; inset: 0; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
 .tile-video { position: absolute; inset: 0; width: 100%; height: 100%; }
 .tile-video video { width: 100%; height: 100%; object-fit: cover; }
 .tile-avatar { display: flex; align-items: center; justify-content: center; position: absolute; inset: 0; }
 .tile-avatar.hidden { display: none; }
 .avatar-circle { width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: #fff; }
 .tile-name { position: absolute; bottom: 8px; left: 10px; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 500; color: #fff; max-width: calc(100% - 60px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
 .tile-indicators { position: absolute; bottom: 8px; right: 10px; display: flex; gap: 4px; align-items: center; }
 .indicator { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
 .indicator svg { width: 14px; height: 14px; }
 .mic-on { background: rgba(0,0,0,0.45); color: #fff; }
 .mic-off { background: #ea4335; color: #fff; }

 /* ---- Local PiP ---- */
 #local-pip-wrapper { flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; width: 220px; }
 #local-pip { position: relative; background: #3c4043; border-radius: 12px; overflow: hidden; aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; width: 100%; }
 #local-pip video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
 #local-avatar { display: flex; align-items: center; justify-content: center; position: absolute; inset: 0; background: #3c4043; }
 #local-avatar.hidden { display: none; }
 #local-pip.speaking { outline: 3px solid #1a73e8; box-shadow: 0 0 0 5px rgba(26,115,232,0.25); }
 .local-name { position: absolute; bottom: 6px; left: 8px; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); padding: 2px 8px; border-radius: 6px; font-size: 12px; color: #fff; pointer-events: none; }
 .local-hand { position: absolute; bottom: 6px; right: 8px; display: none; }

 /* ---- Screen Share ---- */
 #screenshare-view { display: flex; flex-direction: column; flex: 1; min-width: 0; }
 #screenshare-view.hidden { display: none; }
 #screenshare-video { flex: 1; background: #000; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative; }
 #screenshare-video video { max-width: 100%; max-height: 100%; object-fit: contain; }
 #screenshare-label { font-size: 12px; color: #9aa0a6; text-align: center; margin-top: 6px; }
 
 /* Active Screen Share Layout */
 #video-section.screenshare-active #screenshare-view { flex: 1; min-width: 0; }
 #video-section.screenshare-active #participants-sidebar { flex: 0 0 240px; width: 240px; overflow-y: auto; overflow-x: hidden; justify-content: flex-start; }
 #video-section.screenshare-active #participants-grid { grid-template-columns: 1fr !important; align-content: start; }
 #video-section.screenshare-active #participants-grid .participant-tile { grid-column: span 1 !important; grid-row: span 1 !important; aspect-ratio: 16/9; height: auto; min-height: 120px; }

 /* ---- Side Panel ---- */
 #side-panel-wrapper { width: 360px; flex-shrink: 0; display: flex; flex-direction: column; background: #2d2f31; border-left: 1px solid rgba(255,255,255,0.1); overflow: hidden; animation: slideInRight 0.22s cubic-bezier(0.22,1,0.36,1); }
 #side-panel-wrapper.panel-hidden { display: none !important; }
 @keyframes slideInRight { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
 .panel-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px 14px; border-bottom: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; }
 .panel-header span { font-size: 15px; font-weight: 600; color: #e8eaed; }
 .panel-close { background: none; border: none; cursor: pointer; color: #9aa0a6; padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
 .panel-close:hover { background: rgba(255,255,255,0.1); color: #fff; }
 #panel-chat { display: flex; flex-direction: column; flex: 1; overflow: hidden; min-height: 0; }
 #panel-chat.panel-hidden { display: none; }
 #panel-participants { display: flex; flex-direction: column; flex: 1; overflow: hidden; min-height: 0; }
 #panel-participants.panel-hidden { display: none; }

 /* Chat */
 #chat-messages { flex: 1; overflow-y: auto; padding: 12px; min-height: 0; }
 #chat-messages::-webkit-scrollbar { width: 4px; }
 #chat-messages::-webkit-scrollbar-thumb { background: #5f6368; border-radius: 2px; }
 .chat-input-area { padding: 10px 12px; border-top: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; }
 .chat-input-box { position: relative; background: #3c4043; border-radius: 16px; overflow: visible; }
 #chat-input { width: 100%; background: transparent; color: #fff; font-size: 13px; padding: 10px 80px 10px 14px; border: none; outline: none; resize: none; max-height: 80px; line-height: 1.4; }
 #chat-input::placeholder { color: #6b7280; }
 .chat-send-btn { position: absolute; right: 6px; bottom: 6px; width: 30px; height: 30px; border-radius: 50%; background: #1a73e8; border: none; cursor: pointer; color: #fff; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
 .chat-send-btn:hover { background: #1558d6; }
 .chat-send-btn:disabled { background: #3c4043; cursor: not-allowed; color: #5f6368; }
 .chat-emoji-btn { position: absolute; right: 42px; bottom: 6px; width: 30px; height: 30px; border-radius: 50%; background: transparent; border: none; cursor: pointer; color: #9aa0a6; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: background 0.15s; }
 .chat-emoji-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
 #emoji-picker { position: absolute; bottom: 52px; right: 0; background: #3c4043; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); padding: 8px; display: grid; grid-template-columns: repeat(5,1fr); gap: 4px; z-index: 100; }
 #emoji-picker.hidden { display: none; }
 #emoji-picker button { background: none; border: none; cursor: pointer; font-size: 18px; padding: 6px; border-radius: 8px; transition: background 0.1s; line-height: 1; }
 #emoji-picker button:hover { background: rgba(255,255,255,0.1); }

 /* Participants */
 #participant-search { width: 100%; background: #3c4043; color: #fff; font-size: 13px; padding: 8px 12px; border: none; border-radius: 8px; outline: none; }
 #participant-search::placeholder { color: #6b7280; }
 #participants-list { flex: 1; overflow-y: auto; padding: 4px 8px; min-height: 0; }
 #participants-list::-webkit-scrollbar { width: 4px; }
 #participants-list::-webkit-scrollbar-thumb { background: #5f6368; border-radius: 2px; }

 /* ---- Control Bar ---- */
 .controlbar-left { display: flex; align-items: center; gap: 8px; min-width: 160px; }
 .controlbar-center { display: flex; align-items: center; gap: 6px; }
 .controlbar-right { display: flex; align-items: center; gap: 6px; justify-content: flex-end; min-width: 160px; }
 .ctrl-btn { width: 48px; height: 48px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s, transform 0.1s; flex-shrink: 0; }
 .ctrl-btn:hover { transform: scale(1.06); }
 .ctrl-btn svg { width: 22px; height: 22px; }
 .ctrl-btn-sm { width: 38px; height: 38px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s, transform 0.1s; flex-shrink: 0; position: relative; }
 .ctrl-btn-sm:hover { transform: scale(1.06); }
 .ctrl-btn-sm svg { width: 18px; height: 18px; }
 .ctrl-on { background: #3c4043; color: #fff; }
 .ctrl-on:hover { background: #4a4d51; }
 .ctrl-off { background: #ea4335; color: #fff; }
 .ctrl-off:hover { background: #d93025; }
 .ctrl-action { background: #3c4043; color: #e8eaed; }
 .ctrl-action:hover { background: #4a4d51; }
 .ctrl-active { background: #1a73e8 !important; color: #fff; }
 .ctrl-raised { background: #f4b400 !important; color: #202124; }
 .ctrl-end { display: flex; align-items: center; gap: 6px; padding: 0 18px; height: 48px; border-radius: 24px; background: #ea4335; color: #fff; border: none; cursor: pointer; font-size: 13px; font-weight: 600; transition: background 0.15s, transform 0.1s; }
 .ctrl-end:hover { background: #d93025; transform: scale(1.03); }
 .ctrl-end svg { width: 20px; height: 20px; }

 /* Reactions popup */
 #reactions-popup { position: absolute; bottom: 68px; left: 50%; transform: translateX(-50%); background: #3c4043; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); padding: 8px; display: flex; gap: 4px; z-index: 100; }
 #reactions-popup.hidden { display: none; }
 #reactions-popup button { background: none; border: none; cursor: pointer; font-size: 22px; padding: 8px; border-radius: 12px; transition: background 0.1s, transform 0.1s; }
 #reactions-popup button:hover { background: rgba(255,255,255,0.1); transform: scale(1.2); }

 /* More menu */
 #more-menu { position: absolute; bottom: 66px; left: 50%; transform: translateX(-20%); background: #3c4043; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); padding: 6px 0; min-width: 190px; z-index: 100; }
 #more-menu.hidden { display: none; }
 .more-menu-item { width: 100%; padding: 10px 16px; text-align: left; font-size: 13px; color: #e8eaed; background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: background 0.1s; }
 .more-menu-item:hover { background: rgba(255,255,255,0.08); }
 .more-menu-item svg { width: 16px; height: 16px; color: #9aa0a6; }

 /* Badge */
 .count-badge { position: absolute; top: -2px; right: -2px; min-width: 16px; height: 16px; border-radius: 8px; background: #ea4335; color: #fff; font-size: 10px; display: flex; align-items: center; justify-content: center; padding: 0 3px; font-weight: 600; }
 .count-badge.blue { background: #1a73e8; }

 /* Floating Reaction */
 .reaction-float { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); font-size: 36px; pointer-events: none; z-index: 999; animation: floatUp 2.8s ease-out forwards; }
 @keyframes floatUp { 0% { opacity:0; transform:translateX(-50%) scale(0.5) translateY(0); } 10% { opacity:1; transform:translateX(-50%) scale(1.4) translateY(0); } 80% { opacity:1; transform:translateX(-50%) scale(1) translateY(-120px); } 100% { opacity:0; transform:translateX(-50%) scale(0.7) translateY(-180px); } }

 /* Settings Modal */
 #settings-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.72); backdrop-filter: blur(6px); }
 #settings-modal.hidden { display: none; }
 .settings-box { background: #2d2f31; border-radius: 16px; width: 520px; max-width: 96vw; box-shadow: 0 24px 64px rgba(0,0,0,0.6); overflow: hidden; }
 .settings-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid #3c4043; }
 .settings-header h2 { font-size: 17px; font-weight: 600; color: #e8eaed; }
 .settings-close { background: none; border: none; cursor: pointer; color: #9aa0a6; padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.15s; }
 .settings-close:hover { background: #3c4043; color: #e8eaed; }
 .settings-tabs { display: flex; padding: 0 24px; border-bottom: 1px solid #3c4043; }
 .settings-tab { padding: 12px 18px; font-size: 13px; font-weight: 500; color: #9aa0a6; cursor: pointer; border-bottom: 2px solid transparent; border-top: none; border-left: none; border-right: none; background: none; margin-bottom: -1px; transition: all 0.15s; }
 .settings-tab:hover { color: #e8eaed; }
 .settings-tab.active { color: #8ab4f8; border-bottom-color: #8ab4f8; }
 .settings-body { padding: 20px 24px; min-height: 220px; }
 .settings-panel.hidden { display: none; }
 .settings-field { margin-bottom: 20px; }
 .settings-field label { display: block; font-size: 11px; font-weight: 500; color: #9aa0a6; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em; }
 .settings-select { width: 100%; background: #3c4043; color: #e8eaed; border: 1px solid #5f6368; border-radius: 8px; padding: 10px 14px; font-size: 13px; outline: none; appearance: none; cursor: pointer; transition: border-color 0.15s; }
 .settings-select:focus { border-color: #8ab4f8; }
 .settings-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px; border-top: 1px solid #3c4043; }
 .btn-settings-cancel { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; background: transparent; color: #8ab4f8; border: none; cursor: pointer; transition: background 0.15s; }
 .btn-settings-cancel:hover { background: rgba(138,180,248,0.1); }
 .btn-settings-save { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; background: #8ab4f8; color: #202124; border: none; cursor: pointer; transition: background 0.15s; }
 .btn-settings-save:hover { background: #aecbfa; }
 .mic-test-status { font-size: 12px; color: #9aa0a6; min-height: 18px; }
 .mic-test-status.status-active { color: #34a853; }
 .mic-test-status.status-error { color: #ea4335; }
 .btn-mic-test-active { background: #1a73e8 !important; color: #fff !important; border-color: #1a73e8 !important; }
 .mic-bar.active { height: 100% !important; }
 </style>
</head>
<body>
<div id="room-wrapper">

 {{-- Top Bar --}}
 <div id="topbar">
 {{-- Left: Logo + Meeting info --}}
 <div class="flex items-center gap-4">
 <div class="app-logo">
 <div class="logo-bars">
 <div class="logo-bar"></div>
 <div class="logo-bar"></div>
 <div class="logo-bar"></div>
 <div class="logo-bar"></div>
 </div>
 <div>
 <div class="logo-text">EduConf</div>
 <div class="logo-sub">by LiveKit</div>
 </div>
 </div>
 <div style="width:1px;height:28px;background:rgba(255,255,255,0.12)"></div>
 <div>
 <div class="meeting-title">{{ $conference->title }}</div>
 <div class="meeting-meta">{{ $conference->course->nama_matkul }}</div>
 </div>
 </div>

 {{-- Center: Status indicators --}}
 <div class="flex items-center gap-3">
 <div id="screenshare-status" class="status-pill screensharing hidden">
 <span class="status-dot green"></span>
 Berbagi Layar Aktif
 </div>
 </div>

 {{-- Right: Clock + status --}}
 <div class="flex items-center gap-4">
 <div style="text-align:right">
 <div id="elapsed-timer" style="font-size:14px;color:#bdc1c6;font-weight:500;">00:00</div>
 <div id="participant-count" style="font-size:11px;color:#9aa0a6;">1 peserta</div>
 </div>
 <div id="conn-status" style="font-size:11px;color:#fbbc04;font-weight:500;">Menghubungkan...</div>
 </div>
 </div>

 {{-- Main Area --}}
 <div id="main-area">
 {{-- Video Area --}}
 <div id="video-section" style="flex:1;display:flex;overflow:hidden;min-width:0;gap:8px;">
 {{-- Screen Share View --}}
 <div id="screenshare-view" class="hidden">
 <div id="screenshare-video"></div>
 <div id="screenshare-label"></div>
 </div>

 <div id="participants-sidebar" class="solo-mode">
 <div id="participants-grid"></div>
 <div id="local-pip-wrapper">
 <div id="local-pip">
 <div id="local-avatar">
 <div class="avatar-circle" style="width:96px;height:96px;font-size:36px;background:{{ '#' . substr(md5(auth()->user()->name), 0, 6) }}">
 {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
 </div>
 </div>
 <div class="local-name">{{ auth()->user()->name }} (Anda)</div>
 <div class="local-hand" id="local-hand-indicator">✋</div>
 </div>
 </div>
 </div>

 {{-- Side Panel --}}
 <div id="side-panel-wrapper" class="panel-hidden">

 {{-- Chat Panel --}}
 <div id="panel-chat" class="panel-hidden">
 <div class="panel-header">
 <span>Pesan dalam rapat</span>
 <button class="panel-close" onclick="ConferenceUI.closePanel()">
 <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
 </button>
 </div>
 <div id="chat-messages">
 <p style="text-align:center;font-size:12px;color:#6b7280;padding:24px 0 8px;">Pesan hanya terlihat oleh peserta dalam rapat ini</p>
 </div>
 <div class="chat-input-area">
 <div class="chat-input-box">
 <textarea id="chat-input" rows="1" placeholder="Kirim pesan ke semua orang..."></textarea>
 <button class="chat-emoji-btn" id="btn-emoji" onclick="document.getElementById('emoji-picker').classList.toggle('hidden')">😊</button>
 <button class="chat-send-btn" id="btn-chat-send" disabled>
 <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
 </button>
 <div id="emoji-picker" class="hidden">
 @foreach(['👍','❤️','😂','😮','👏','🎉','🔥','💯','✅','🙏'] as $emoji)
 <button onclick="ChatHelper.addEmoji('{{ $emoji }}')">{{ $emoji }}</button>
 @endforeach
 </div>
 </div>
 </div>
 </div>

 {{-- Participants Panel --}}
 <div id="panel-participants" class="panel-hidden">
 <div class="panel-header">
 <span>Peserta (<span id="panel-participant-total">1</span>)</span>
 <button class="panel-close" onclick="ConferenceUI.closePanel()">
 <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
 </button>
 </div>
 <div style="padding:10px 12px 4px;flex-shrink:0;">
 <input type="text" id="participant-search" placeholder="Cari peserta..." oninput="ConferenceUI.renderParticipants()">
 </div>
 <div id="participants-list" style="flex:1;overflow-y:auto;padding:4px 8px;min-height:0;"></div>
 <div id="participants-footer" style="display:flex;justify-content:space-around;padding:10px 16px;border-top:1px solid rgba(255,255,255,0.08);flex-shrink:0;"></div>
 </div>
 </div>
 </div>

 {{-- Bottom Control Bar --}}
 <div id="bottombar">
 {{-- Left: Meeting info --}}
 <div class="controlbar-left">
 <div>
 <div style="font-size:13px;font-weight:500;color:#e8eaed;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;">{{ $conference->title }}</div>
 <div id="elapsed-timer-sm" style="font-size:11px;color:#9aa0a6;display:none;">00:00</div>
 </div>
 </div>

 {{-- Center: Main controls --}}
 <div class="controlbar-center" style="position:relative;">
 {{-- Mic --}}
 <button id="btn-mic" class="ctrl-btn ctrl-on" onclick="ConferenceRoom.toggleMic()" title="Toggle Mikrofon">
 <svg id="icon-mic" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
 </button>

 {{-- Camera --}}
 <button id="btn-cam" class="ctrl-btn ctrl-on" onclick="ConferenceRoom.toggleCamera()" title="Toggle Kamera">
 <svg id="icon-cam" fill="currentColor" viewBox="0 0 24 24"><path d="M15 8v8H5V8h10m1-2H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4V7c0-.55-.45-1-1-1z"/></svg>
 </button>

 {{-- Screen Share --}}
 <button id="btn-screen" class="ctrl-btn ctrl-action" onclick="ConferenceRoom.toggleScreenShare()" title="Bagikan Layar">
 <svg fill="currentColor" viewBox="0 0 24 24"><path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4ZM4 6h16v10H4V6Zm8 9-4-4h3V8h2v3h3-4 4Z"/></svg>
 </button>

 {{-- Raise Hand --}}
 <button id="btn-hand" class="ctrl-btn ctrl-action" onclick="ConferenceUI.toggleHand()" title="Angkat / Turunkan Tangan">
 <span style="font-size:20px;line-height:1;">✋</span>
 </button>

 {{-- Reactions --}}
 <div style="position:relative;">
 <button class="ctrl-btn ctrl-action" onclick="document.getElementById('reactions-popup').classList.toggle('hidden');document.getElementById('more-menu').classList.add('hidden');" title="Reaksi">
 <span style="font-size:20px;line-height:1;">😊</span>
 </button>
 <div id="reactions-popup" class="hidden">
 @foreach(['👍','❤️','😂','😮','👏','🎉'] as $emoji)
 <button onclick="ConferenceUI.sendReaction('{{ $emoji }}');document.getElementById('reactions-popup').classList.add('hidden');">{{ $emoji }}</button>
 @endforeach
 </div>
 </div>

 {{-- More options --}}
 <div style="position:relative;">
 <button class="ctrl-btn ctrl-action" onclick="document.getElementById('more-menu').classList.toggle('hidden');document.getElementById('reactions-popup').classList.add('hidden');" title="Opsi lainnya">
 <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
 </button>
 <div id="more-menu" class="hidden">
 <button class="more-menu-item" onclick="Settings.open();document.getElementById('more-menu').classList.add('hidden');">
 <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4 2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41L9.25 5.35c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
 Pengaturan
 </button>
 </div>
 </div>

 {{-- Leave --}}
 <button class="ctrl-end" onclick="ConferenceRoom.disconnect('{{ route('mahasiswa.courses.show', $conference->course) }}')" title="Keluar dari Ruangan">
 <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .39-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08c-.18-.17-.29-.42-.29-.7 0-.28.11-.53.29-.71C3.34 8.78 7.46 7 12 7s8.66 1.78 11.71 4.67c.18.18.29.43.29.71s-.11.53-.29.71l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.1-.7-.28-.79-.74-1.69-1.36-2.67-1.85-.33-.16-.56-.5-.56-.9v-3.1C15.15 9.25 13.6 9 12 9z"/></svg>
 Keluar
 </button>
 </div>

 {{-- Right: View + Panel toggles --}}
 <div class="controlbar-right">
 {{-- View Mode --}}
 <button id="btn-viewmode" class="ctrl-btn-sm ctrl-action" onclick="ConferenceUI.toggleViewMode()" title="Toggle tampilan">
 <svg fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/></svg>
 </button>

 {{-- Chat --}}
 <button class="ctrl-btn-sm ctrl-action" onclick="ConferenceUI.togglePanel('chat');document.getElementById('reactions-popup').classList.add('hidden');document.getElementById('more-menu').classList.add('hidden');" title="Chat" style="position:relative;" id="btn-chat">
 <svg fill="currentColor" width="18" height="18" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg>
 <span id="chat-unread-badge" class="count-badge hidden"></span>
 </button>

 {{-- Participants --}}
 <button class="ctrl-btn-sm ctrl-action" onclick="ConferenceUI.togglePanel('participants');document.getElementById('reactions-popup').classList.add('hidden');document.getElementById('more-menu').classList.add('hidden');" title="Peserta" style="position:relative;" id="btn-participants">
 <svg fill="currentColor" width="18" height="18" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
 <span id="participants-count-badge" class="count-badge blue">1</span>
 </button>
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

 document.addEventListener('click', function(e) {
 if (!e.target.closest('#reactions-popup') && !e.target.closest('[onclick*="reactions-popup"]')) {
 document.getElementById('reactions-popup')?.classList.add('hidden');
 }
 if (!e.target.closest('#more-menu') && !e.target.closest('[onclick*="more-menu"]')) {
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

{{-- Settings Modal --}}
<div id="settings-modal" class="hidden">
 <div class="settings-box">
 <div class="settings-header">
 <h2>Pengaturan</h2>
 <button class="settings-close" onclick="Settings.close()">
 <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
 </button>
 </div>
 <div class="settings-tabs">
 <button id="tab-audio" class="settings-tab active" onclick="Settings.switchTab('audio')">Audio</button>
 <button id="tab-video" class="settings-tab" onclick="Settings.switchTab('video')">Video</button>
 </div>
 <div class="settings-body">
 <div id="panel-audio" class="settings-panel">
 <div class="settings-field">
 <label>Mikrofon</label>
 <select id="select-mic" class="settings-select" onchange="Settings.applyMic()"></select>
 </div>
 <div class="settings-field">
 <label>Speaker</label>
 <select id="select-speaker" class="settings-select" onchange="Settings.applySpeaker()"></select>
 </div>
 <div class="settings-field">
 <label>Test Mikrofon</label>
 <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
 <button id="btn-mic-test" onclick="MicTest.start()" style="padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;background:#3c4043;color:#e8eaed;border:1px solid #5f6368;cursor:pointer;transition:all 0.15s;white-space:nowrap;">
 Test Mikrofon
 </button>
 <div id="mic-meter" style="display:flex;align-items:flex-end;gap:3px;height:28px;flex:1;">
 @for($i = 0; $i < 20; $i++)
 <div class="mic-bar" style="width:4px;height:30%;border-radius:2px;background:#3c4043;transition:background 0.08s,height 0.08s;"></div>
 @endfor
 </div>
 </div>
 <div id="mic-test-status" class="mic-test-status">Klik "Test Mikrofon" untuk mulai.</div>
 </div>
 </div>
 <div id="panel-video" class="settings-panel hidden">
 <div class="settings-field">
 <label>Kamera</label>
 <select id="select-cam" class="settings-select" onchange="Settings.applyCam()"></select>
 </div>
 </div>
 </div>
 <div class="settings-footer">
 <button class="btn-settings-cancel" onclick="Settings.close()">Batal</button>
 <button class="btn-settings-save" onclick="Settings.applyAll()">Simpan</button>
 </div>
 </div>
</div>
</body>
</html>
