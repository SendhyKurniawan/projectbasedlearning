import React, { useState, useRef, useEffect } from "react";
import { useNavigate } from "react-router";
import {
  Mic,
  MicOff,
  Video,
  VideoOff,
  ChevronDown,
  Plus,
  ArrowRight,
  Settings,
  Shield,
  Users,
  Clock,
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { generateRoomId } from "../../services/livekitService";
import { getInitials, AVATAR_COLORS } from "./mockData";

/**
 * LobbyPage - Pre-join screen
 *
 * Laravel 12 Integration:
 * =======================
 * When user clicks "Bergabung":
 * 1. Call `getLiveKitToken(roomId, userName)` from livekitService.ts
 * 2. Use the returned token & URL to connect via LiveKitRoom component
 * 3. Navigate to /room/:roomId
 *
 * Example:
 * const { token, url } = await getLiveKitToken(roomId, name);
 * navigate(`/room/${roomId}`, { state: { token, url } });
 */

export function LobbyPage() {
  const navigate = useNavigate();
  const [name, setName] = useState("Pengguna Baru");
  const [isMuted, setIsMuted] = useState(false);
  const [isVideoOff, setIsVideoOff] = useState(false);
  const [roomInput, setRoomInput] = useState("");
  const [activeTab, setActiveTab] = useState<"new" | "join">("new");
  const [isLoading, setIsLoading] = useState(false);
  const [nameColor] = useState(AVATAR_COLORS[Math.floor(Math.random() * AVATAR_COLORS.length)]);
  const [showSettings, setShowSettings] = useState(false);

  const handleCreateMeeting = async () => {
    if (!name.trim()) return;
    setIsLoading(true);
    try {
      const newRoomId = generateRoomId();
      // TODO: Call Laravel API to create meeting
      // const room = await createMeeting("Rapat Baru");
      // navigate(`/room/${room.room_id}`);
      await new Promise((r) => setTimeout(r, 800)); // Simulate API call
      navigate(`/room/${newRoomId}`);
    } catch (err) {
      console.error("Failed to create meeting:", err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleJoinMeeting = async () => {
    const id = roomInput.trim();
    if (!id || !name.trim()) return;
    setIsLoading(true);
    try {
      // TODO: Call Laravel API to get token
      // const { token, url } = await getLiveKitToken(id, name);
      await new Promise((r) => setTimeout(r, 600));
      navigate(`/room/${id}`);
    } catch (err) {
      console.error("Failed to join meeting:", err);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#202124] flex flex-col">
      {/* Header */}
      <header className="flex items-center justify-between px-6 py-4">
        <div className="flex items-center gap-3">
          <div className="flex gap-0.5">
            <div className="w-2.5 h-5 bg-blue-500 rounded-sm" />
            <div className="w-2.5 h-5 bg-green-500 rounded-sm" />
            <div className="w-2.5 h-5 bg-yellow-500 rounded-sm" />
            <div className="w-2.5 h-5 bg-red-500 rounded-sm" />
          </div>
          <div>
            <h1 className="text-white font-semibold text-lg leading-tight">LiveConf</h1>
            <p className="text-gray-500 text-xs">powered by LiveKit + Laravel 12</p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <div
            className="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-medium cursor-pointer hover:ring-2 hover:ring-white/30 transition-all"
            style={{ background: nameColor }}
          >
            {getInitials(name)}
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="flex-1 flex items-center justify-center px-4 py-8">
        <div className="w-full max-w-5xl flex flex-col lg:flex-row gap-8 items-center">
          {/* Left: Actions */}
          <div className="flex-1 max-w-md w-full">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.4 }}
            >
              <h2 className="text-white text-3xl mb-2">
                Video call & konferensi
              </h2>
              <p className="text-gray-400 mb-8">
                Hubungkan, kolaborasi, dan rayakan dari mana saja menggunakan LiveKit + Laravel 12
              </p>

              {/* Name Input */}
              <div className="mb-6">
                <label className="text-gray-400 text-sm mb-2 block">Nama tampilan</label>
                <input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full bg-[#3c4043] text-white px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 placeholder-gray-500 text-sm"
                  placeholder="Masukkan nama Anda..."
                />
              </div>

              {/* Tabs */}
              <div className="flex bg-[#3c4043] rounded-xl p-1 mb-4">
                <button
                  onClick={() => setActiveTab("new")}
                  className={`flex-1 py-2.5 rounded-lg text-sm font-medium transition-all ${
                    activeTab === "new"
                      ? "bg-[#1a73e8] text-white shadow"
                      : "text-gray-400 hover:text-white"
                  }`}
                >
                  Rapat Baru
                </button>
                <button
                  onClick={() => setActiveTab("join")}
                  className={`flex-1 py-2.5 rounded-lg text-sm font-medium transition-all ${
                    activeTab === "join"
                      ? "bg-[#1a73e8] text-white shadow"
                      : "text-gray-400 hover:text-white"
                  }`}
                >
                  Gabung Rapat
                </button>
              </div>

              <AnimatePresence mode="wait">
                {activeTab === "new" ? (
                  <motion.div
                    key="new"
                    initial={{ opacity: 0, x: -10 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: 10 }}
                    transition={{ duration: 0.2 }}
                  >
                    <button
                      onClick={handleCreateMeeting}
                      disabled={!name.trim() || isLoading}
                      className="w-full flex items-center justify-center gap-2 bg-[#1a73e8] hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white py-3.5 rounded-xl transition-all font-medium"
                    >
                      {isLoading ? (
                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      ) : (
                        <Plus size={18} />
                      )}
                      {isLoading ? "Membuat rapat..." : "Buat Rapat Baru"}
                    </button>
                    <p className="text-gray-500 text-xs mt-3 text-center">
                      Tautan rapat akan dibuat secara otomatis
                    </p>
                  </motion.div>
                ) : (
                  <motion.div
                    key="join"
                    initial={{ opacity: 0, x: 10 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -10 }}
                    transition={{ duration: 0.2 }}
                    className="flex flex-col gap-3"
                  >
                    <input
                      type="text"
                      value={roomInput}
                      onChange={(e) => setRoomInput(e.target.value)}
                      onKeyDown={(e) => e.key === "Enter" && handleJoinMeeting()}
                      placeholder="Masukkan kode atau tautan rapat..."
                      className="w-full bg-[#3c4043] text-white px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 placeholder-gray-500 text-sm"
                    />
                    <button
                      onClick={handleJoinMeeting}
                      disabled={!roomInput.trim() || !name.trim() || isLoading}
                      className="w-full flex items-center justify-center gap-2 bg-[#1a73e8] hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white py-3.5 rounded-xl transition-all font-medium"
                    >
                      {isLoading ? (
                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      ) : (
                        <ArrowRight size={18} />
                      )}
                      {isLoading ? "Bergabung..." : "Bergabung"}
                    </button>
                  </motion.div>
                )}
              </AnimatePresence>

              {/* Feature highlights */}
              <div className="mt-8 grid grid-cols-3 gap-3">
                {[
                  { icon: <Shield size={16} />, label: "Enkripsi E2E" },
                  { icon: <Users size={16} />, label: "Multi peserta" },
                  { icon: <Clock size={16} />, label: "Rapat tak terbatas" },
                ].map((f) => (
                  <div
                    key={f.label}
                    className="flex flex-col items-center gap-1.5 p-3 bg-[#3c4043] rounded-xl"
                  >
                    <span className="text-[#1a73e8]">{f.icon}</span>
                    <span className="text-gray-400 text-xs text-center">{f.label}</span>
                  </div>
                ))}
              </div>
            </motion.div>
          </div>

          {/* Right: Camera Preview */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.4, delay: 0.1 }}
            className="flex-1 max-w-md w-full"
          >
            <div className="relative rounded-2xl overflow-hidden bg-[#3c4043] aspect-video flex items-center justify-center">
              {/* Simulated camera preview */}
              {isVideoOff ? (
                <div className="flex flex-col items-center gap-3">
                  <div
                    className="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-medium"
                    style={{ background: nameColor }}
                  >
                    {getInitials(name || "?")}
                  </div>
                  <p className="text-gray-400 text-sm">Kamera mati</p>
                </div>
              ) : (
                <div
                  className="w-full h-full flex items-center justify-center"
                  style={{
                    background: `radial-gradient(ellipse at 50% 50%, ${nameColor}33 0%, #2a2a2a 70%)`,
                  }}
                >
                  <div
                    className="w-24 h-24 rounded-full flex items-center justify-center text-white text-3xl font-medium"
                    style={{ background: nameColor }}
                  >
                    {getInitials(name || "?")}
                  </div>
                </div>
              )}

              {/* Preview Controls */}
              <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-3">
                <button
                  onClick={() => setIsMuted(!isMuted)}
                  className={`p-3 rounded-full transition-all ${
                    isMuted ? "bg-red-500 text-white" : "bg-black/50 text-white hover:bg-black/70"
                  }`}
                  title={isMuted ? "Aktifkan Mikrofon" : "Matikan Mikrofon"}
                >
                  {isMuted ? <MicOff size={18} /> : <Mic size={18} />}
                </button>
                <button
                  onClick={() => setIsVideoOff(!isVideoOff)}
                  className={`p-3 rounded-full transition-all ${
                    isVideoOff ? "bg-red-500 text-white" : "bg-black/50 text-white hover:bg-black/70"
                  }`}
                  title={isVideoOff ? "Aktifkan Kamera" : "Matikan Kamera"}
                >
                  {isVideoOff ? <VideoOff size={18} /> : <Video size={18} />}
                </button>
                <button
                  onClick={() => setShowSettings(!showSettings)}
                  className="p-3 rounded-full bg-black/50 text-white hover:bg-black/70 transition-all"
                  title="Pengaturan"
                >
                  <Settings size={18} />
                </button>
              </div>

              {/* Status Indicators */}
              <div className="absolute top-3 left-3 flex items-center gap-2">
                {isMuted && (
                  <span className="flex items-center gap-1.5 bg-red-500/80 text-white text-xs px-2.5 py-1 rounded-full">
                    <MicOff size={11} /> Mik mati
                  </span>
                )}
                {isVideoOff && (
                  <span className="flex items-center gap-1.5 bg-red-500/80 text-white text-xs px-2.5 py-1 rounded-full">
                    <VideoOff size={11} /> Kamera mati
                  </span>
                )}
              </div>

              {/* Settings Panel */}
              <AnimatePresence>
                {showSettings && (
                  <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 10 }}
                    className="absolute inset-0 bg-[#202124]/95 flex flex-col p-4 gap-3"
                  >
                    <div className="flex items-center justify-between">
                      <span className="text-white font-medium text-sm">Pengaturan Audio/Video</span>
                      <button
                        onClick={() => setShowSettings(false)}
                        className="text-gray-400 hover:text-white"
                      >
                        ✕
                      </button>
                    </div>
                    <SettingSelect label="Mikrofon" options={["Mikrofon Default", "Mikrofon Built-in"]} />
                    <SettingSelect label="Speaker" options={["Speaker Default", "Speaker Built-in"]} />
                    <SettingSelect label="Kamera" options={["Kamera Default", "FaceTime HD Camera"]} />
                    <SettingSelect label="Resolusi" options={["720p (Direkomendasikan)", "1080p", "480p"]} />
                  </motion.div>
                )}
              </AnimatePresence>
            </div>

            {/* Device status */}
            <div className="mt-3 flex items-center justify-center gap-4 text-xs text-gray-500">
              <span className={`flex items-center gap-1 ${isMuted ? "text-red-400" : "text-green-400"}`}>
                <span className={`w-1.5 h-1.5 rounded-full ${isMuted ? "bg-red-400" : "bg-green-400"}`} />
                {isMuted ? "Mikrofon mati" : "Mikrofon aktif"}
              </span>
              <span className={`flex items-center gap-1 ${isVideoOff ? "text-red-400" : "text-green-400"}`}>
                <span className={`w-1.5 h-1.5 rounded-full ${isVideoOff ? "bg-red-400" : "bg-green-400"}`} />
                {isVideoOff ? "Kamera mati" : "Kamera aktif"}
              </span>
            </div>
          </motion.div>
        </div>
      </main>

      {/* Footer */}
      <footer className="px-6 py-4 text-center">
        <p className="text-gray-600 text-xs">
          LiveConf menggunakan{" "}
          <span className="text-gray-500">LiveKit</span> untuk real-time video &{" "}
          <span className="text-gray-500">Laravel 12</span> untuk backend API
        </p>
      </footer>
    </div>
  );
}

function SettingSelect({ label, options }: { label: string; options: string[] }) {
  return (
    <div className="flex flex-col gap-1">
      <label className="text-gray-400 text-xs">{label}</label>
      <select className="bg-[#3c4043] text-white text-sm px-3 py-2 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
        {options.map((o) => (
          <option key={o} value={o}>
            {o}
          </option>
        ))}
      </select>
    </div>
  );
}
