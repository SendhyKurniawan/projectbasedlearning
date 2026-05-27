# Ops handoff — Self-hosted Jitsi on `pjbl-vm`

This document covers everything that runs on the GCP VM. The Laravel-side code changes (controllers, service class, env keys, views, JS) are already committed; what remains is the VM-side work: DNS, firewall, Caddy reverse proxy, Jitsi stack, and final cutover.

**Target VM**: `pjbl-vm` in project `pjbl-app-btgs6`, IP `34.50.107.24`.
**Target domains**:
- `polimedia.pblworkspace.com` → Laravel app (HTTPS, port 443 via Caddy)
- `meet.polimedia.pblworkspace.com` → Jitsi (HTTPS, port 443 via Caddy)

## Pacing rule — apply between steps

After finishing each numbered step below, run `/usage` in the Claude Code session. If the 5-hour usage bar is ≥ ~80%, **stop**, append a "Progress log" entry to `~/.claude/plans/i-need-you-to-glowing-lerdorf.md` (last step done, in-flight VM state, next sub-step), and use `ScheduleWakeup` to resume after the usage window resets. See memory `feedback_usage_threshold_schedule`.

---

## Step 0 — Prerequisites

### 0.1 DNS records

Create two `A` records pointing at `34.50.107.24`:
- `polimedia.pblworkspace.com` → `34.50.107.24`
- `meet.polimedia.pblworkspace.com` → `34.50.107.24`

Wait for propagation, then verify from any machine:
```bash
dig +short polimedia.pblworkspace.com
dig +short meet.polimedia.pblworkspace.com
```
Both must return `34.50.107.24` before continuing — Caddy's Let's Encrypt challenge will fail otherwise.

### 0.2 Check VM size

```bash
gcloud compute instances describe pjbl-vm --zone <zone> \
  --format="value(machineType.basename())"
```

- `e2-standard-4` (4 vCPU / 16 GB) or larger → proceed as-is.
- Smaller (e.g. `e2-medium` = 2 vCPU / 4 GB) → resize first:
  ```bash
  gcloud compute instances stop pjbl-vm --zone <zone>
  gcloud compute instances set-machine-type pjbl-vm --zone <zone> \
    --machine-type=e2-standard-4
  gcloud compute instances start pjbl-vm --zone <zone>
  ```
- If you expect a single big 30-person room (worst case), use `e2-standard-8` instead.

### 0.3 Firewall

Open the media ports for Jitsi:
```bash
gcloud compute firewall-rules create allow-jitsi-media \
  --network default --direction INGRESS \
  --action allow --rules udp:10000,tcp:4443 \
  --source-ranges 0.0.0.0/0
```

Confirm 80 + 443 are already allowed (they should be, for the existing setup). Once Caddy is in front, **remove any public allow on 8000** — that port should not be reachable from outside the VM after cutover.

---

## Step 1 — Install Caddy on the host

Caddy runs on the VM directly (not in docker). It binds 80/443 and survives docker restarts.

```bash
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
  | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
  | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update && sudo apt install -y caddy
```

Write `/etc/caddy/Caddyfile`:
```caddy
polimedia.pblworkspace.com {
    reverse_proxy 127.0.0.1:8000
}

meet.polimedia.pblworkspace.com {
    reverse_proxy 127.0.0.1:8080
}
```

Reload Caddy and watch the log for Let's Encrypt issuance:
```bash
sudo systemctl reload caddy
sudo journalctl -u caddy -f
```

(At this point only the app at `127.0.0.1:8000` exists — `meet.*` will 502 until Step 2. That's expected.)

**Verify the app**:
```bash
curl -I https://polimedia.pblworkspace.com
```
Should return 200 from the Laravel app via Caddy's TLS termination.

**Pacing check**: run `/usage`. If ≥ 80%, stop here.

---

## Step 2 — Stand up Jitsi

Clone `docker-jitsi-meet` into `~/jitsi-meet` (pin to a stable tag):

```bash
cd ~
git clone https://github.com/jitsi/docker-jitsi-meet.git
cd jitsi-meet
git checkout stable-9909   # or the latest stable tag at the time
cp env.example .env
./gen-passwords.sh          # generates internal Prosody/Jicofo/JVB secrets
mkdir -p ~/.jitsi-meet-cfg/{web,transcripts,prosody/config,prosody/prosody-plugins-custom,jicofo,jvb,jigasi,jibri}
```

Edit `~/jitsi-meet/.env` — set / change these keys:
```
PUBLIC_URL=https://meet.polimedia.pblworkspace.com
HTTP_PORT=8080
HTTPS_PORT=8443
TZ=Asia/Jakarta
DOCKER_HOST_ADDRESS=34.50.107.24

# Caddy terminates TLS, not Jitsi
DISABLE_HTTPS=1
ENABLE_LETSENCRYPT=0
ENABLE_HTTP_REDIRECT=0

# JWT auth — only authenticated tokens can join
ENABLE_AUTH=1
ENABLE_GUESTS=0
AUTH_TYPE=jwt
JWT_APP_ID=pjbl
JWT_APP_SECRET=<paste output of `openssl rand -hex 32`>
JWT_ACCEPTED_ISSUERS=pjbl
JWT_ACCEPTED_AUDIENCES=pjbl
```

**Save `JWT_APP_ID` and `JWT_APP_SECRET`** — both go into the Laravel app's `.env` in Step 3.

Edit `~/jitsi-meet/docker-compose.yml`: find the `web:` service's `ports:` section and change it from:
```yaml
ports:
    - '${HTTP_PORT}:80'
    - '${HTTPS_PORT}:443'
```
to:
```yaml
ports:
    - '127.0.0.1:${HTTP_PORT}:80'
```
This keeps the container only reachable on localhost (so Caddy proxies to it but outside traffic must go through Caddy on 443).

Bring up the stack:
```bash
docker compose up -d
docker compose ps   # all services should be "Up"/"healthy"
```

Verify locally on the VM:
```bash
curl -I http://localhost:8080
```
Should return 200 (Jitsi web container).

Verify externally:
```bash
curl -I https://meet.polimedia.pblworkspace.com
```
Should return 200 via Caddy. Visit the URL in a browser — Jitsi should refuse to let you join without a token ("Authentication required").

**Pacing check**: run `/usage`. If ≥ 80%, stop here.

---

## Step 3 — Update Laravel `.env` on the VM

The app's `.env` lives at `/var/www/.env` inside the container (mounted from the host repo). Edit it:

```env
APP_URL=https://polimedia.pblworkspace.com
SESSION_DOMAIN=polimedia.pblworkspace.com
SESSION_SECURE_COOKIE=true

JITSI_DOMAIN=meet.polimedia.pblworkspace.com
JITSI_JWT_APP_ID=pjbl
JITSI_JWT_APP_SECRET=<same value as JWT_APP_SECRET in Step 2>
```

**Remove** the old keys: `JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH`.

Apply:
```bash
cd /path/to/app/repo
git pull   # pulls the JitsiTokenService + view/JS changes
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

**Rebuild Vite assets** — remember the named-volume gotcha (`pjbl_app_build` shadows new builds):
```bash
docker compose down
docker volume rm pjbl_app_build
docker compose up -d
docker compose exec app npm run build
```

Confirm the app is reachable through the new domain:
```bash
curl -I https://polimedia.pblworkspace.com
```

**Pacing check**: run `/usage`. If ≥ 80%, stop here.

---

## Step 4 — Verification

End-to-end on the live VM. Run each in order and stop if any fails.

1. **App TLS**: `curl -I https://polimedia.pblworkspace.com` → 200, Let's Encrypt cert.
2. **Jitsi TLS**: `curl -I https://meet.polimedia.pblworkspace.com` → 200, separate Let's Encrypt cert.
3. **Old port closed**: from a machine *outside* the VM, `curl -I http://34.50.107.24:8000` → connection refused / timeout. (App should no longer be reachable bypassing TLS.)
4. **Anonymous join blocked**: open `https://meet.polimedia.pblworkspace.com/test123` in a browser → "Authentication required" page. Confirms JWT enforcement.
5. **Dosen happy path**:
   - Log in to `https://polimedia.pblworkspace.com` as a dosen.
   - Create a conference for a course, click "Mulai", then "Join".
   - Room loads; you're prompted for camera/mic; moderator toolbar visible.
   - DevTools Network: `external_api.js` loads from `meet.polimedia.pblworkspace.com` (not `8x8.vc`).
6. **Mahasiswa join from a second device**:
   - Log in as a student enrolled in that course on a different device / browser profile.
   - Join the same conference.
   - Video and audio flow both directions.
   - **This is the load-bearing test for UDP/10000.** If video shows "connecting" but never streams, the firewall is wrong.
7. **End session**: dosen clicks "Akhiri Sesi" → all participants kicked, `conferences.status = 'ended'` in DB.
8. **Token rejection**: temporarily change `JITSI_JWT_APP_SECRET` in app `.env` to a wrong value, `php artisan config:clear`, try to join → Jitsi rejects with "invalid token". Restore the correct secret.
9. **Optional load test**: open 10 browser tabs against the same room and watch `docker stats` on the VM — JVB CPU should be the dominant load.

**Pacing check**: run `/usage`. If ≥ 80%, stop and write the final progress log.

---

## Step 5 — Cleanup

Once verification passes:

1. Delete the old RSA key (no longer used):
   ```bash
   docker compose exec app rm -f storage/app/private/jaas-private-key.pk
   ```
2. Snapshot the VM (GCP console → `Compute Engine` → `Snapshots`) as a rollback point.
3. Remove any documentation references to JaaS / 8x8.vc that you spot during normal work — code references are already cleaned up.

---

## Rollback

If Step 4 fails badly and you need JaaS back quickly:

1. Revert the Laravel feature branch (`git revert <merge-sha>`) — no destructive ops.
2. Restore the old env keys in the VM's app `.env`: `JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH`.
3. `docker compose exec app php artisan config:clear`. JaaS comes back instantly — the RSA key file is still at `storage/app/private/jaas-private-key.pk` (assuming Step 5 cleanup wasn't run yet).
4. The self-hosted Jitsi stack can keep running idle, or `docker compose down` in `~/jitsi-meet` to free ports.
5. Caddy + the new domain setup stays in place even on Jitsi rollback — the app continues to serve over HTTPS at `polimedia.pblworkspace.com`, which is an improvement worth keeping.
