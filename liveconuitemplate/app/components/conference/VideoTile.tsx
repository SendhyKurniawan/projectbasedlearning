import React, { useState, useRef, useEffect } from "react";
import {
  Mic,
  MicOff,
  Pin,
  PinOff,
  MoreVertical,
  Volume2,
  Crown,
  UserMinus,
} from "lucide-react";
import { MockParticipant } from "./types";
import { getInitials } from "./mockData";
import { motion, AnimatePresence } from "motion/react";

interface VideoTileProps {
  participant: MockParticipant;
  isLarge?: boolean;
  onPin: (id: string) => void;
}

export function VideoTile({ participant, isLarge = false, onPin }: VideoTileProps) {
  const [showMenu, setShowMenu] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);
  const initials = getInitials(participant.name);

  // Close menu on outside click
  useEffect(() => {
    if (!showMenu) return;
    const handler = (e: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        setShowMenu(false);
      }
    };
    document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, [showMenu]);

  const avatarSize = isLarge ? 88 : 52;
  const avatarFontSize = isLarge ? 30 : 18;

  return (
    // ← w-full h-full adalah kunci agar tile mengisi sel grid / flex container
    <motion.div
      className="relative rounded-xl overflow-hidden bg-[#202124] flex items-center justify-center group w-full h-full cursor-default"
      style={{
        border: participant.isSpeaking
          ? `3px solid ${participant.color}`
          : "3px solid transparent",
        transition: "border-color 0.25s ease",
      }}
      initial={{ opacity: 0, scale: 0.92 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.88 }}
      transition={{ duration: 0.2 }}
    >
      {/* ── VIDEO / AVATAR AREA ── */}
      {participant.isVideoOff ? (
        /* Camera OFF – gradient + avatar */
        <div
          className="absolute inset-0 flex items-center justify-center"
          style={{
            background: `linear-gradient(145deg, ${participant.color}bb 0%, ${participant.color}44 100%)`,
          }}
        >
          <div
            className="rounded-full flex items-center justify-center text-white font-semibold select-none shadow-lg"
            style={{
              width: avatarSize,
              height: avatarSize,
              fontSize: avatarFontSize,
              background: participant.color,
              boxShadow: `0 0 0 4px ${participant.color}44`,
            }}
          >
            {initials}
          </div>
        </div>
      ) : (
        /* Camera ON – simulated video feed */
        <div
          className="absolute inset-0 flex items-center justify-center"
          style={{
            background: `linear-gradient(135deg, ${participant.color}33 0%, #16213e 55%, ${participant.color}1a 100%)`,
          }}
        >
          <div
            className="absolute inset-0 opacity-20"
            style={{
              background: `
                radial-gradient(ellipse at 28% 38%, ${participant.color}88 0%, transparent 55%),
                radial-gradient(ellipse at 72% 62%, ${participant.color}55 0%, transparent 45%)
              `,
            }}
          />
          <div
            className="rounded-full flex items-center justify-center text-white font-semibold select-none shadow-lg z-10"
            style={{
              width: avatarSize,
              height: avatarSize,
              fontSize: avatarFontSize,
              background: participant.color,
            }}
          >
            {initials}
          </div>
          {/* Live dot */}
          <span className="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-green-400 opacity-80 z-10" />
        </div>
      )}

      {/* ── SPEAKING RING ── */}
      <AnimatePresence>
        {participant.isSpeaking && (
          <motion.div
            key="ring"
            className="absolute inset-0 rounded-xl pointer-events-none"
            style={{ boxShadow: `inset 0 0 0 3px ${participant.color}` }}
            initial={{ opacity: 0 }}
            animate={{ opacity: [0.5, 1, 0.5] }}
            exit={{ opacity: 0 }}
            transition={{ duration: 1.4, repeat: Infinity }}
          />
        )}
      </AnimatePresence>

      {/* ── HOVER OVERLAY ── */}
      <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-black/25 z-20">
        {/* Top-right action buttons */}
        <div className="absolute top-2 right-2 flex gap-1" ref={menuRef}>
          <button
            onClick={() => onPin(participant.id)}
            className="p-1.5 rounded-full bg-black/60 text-white hover:bg-black/80 backdrop-blur-sm transition-colors"
            title={participant.isPinned ? "Lepas sematan" : "Sematkan"}
          >
            {participant.isPinned ? <PinOff size={13} /> : <Pin size={13} />}
          </button>
          <button
            className="p-1.5 rounded-full bg-black/60 text-white hover:bg-black/80 backdrop-blur-sm transition-colors"
            title="Opsi lainnya"
            onClick={(e) => {
              e.stopPropagation();
              setShowMenu((v) => !v);
            }}
          >
            <MoreVertical size={13} />
          </button>

          {/* Context menu */}
          <AnimatePresence>
            {showMenu && (
              <motion.div
                initial={{ opacity: 0, scale: 0.92, y: -4 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                exit={{ opacity: 0, scale: 0.92, y: -4 }}
                transition={{ duration: 0.12 }}
                className="absolute top-8 right-0 bg-[#3c4043] rounded-xl shadow-2xl py-1.5 min-w-[148px] z-30 border border-white/10"
              >
                <CtxItem
                  icon={participant.isPinned ? <PinOff size={13} /> : <Pin size={13} />}
                  label={participant.isPinned ? "Lepas sematan" : "Sematkan video"}
                  onClick={() => { onPin(participant.id); setShowMenu(false); }}
                />
                {!participant.isMe && (
                  <>
                    <CtxItem
                      icon={<MicOff size={13} />}
                      label="Bisukan mikrofon"
                    />
                    <CtxItem
                      icon={<Volume2 size={13} />}
                      label="Sorot peserta"
                    />
                    <div className="border-t border-white/10 my-1" />
                    <CtxItem
                      icon={<UserMinus size={13} />}
                      label="Keluarkan"
                      danger
                    />
                  </>
                )}
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {/* Center — double-click to pin hint */}
        <div
          className="absolute inset-0 flex items-center justify-center"
          onDoubleClick={() => onPin(participant.id)}
        />
      </div>

      {/* ── BOTTOM NAME BAR ── */}
      <div className="absolute bottom-0 left-0 right-0 px-2 py-1.5 flex items-center justify-between bg-gradient-to-t from-black/65 to-transparent z-10 pointer-events-none">
        <div className="flex items-center gap-1.5 min-w-0">
          {participant.isHost && (
            <Crown size={11} className="text-yellow-400 shrink-0" />
          )}
          {participant.isPinned && (
            <Pin size={10} className="text-blue-400 shrink-0" />
          )}
          <span
            className="text-white truncate"
            style={{ fontSize: isLarge ? "13px" : "11px" }}
          >
            {participant.name}
            {participant.isMe && (
              <span className="text-white/50 ml-1">(Anda)</span>
            )}
          </span>
        </div>

        {/* Status icons */}
        <div className="flex items-center gap-1 shrink-0 ml-1">
          {participant.isHandRaised && (
            <span style={{ fontSize: "12px" }}>✋</span>
          )}
          {participant.reaction && (
            <motion.span
              key={participant.reaction}
              initial={{ scale: 0.5, y: 4 }}
              animate={{ scale: 1, y: 0 }}
              style={{ fontSize: "12px" }}
            >
              {participant.reaction}
            </motion.span>
          )}
          {participant.isMuted ? (
            <div className="p-0.5 rounded-full bg-red-500/80">
              <MicOff size={9} className="text-white" />
            </div>
          ) : participant.isSpeaking ? (
            <SpeakingBars />
          ) : (
            <div className="p-0.5 rounded-full bg-white/20">
              <Mic size={9} className="text-white/70" />
            </div>
          )}
        </div>
      </div>
    </motion.div>
  );
}

function CtxItem({
  icon,
  label,
  onClick,
  danger,
}: {
  icon: React.ReactNode;
  label: string;
  onClick?: () => void;
  danger?: boolean;
}) {
  return (
    <button
      className={`w-full px-3 py-2 text-left flex items-center gap-2.5 hover:bg-white/10 transition-colors ${
        danger ? "text-red-400" : "text-white"
      }`}
      style={{ fontSize: "12px" }}
      onClick={onClick}
    >
      <span className={danger ? "text-red-400" : "text-gray-400"}>{icon}</span>
      {label}
    </button>
  );
}

// Animated audio bars when speaking
function SpeakingBars() {
  return (
    <div className="flex items-end gap-px h-3 px-0.5">
      {[0.6, 1, 0.7, 0.9, 0.5].map((h, i) => (
        <motion.div
          key={i}
          className="w-0.5 bg-green-400 rounded-full"
          animate={{ scaleY: [h, 1, h * 0.4, 1, h] }}
          transition={{
            duration: 0.8,
            repeat: Infinity,
            delay: i * 0.1,
            ease: "easeInOut",
          }}
          style={{ height: "10px", transformOrigin: "bottom" }}
        />
      ))}
    </div>
  );
}
