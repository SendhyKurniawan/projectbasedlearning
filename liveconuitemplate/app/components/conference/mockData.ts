import { MockParticipant, ChatMessage } from "./types";

export const MOCK_PARTICIPANTS: MockParticipant[] = [
  {
    id: "me",
    name: "Anda (Me)",
    color: "#1a73e8",
    isMuted: false,
    isVideoOff: false,
    isSpeaking: false,
    isHandRaised: false,
    isPinned: false,
    isHost: true,
    isMe: true,
  },
  {
    id: "p1",
    name: "Budi Santoso",
    color: "#0f9d58",
    isMuted: false,
    isVideoOff: false,
    isSpeaking: true,
    isHandRaised: false,
    isPinned: false,
    isHost: false,
  },
  {
    id: "p2",
    name: "Siti Rahayu",
    color: "#f4b400",
    isMuted: true,
    isVideoOff: false,
    isSpeaking: false,
    isHandRaised: true,
    isPinned: false,
    isHost: false,
  },
  {
    id: "p3",
    name: "Ahmad Fauzi",
    color: "#db4437",
    isMuted: false,
    isVideoOff: true,
    isSpeaking: false,
    isHandRaised: false,
    isPinned: false,
    isHost: false,
  },
  {
    id: "p4",
    name: "Dewi Lestari",
    color: "#9c27b0",
    isMuted: true,
    isVideoOff: true,
    isSpeaking: false,
    isHandRaised: false,
    isPinned: false,
    isHost: false,
  },
  {
    id: "p5",
    name: "Reza Pratama",
    color: "#00bcd4",
    isMuted: false,
    isVideoOff: false,
    isSpeaking: false,
    isHandRaised: false,
    isPinned: false,
    isHost: false,
  },
];

export const INITIAL_MESSAGES: ChatMessage[] = [
  {
    id: "msg1",
    senderId: "p1",
    senderName: "Budi Santoso",
    senderColor: "#0f9d58",
    message: "Halo semua, sudah bisa bergabung?",
    timestamp: new Date(Date.now() - 5 * 60 * 1000),
    isMe: false,
  },
  {
    id: "msg2",
    senderId: "p2",
    senderName: "Siti Rahayu",
    senderColor: "#f4b400",
    message: "Sudah! Audio dan video saya sudah aktif 👍",
    timestamp: new Date(Date.now() - 4 * 60 * 1000),
    isMe: false,
  },
  {
    id: "msg3",
    senderId: "me",
    senderName: "Anda",
    senderColor: "#1a73e8",
    message: "Baik, kita mulai rapat sekarang ya. Agenda hari ini adalah review sprint Q1.",
    timestamp: new Date(Date.now() - 3 * 60 * 1000),
    isMe: true,
  },
  {
    id: "msg4",
    senderId: "p3",
    senderName: "Ahmad Fauzi",
    senderColor: "#db4437",
    message: "Siap! Saya sudah menyiapkan presentasinya.",
    timestamp: new Date(Date.now() - 2 * 60 * 1000),
    isMe: false,
  },
];

export const AVATAR_COLORS = [
  "#1a73e8",
  "#0f9d58",
  "#f4b400",
  "#db4437",
  "#9c27b0",
  "#00bcd4",
  "#ff5722",
  "#607d8b",
];

export const getInitials = (name: string): string => {
  return name
    .split(" ")
    .slice(0, 2)
    .map((n) => n[0])
    .join("")
    .toUpperCase();
};
