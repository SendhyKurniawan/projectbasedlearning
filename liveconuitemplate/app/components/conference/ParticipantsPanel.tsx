import React, { useState } from "react";
import {
  X,
  Mic,
  MicOff,
  Video,
  VideoOff,
  Crown,
  Hand,
  UserPlus,
  MoreVertical,
  Pin,
  UserMinus,
  Volume2,
  Copy,
} from "lucide-react";
import { motion } from "motion/react";
import { MockParticipant } from "./types";
import { getInitials } from "./mockData";

interface ParticipantsPanelProps {
  participants: MockParticipant[];
  onClose: () => void;
  roomId: string;
}

export function ParticipantsPanel({
  participants,
  onClose,
  roomId,
}: ParticipantsPanelProps) {
  const [search, setSearch] = useState("");
  const [openMenuId, setOpenMenuId] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);

  const filtered = participants.filter((p) =>
    p.name.toLowerCase().includes(search.toLowerCase())
  );

  const hosts = filtered.filter((p) => p.isHost);
  const guests = filtered.filter((p) => !p.isHost);

  const handleCopyLink = () => {
    navigator.clipboard.writeText(`${window.location.origin}/room/${roomId}`);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <motion.div
      initial={{ x: 320, opacity: 0 }}
      animate={{ x: 0, opacity: 1 }}
      exit={{ x: 320, opacity: 0 }}
      transition={{ type: "spring", damping: 25, stiffness: 200 }}
      className="flex flex-col bg-[#202124] border-l border-white/10 w-80 shrink-0 h-full"
    >
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 border-b border-white/10 shrink-0">
        <span className="text-white font-medium">
          Peserta ({participants.length})
        </span>
        <button
          onClick={onClose}
          className="p-1.5 rounded-full hover:bg-white/10 text-gray-400 hover:text-white transition-colors"
        >
          <X size={18} />
        </button>
      </div>

      {/* Search */}
      <div className="px-3 py-2 shrink-0">
        <input
          type="text"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Cari peserta..."
          className="w-full bg-[#3c4043] text-white text-sm px-3 py-2 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 placeholder-gray-500"
        />
      </div>

      {/* Invite button */}
      <div className="px-3 pb-2 shrink-0">
        <button
          onClick={handleCopyLink}
          className="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg bg-[#3c4043] hover:bg-[#4a4d51] text-white text-sm transition-colors"
        >
          <UserPlus size={16} className="text-[#1a73e8]" />
          {copied ? (
            <span className="text-green-400">Tautan disalin!</span>
          ) : (
            <span>Undang peserta</span>
          )}
          <Copy size={14} className="ml-auto text-gray-400" />
        </button>
      </div>

      {/* List */}
      <div className="flex-1 overflow-y-auto px-2 min-h-0">
        {hosts.length > 0 && (
          <div className="mb-2">
            <p className="text-xs text-gray-500 px-2 py-1 uppercase tracking-wider">
              Host ({hosts.length})
            </p>
            {hosts.map((p) => (
              <ParticipantItem
                key={p.id}
                participant={p}
                isMenuOpen={openMenuId === p.id}
                onMenuToggle={() =>
                  setOpenMenuId(openMenuId === p.id ? null : p.id)
                }
              />
            ))}
          </div>
        )}

        {guests.length > 0 && (
          <div>
            <p className="text-xs text-gray-500 px-2 py-1 uppercase tracking-wider">
              Peserta ({guests.length})
            </p>
            {guests.map((p) => (
              <ParticipantItem
                key={p.id}
                participant={p}
                isMenuOpen={openMenuId === p.id}
                onMenuToggle={() =>
                  setOpenMenuId(openMenuId === p.id ? null : p.id)
                }
              />
            ))}
          </div>
        )}

        {filtered.length === 0 && (
          <div className="text-center text-gray-500 text-sm py-8">
            Tidak ada peserta ditemukan
          </div>
        )}
      </div>

      {/* Footer Stats */}
      <div className="px-4 py-3 border-t border-white/10 shrink-0">
        <div className="flex justify-around text-center">
          <div>
            <p className="text-white font-medium">{participants.length}</p>
            <p className="text-gray-500 text-xs">Peserta</p>
          </div>
          <div>
            <p className="text-white font-medium">
              {participants.filter((p) => !p.isMuted).length}
            </p>
            <p className="text-gray-500 text-xs">Mik aktif</p>
          </div>
          <div>
            <p className="text-white font-medium">
              {participants.filter((p) => !p.isVideoOff).length}
            </p>
            <p className="text-gray-500 text-xs">Video aktif</p>
          </div>
          <div>
            <p className="text-white font-medium">
              {participants.filter((p) => p.isHandRaised).length}
            </p>
            <p className="text-gray-500 text-xs">Tangan</p>
          </div>
        </div>
      </div>
    </motion.div>
  );
}

function ParticipantItem({
  participant,
  isMenuOpen,
  onMenuToggle,
}: {
  participant: MockParticipant;
  isMenuOpen: boolean;
  onMenuToggle: () => void;
}) {
  return (
    <div className="relative flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/5 group transition-colors">
      {/* Avatar */}
      <div className="relative shrink-0">
        <div
          className="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-medium"
          style={{
            background: participant.color,
            boxShadow: participant.isSpeaking
              ? `0 0 0 2px ${participant.color}80`
              : "none",
          }}
        >
          {getInitials(participant.name)}
        </div>
        {participant.isSpeaking && (
          <span className="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-green-500 border-2 border-[#202124]" />
        )}
      </div>

      {/* Name & status */}
      <div className="flex-1 min-w-0">
        <div className="flex items-center gap-1.5">
          {participant.isHost && (
            <Crown size={12} className="text-yellow-400 shrink-0" />
          )}
          <span className="text-white text-sm truncate">
            {participant.name}
            {participant.isMe && (
              <span className="text-gray-400 ml-1">(Anda)</span>
            )}
          </span>
        </div>
        {participant.isHandRaised && (
          <p className="text-xs text-yellow-400 flex items-center gap-1">
            ✋ Tangan terangkat
          </p>
        )}
      </div>

      {/* Status Icons */}
      <div className="flex items-center gap-1 shrink-0">
        {participant.isMuted ? (
          <MicOff size={14} className="text-red-400" />
        ) : (
          <Mic
            size={14}
            className={participant.isSpeaking ? "text-green-400" : "text-gray-400"}
          />
        )}
        {participant.isVideoOff ? (
          <VideoOff size={14} className="text-red-400" />
        ) : (
          <Video size={14} className="text-gray-400" />
        )}
      </div>

      {/* More button (only for non-me) */}
      {!participant.isMe && (
        <div className="relative">
          <button
            onClick={onMenuToggle}
            className="p-1 rounded-full hover:bg-white/10 text-gray-400 hover:text-white transition-colors opacity-0 group-hover:opacity-100"
          >
            <MoreVertical size={14} />
          </button>
          {isMenuOpen && (
            <div className="absolute right-0 top-6 bg-[#3c4043] rounded-lg shadow-xl py-1 min-w-[160px] z-20">
              <button className="w-full px-3 py-2 text-left text-sm text-white hover:bg-white/10 flex items-center gap-2">
                <MicOff size={14} /> Bisukan
              </button>
              <button className="w-full px-3 py-2 text-left text-sm text-white hover:bg-white/10 flex items-center gap-2">
                <Pin size={14} /> Sematkan video
              </button>
              <button className="w-full px-3 py-2 text-left text-sm text-white hover:bg-white/10 flex items-center gap-2">
                <Volume2 size={14} /> Sorot
              </button>
              <div className="border-t border-white/10 my-1" />
              <button className="w-full px-3 py-2 text-left text-sm text-red-400 hover:bg-white/10 flex items-center gap-2">
                <UserMinus size={14} /> Keluarkan
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
