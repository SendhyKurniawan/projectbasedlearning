/*
 * PBL Workspace — Jitsi config.js overrides (SSO).
 *
 * The docker-jitsi-meet `web` container appends this to the generated config.js
 * at startup. It wires Jitsi's authentication to PBL so a tokenless visitor
 * (e.g. someone opening Jitsi's in-room "Share" link) is redirected to the PBL
 * app to log in, rather than hitting the dead-end "Authentication required" wall.
 *
 * Flow: no token -> Jitsi redirects to tokenAuthUrl (PBL, {room} substituted)
 *   -> PBL `auth` middleware -> login if needed -> ConferenceJoinController
 *   verifies access, mints a per-user JWT, redirects back to the room with ?jwt=.
 *
 * Deploy: copy to ~/.jitsi-meet-cfg/web/custom-config.js on pjbl-vm, then
 * `docker compose restart web`. See docs/ops/jitsi-self-host.md Step 7.
 */
config.tokenAuthUrl = 'https://polimedia.pblworkspace.com/conferences/jitsi-auth?room={room}';

// Skip the intermediate "I am the host / authenticate" button — go straight to PBL.
config.tokenAuthUrlAutoRedirect = true;
