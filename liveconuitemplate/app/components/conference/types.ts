export type ViewMode = "grid" | "spotlight";

export type SidePanel = "chat" | "participants" | null;

export interface ChatMessage {
  id: string;
  senderId: string;
  senderName: string;
  senderColor: string;
  message: string;
  timestamp: Date;
  isMe?: boolean;
}

export interface MockParticipant {
  id: string;
  name: string;
  color: string;
  isMuted: boolean;
  isVideoOff: boolean;
  isSpeaking: boolean;
  isHandRaised: boolean;
  isPinned: boolean;
  isHost: boolean;
  isMe?: boolean;
  reaction?: string;
}

export interface ConferenceState {
  isMuted: boolean;
  isVideoOff: boolean;
  isScreenSharing: boolean;
  isRecording: boolean;
  isHandRaised: boolean;
  viewMode: ViewMode;
  sidePanel: SidePanel;
  participants: MockParticipant[];
  messages: ChatMessage[];
  unreadCount: number;
  pinnedParticipantId: string | null;
  reaction: string | null;
}
