import React, { useState, useEffect } from "react";
import { MonitorX, Monitor, Maximize2 } from "lucide-react";
import { motion } from "motion/react";

interface ScreenShareTileProps {
  sharerName: string;
  sharerColor: string;
  onStopShare?: () => void;
  isMe?: boolean;
}

// Simulasi konten layar yang dibagikan (presentasi / kode / browser)
const MOCK_SCREENS = ["presentation", "code", "browser"] as const;
type ScreenType = (typeof MOCK_SCREENS)[number];

function PresentationMock() {
  return (
    <div className="w-full h-full flex flex-col bg-white rounded overflow-hidden">
      {/* Slide header */}
      <div className="bg-[#1a73e8] px-8 py-6 flex-1 flex flex-col items-center justify-center gap-4">
        <div className="text-white text-center">
          <div className="h-3 bg-white/80 rounded w-64 mb-3 mx-auto" />
          <div className="h-2 bg-white/50 rounded w-48 mx-auto" />
        </div>
        <div className="grid grid-cols-3 gap-3 w-full max-w-xs mt-4">
          {[...Array(3)].map((_, i) => (
            <div key={i} className="bg-white/20 rounded-lg p-3 flex flex-col gap-1.5">
              <div className="h-2 bg-white/70 rounded" />
              <div className="h-1.5 bg-white/40 rounded w-3/4" />
              <div className="h-1.5 bg-white/40 rounded w-1/2" />
            </div>
          ))}
        </div>
      </div>
      {/* Slide footer */}
      <div className="bg-gray-100 px-4 py-1.5 flex items-center justify-between">
        <div className="flex gap-2">
          {[...Array(5)].map((_, i) => (
            <div
              key={i}
              className={`h-1.5 w-6 rounded-full ${i === 1 ? "bg-blue-500" : "bg-gray-300"}`}
            />
          ))}
        </div>
        <span className="text-gray-400" style={{ fontSize: "10px" }}>
          Slide 2 / 8
        </span>
      </div>
    </div>
  );
}

function CodeMock() {
  const lines = [
    { indent: 0, content: "class MeetingController extends Controller", color: "#569cd6" },
    { indent: 0, content: "{", color: "#d4d4d4" },
    { indent: 1, content: "public function getToken(Request $request)", color: "#dcdcaa" },
    { indent: 1, content: "{", color: "#d4d4d4" },
    { indent: 2, content: "$accessToken = new AccessToken(", color: "#d4d4d4" },
    { indent: 3, content: "env('LIVEKIT_API_KEY'),", color: "#ce9178" },
    { indent: 3, content: "env('LIVEKIT_API_SECRET')", color: "#ce9178" },
    { indent: 2, content: ");", color: "#d4d4d4" },
    { indent: 2, content: "$grant = new VideoGrant();", color: "#d4d4d4" },
    { indent: 2, content: "$grant->setRoomJoin(true);", color: "#4ec9b0" },
    { indent: 2, content: "$grant->setRoomName($request->room);", color: "#4ec9b0" },
    { indent: 2, content: "$accessToken->addGrant($grant);", color: "#d4d4d4" },
    { indent: 0, content: "", color: "" },
    { indent: 2, content: "return response()->json([", color: "#d4d4d4" },
    { indent: 3, content: "'token' => $accessToken->toJwt(),", color: "#ce9178" },
    { indent: 3, content: "'url' => env('LIVEKIT_URL'),", color: "#ce9178" },
    { indent: 2, content: "]);", color: "#d4d4d4" },
    { indent: 1, content: "}", color: "#d4d4d4" },
    { indent: 0, content: "}", color: "#d4d4d4" },
  ];

  return (
    <div className="w-full h-full flex flex-col bg-[#1e1e1e] rounded overflow-hidden">
      {/* VSCode-like title bar */}
      <div className="bg-[#323233] px-3 py-1.5 flex items-center gap-2 shrink-0">
        <div className="flex gap-1.5">
          <div className="w-2.5 h-2.5 rounded-full bg-[#ff5f57]" />
          <div className="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]" />
          <div className="w-2.5 h-2.5 rounded-full bg-[#28c840]" />
        </div>
        <span className="text-gray-400 text-xs ml-2">MeetingController.php — Laravel 12</span>
      </div>
      {/* Code */}
      <div className="flex-1 overflow-hidden p-3">
        {lines.map((line, i) => (
          <div key={i} className="flex items-center gap-2" style={{ marginBottom: "1px" }}>
            <span className="text-gray-600 w-4 shrink-0" style={{ fontSize: "9px" }}>
              {i + 1}
            </span>
            <span
              style={{
                paddingLeft: `${line.indent * 14}px`,
                color: line.color,
                fontSize: "9px",
                fontFamily: "monospace",
                whiteSpace: "nowrap",
              }}
            >
              {line.content}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}

function BrowserMock() {
  return (
    <div className="w-full h-full flex flex-col bg-white rounded overflow-hidden">
      {/* Browser chrome */}
      <div className="bg-[#f1f3f4] px-3 py-2 flex items-center gap-2 shrink-0 border-b border-gray-200">
        <div className="flex gap-1.5">
          <div className="w-2.5 h-2.5 rounded-full bg-[#ff5f57]" />
          <div className="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]" />
          <div className="w-2.5 h-2.5 rounded-full bg-[#28c840]" />
        </div>
        <div className="flex-1 bg-white rounded-full px-3 py-0.5 flex items-center gap-1.5 border border-gray-200">
          <div className="w-2 h-2 rounded-full bg-green-500" />
          <span className="text-gray-500" style={{ fontSize: "10px" }}>
            https://livekit.io/docs/getting-started
          </span>
        </div>
      </div>
      {/* Page content */}
      <div className="flex-1 p-4 overflow-hidden flex gap-4">
        <div className="w-36 shrink-0 flex flex-col gap-2">
          <div className="h-2 bg-blue-500 rounded w-full" />
          {[...Array(6)].map((_, i) => (
            <div key={i} className="h-2 bg-gray-200 rounded" style={{ width: `${60 + i * 8}%` }} />
          ))}
        </div>
        <div className="flex-1 flex flex-col gap-2">
          <div className="h-3 bg-gray-800 rounded w-3/4" />
          <div className="h-2 bg-gray-300 rounded w-full" />
          <div className="h-2 bg-gray-300 rounded w-5/6" />
          <div className="h-2 bg-gray-300 rounded w-4/5" />
          <div className="h-12 bg-gray-100 rounded mt-1 border border-gray-200" />
          <div className="h-2 bg-gray-300 rounded w-full" />
          <div className="h-2 bg-gray-300 rounded w-3/4" />
        </div>
      </div>
    </div>
  );
}

export function ScreenShareTile({
  sharerName,
  sharerColor,
  onStopShare,
  isMe,
}: ScreenShareTileProps) {
  const [screenType] = useState<ScreenType>("code");

  return (
    <div className="relative w-full h-full rounded-xl overflow-hidden bg-[#1a1a2e] flex flex-col">
      {/* Screen content area */}
      <div className="flex-1 min-h-0 p-3">
        {screenType === "presentation" && <PresentationMock />}
        {screenType === "code" && <CodeMock />}
        {screenType === "browser" && <BrowserMock />}
      </div>

      {/* Top badge */}
      <div className="absolute top-3 left-3 flex items-center gap-2 bg-black/70 backdrop-blur-sm rounded-full px-3 py-1.5 z-10">
        <motion.div
          className="w-2 h-2 rounded-full bg-green-400"
          animate={{ opacity: [1, 0.4, 1] }}
          transition={{ duration: 1.5, repeat: Infinity }}
        />
        <Monitor size={12} className="text-green-400" />
        <span className="text-white text-xs">
          {isMe ? "Anda sedang berbagi layar" : `${sharerName} sedang berbagi layar`}
        </span>
      </div>

      {/* Stop sharing button (only for self) */}
      {isMe && onStopShare && (
        <div className="absolute top-3 right-3 z-10">
          <button
            onClick={onStopShare}
            className="flex items-center gap-1.5 bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-full transition-colors"
          >
            <MonitorX size={12} />
            Hentikan Berbagi
          </button>
        </div>
      )}

      {/* Bottom sharer info */}
      <div className="absolute bottom-0 left-0 right-0 px-3 py-2 bg-gradient-to-t from-black/70 to-transparent flex items-center gap-2">
        <div
          className="w-5 h-5 rounded-full flex items-center justify-center text-white shrink-0"
          style={{ background: sharerColor, fontSize: "9px" }}
        >
          {sharerName.charAt(0).toUpperCase()}
        </div>
        <span className="text-white text-xs">{sharerName}</span>
        <span className="text-gray-400 text-xs ml-auto flex items-center gap-1">
          <Maximize2 size={10} /> Layar utama
        </span>
      </div>
    </div>
  );
}
