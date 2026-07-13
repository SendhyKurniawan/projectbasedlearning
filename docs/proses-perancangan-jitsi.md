# Proses Perancangan Fitur Konferensi Video (Jitsi)

Dokumen ini menjelaskan **proses perancangan fitur konferensi video** PBL Workspace — dari analisis kebutuhan, tiga generasi implementasi (LiveKit → Jitsi JaaS → Jitsi self-hosted), keputusan perancangan di setiap tahap, sampai pembuktian kapasitas lewat uji beban. Ditulis sebagai bahan penjelasan kepada dosen penguji, sebagai pendamping [proses-perancangan.md](proses-perancangan.md) yang membahas sistem secara keseluruhan. Untuk detail teknis fitur, lihat [features/conferences.md](features/conferences.md); untuk runbook provisioning server, lihat [ops/jitsi-self-host.md](ops/jitsi-self-host.md).

---

## Daftar Isi

1. [Mengapa Fitur Ini Menarik untuk Dibahas](#1-mengapa-fitur-ini-menarik-untuk-dibahas)
2. [Analisis Kebutuhan](#2-analisis-kebutuhan)
3. [Evolusi Tiga Generasi](#3-evolusi-tiga-generasi)
4. [Perancangan Solusi Final (Jitsi Self-Hosted)](#4-perancangan-solusi-final-jitsi-self-hosted)
5. [Pendalaman Teknis: JitsiTokenService dan JWT HS256](#5-pendalaman-teknis-jitsitokenservice-dan-jwt-hs256)
6. [Perancangan Keamanan Konferensi](#6-perancangan-keamanan-konferensi)
7. [Pengujian dan Pembuktian Kapasitas](#7-pengujian-dan-pembuktian-kapasitas)
8. [Justifikasi Keputusan (Antisipasi Pertanyaan Penguji)](#8-justifikasi-keputusan-antisipasi-pertanyaan-penguji)

---

## 1. Mengapa Fitur Ini Menarik untuk Dibahas

Konferensi video adalah fitur dengan **risiko teknis tertinggi** di sistem ini: ia melibatkan protokol media real-time (WebRTC), infrastruktur yang tidak dimiliki framework web biasa (server media, relay NAT), integrasi identitas lintas sistem, dan beban server yang tumbuh cepat terhadap jumlah peserta. Fitur ini juga satu-satunya yang **dirancang ulang dua kali** — dan justru itu yang membuatnya bahan cerita proses perancangan yang jujur: setiap penggantian punya alasan terukur, dan solusi finalnya dibuktikan dengan uji beban di server produksi.

---

## 2. Analisis Kebutuhan

Kebutuhan fungsional yang dirumuskan dari konteks perkuliahan daring:

| # | Kebutuhan | Alasan |
|---|---|---|
| K1 | Dosen menjadwalkan sesi per mata kuliah; mahasiswa kelas itu bisa bergabung | Kelas daring terjadwal, bukan ruang bebas |
| K2 | Hanya peserta sah (login + terdaftar) yang bisa masuk ruang | Ruang kelas bukan ruang publik |
| K3 | Dosen otomatis menjadi **moderator**; mahasiswa bukan | Kontrol kelas (mute, kick, kunci ruang) di tangan pengajar |
| K4 | Identitas peserta (nama asli) tampil otomatis, tanpa akun/registrasi terpisah | Presensi visual; menghilangkan friksi login ganda |
| K5 | Kapasitas satu ruang ≥ 1 kelas besar (target: ~100 peserta) | Kuliah umum / kelas gabungan |
| K6 | Daur hidup sesi terkontrol: `scheduled → live → ended` | Mahasiswa tidak bisa masuk sebelum dimulai atau setelah diakhiri |

Kebutuhan non-fungsional yang kemudian menjadi penentu arah:

- **Biaya berkelanjutan** — solusi berbayar per-menit/per-peserta tidak realistis untuk institusi pendidikan.
- **Kedaulatan data & branding** — sesi kelas sebaiknya berjalan di infrastruktur dan identitas visual institusi.
- **Beban satu VM** — konferensi harus muat berdampingan dengan aplikasi di VM produksi yang sama (batasan biaya dari perancangan sistem keseluruhan).

---

## 3. Evolusi Tiga Generasi

Riwayat Git merekam tiga generasi implementasi. Ini bukan kegagalan perencanaan, melainkan siklus iteratif *bangun → evaluasi → rancang ulang* dengan kriteria evaluasi yang jelas di tiap tahap.

| Generasi | Periode | Teknologi | Mengapa dipilih | Mengapa diganti |
|---|---|---|---|---|
| **1. LiveKit** | Mar–Apr 2026 | LiveKit (SFU open-source, SDK JavaScript) | Validasi cepat konsep "kelas daring di dalam LMS"; SDK modern | UI konferensi harus dibangun **sendiri** dari primitif SDK (grid video, kontrol, layar) — beban pengembangan dan pemeliharaan antarmuka call ternyata sebesar aplikasi kedua |
| **2. Jitsi JaaS** | Mei 2026 (awal) | Jitsi-as-a-Service (8x8.vc, JWT RS256, iframe) | UI konferensi matang bawaan Jitsi; commit-nya sendiri menyebut "*using jaas as temporary*" — sadar sementara, untuk memvalidasi UX Jitsi tanpa provisioning server | Bergantung kuota/penagihan pihak ketiga (bertentangan dengan kebutuhan biaya); ruang ber-prefiks tenant milik vendor; data media lewat server luar |
| **3. Jitsi self-hosted** | Mei 2026 (akhir) — final | Jitsi Meet di VM sendiri, JWT HS256, peluncur tab | Memenuhi ketiga kebutuhan non-fungsional sekaligus: biaya tetap (VM sudah ada), kontrol penuh identitas + branding, data di infrastruktur sendiri | — |

Dua pelajaran proses yang layak disorot ke penguji:

1. **Generasi 2 disengaja sebagai jembatan.** Migrasi LiveKit → Jitsi mengubah dua hal sekaligus (mesin media *dan* model UI). JaaS memungkinkan memvalidasi separuh perubahan (UX Jitsi, model JWT) tanpa investasi provisioning server — begitu tervalidasi, migrasi ke self-hosted tinggal mengganti penandatangan token (RSA tenant → HS256 domain sendiri) dan menunjuk domain sendiri.
2. **Setiap generasi mempersempit masalah.** Generasi 1 membuktikan kebutuhan dan alurnya benar; generasi 2 membuktikan Jitsi adalah UX yang tepat; generasi 3 tinggal menyelesaikan kepemilikan infrastruktur.

---

## 4. Perancangan Solusi Final (Jitsi Self-Hosted)

### 4.1 Pemisahan tanggung jawab: LMS mengatur akses, Jitsi mengurus media

Prinsip perancangan utamanya: **aplikasi tidak ikut campur urusan media real-time** — itu domain Jitsi yang sudah matang. Peran aplikasi dipersempit menjadi tiga hal: (i) menyimpan jadwal dan daur hidup sesi, (ii) memutuskan *siapa boleh masuk sebagai apa*, dan (iii) menerbitkan bukti keputusan itu dalam bentuk **JWT** yang diverifikasi server Jitsi.

```
Dosen/Mahasiswa ── login ──► PBL Workspace (Laravel)
                                  │ otorisasi: enrolled? course milik dosen? status live?
                                  ▼
                       JitsiTokenService::mint()  ── JWT HS256 (2 jam) ──┐
                                  │                                      │
                                  ▼                                      ▼
                    buka tab baru: https://meet.…/{room_name}?jwt=…  ──► Jitsi (prosody
                                                                         memverifikasi token)
```

### 4.2 Perancangan data dan daur hidup

- Entitas `Conference` milik satu `Course` dan satu dosen; nama ruang dibangkitkan `room-{course_id}-{uuid}` — unik global, tidak bisa ditebak, sehingga URL ruang tidak dapat dienumerasi.
- Status `scheduled → live → ended` (K6): mahasiswa hanya bisa bergabung saat `live`; tombol mulai/akhiri hanya milik dosen (dan admin untuk akhiri paksa).
- Konferensi ikut pola *copy fan-out* sistem: dosen kelas paralel dapat menyalin jadwal sesi ke kelas sibling dengan validasi kepemilikan yang sama.

### 4.3 Perancangan token (kontrak LMS ↔ Jitsi)

Token dirancang membawa **keputusan otorisasi yang sudah final** — Jitsi tidak perlu (dan tidak bisa) bertanya balik ke aplikasi:

| Klaim | Isi | Fungsi |
|---|---|---|
| `aud`, `iss` | app ID bersama | Identifikasi penerbit yang disepakati kedua sisi |
| `sub` | domain Jitsi | Mengunci token ke server tujuan |
| `room` | `room_name` sesi | Token hanya berlaku untuk **satu ruang** — bocor pun tak bisa dipakai ruang lain |
| `exp` (2 jam), `nbf` (−10 dtk) | masa berlaku | Membatasi umur token; toleransi selisih jam antar server |
| `context.user` | id, nama, email, **moderator: true/false** | K3 + K4: identitas asli dan peran dibawa token, bukan diisi pengguna |

Tanda tangan memakai **HS256** (secret simetris bersama aplikasi ↔ Jitsi) — dibedah tuntas di §5; pertimbangan HS256 vs RS256 di §8 pertanyaan 4.

### 4.4 Peluncur tab baru, bukan iframe

Generasi JaaS menanamkan ruang dalam `<iframe>` di halaman aplikasi. Rancangan final menggantinya dengan **membuka Jitsi di tab tersendiri**. Alasan: (i) iframe menumpuk dua lapis UI dan bermasalah dengan izin kamera/mikrofon serta perilaku mobile; (ii) tab penuh memberi Jitsi seluruh layar — pengalaman standar yang sudah dikenal pengguna; (iii) halaman aplikasi tetap ringan, cukup menjadi "ruang tunggu" berisi tombol gabung. Deep-link aplikasi Android sempat dicoba dan **dihapus** setelah terbukti rapuh — contoh kecil evaluasi iteratif.

### 4.5 Branding dan SSO — menutup jahitan integrasi

Dua sentuhan akhir agar Jitsi terasa bagian dari sistem, bukan layanan asing:

- **Branding via URL fragment**: nama aplikasi, logo watermark, dan penyembunyian merek Jitsi disuntikkan sebagai *override* `interfaceConfig` pada hash URL yang dibangun `JitsiTokenService::roomUrl()` — tanpa mem-fork citra Docker Jitsi.
- **SSO via `tokenAuthUrl`**: pengguna yang membuka URL Jitsi *tanpa* token (mis. dari riwayat browser) diarahkan balik ke login PBL Workspace, bukan ditolak mentah — satu pintu identitas (K4).

### 4.6 Infrastruktur

Jitsi (kontainer `web`, `prosody`, `jicofo`, `jvb`) berjalan di VM yang sama dengan aplikasi, di belakang Caddy yang menerminasi TLS untuk subdomain `meet.…`. Dua jalur media dirancang: **UDP 10000 langsung ke JVB** (jalur utama), dan **relay coturn (TURN)** untuk peserta di balik NAT/firewall ketat yang tidak bisa menerima UDP langsung — tanpa TURN, sebagian peserta di jaringan kampus/kantor tidak akan pernah tersambung.

---

## 5. Pendalaman Teknis: JitsiTokenService dan JWT HS256

Bagian ini membedah mekanisme token sampai level implementasi — bekal menjawab pertanyaan "bagaimana persisnya token ini bekerja". Seluruh logika ada di satu kelas kecil: `app/Services/JitsiTokenService.php` (dua metode: `mint()` dan `roomUrl()`).

### 5.1 Anatomi JWT

JWT secara fisik adalah **tiga blok teks base64url yang dipisah titik**:

```
eyJhbGciOiJIUzI1NiJ9 . eyJhdWQiOiJwYmwt... . 4kX9vP2mQ...
       HEADER                PAYLOAD           SIGNATURE
```

- **Header** — JSON kecil berisi algoritma: `{"alg":"HS256","typ":"JWT"}`.
- **Payload** — JSON berisi *claims* (klaim): pernyataan tentang siapa pemegang token dan apa haknya.
- **Signature** — hasil perhitungan kriptografis atas dua blok pertama.

Kesalahpahaman umum yang perlu diluruskan: **JWT tidak dienkripsi**. Base64 adalah *encoding*, bukan enkripsi — siapa pun yang memegang token bisa membaca payload-nya. Yang dijamin JWT adalah **integritas dan keaslian**: payload tidak bisa *diubah* tanpa membuat signature tidak cocok. Karena itu payload di sistem ini hanya berisi identitas dan peran — tidak pernah password atau secret.

### 5.2 Cara kerja HS256

HS256 = **HMAC-SHA256**, skema tanda tangan **simetris**:

```
signature = HMAC-SHA256( base64url(header) + "." + base64url(payload),  secret )
```

Satu **secret** yang sama dipegang dua pihak: aplikasi Laravel (`JITSI_JWT_APP_SECRET` di `.env`) dan server Jitsi (`JWT_APP_SECRET` di konfigurasi kontainernya). Aplikasi memakai secret untuk *membuat* signature; **prosody** (komponen XMPP Jitsi yang mengelola keanggotaan ruang) menghitung ulang HMAC dengan secret yang sama dan membandingkan hasilnya. Cocok → token asli dan utuh. Beda satu karakter saja di payload → HMAC hasil hitung ulang berbeda total → token ditolak.

Skenario serangan sebagai ilustrasi: mahasiswa men-decode payload, mengganti `"moderator":"false"` menjadi `"true"`, meng-encode ulang, lalu mengirimkannya. Prosody menghitung HMAC atas payload baru → tidak sama dengan signature lama → **ditolak**. Membuat signature baru yang valid membutuhkan secret 32-byte acak (`openssl rand -hex 32`) yang tidak pernah meninggalkan server — browser tidak pernah memegangnya.

### 5.3 Bedah `mint()`

```php
public function mint(string $room, int $userId, string $name, bool $moderator, ?string $email = null): string
```

**Guard clause**: bila `JITSI_JWT_APP_ID`/`JITSI_JWT_APP_SECRET` kosong, metode melempar `RuntimeException` — *fail closed*: sistem memilih halaman error daripada diam-diam membuka ruang tanpa autentikasi.

Payload yang dibangun:

| Klaim | Nilai | Alasan perancangan |
|---|---|---|
| `aud` (audience) | app ID | Untuk siapa token ini; prosody menolak `aud` yang bukan app ID miliknya |
| `iss` (issuer) | app ID (sama) | Siapa penerbit; konvensi Jitsi self-hosted: `aud` = `iss` = app ID |
| `sub` (subject) | domain Jitsi | Mengunci token ke satu server tujuan |
| `room` | `room_name` sesi | **Klaim otorisasi terpenting**: prosody mencocokkannya dengan ruang yang benar-benar dimasuki — token bocor radius kerusakannya satu ruang |
| `iat` | waktu sekarang | Waktu penerbitan |
| `nbf` | sekarang − 10 detik | *Not before* dengan **toleransi selisih jam** antar kontainer; tanpa mundur 10 detik, jam prosody yang telat sedikit akan menolak token yang baru dicetak — bug klasik integrasi JWT |
| `exp` | sekarang + 7200 | Kedaluwarsa 2 jam — cukup untuk satu sesi kuliah, cukup pendek membatasi umur token bocor |
| `context.user` | id, name, avatar, email, **moderator** | Bukan klaim standar JWT — kontrak spesifik Jitsi: prosody membacanya untuk menampilkan nama asli dan menetapkan hak moderator |

Dua detail implementasi yang menunjukkan pemahaman kontrak Jitsi:

- `'moderator' => $moderator ? 'true' : 'false'` — dikirim sebagai **string**, bukan boolean JSON, menyesuaikan cara plugin token prosody membacanya; tipe yang salah membuat flag diam-diam tidak dikenali.
- `'id' => (string) $userId` — di-cast ke string sesuai ekspektasi spesifikasi context Jitsi.

Baris terakhir, `JWT::encode($payload, $secret, 'HS256')`, memakai pustaka **`firebase/php-jwt`** (^7.0) — encoding dan HMAC dikerjakan pustaka teraudit, bukan kriptografi buatan sendiri.

### 5.4 Siapa memanggil, dan dari mana nilai `$moderator`

Tiga controller `ConferenceController::room` (Admin, Dosen, Mahasiswa) memanggil `mint()` saat pengguna membuka halaman ruang. **Nilai `$moderator` ditentukan server dari `users.role`** — dosen/admin `true`, mahasiswa `false` — bukan dari input apa pun yang bisa disentuh pengguna. Urutan lengkapnya:

```
1. Middleware auth + role  →  siapa kamu, peran apa
2. Otorisasi controller    →  boleh masuk ruang INI? (enrolled / pemilik course, status live)
3. mint()                  →  keputusan langkah 1–2 "dibekukan" ke dalam token bertanda tangan
4. Browser membawa token   →  prosody memverifikasi tanpa bertanya balik ke Laravel
```

Token dengan demikian adalah **keputusan otorisasi final yang dibawa-bawa** (*bearer token*) — Jitsi tidak punya koneksi ke database aplikasi dan tidak membutuhkannya.

### 5.5 Verifikasi di sisi Jitsi

Saat browser membuka `https://meet.…/{room}?jwt={token}`, prosody memeriksa berurutan:

1. **Signature** — hitung ulang HMAC dengan `JWT_APP_SECRET` miliknya; harus identik.
2. **Waktu** — `nbf ≤ sekarang ≤ exp`.
3. **Penerbit/tujuan** — `iss` dan `aud` cocok dengan `JWT_APP_ID`; `sub` cocok dengan domainnya.
4. **Ruang** — klaim `room` sama dengan ruang yang diminta.
5. Lolos semua → peserta masuk dengan nama dari `context.user.name` dan status moderator dari `context.user.moderator`.

Gagal di titik mana pun → ditolak, dan karena server dikonfigurasi *JWT-only* (tanpa mode tamu), tidak ada jalur masuk lain. Satu syarat server melengkapi rancangan ini: `ENABLE_AUTO_OWNER=0` (§6 poin 2) — tanpanya, prosody mengabaikan flag moderator token dan mempromosikan peserta pertama.

### 5.6 Contoh token ter-decode

```json
// HEADER
{ "alg": "HS256", "typ": "JWT" }

// PAYLOAD
{
  "aud": "pbl-workspace",
  "iss": "pbl-workspace",
  "sub": "meet.polimedia.pblworkspace.com",
  "room": "room-42-9f8e7d6c-...",
  "iat": 1780000000,
  "nbf": 1779999990,
  "exp": 1780007200,
  "context": {
    "user": {
      "id": "137",
      "name": "Budi Santoso",
      "avatar": "",
      "email": "budi@polimedia...",
      "moderator": "false"
    }
  }
}

// SIGNATURE = HMAC-SHA256(base64url(header) + "." + base64url(payload), JITSI_JWT_APP_SECRET)
```

### 5.7 Fungsi kedua: `roomUrl()` — pengantaran token dan branding

`mint()` hanya separuh cerita; `roomUrl()` merakit URL final:

```
https://{domain}/{room}?jwt={token}#config.subject=...&interfaceConfig.APP_NAME=...
```

- Token diantar lewat **query param `?jwt=`** — mekanisme standar yang dibaca frontend Jitsi.
- Bagian **`#fragment`** membawa override branding (`interfaceConfig.APP_NAME`, logo watermark, penyembunyian merek Jitsi). Detail cerdiknya: fragment URL **tidak pernah dikirim ke server** (sifat protokol HTTP) — ia dibaca JavaScript Jitsi di browser, sehingga branding dapat disuntik per-URL tanpa mengubah konfigurasi server atau mem-fork citra Docker Jitsi.

**Ringkasan satu kalimat untuk sidang:** aplikasi bertindak sebagai *penerbit tiket* — setelah memverifikasi login, peran, dan hak akses ruang, ia mencetak JWT HS256 (tiket bertanda tangan kriptografis berisi identitas, satu nama ruang, masa berlaku 2 jam, dan flag moderator), lalu server Jitsi, yang memegang secret yang sama, cukup memverifikasi tanda tangan itu untuk menerima peserta tanpa bertanya kembali ke aplikasi.

---

## 6. Perancangan Keamanan Konferensi

Model ancamannya: *peserta tidak sah masuk ruang* dan *mahasiswa memperoleh hak moderator*.

1. **Autentikasi ruang hanya via JWT** — server Jitsi dikonfigurasi menolak peserta tanpa token valid; tidak ada mode tamu.
2. **Moderator hanya dari klaim token** — temuan terpenting selama implementasi: konfigurasi bawaan Jitsi (`ENABLE_AUTO_OWNER=1`) **otomatis mempromosikan peserta pertama menjadi moderator, mengabaikan token**. Artinya mahasiswa yang bergabung sebelum dosen akan menguasai ruang. Ini celah yang tidak tertulis jelas di dokumentasi dan baru ketahuan lewat pengujian lintas-peran; solusinya mematikan opsi itu sehingga status moderator murni dari `context.user.moderator`. Dosen tetap bisa memberi moderator manual dalam call bila perlu.
3. **Token berumur pendek dan sempit** — 2 jam, satu ruang, diterbitkan hanya setelah otorisasi aplikasi lolos (enrolled/pemilik course + status `live` untuk mahasiswa).
4. **Secret tidak pernah di repo** — `JITSI_JWT_APP_SECRET` dibangkitkan acak (`openssl rand -hex 32`), hidup di `.env` kedua sisi.
5. **Gagal aman** — bila kredensial JWT tidak terkonfigurasi, `mint()` melempar exception dan halaman ruang error; sistem tidak pernah *fallback* ke ruang tanpa autentikasi.

---

## 7. Pengujian dan Pembuktian Kapasitas

Kebutuhan K5 (≥100 peserta) tidak dibiarkan sebagai asumsi — dibuktikan bertahap di server produksi:

| Tanggal | Uji | Hasil |
|---|---|---|
| 31 Mei 2026 | **Uji fungsional lintas-peran** (manual) | Moderator/peserta sesuai token, daur hidup ruang, SSO — terdokumentasi di [testing/conference-test-report-2026-05-31.md](testing/conference-test-report-2026-05-31.md) |
| 1 Jun 2026 | **Uji beban awal 20 bot** | Validasi pipeline pengujian dan perilaku dasar ([testing/loadtest/](testing/loadtest/)) |
| 5 Jun 2026 | **Uji kapasitas 100 peserta** dalam satu ruang | Lihat rincian di bawah — [testing/jitsi-stress-test-report-2026-06-05.md](testing/jitsi-stress-test-report-2026-06-05.md) |

Rancangan uji kapasitas dibuat menyerupai kelas nyata: **4 pengirim video aktif** (dosen + presenter) dan **96 penerima** (mahasiswa menonton). Beban dibangkitkan VM generator terpisah; server dipantau lewat metrik Prometheus JVB dengan ambang batas pengaman (uji dihentikan bila CPU VM > 75%) — ambang **tidak pernah tersentuh**.

Hasil pada 100 peserta: JVB memakai **~1,95 dari 4 core (~54% CPU VM)**, egress **~60 Mbps**, packet loss keluar **0,03%**, **nol** kegagalan DTLS, seluruh 100 endpoint tersambung. Skalanya nyaris linier (~0,02 core dan ~0,6 Mbps per peserta), sehingga plafon aman diekstrapolasi **~140–150 peserta** — yang mentok justru VM *generator* beban (24 vCPU, batas kuota GCP), bukan server.

Kesimpulan untuk penguji: kebutuhan kapasitas terpenuhi **dengan bukti terukur di lingkungan produksi**, bukan klaim teoretis — dan keputusan "satu VM untuk semuanya" (§2) tervalidasi: konferensi 100 peserta menyisakan hampir separuh kapasitas VM untuk aplikasi.

---

## 8. Justifikasi Keputusan (Antisipasi Pertanyaan Penguji)

**1. "Mengapa tidak pakai Zoom / Google Meet saja?"**
Tiga alasan: (i) **biaya** — lisensi per-host/per-menit tidak berkelanjutan untuk institusi; (ii) **integrasi** — Zoom/Meet tidak bisa menerima identitas dan peran moderator langsung dari LMS; peserta harus login akun terpisah dan dosen mengelola undangan manual; (iii) **data** — media kelas berjalan di server pihak ketiga. Jitsi self-hosted menyelesaikan ketiganya: gratis, identitas via JWT, media di VM sendiri.

**2. "Mengapa LiveKit ditinggalkan? Bukankah sudah jalan?"**
LiveKit adalah SFU + SDK, bukan aplikasi konferensi jadi — antarmuka call (grid video, kontrol, berbagi layar, mode mobile) harus dibangun dan dipelihara sendiri. Setelah dua iterasi perbaikan UI, jelas bahwa biaya memelihara "aplikasi video buatan sendiri" tidak sebanding, sementara Jitsi menyediakan UI matang yang sudah teruji jutaan pengguna. Keputusan menggantinya justru penerapan prinsip *jangan membangun ulang yang sudah matang*.

**3. "Kalau begitu kenapa tidak langsung self-hosted, kenapa mampir ke JaaS?"**
Disengaja sebagai langkah antara (commit-nya menyebut "temporary"). Migrasi LiveKit→Jitsi mengubah mesin media dan model UI sekaligus; JaaS memungkinkan memvalidasi UX Jitsi dan model token **tanpa** investasi provisioning server dulu. Setelah tervalidasi, tahap self-hosted tinggal memindahkan penandatanganan token dan domain. Ini manajemen risiko iteratif, bukan keraguan.

**4. "Mengapa JWT HS256 (simetris), bukan RS256 (asimetris)?"**
RS256 tepat ketika penerbit dan pemverifikasi adalah pihak berbeda yang tidak saling percaya penuh (seperti JaaS: kami menerbitkan, server 8x8 memverifikasi dengan kunci publik). Pada self-hosted, penerbit (aplikasi) dan pemverifikasi (Jitsi) adalah **dua layanan milik sendiri di VM yang sama** — secret bersama HS256 lebih sederhana (satu nilai env di dua sisi), tanpa manajemen pasangan kunci, dengan jaminan keamanan setara untuk kasus ini.

**5. "Bagaimana mencegah mahasiswa menjadi moderator?"**
Dua lapis: klaim `moderator:false` di token yang tidak bisa dipalsukan (ditandatangani server), dan mematikan `ENABLE_AUTO_OWNER` di Jitsi. Yang kedua ini temuan penting: konfigurasi bawaan Jitsi mempromosikan peserta *pertama* menjadi moderator mengabaikan token — celah yang hanya ketahuan lewat pengujian lintas-peran (mahasiswa join sebelum dosen). Tanpa pengujian itu, sistem akan tampak aman padahal tidak.

**6. "Bagaimana peserta yang jaringannya diblokir firewall bisa ikut?"**
Jalur utama media adalah UDP 10000 langsung ke JVB. Untuk peserta di balik NAT/firewall ketat yang menolak UDP, disediakan server **TURN (coturn)** yang me-relay media lewat port standar — degradasi anggun alih-alih gagal total.

**7. "Angka 100 peserta itu klaim atau terukur?"**
Terukur, di server produksi sungguhan: 100 endpoint tersambung, JVB ~2 dari 4 core, loss 0,03%, nol kegagalan koneksi — dengan metodologi, ambang pengaman, dan data mentah terdokumentasi dalam laporan uji. Ekstrapolasi konservatif menempatkan plafon ~140–150 peserta; yang jenuh duluan justru mesin pembangkit bebannya.

**8. "Apa kelemahan rancangan ini?"**
(a) **Berbagi VM** — konferensi dan aplikasi berebut satu mesin; uji beban menunjukkan masih ada ruang, tetapi kelas 100 peserta *bersamaan* dengan lonjakan trafik aplikasi belum diuji sebagai skenario gabungan. (b) **Belum ada rekaman sesi** — merekam (Jibri) menuntut CPU besar per ruang; sadar ditunda. (c) **Token 2 jam tidak bisa dicabut** — peserta yang dikeluarkan dari kelas masih memegang token sah sampai kedaluwarsa; mitigasinya moderator bisa mengunci ruang / kick manual, dan token terikat satu ruang saja.

---

## Referensi silang

- [proses-perancangan.md](proses-perancangan.md) — proses perancangan sistem keseluruhan
- [features/conferences.md](features/conferences.md) — detail teknis fitur (model, controller, view)
- [ops/jitsi-self-host.md](ops/jitsi-self-host.md) — runbook provisioning server Jitsi langkah demi langkah
- [testing/conference-test-report-2026-05-31.md](testing/conference-test-report-2026-05-31.md) — laporan uji fungsional
- [testing/jitsi-stress-test-report-2026-06-05.md](testing/jitsi-stress-test-report-2026-06-05.md) — laporan uji kapasitas 100 peserta
- `app/Services/JitsiTokenService.php` — implementasi pembuatan token dan URL ruang
