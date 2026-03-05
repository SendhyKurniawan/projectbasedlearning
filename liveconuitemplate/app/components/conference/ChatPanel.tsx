import React, { useState, useRef, useEffect } from "react";
import { Send, X, Smile } from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { ChatMessage } from "./types";
import { getInitials } from "./mockData";

interface ChatPanelProps {
  messages: ChatMessage[];
  onSendMessage: (text: string) => void;
  onClose: () => void;
}

const EMOJI_LIST = ["👍", "❤️", "😂", "😮", "👏", "🎉", "🔥", "💯", "✅", "🙏"];

export function ChatPanel({ messages, onSendMessage, onClose }: ChatPanelProps) {
  const [input, setInput] = useState("");
  const [showEmoji, setShowEmoji] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLTextAreaElement>(null);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages]);

  const handleSend = () => {
    const text = input.trim();
    if (!text) return;
    onSendMessage(text);
    setInput("");
    setShowEmoji(false);
    inputRef.current?.focus();
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const formatTime = (date: Date) => {
    return date.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
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
        <span className="text-white font-medium">Pesan dalam rapat</span>
        <button
          onClick={onClose}
          className="p-1.5 rounded-full hover:bg-white/10 text-gray-400 hover:text-white transition-colors"
        >
          <X size={18} />
        </button>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto p-3 space-y-3 min-h-0">
        <p className="text-center text-xs text-gray-500 py-2">
          Pesan hanya terlihat oleh peserta dalam rapat ini
        </p>
        <AnimatePresence initial={false}>
          {messages.map((msg) => (
            <motion.div
              key={msg.id}
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.2 }}
              className={`flex gap-2 ${msg.isMe ? "flex-row-reverse" : "flex-row"}`}
            >
              {/* Avatar */}
              <div
                className="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-medium shrink-0 self-end"
                style={{ background: msg.senderColor }}
              >
                {getInitials(msg.senderName)}
              </div>

              {/* Bubble */}
              <div
                className={`max-w-[75%] flex flex-col gap-0.5 ${
                  msg.isMe ? "items-end" : "items-start"
                }`}
              >
                {!msg.isMe && (
                  <span className="text-xs text-gray-400 px-1">{msg.senderName}</span>
                )}
                <div
                  className={`px-3 py-2 rounded-2xl text-sm text-white ${
                    msg.isMe
                      ? "rounded-br-sm bg-[#1a73e8]"
                      : "rounded-bl-sm bg-[#3c4043]"
                  }`}
                >
                  {msg.message}
                </div>
                <span className="text-xs text-gray-500 px-1">
                  {formatTime(msg.timestamp)}
                </span>
              </div>
            </motion.div>
          ))}
        </AnimatePresence>
        <div ref={messagesEndRef} />
      </div>

      {/* Input Area */}
      <div className="p-3 border-t border-white/10 shrink-0">
        <div className="relative bg-[#3c4043] rounded-2xl overflow-hidden">
          <textarea
            ref={inputRef}
            value={input}
            onChange={(e) => setInput(e.target.value)}
            onKeyDown={handleKeyDown}
            placeholder="Kirim pesan ke semua orang..."
            rows={1}
            className="w-full bg-transparent text-white text-sm px-4 py-3 pr-24 resize-none focus:outline-none placeholder-gray-500 max-h-24 overflow-y-auto"
            style={{ scrollbarWidth: "none" }}
          />
          <div className="absolute right-2 bottom-2 flex items-center gap-1">
            <div className="relative">
              <button
                onClick={() => setShowEmoji(!showEmoji)}
                className="p-2 rounded-full hover:bg-white/10 text-gray-400 hover:text-white transition-colors"
                title="Emoji"
              >
                <Smile size={16} />
              </button>
              <AnimatePresence>
                {showEmoji && (
                  <motion.div
                    initial={{ opacity: 0, y: 8, scale: 0.9 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, y: 8, scale: 0.9 }}
                    className="absolute bottom-10 right-0 bg-[#3c4043] rounded-xl shadow-xl p-2 grid grid-cols-5 gap-1 z-50"
                  >
                    {EMOJI_LIST.map((e) => (
                      <button
                        key={e}
                        onClick={() => {
                          setInput((prev) => prev + e);
                          setShowEmoji(false);
                          inputRef.current?.focus();
                        }}
                        className="p-1.5 rounded hover:bg-white/10 text-lg"
                      >
                        {e}
                      </button>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
            <button
              onClick={handleSend}
              disabled={!input.trim()}
              className={`p-2 rounded-full transition-all ${
                input.trim()
                  ? "bg-[#1a73e8] hover:bg-blue-600 text-white"
                  : "text-gray-600 cursor-not-allowed"
              }`}
              title="Kirim pesan"
            >
              <Send size={16} />
            </button>
          </div>
        </div>
      </div>
    </motion.div>
  );
}
