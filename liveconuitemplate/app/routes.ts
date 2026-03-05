import { createBrowserRouter } from "react-router";
import { LobbyPage } from "./components/conference/LobbyPage";
import { ConferenceRoom } from "./components/conference/ConferenceRoom";

export const router = createBrowserRouter([
  {
    path: "/",
    Component: LobbyPage,
  },
  {
    path: "/room/:roomId",
    Component: ConferenceRoom,
  },
]);
