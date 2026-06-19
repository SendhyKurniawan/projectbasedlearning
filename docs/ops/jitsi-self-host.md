# Serah-terima Ops — Jitsi self-hosted di `pjbl-vm`

Dokumen ini mencakup segala sesuatu yang berjalan di VM GCP. Perubahan kode sisi-Laravel (controller, kelas service, kunci env, view, JS) sudah di-commit; yang tersisa adalah pekerjaan sisi-VM: DNS, firewall, reverse proxy Caddy, stack Jitsi, dan cutover akhir.

**VM target**: `pjbl-vm` di project `pjbl-app-btgs6`, zona `asia-southeast2-a`, IP `34.50.107.24` (`e2-standard-4`, Debian 12).
**Domain target**:
- `polimedia.pblworkspace.com` → aplikasi Laravel (HTTPS, port 443 via Caddy)
- `meet.polimedia.pblworkspace.com` → Jitsi (HTTPS, port 443 via Caddy)

---

## Keadaan terdeploy saat ini (sebagaimana dibangun)

> Runbook ini menangkap *rencana*. Deployment yang sebenarnya dikirim berbeda di beberapa tempat — catatan di bawah ini otoritatif; langkah-langkah berikutnya dipertahankan demi sejarah dan bagian yang masih akurat (DNS, firewall, `.env` Jitsi, JWT, branding, SSO).

- **Caddy berjalan sebagai kontainer Docker**, bukan di host. Didefinisikan di `docker-compose.override.yml` repo aplikasi sebagai service `caddy` (`caddy:2-alpine`, kontainer `pjbl-caddy`), bergabung ke `pjbl-network`, mengikat `80:80`, `443:443`, `443:443/udp`. Caddyfile adalah `~/pjbl/Caddyfile`, di-bind-mount ke `/etc/caddy/Caddyfile`. Ia mem-reverse-proxy **berdasarkan nama kontainer** (`pjbl-web:80`, `jitsi-web:80`), bukan `127.0.0.1:8000/8080`. Reload dengan `docker compose exec caddy caddy reload --config /etc/caddy/Caddyfile`.
- **Jitsi bergabung ke jaringan docker aplikasi.** `~/jitsi-meet/docker-compose.override.yml` melampirkan kontainer `web` ke jaringan external `pjbl_pjbl-network` dengan alias `jitsi-web`, sehingga kontainer Caddy dapat me-resolve-nya berdasarkan nama. Kontainer web mengikat `127.0.0.1:8080:80` dan menjalankan `DISABLE_HTTPS=1`.
- **Relay coturn mandiri** (`coturn/coturn:latest`, kontainer `pjbl-coturn`, juga di override, `network_mode: host`) melayani TURN/STUN pada `3478` udp/tcp untuk peserta di balik NAT restriktif. `.env` Jitsi menyetel `TURN_HOST=meet.polimedia.pblworkspace.com`, `TURN_PORT=3478`, `TURN_TRANSPORT=udp,tcp`. JVB tetap melakukan UDP 10000 langsung bila memungkinkan.
- **Host lama** `pbl.kurniawansendhy.site` juga dilayani Caddy → `pjbl-web:80`.
- Image Jitsi yang berjalan: `jitsi/{web,prosody,jicofo,jvb}:stable-9909`.

## Aturan pacing — terapkan antar langkah

Setelah menyelesaikan tiap langkah bernomor di bawah, jalankan `/usage` di sesi Claude Code. Bila bar penggunaan 5-jam ≥ ~80%, **berhenti**, tambahkan entri "Progress log" ke `~/.claude/plans/i-need-you-to-glowing-lerdorf.md` (langkah terakhir selesai, keadaan VM yang sedang berjalan, sub-langkah berikutnya), dan gunakan `ScheduleWakeup` untuk melanjutkan setelah jendela penggunaan reset. Lihat memori `feedback_usage_threshold_schedule`.

---

## Langkah 0 — Prasyarat

### 0.1 Record DNS

Buat dua record `A` mengarah ke `34.50.107.24`:
- `polimedia.pblworkspace.com` → `34.50.107.24`
- `meet.polimedia.pblworkspace.com` → `34.50.107.24`

Tunggu propagasi, lalu verifikasi dari mesin mana pun:
```bash
dig +short polimedia.pblworkspace.com
dig +short meet.polimedia.pblworkspace.com
```
Keduanya harus mengembalikan `34.50.107.24` sebelum melanjutkan — challenge Let's Encrypt Caddy akan gagal jika tidak.

### 0.2 Cek ukuran VM

```bash
gcloud compute instances describe pjbl-vm --zone <zone> \
  --format="value(machineType.basename())"
```

- `e2-standard-4` (4 vCPU / 16 GB) atau lebih besar → lanjut apa adanya.
- Lebih kecil (mis. `e2-medium` = 2 vCPU / 4 GB) → resize dulu:
  ```bash
  gcloud compute instances stop pjbl-vm --zone <zone>
  gcloud compute instances set-machine-type pjbl-vm --zone <zone> \
    --machine-type=e2-standard-4
  gcloud compute instances start pjbl-vm --zone <zone>
  ```
- Bila Anda mengantisipasi satu ruang besar 30-orang (kasus terburuk), pakai `e2-standard-8`.

### 0.3 Firewall

Buka port media untuk Jitsi (JVB UDP 10000, harvester TCP 4443) dan relay TURN coturn (UDP+TCP 3478):
```bash
gcloud compute firewall-rules create allow-jitsi-media \
  --network default --direction INGRESS \
  --action allow --rules udp:10000,tcp:4443,udp:3478,tcp:3478 \
  --source-ranges 0.0.0.0/0
```

Pastikan 80 + 443 sudah diizinkan (seharusnya, untuk setup yang ada). Setelah Caddy di depan, **hapus izin publik apa pun pada 8000** — port itu seharusnya tak terjangkau dari luar VM setelah cutover.

---

## Langkah 1 — Caddy (berkontainer)

> **Sebagaimana dibangun:** Caddy berjalan sebagai kontainer Docker di proyek compose aplikasi, bukan sebagai paket host. Jalur host-install di bawah ditinggalkan sebagai referensi, tetapi deployment memakai kontainer.

`docker-compose.override.yml` repo aplikasi menambahkan service Caddy:

```yaml
services:
  caddy:
    image: caddy:2-alpine
    container_name: pjbl-caddy
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
      - "443:443/udp"   # HTTP/3
    volumes:
      - ./Caddyfile:/etc/caddy/Caddyfile
      - caddy_data:/data
      - caddy_config:/config
    networks:
      - pjbl-network
    depends_on:
      - web
```

Tulis `~/pjbl/Caddyfile` (mem-proxy berdasarkan nama kontainer lewat `pjbl-network`):
```caddy
polimedia.pblworkspace.com {
    encode gzip
    reverse_proxy pjbl-web:80
}

meet.polimedia.pblworkspace.com {
    encode gzip
    reverse_proxy jitsi-web:80
}
```

Naikkan dan pantau log untuk penerbitan Let's Encrypt:
```bash
docker compose up -d caddy
docker compose logs -f caddy
```

(Pada titik ini hanya aplikasi di `pjbl-web:80` yang ada — `meet.*` akan 502 sampai Langkah 2 melampirkan `jitsi-web` ke jaringan. Itu wajar.)

<details><summary>Lama: Caddy terinstal-host (tidak dipakai)</summary>

```bash
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
  | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
  | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update && sudo apt install -y caddy
sudo systemctl reload caddy
```
</details>

**Verifikasi aplikasi**:
```bash
curl -I https://polimedia.pblworkspace.com
```
Seharusnya mengembalikan 200 dari aplikasi Laravel via terminasi TLS Caddy.

**Cek pacing**: jalankan `/usage`. Bila ≥ 80%, berhenti di sini.

---

## Langkah 2 — Mendirikan Jitsi

Klon `docker-jitsi-meet` ke `~/jitsi-meet` (pin ke tag stabil):

```bash
cd ~
git clone https://github.com/jitsi/docker-jitsi-meet.git
cd jitsi-meet
git checkout stable-9909   # atau tag stabil terbaru saat itu
cp env.example .env
./gen-passwords.sh          # men-generate secret internal Prosody/Jicofo/JVB
mkdir -p ~/.jitsi-meet-cfg/{web,transcripts,prosody/config,prosody/prosody-plugins-custom,jicofo,jvb,jigasi,jibri}
```

Edit `~/jitsi-meet/.env` — set / ubah kunci ini:
```
PUBLIC_URL=https://meet.polimedia.pblworkspace.com
HTTP_PORT=8080
HTTPS_PORT=8443
TZ=Asia/Jakarta
DOCKER_HOST_ADDRESS=34.50.107.24

# Caddy menerminasi TLS, bukan Jitsi
DISABLE_HTTPS=1
ENABLE_LETSENCRYPT=0
ENABLE_HTTP_REDIRECT=0

# Auth JWT — hanya token terautentikasi yang dapat bergabung
ENABLE_AUTH=1
ENABLE_GUESTS=0
AUTH_TYPE=jwt
JWT_APP_ID=pjbl
JWT_APP_SECRET=<tempel output `openssl rand -hex 32`>
JWT_ACCEPTED_ISSUERS=pjbl
JWT_ACCEPTED_AUDIENCES=pjbl

# Peran moderator ditentukan HANYA oleh klaim JWT `context.user.moderator`.
# Default ENABLE_AUTO_OWNER=1 otomatis mempromosikan joiner PERTAMA ke moderator
# tanpa memandang token — yang akan memberi mahasiswa moderator bila ia
# bergabung sebelum dosen. Nonaktifkan agar mahasiswa (moderator:false) tak pernah
# jadi moderator otomatis. Dosen/admin tetap bisa memberi moderator dalam-call
# via menu peserta ("Grant moderator").
ENABLE_AUTO_OWNER=0
```

**Simpan `JWT_APP_ID` dan `JWT_APP_SECRET`** — keduanya masuk ke `.env` aplikasi Laravel di Langkah 3.

Alih-alih mengedit `docker-compose.yml` di tempat, deployment memakai `~/jitsi-meet/docker-compose.override.yml` untuk (a) mengikat port web hanya ke localhost, dan (b) bergabung ke jaringan docker aplikasi agar kontainer Caddy dapat menjangkau `jitsi-web` berdasarkan nama:

```yaml
services:
  web:
    container_name: jitsi-web
    ports: !reset
      - '127.0.0.1:${HTTP_PORT}:80'   # localhost saja; Caddy mem-proxy masuk
    networks:
      meet.jitsi: null
      pjbl-shared:
        aliases:
          - jitsi-web
    volumes:
      - ${CONFIG}/web/favicon.svg:/usr/share/jitsi-meet/images/favicon.svg:ro

networks:
  pjbl-shared:
    name: pjbl_pjbl-network   # jaringan stack aplikasi
    external: true
```

Ini menjaga kontainer hanya terjangkau di localhost (lalu lintas luar harus lewat Caddy di 443) sembari membuatnya dapat diresolusi sebagai `jitsi-web` di dalam `pjbl-network`.

Naikkan stack:
```bash
docker compose up -d
docker compose ps   # semua service harus "Up"/"healthy"
```

Verifikasi lokal di VM:
```bash
curl -I http://localhost:8080
```
Seharusnya mengembalikan 200 (kontainer web Jitsi).

Verifikasi eksternal:
```bash
curl -I https://meet.polimedia.pblworkspace.com
```
Seharusnya mengembalikan 200 via Caddy. Kunjungi URL di browser — Jitsi seharusnya menolak Anda bergabung tanpa token ("Authentication required").

**Cek pacing**: jalankan `/usage`. Bila ≥ 80%, berhenti di sini.

---

## Langkah 3 — Perbarui `.env` Laravel di VM

`.env` aplikasi berada di `/var/www/.env` dalam kontainer (di-mount dari repo host). Edit:

```env
APP_URL=https://polimedia.pblworkspace.com
SESSION_DOMAIN=polimedia.pblworkspace.com
SESSION_SECURE_COOKIE=true

JITSI_DOMAIN=meet.polimedia.pblworkspace.com
JITSI_JWT_APP_ID=pjbl
JITSI_JWT_APP_SECRET=<nilai yang sama dengan JWT_APP_SECRET di Langkah 2>
```

**Hapus** kunci lama: `JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH`.

Terapkan:
```bash
cd /path/to/app/repo
git pull   # menarik perubahan JitsiTokenService + view/JS
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

**Rebuild aset Vite** — ingat gotcha named-volume (`pjbl_app_build` membayangi build baru):
```bash
docker compose down
docker volume rm pjbl_app_build
docker compose up -d
docker compose exec app npm run build
```

Konfirmasi aplikasi terjangkau lewat domain baru:
```bash
curl -I https://polimedia.pblworkspace.com
```

**Cek pacing**: jalankan `/usage`. Bila ≥ 80%, berhenti di sini.

---

## Langkah 4 — Verifikasi

End-to-end di VM live. Jalankan tiap langkah berurutan dan berhenti bila ada yang gagal.

1. **TLS aplikasi**: `curl -I https://polimedia.pblworkspace.com` → 200, sertifikat Let's Encrypt.
2. **TLS Jitsi**: `curl -I https://meet.polimedia.pblworkspace.com` → 200, sertifikat Let's Encrypt terpisah.
3. **Port lama tertutup**: dari mesin *di luar* VM, `curl -I http://34.50.107.24:8000` → connection refused / timeout. (Aplikasi seharusnya tak lagi terjangkau mem-bypass TLS.)
4. **Join anonim terblokir**: buka `https://meet.polimedia.pblworkspace.com/test123` di browser → halaman "Authentication required". Mengonfirmasi penegakan JWT.
5. **Jalur sukses dosen**:
   - Login ke `https://polimedia.pblworkspace.com` sebagai dosen.
   - Buat konferensi untuk sebuah mata kuliah, klik "Mulai", lalu "Join".
   - Ruang termuat; Anda diminta izin kamera/mik; toolbar moderator terlihat.
   - DevTools Network: `external_api.js` dimuat dari `meet.polimedia.pblworkspace.com` (bukan `8x8.vc`).
6. **Join mahasiswa dari perangkat kedua**:
   - Login sebagai mahasiswa terdaftar di mata kuliah itu pada perangkat / profil browser berbeda.
   - Bergabung ke konferensi yang sama.
   - Video dan audio mengalir dua arah.
   - **Ini uji penanggung-beban untuk UDP/10000.** Bila video menampilkan "connecting" tetapi tak pernah streaming, firewall salah.
7. **Akhiri sesi**: dosen klik "Akhiri Sesi" → semua peserta ditendang, `conferences.status = 'ended'` di DB.
8. **Penolakan token**: ubah sementara `JITSI_JWT_APP_SECRET` di `.env` aplikasi ke nilai salah, `php artisan config:clear`, coba bergabung → Jitsi menolak dengan "invalid token". Pulihkan secret yang benar.
9. **Uji beban opsional**: buka 10 tab browser ke ruang yang sama dan pantau `docker stats` di VM — CPU JVB seharusnya jadi beban dominan.

**Cek pacing**: jalankan `/usage`. Bila ≥ 80%, berhenti dan tulis progress log akhir.

---

## Langkah 5 — Pembersihan

Setelah verifikasi lolos:

1. Hapus kunci RSA lama (tak lagi dipakai):
   ```bash
   docker compose exec app rm -f storage/app/private/jaas-private-key.pk
   ```
2. Snapshot VM (GCP console → `Compute Engine` → `Snapshots`) sebagai titik rollback.
3. Hapus referensi dokumentasi apa pun ke JaaS / 8x8.vc yang Anda temukan saat bekerja normal — referensi kode sudah dibersihkan.

---

## Langkah 6 — Branding (nama PBL Workspace, logo, favicon)

Aplikasi sudah mengirim nama/logo dalam-call via hash URL ruang (`JitsiTokenService::roomUrl()`). Langkah ini membuat branding otoritatif di server Jitsi dan mencakup yang tak terjangkau hash URL: **favicon tab-browser**, **judul** dokumen, dan **halaman selamat datang**. Config di-version-control di repo aplikasi di bawah `docker/jitsi/web/` (lihat `README.md`-nya) — salin ke VM.

```bash
# Dari repo aplikasi di VM (sudah ditarik di Langkah 3):
APP_REPO=/path/to/app/repo

# 1. Override interface_config — auto-ditambahkan oleh kontainer web.
cp "$APP_REPO/docker/jitsi/web/custom-interface_config.js" \
   ~/.jitsi-meet-cfg/web/custom-interface_config.js

# 2. Aset logo + favicon, ditempatkan di tempat yang diharapkan bind-mount (di bawah).
cp "$APP_REPO/docker/jitsi/web/pbl-logo.svg" ~/.jitsi-meet-cfg/web/pbl-logo.svg
cp "$APP_REPO/docker/jitsi/web/favicon.svg" ~/.jitsi-meet-cfg/web/favicon.svg
```

Bind-mount aset ke kontainer web. Edit `~/jitsi-meet/docker-compose.yml`, di bawah daftar `volumes:` service `web:`, tambahkan:
```yaml
      - ${CONFIG}/web/pbl-logo.svg:/usr/share/jitsi-meet/images/pbl-logo.svg:ro
      - ${CONFIG}/web/favicon.svg:/usr/share/jitsi-meet/images/favicon.svg:ro
```
(`${CONFIG}` sudah didefinisikan di `.env` Jitsi sebagai `~/.jitsi-meet-cfg`.)

Terapkan dan restart hanya kontainer web:
```bash
cd ~/jitsi-meet
docker compose up -d web      # mengambil mount volume baru
docker compose restart web    # memuat ulang custom-interface_config.js
```

**Verifikasi**:
1. Buka `https://meet.polimedia.pblworkspace.com` di browser → judul tab bertuliskan **PBL Workspace**, favicon tab adalah ikon topi-wisuda biru, halaman selamat datang menampilkan logo kita.
2. Mulai konferensi dari aplikasi dan bergabung → watermark kiri-atas adalah logo kita, header dalam-call bertuliskan **PBL Workspace**, dan tak ada branding "powered by" Jitsi.
3. Hard-refresh (Ctrl+Shift+R) bila Anda masih melihat favicon lama — browser meng-cache-nya agresif. HTML yang disajikan menautkan `images/favicon.svg?v=1`, jadi bind-mount di atas `favicon.svg` yang berlaku.

---

## Langkah 7 — SSO (login Jitsi memakai PBL)

Tanpa ini, pengunjung tanpa-token (mis. seseorang yang membuka tautan **Share** dalam-ruang Jitsi) menabrak dinding buntu "Authentication required" Jitsi. Langkah ini mengarahkan `tokenAuthUrl` Jitsi ke PBL, sehingga pengunjung itu diarahkan ke PBL, login dengan akun normalnya, dan dipantulkan kembali ke ruang dengan JWT yang baru di-mint.

Sisi PBL dikirim di repo aplikasi: route `conferences.jitsi-auth` (`App\Http\Controllers\ConferenceJoinController@jitsiAuth`, di belakang `auth`). Ia mencari konferensi berdasarkan `room_name`, memverifikasi akses (admin / dosen-pemilik / mahasiswa-terdaftar, aturan sama dengan ruang dalam-aplikasi), me-mint JWT per-user, dan redirect kembali ke `https://meet.…/{room}?jwt=…`. Pastikan repo aplikasi sudah ditarik (Langkah 3) agar route ada.

Sisi Jitsi — pasang override config:
```bash
APP_REPO=/path/to/app/repo   # mis. ~/pjbl
cp "$APP_REPO/docker/jitsi/web/custom-config.js" ~/.jitsi-meet-cfg/web/custom-config.js
cd ~/jitsi-meet
docker compose restart web    # memuat ulang config.js (custom-config.js auto-ditambahkan)
```

Konfirmasi tersaji:
```bash
curl -s https://meet.polimedia.pblworkspace.com/config.js | grep -i tokenAuthUrl
```
Seharusnya menampilkan `config.tokenAuthUrl = 'https://polimedia.pblworkspace.com/conferences/jitsi-auth?room={room}';`

**Verifikasi round-trip**:
1. Dalam konferensi yang sudah login, klik tombol **Share** Jitsi → salin tautan (`https://meet.…/{room}`).
2. Buka di jendela incognito baru → Anda mendarat di **halaman login PBL**.
3. Login sebagai user yang terdaftar di / mengajar mata kuliah itu → Anda diarahkan kembali dan bergabung ke ruang (moderator bila dosen/admin, peserta bila mahasiswa).
4. Coba sebagai user yang *bukan* di mata kuliah itu → 403 dari PBL (tak ada token di-mint). Coba tautan untuk konferensi yang **berakhir** → 410.

> Tanpa loop: URL kembali membawa `jwt` valid, jadi Jitsi bergabung alih-alih me-redirect ulang. Bila Anda pernah melihat redirect loop, artinya token yang di-mint tidak valid (`JITSI_JWT_APP_SECRET` salah) — perbaiki secret, `php artisan config:clear`.

---

## Rollback

Migrasi kini sudah merge ke `main` — jalur kode JaaS (`JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH`, penandatanganan JWT RS256, iframe tertanam) telah dihapus dari basis kode. Rollback ke JaaS tak lagi satu `git revert`; Anda perlu:

1. Identifikasi SHA merge untuk migrasi self-hosted (`099a07e refactor(conferences): migrate from Jitsi JaaS to self-hosted (HS256 JWT)`, `db5378a refactor(conferences): drop iframe, use standalone Jitsi tab launcher`) plus commit favicon/pembersihan setelahnya.
2. Revert commit itu berurutan (`git revert` non-destruktif, jangan pernah `git reset --hard`).
3. Pulihkan berkas kunci RSA di `storage/app/private/jaas-private-key.pk` bila pembersihan Langkah 5 telah dijalankan.
4. Pulihkan kunci env lama di `.env` aplikasi VM: `JITSI_APP_ID`, `JITSI_KID`, `JITSI_PRIVATE_KEY_PATH`.
5. `docker compose exec app php artisan config:clear && php artisan config:cache`.

Bila stack Jitsi di VM mati tanpa penyebab tingkat-kode (kegagalan pembaruan sertifikat, crash docker, dll.), bawa kembali tanpa menyentuh aplikasi:

```bash
cd ~/jitsi-meet
docker compose down
docker compose up -d
docker compose ps      # konfirmasi "Up"/"healthy"
# bila sertifikat basi, restart kontainer Caddy dari proyek aplikasi:
cd ~/pjbl && docker compose restart caddy
```

Caddy + setup domain baru tetap di tempat tanpa memandang — aplikasi terus melayani lewat HTTPS di `polimedia.pblworkspace.com`, yang merupakan bagian penanggung-beban bahkan bila Jitsi sementara mati.
