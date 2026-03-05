import React, { useState, useEffect, useCallback, useRef } from "react";
import { useNavigate, useParams } from "react-router";
import { AnimatePresence, motion } from "motion/react";
import { VideoGrid } from "./VideoGrid";
import { ControlBar } from "./ControlBar";
import { ChatPanel } from "./ChatPanel";
import { ParticipantsPanel } from "./ParticipantsPanel";
import {
  ConferenceState,
  MockParticipant,
  ChatMessage,
  SidePanel,
} from "./types";
import { MOCK_PARTICIPANTS, INITIAL_MESSAGES } from "./mockData";

/**
 * ConferenceRoom Component
 *
 * LiveKit Integration with Laravel 12:
 * =====================================
 * To connect to a REAL LiveKit server:
 *
 * 1. Replace the mock state with actual LiveKit hooks:
 *    import { useRoom, useLocalParticipant, useParticipants } from '@livekit/components-react';
 *
 * 2. Get token from your Laravel 12 API:
 *    const { token, url } = await getLiveKitToken(roomId, userName);
 *
 * 3. Wrap this component with LiveKitRoom:
 *    <LiveKitRoom token={token} serverUrl={url} connect={true}>
 *      <ConferenceRoom />
 *    </LiveKitRoom>
 *
 * 4. Use LiveKit hooks:
 *    const { localParticipant } = useLocalParticipant();
 *    const participants = useParticipants();
 *    localParticipant.setMicrophoneEnabled(!isMuted);
 *    localParticipant.setCameraEnabled(!isVideoOff);
 *    localParticipant.setScreenShareEnabled(isScreenSharing);
 */

function formatDuration(seconds: number): string {
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = seconds % 60;
  if (h > 0) {
    return `${h}:${m.toString().padStart(2, "0")}:${s.toString().padStart(2, "0")}`;
  }
  return `${m.toString().padStart(2, "0")}:${s.toString().padStart(2, "0")}`;
}

export function ConferenceRoom() {
  const { roomId } = useParams<{ roomId: string }>();
  const navigate = useNavigate();

  const [state, setState] = useState<ConferenceState>({
    isMuted: false,
    isVideoOff: false,
    isScreenSharing: false,
    isRecording: false,
    isHandRaised: false,
    viewMode: "grid",
    sidePanel: null,
    participants: MOCK_PARTICIPANTS,
    messages: INITIAL_MESSAGES,
    unreadCount: 0,
    pinnedParticipantId: null,
    reaction: null,
  });

  const [elapsedSeconds, setElapsedSeconds] = useState(0);
  const [floatingReaction, setFloatingReaction] = useState<{
    emoji: string;
    id: number;
  } | null>(null);
  const reactionCounter = useRef(0);

  // Timer
  useEffect(() => {
    const timer = setInterval(() => {
      setElapsedSeconds((s) => s + 1);
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // Simulate speaking participants
  useEffect(() => {
    const interval = setInterval(() => {
      setState((prev) => ({
        ...prev,
        participants: prev.participants.map((p) => ({
          ...p,
          isSpeaking:
            !p.isMuted && !p.isMe
              ? Math.random() < 0.25
              : p.isMe
              ? !prev.isMuted && Math.random() < 0.1
              : false,
        })),
      }));
    }, 2000);
    return () => clearInterval(interval);
  }, []);

  // Simulate incoming messages
  useEffect(() => {
    const names = ["Budi Santoso", "Siti Rahayu", "Ahmad Fauzi", "Dewi Lestari", "Reza Pratama"];
    const colors = ["#0f9d58", "#f4b400", "#db4437", "#9c27b0", "#00bcd4"];
    const msgs = [
      "Setuju dengan pendapat tadi 👍",
      "Bisa screen share slide-nya?",
      "Koneksi saya sedikit lambat...",
      "Apakah bisa diulang bagian terakhir?",
      "Saya tidak bisa mendengar dengan jelas",
      "Bagaimana jadwal untuk minggu depan?",
      "Nanti saya kirim dokumennya via email",
      "Terima kasih atas presentasinya!",
    ];

    const timeout = setTimeout(() => {
      const idx = Math.floor(Math.random() * names.length);
      const newMsg: ChatMessage = {
        id: `auto-${Date.now()}`,
        senderId: `auto-${idx}`,
        senderName: names[idx],
        senderColor: colors[idx],
        message: msgs[Math.floor(Math.random() * msgs.length)],
        timestamp: new Date(),
        isMe: false,
      };

      setState((prev) => ({
        ...prev,
        messages: [...prev.messages, newMsg],
        unreadCount:
          prev.sidePanel !== "chat" ? prev.unreadCount + 1 : prev.unreadCount,
      }));
    }, 8000 + Math.random() * 10000);

    return () => clearTimeout(timeout);
  }, [state.messages.length]);

  const handleToggleMute = useCallback(() => {
    setState((prev) => {
      const newMuted = !prev.isMuted;
      return {
        ...prev,
        isMuted: newMuted,
        participants: prev.participants.map((p) =>
          p.isMe ? { ...p, isMuted: newMuted } : p
        ),
      };
    });
  }, []);

  const handleToggleVideo = useCallback(() => {
    setState((prev) => {
      const newVideoOff = !prev.isVideoOff;
      return {
        ...prev,
        isVideoOff: newVideoOff,
        participants: prev.participants.map((p) =>
          p.isMe ? { ...p, isVideoOff: newVideoOff } : p
        ),
      };
    });
  }, []);

  const handleToggleScreenShare = useCallback(() => {
    setState((prev) => ({
      ...prev,
      isScreenSharing: !prev.isScreenSharing,
    }));
  }, []);

  const handleToggleHand = useCallback(() => {
    setState((prev) => {
      const newHandRaised = !prev.isHandRaised;
      return {
        ...prev,
        isHandRaised: newHandRaised,
        participants: prev.participants.map((p) =>
          p.isMe ? { ...p, isHandRaised: newHandRaised } : p
        ),
      };
    });
  }, []);

  const handleTogglePanel = useCallback((panel: SidePanel) => {
    setState((prev) => ({
      ...prev,
      sidePanel: prev.sidePanel === panel ? null : panel,
      unreadCount: panel === "chat" ? 0 : prev.unreadCount,
    }));
  }, []);

  const handleToggleViewMode = useCallback(() => {
    setState((prev) => ({
      ...prev,
      viewMode: prev.viewMode === "grid" ? "spotlight" : "grid",
    }));
  }, []);

  const handlePin = useCallback((id: string) => {
    setState((prev) => ({
      ...prev,
      pinnedParticipantId: prev.pinnedParticipantId === id ? null : id,
      participants: prev.participants.map((p) => ({
        ...p,
        isPinned: p.id === id ? !p.isPinned : false,
      })),
    }));
  }, []);

  const handleSendMessage = useCallback((text: string) => {
    const newMsg: ChatMessage = {
      id: `msg-${Date.now()}`,
      senderId: "me",
      senderName: "Anda",
      senderColor: "#1a73e8",
      message: text,
      timestamp: new Date(),
      isMe: true,
    };
    setState((prev) => ({
      ...prev,
      messages: [...prev.messages, newMsg],
    }));
  }, []);

  const handleReaction = useCallback((emoji: string) => {
    reactionCounter.current += 1;
    const id = reactionCounter.current;
    setFloatingReaction({ emoji, id });
    setState((prev) => ({
      ...prev,
      participants: prev.participants.map((p) =>
        p.isMe ? { ...p, reaction: emoji } : p
      ),
    }));
    setTimeout(() => {
      setFloatingReaction(null);
      setState((prev) => ({
        ...prev,
        participants: prev.participants.map((p) =>
          p.isMe ? { ...p, reaction: undefined } : p
        ),
      }));
    }, 3000);
  }, []);

  const handleEndCall = useCallback(() => {
    navigate("/");
  }, [navigate]);

  return (
    <div
      className="flex flex-col h-screen bg-[#202124] overflow-hidden select-none"
      onClick={() => {}}
    >
      {/* Top Bar */}
      <div className="flex items-center justify-between px-4 py-2 bg-[#202124] shrink-0 border-b border-white/5">
        {/* Logo */}
        <div className="flex items-center gap-2">
          <div className="flex gap-0.5">
            <div className="w-2 h-4 bg-blue-500 rounded-sm" />
            <div className="w-2 h-4 bg-green-500 rounded-sm" />
            <div className="w-2 h-4 bg-yellow-500 rounded-sm" />
            <div className="w-2 h-4 bg-red-500 rounded-sm" />
          </div>
          <span className="text-white font-medium text-sm">LiveConf</span>
          <span className="text-gray-500 text-xs">powered by LiveKit + Laravel 12</span>
        </div>

        {/* Recording indicator */}
        {state.isRecording && (
          <div className="flex items-center gap-2 bg-red-500/20 border border-red-500/40 text-red-400 px-3 py-1 rounded-full text-sm">
            <span className="w-2 h-2 rounded-full bg-red-500 animate-pulse" />
            Sedang Merekam
          </div>
        )}

        {/* Screen share indicator */}
        {state.isScreenSharing && (
          <div className="flex items-center gap-2 bg-green-500/20 border border-green-500/40 text-green-400 px-3 py-1 rounded-full text-sm">
            <span className="w-2 h-2 rounded-full bg-green-500 animate-pulse" />
            Berbagi Layar Aktif
          </div>
        )}

        <div className="flex items-center gap-2">
          <span className="text-gray-400 text-sm">{formatDuration(elapsedSeconds)}</span>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex flex-1 overflow-hidden min-h-0">
        {/* Video Area */}
        <div className="flex-1 overflow-hidden min-w-0">
          <VideoGrid
            participants={state.participants}
            viewMode={state.viewMode}
            pinnedParticipantId={state.pinnedParticipantId}
            isScreenSharing={state.isScreenSharing}
            screenSharerName="Anda (Me)"
            screenSharerColor="#1a73e8"
            onPin={handlePin}
            onStopScreenShare={handleToggleScreenShare}
            hasSidePanel={state.sidePanel !== null}
          />
        </div>

        {/* Side Panels */}
        <AnimatePresence>
          {state.sidePanel === "chat" && (
            <ChatPanel
              key="chat"
              messages={state.messages}
              onSendMessage={handleSendMessage}
              onClose={() => handleTogglePanel("chat")}
            />
          )}
          {state.sidePanel === "participants" && (
            <ParticipantsPanel
              key="participants"
              participants={state.participants}
              onClose={() => handleTogglePanel("participants")}
              roomId={roomId || ""}
            />
          )}
        </AnimatePresence>
      </div>

      {/* Floating Reaction */}
      <AnimatePresence>
        {floatingReaction && (
          <motion.div
            key={floatingReaction.id}
            initial={{ opacity: 0, y: 0, x: "-50%", scale: 0.5 }}
            animate={{ opacity: 1, y: -120, x: "-50%", scale: 1.5 }}
            exit={{ opacity: 0, y: -200, x: "-50%", scale: 0.5 }}
            transition={{ duration: 2.5, ease: "easeOut" }}
            className="fixed bottom-24 left-1/2 text-4xl pointer-events-none z-50"
          >
            {floatingReaction.emoji}
          </motion.div>
        )}
      </AnimatePresence>

      {/* Control Bar */}
      <ControlBar
        isMuted={state.isMuted}
        isVideoOff={state.isVideoOff}
        isScreenSharing={state.isScreenSharing}
        isRecording={state.isRecording}
        isHandRaised={state.isHandRaised}
        viewMode={state.viewMode}
        sidePanel={state.sidePanel}
        participantCount={state.participants.length}
        unreadCount={state.unreadCount}
        meetingTime={formatDuration(elapsedSeconds)}
        roomId={roomId || ""}
        onToggleMute={handleToggleMute}
        onToggleVideo={handleToggleVideo}
        onToggleScreenShare={handleToggleScreenShare}
        onToggleHand={handleToggleHand}
        onTogglePanel={handleTogglePanel}
        onToggleViewMode={handleToggleViewMode}
        onReaction={handleReaction}
        onEndCall={handleEndCall}
      />
    </div>
  );
}