import React from "react";
import { AnimatePresence, motion } from "motion/react";
import { MockParticipant, ViewMode } from "./types";
import { VideoTile } from "./VideoTile";
import { ScreenShareTile } from "./ScreenShareTile";

interface VideoGridProps {
  participants: MockParticipant[];
  viewMode: ViewMode;
  pinnedParticipantId: string | null;
  isScreenSharing: boolean;
  screenSharerName: string;
  screenSharerColor: string;
  onPin: (id: string) => void;
  onStopScreenShare: () => void;
  hasSidePanel: boolean;
}

// Hitung kolom & baris optimal untuk n peserta
function getGridLayout(count: number, hasSidePanel: boolean) {
  if (count === 1) return { cols: 1, rows: 1 };
  if (count === 2) return { cols: 2, rows: 1 };
  if (count === 3) return hasSidePanel ? { cols: 2, rows: 2 } : { cols: 3, rows: 1 };
  if (count === 4) return { cols: 2, rows: 2 };
  if (count <= 6) return hasSidePanel ? { cols: 2, rows: 3 } : { cols: 3, rows: 2 };
  if (count <= 9) return { cols: 3, rows: 3 };
  return { cols: 4, rows: Math.ceil(count / 4) };
}

export function VideoGrid({
  participants,
  viewMode,
  pinnedParticipantId,
  isScreenSharing,
  screenSharerName,
  screenSharerColor,
  onPin,
  onStopScreenShare,
  hasSidePanel,
}: VideoGridProps) {

  // ══════════════════════════════════════════════════
  // MODE 1: SCREEN SHARE — featured screen + strip bawah
  // ══════════════════════════════════════════════════
  if (isScreenSharing) {
    return (
      <div className="flex flex-col w-full h-full gap-2 p-2">
        {/* Layar utama – mengisi sisa ruang */}
        <div className="flex-1 min-h-0">
          <ScreenShareTile
            sharerName={screenSharerName}
            sharerColor={screenSharerColor}
            onStopShare={onStopScreenShare}
            isMe
          />
        </div>

        {/* Strip peserta di bawah – tinggi tetap */}
        <div
          className="flex gap-2 overflow-x-auto shrink-0 pb-0.5"
          style={{ height: "116px" }}
        >
          <AnimatePresence>
            {participants.map((p) => (
              <motion.div
                key={p.id}
                className="shrink-0 h-full rounded-xl overflow-hidden"
                style={{ width: "156px" }}
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 20 }}
                transition={{ duration: 0.2 }}
              >
                <VideoTile participant={p} onPin={onPin} />
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
      </div>
    );
  }

  // ══════════════════════════════════════════════════
  // MODE 2: SPOTLIGHT — 1 peserta besar + strip kanan
  // ══════════════════════════════════════════════════
  if (viewMode === "spotlight" || pinnedParticipantId) {
    const featured = pinnedParticipantId
      ? participants.find((p) => p.id === pinnedParticipantId)
      : participants.find((p) => p.isSpeaking) ?? participants[0];

    const rest = participants.filter((p) => p.id !== featured?.id);

    return (
      <div className="flex w-full h-full gap-2 p-2">
        {/* ─ Featured tile ─ */}
        <div className="flex-1 min-w-0 h-full">
          {featured && (
            <VideoTile participant={featured} isLarge onPin={onPin} />
          )}
        </div>

        {/* ─ Strip kanan ─ */}
        {rest.length > 0 && (
          <div
            className="flex flex-col gap-2 overflow-y-auto overflow-x-hidden shrink-0 h-full pr-0.5"
            style={{ width: "152px" }}
          >
            <AnimatePresence>
              {rest.map((p) => (
                <motion.div
                  key={p.id}
                  className="shrink-0 rounded-xl overflow-hidden"
                  style={{ height: "108px", width: "152px" }}
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 20 }}
                  transition={{ duration: 0.2 }}
                >
                  <VideoTile participant={p} onPin={onPin} />
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}
      </div>
    );
  }

  // ══════════════════════════════════════════════════
  // MODE 3: GRID — tata letak grid adaptif
  // ══════════════════════════════════════════════════
  const count = participants.length;
  const { cols, rows } = getGridLayout(count, hasSidePanel);

  return (
    <div
      className="w-full h-full p-2"
      style={{
        display: "grid",
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gridTemplateRows: `repeat(${rows}, 1fr)`,
        gap: "8px",
      }}
    >
      <AnimatePresence>
        {participants.map((participant) => (
          <VideoTile
            key={participant.id}
            participant={participant}
            isLarge={count === 1}
            onPin={onPin}
          />
        ))}
      </AnimatePresence>
    </div>
  );
}
