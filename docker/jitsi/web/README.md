# Jitsi web config (PBL Workspace branding + SSO)

These files brand the **self-hosted Jitsi** at `meet.polimedia.pblworkspace.com` and wire
its login to PBL. They are version-controlled here but **deployed to `pjbl-vm`** into the
`docker-jitsi-meet` clone at `~/jitsi-meet` — they are not used by the Laravel
`docker-compose.yml` in the repo root.

| File | Purpose | Lands at (on VM) |
| --- | --- | --- |
| `custom-interface_config.js` | App name, page `<title>`, welcome-page + watermark logo, hides Jitsi brand chrome | `~/.jitsi-meet-cfg/web/custom-interface_config.js` (auto-appended to `interface_config.js` by the web container) |
| `custom-config.js` | **SSO** — `config.tokenAuthUrl` points tokenless visitors at the PBL login/mint endpoint instead of Jitsi's "Authentication required" dead end | `~/.jitsi-meet-cfg/web/custom-config.js` (auto-appended to `config.js` by the web container) |
| `pbl-logo.svg` | In-call watermark + welcome-page logo (referenced as `images/pbl-logo.svg`) | bind-mounted to `/usr/share/jitsi-meet/images/pbl-logo.svg` |
| `favicon.svg` | Browser-tab favicon for the Jitsi site | bind-mounted over `/usr/share/jitsi-meet/images/favicon.svg` (the served HTML links `images/favicon.svg?v=1`) |

**SSO (`custom-config.js`)**: when someone opens a Jitsi room without a token (e.g. via
Jitsi's in-room **Share** link), Jitsi redirects them to `tokenAuthUrl` —
`https://polimedia.pblworkspace.com/conferences/jitsi-auth?room={room}`. That PBL route
(`ConferenceJoinController@jitsiAuth`, behind `auth`) shows the PBL login if needed,
verifies the user is allowed in that room (admin / owning-dosen / enrolled-mahasiswa),
mints a per-user JWT, and redirects back to the room with `?jwt=`. So the Jitsi share link
now flows through PBL login — same auth as the rest of the app.

What the Laravel app sets vs. what these files set:

- The app's `JitsiTokenService::roomUrl()` already passes `interfaceConfig.APP_NAME`,
  `DEFAULT_LOGO_URL`, etc. via the room URL hash. That covers the **in-call** name/logo
  on Jitsi versions that still honour URL overrides.
- These server files are the **authoritative** branding and additionally cover what the
  URL hash cannot touch: the browser-tab **favicon**, the document **title**, and the
  **welcome page** (shown when a room URL has no token / is opened bare).

Deploy steps are in `docs/ops/jitsi-self-host.md` → **Step 6 — Branding**.

> Favicon note: this Jitsi build (`stable-9909`) links `images/favicon.svg?v=1`, so we
> bind-mount over the SVG. The `apple-touch-icon.png` is left as Jitsi's default (we only
> have an SVG asset); supply a PNG and add a third bind-mount if you want it branded too.
