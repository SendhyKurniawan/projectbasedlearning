import React, { useState } from "react";
import {
  Mic,
  MicOff,
  Video,
  VideoOff,
  PhoneOff,
  MonitorUp,
  MonitorX,
  Hand,
  MoreVertical,
  MessageSquare,
  Users,
  LayoutGrid,
  Presentation,
  Settings,
  Subtitles,
  Shield,
  Info,
  EllipsisVertical,
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { SidePanel, ViewMode } from "./types";

interface ControlBarProps {
  isMuted: boolean;
  isVideoOff: boolean;
  isScreenSharing: boolean;
  isRecording: boolean;
  isHandRaised: boolean;
  viewMode: ViewMode;
  sidePanel: SidePanel;
  participantCount: number;
  unreadCount: number;
  meetingTime: string;
  roomId: string;
  onToggleMute: () => void;
  onToggleVideo: () => void;
  onToggleScreenShare: () => void;
  onToggleHand: () => void;
  onTogglePanel: (panel: SidePanel) => void;
  onToggleViewMode: () => void;
  onReaction: (emoji: string) => void;
  onEndCall: () => void;
}

const REACTIONS = ["👍", "❤️", "😂", "😮", "👏", "🎉"];

export function ControlBar({
  isMuted,
  isVideoOff,
  isScreenSharing,
  isRecording,
  isHandRaised,
  viewMode,
  sidePanel,
  participantCount,
  unreadCount,
  meetingTime,
  roomId,
  onToggleMute,
  onToggleVideo,
  onToggleScreenShare,
  onToggleHand,
  onTogglePanel,
  onToggleViewMode,
  onReaction,
  onEndCall,
}: ControlBarProps) {
  const [showReactions, setShowReactions] = useState(false);
  const [showMoreMenu, setShowMoreMenu] = useState(false);
  const [copied, setCopied] = useState(false);

  const handleCopyLink = () => {
    navigator.clipboard.writeText(`${window.location.origin}/room/${roomId}`);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="relative flex items-center justify-between px-4 py-2 bg-[#202124] border-t border-white/10 shrink-0">
      {/* Left: Meeting info */}
      <div className="flex items-center gap-3 min-w-0 w-48">
        <div className="flex flex-col min-w-0">
          <div className="flex items-center gap-2">
            <span className="text-white text-sm font-medium truncate">{roomId}</span>
            {isRecording && (
              <span className="flex items-center gap-1 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full shrink-0">
                <span className="w-1.5 h-1.5 rounded-full bg-white animate-pulse" />
                REC
              </span>
            )}
          </div>
          <span className="text-gray-400 text-xs">{meetingTime}</span>
        </div>
        <button
          onClick={handleCopyLink}
          className="shrink-0 text-gray-400 hover:text-white transition-colors p-1 rounded"
          title="Salin tautan rapat"
        >
          {copied ? (
            <span className="text-xs text-green-400">Tersalin!</span>
          ) : (
            <Info size={16} />
          )}
        </button>
      </div>

      {/* Center: Main Controls */}
      <div className="flex items-center gap-2">
        {/* Mic */}
        <ControlButton
          active={!isMuted}
          activeColor="#1a73e8"
          inactiveColor="#ea4335"
          onClick={onToggleMute}
          label={isMuted ? "Aktifkan Mikrofon" : "Matikan Mikrofon"}
          icon={isMuted ? <MicOff size={20} /> : <Mic size={20} />}
        />

        {/* Camera */}
        <ControlButton
          active={!isVideoOff}
          activeColor="#1a73e8"
          inactiveColor="#ea4335"
          onClick={onToggleVideo}
          label={isVideoOff ? "Aktifkan Kamera" : "Matikan Kamera"}
          icon={isVideoOff ? <VideoOff size={20} /> : <Video size={20} />}
        />

        {/* Screen Share */}
        <ControlButton
          active={!isScreenSharing}
          activeColor="#1a73e8"
          inactiveColor="#0f9d58"
          activeWhenOn
          onClick={onToggleScreenShare}
          label={isScreenSharing ? "Hentikan Berbagi Layar" : "Bagikan Layar"}
          icon={isScreenSharing ? <MonitorX size={20} /> : <MonitorUp size={20} />}
        />

        {/* Raise Hand */}
        <ControlButton
          active={!isHandRaised}
          activeColor="#1a73e8"
          inactiveColor="#f4b400"
          activeWhenOn
          onClick={onToggleHand}
          label={isHandRaised ? "Turunkan Tangan" : "Angkat Tangan"}
          icon={
            <span
              style={{
                fontSize: "18px",
                filter: isHandRaised ? "none" : "grayscale(0)",
              }}
            >
              ✋
            </span>
          }
        />

        {/* Reactions */}
        <div className="relative">
          <button
            onClick={() => setShowReactions(!showReactions)}
            className="flex flex-col items-center gap-0.5 p-3 rounded-full transition-all bg-[#3c4043] hover:bg-[#4a4d51] text-white"
            title="Reaksi"
          >
            <span style={{ fontSize: "18px" }}>😊</span>
          </button>
          <AnimatePresence>
            {showReactions && (
              <motion.div
                initial={{ opacity: 0, y: 8, scale: 0.9 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, y: 8, scale: 0.9 }}
                transition={{ duration: 0.15 }}
                className="absolute bottom-16 left-1/2 -translate-x-1/2 bg-[#3c4043] rounded-2xl shadow-2xl p-2 flex gap-1 z-50"
              >
                {REACTIONS.map((emoji) => (
                  <button
                    key={emoji}
                    onClick={() => {
                      onReaction(emoji);
                      setShowReactions(false);
                    }}
                    className="p-2 rounded-xl hover:bg-white/10 transition-colors text-2xl"
                  >
                    {emoji}
                  </button>
                ))}
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {/* More Options */}
        <div className="relative">
          <button
            onClick={() => setShowMoreMenu(!showMoreMenu)}
            className="flex items-center justify-center w-12 h-12 rounded-full bg-[#3c4043] hover:bg-[#4a4d51] text-white transition-all"
            title="Opsi lainnya"
          >
            <EllipsisVertical size={20} />
          </button>
          <AnimatePresence>
            {showMoreMenu && (
              <motion.div
                initial={{ opacity: 0, y: 8, scale: 0.95 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, y: 8, scale: 0.95 }}
                transition={{ duration: 0.15 }}
                className="absolute bottom-16 left-1/2 -translate-x-1/2 bg-[#3c4043] rounded-xl shadow-2xl py-2 min-w-[200px] z-50"
              >
                <MenuItem icon={<Settings size={16} />} label="Pengaturan" />
                <MenuItem icon={<Subtitles size={16} />} label="Aktifkan subtitel" />
                <MenuItem icon={<Shield size={16} />} label="Kontrol host" />
                <div className="border-t border-white/10 my-1" />
                <MenuItem icon={<Info size={16} />} label="Detail rapat" onClick={handleCopyLink} />
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {/* End Call */}
        <button
          onClick={onEndCall}
          className="flex items-center gap-2 px-5 py-3 rounded-full bg-red-500 hover:bg-red-600 text-white transition-all ml-2"
          title="Akhiri panggilan"
        >
          <PhoneOff size={20} />
        </button>
      </div>

      {/* Right: View controls */}
      <div className="flex items-center gap-2 justify-end w-48">
        {/* View Mode Toggle */}
        <button
          onClick={onToggleViewMode}
          className={`p-2.5 rounded-full transition-all ${
            viewMode === "spotlight"
              ? "bg-blue-500/20 text-blue-400"
              : "bg-[#3c4043] text-gray-300 hover:bg-[#4a4d51] hover:text-white"
          }`}
          title={viewMode === "grid" ? "Mode sorotan" : "Mode grid"}
        >
          {viewMode === "grid" ? <Presentation size={18} /> : <LayoutGrid size={18} />}
        </button>

        {/* Chat */}
        <button
          onClick={() => onTogglePanel("chat")}
          className={`relative p-2.5 rounded-full transition-all ${
            sidePanel === "chat"
              ? "bg-blue-500/20 text-blue-400"
              : "bg-[#3c4043] text-gray-300 hover:bg-[#4a4d51] hover:text-white"
          }`}
          title="Chat"
        >
          <MessageSquare size={18} />
          {unreadCount > 0 && sidePanel !== "chat" && (
            <span className="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">
              {unreadCount > 9 ? "9+" : unreadCount}
            </span>
          )}
        </button>

        {/* Participants */}
        <button
          onClick={() => onTogglePanel("participants")}
          className={`relative p-2.5 rounded-full transition-all ${
            sidePanel === "participants"
              ? "bg-blue-500/20 text-blue-400"
              : "bg-[#3c4043] text-gray-300 hover:bg-[#4a4d51] hover:text-white"
          }`}
          title={`Peserta (${participantCount})`}
        >
          <Users size={18} />
          <span className="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-[#1a73e8] text-white text-xs flex items-center justify-center">
            {participantCount}
          </span>
        </button>
      </div>
    </div>
  );
}

function ControlButton({
  active,
  activeColor,
  inactiveColor,
  onClick,
  label,
  icon,
  activeWhenOn,
}: {
  active: boolean;
  activeColor: string;
  inactiveColor: string;
  onClick: () => void;
  label: string;
  icon: React.ReactNode;
  activeWhenOn?: boolean;
}) {
  const isOn = activeWhenOn ? !active : !active;
  // For mic/camera: active=true means ON (blue bg), active=false means OFF (red bg)
  // For screen share/hand: activeWhenOn=true means we highlight when ON
  const bg = activeWhenOn
    ? active
      ? "#3c4043" // off = normal
      : inactiveColor // on = colored
    : active
    ? "#3c4043" // on = normal
    : inactiveColor; // off = red

  return (
    <button
      onClick={onClick}
      className="flex items-center justify-center w-12 h-12 rounded-full transition-all hover:opacity-90 active:scale-95 text-white"
      style={{ backgroundColor: bg }}
      title={label}
    >
      {icon}
    </button>
  );
}

function MenuItem({
  icon,
  label,
  onClick,
}: {
  icon: React.ReactNode;
  label: string;
  onClick?: () => void;
}) {
  return (
    <button
      className="w-full px-4 py-2.5 text-left text-sm text-white hover:bg-white/10 flex items-center gap-3 transition-colors"
      onClick={onClick}
    >
      <span className="text-gray-400">{icon}</span>
      {label}
    </button>
  );
}
