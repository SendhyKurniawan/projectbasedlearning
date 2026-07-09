# Arsitektur dan Perancangan Sistem PBL Workspace

Dokumen ini menjelaskan arsitektur dan perancangan **PBL Workspace** — platform *Learning Management System* (LMS) untuk Project-Based Learning — secara menyeluruh dan mendetail. Berbeda dengan [architecture.md](architecture.md) yang ditulis ringkas untuk pengembang yang sudah paham, dokumen ini ditulis agar dapat dipahami pembaca yang belum akrab dengan seluruh istilah teknisnya: **setiap istilah teknis dijelaskan pada saat pertama kali muncul**, dan glosarium lengkap tersedia di bagian akhir.

---

## Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Pola Arsitektur: Monolith](#2-pola-arsitektur-monolith)
3. [Tumpukan Teknologi (Technology Stack)](#3-tumpukan-teknologi-technology-stack)
4. [Arsitektur Aplikasi: Pola MVC](#4-arsitektur-aplikasi-pola-mvc)
5. [Perancangan Sistem Peran (Role)](#5-perancangan-sistem-peran-role)
6. [Perancangan Basis Data](#6-perancangan-basis-data)
7. [Arsitektur Server dan Infrastruktur](#7-arsitektur-server-dan-infrastruktur)
8. [Alur Sebuah Request dari Awal sampai Akhir](#8-alur-sebuah-request-dari-awal-sampai-akhir)
9. [Integrasi Layanan Eksternal](#9-integrasi-layanan-eksternal)
10. [Perancangan Keamanan](#10-perancangan-keamanan)
11. [Perancangan Pengujian](#11-perancangan-pengujian)
12. [Glosarium Istilah Teknis](#12-glosarium-istilah-teknis)

---

## 1. Gambaran Umum Sistem

PBL Workspace adalah aplikasi web untuk mendukung pembelajaran berbasis proyek (*Project-Based Learning*, PBL) di lingkungan perguruan tinggi. Sistem melayani tiga jenis pengguna:

| Peran | Tugas utama di sistem |
|---|---|
| **Admin** | Mengelola hierarki akademik (tahun ajaran → semester → jurusan → program studi → kelas), data pengguna, dan data mata kuliah. Admin **tidak** menulis konten pembelajaran. |
| **Dosen** | Memiliki mata kuliah; membuat materi, tugas (tiga tipe: `tugas`, `quiz`, `exercise`), dan konferensi video; menilai pengumpulan mahasiswa. |
| **Mahasiswa** | Terdaftar (enrolled) pada mata kuliah; membaca materi, mengumpulkan tugas, mengerjakan kuis, menulis kode pada exercise, dan bergabung ke konferensi video. |

Fitur inti: manajemen mata kuliah multi-kelas, materi berformat Markdown, tiga tipe penugasan, penilaian, kelompok (grup) proyek, forum diskusi, pengumuman, notifikasi (dalam aplikasi + *push notification* browser), konferensi video (Jitsi), dan eksekusi kode pemrograman langsung di browser (melalui sandbox Piston).

Istilah domain sengaja dipertahankan dalam Bahasa Indonesia di kode sumber (`mata_kuliah`, `kode_matkul`, `sks`, `nim`, `nip`, `dosen`, `mahasiswa`) karena sistem ini dirancang untuk konteks akademik Indonesia.

---

## 2. Pola Arsitektur: Monolith

### 2.1 Apa itu arsitektur monolith?

**Monolith** (monolitik) adalah pola arsitektur di mana **seluruh fungsionalitas aplikasi berada dalam satu basis kode (codebase) dan berjalan sebagai satu unit aplikasi**. Semua fitur — autentikasi, manajemen mata kuliah, penilaian, diskusi, notifikasi — dikompilasi dan dideploy bersama-sama, berbagi satu basis data yang sama, dan berkomunikasi lewat pemanggilan fungsi biasa di dalam memori (bukan lewat jaringan).

Lawan dari monolith adalah **microservices**: pola di mana aplikasi dipecah menjadi banyak layanan kecil yang independen (misalnya "layanan autentikasi", "layanan penilaian", "layanan notifikasi"), masing-masing punya basis data sendiri dan berkomunikasi lewat jaringan (biasanya HTTP/API atau *message queue*).

### 2.2 Mengapa PBL Workspace memilih monolith?

| Pertimbangan | Penjelasan |
|---|---|
| **Kesederhanaan pengembangan** | Satu repositori, satu bahasa utama (PHP), satu kerangka kerja (Laravel). Pengembang tidak perlu mengelola kontrak API antar layanan. |
| **Konsistensi data** | Semua fitur berbagi satu basis data MySQL, sehingga transaksi lintas fitur (misal: menghapus mata kuliah sekaligus tugas dan pengumpulannya) bisa dilakukan atomik dengan *foreign key* dan *cascade delete* — tanpa perlu pola rumit seperti *saga* atau *eventual consistency* yang wajib di microservices. |
| **Biaya operasional rendah** | Cukup satu server virtual (VM) untuk menjalankan seluruh aplikasi. Microservices menuntut orkestrasi (Kubernetes dsb.), *service discovery*, dan pemantauan terdistribusi — berlebihan (over-engineering) untuk skala institusi tunggal. |
| **Skala pengguna yang terukur** | Target pengguna adalah satu politeknik (ratusan hingga ribuan pengguna), bukan jutaan. Monolith yang dirancang baik sanggup melayani skala ini dengan nyaman. |
| **Tim kecil** | Microservices bermanfaat ketika banyak tim bekerja paralel pada layanan berbeda. Untuk tim kecil, ia justru menambah beban koordinasi. |

Penting dicatat: monolith **bukan berarti semuanya berjalan dalam satu proses tunggal**. Pada level infrastruktur, sistem tetap dipisah menjadi beberapa kontainer (aplikasi PHP, web server, basis data, sandbox kode, konferensi video) — lihat Bab 7. Yang monolitik adalah **kode aplikasinya**: satu aplikasi Laravel yang menangani semua fitur.

### 2.3 Monolith server-rendered (bukan SPA)

Selain monolitik, aplikasi ini juga **server-rendered**: HTML halaman dibangun sepenuhnya di sisi server oleh *template engine* Blade, lalu dikirim jadi ke browser. Ini berbeda dengan pola **SPA** (*Single-Page Application*, misal React/Vue), di mana server hanya mengirim kerangka kosong + berkas JavaScript besar, dan browser-lah yang membangun tampilan dengan mengambil data lewat API.

Konsekuensi perancangan:

- **Tidak ada API terpisah** untuk frontend — controller langsung me-render view Blade.
- **JavaScript hanya sebagai "bumbu"** (*sprinkles*): interaktivitas kecil (dropdown, modal, tab) ditangani Alpine.js langsung di atribut HTML, bukan lewat kerangka SPA.
- **Satu-satunya komponen dinamis penuh** adalah halaman detail diskusi, yang memakai satu komponen Livewire (`App\Livewire\Discussion\Show`) agar komentar bisa dikirim tanpa memuat ulang halaman.
- Waktu muat pertama cepat dan SEO-friendly, karena browser menerima HTML utuh.

---

## 3. Tumpukan Teknologi (Technology Stack)

**Technology stack** ("tumpukan teknologi") adalah kumpulan bahasa, kerangka kerja, dan alat yang menyusun sebuah sistem, dari lapisan terbawah (server) sampai teratas (tampilan).

### 3.1 Sisi server (backend)

| Teknologi | Versi | Peran |
|---|---|---|
| **PHP** | ^8.2 | Bahasa pemrograman sisi server. Tanda `^8.2` (notasi *semantic versioning*) berarti "8.2 atau lebih baru, selama masih versi mayor 8". |
| **Laravel** | 12 | *Framework* (kerangka kerja) web PHP — menyediakan routing, ORM, autentikasi, validasi, antrian, notifikasi, dan struktur MVC. |
| **MySQL** | 8.0 | *Relational Database Management System* (RDBMS) — basis data relasional tempat semua data tersimpan dalam tabel-tabel yang saling berelasi. |
| **Laravel Breeze** | ^2.3 | Paket *scaffolding* autentikasi resmi Laravel — menyediakan halaman login, registrasi, reset password. |
| **Livewire** | ^4.1 | Pustaka untuk membuat komponen antarmuka dinamis tanpa menulis JavaScript — komponen dirender ulang di server dan dipatch ke browser lewat AJAX. Hanya dipakai di satu tempat (diskusi). |
| **firebase/php-jwt** | ^7.0 | Pustaka untuk membuat dan menandatangani **JWT** (*JSON Web Token*) — dipakai untuk mengautentikasi pengguna ke server konferensi Jitsi. |
| **laravel-notification-channels/webpush** | ^10.5 | Channel notifikasi **Web Push** — mengirim notifikasi langsung ke browser pengguna bahkan saat tab aplikasi tidak dibuka. |

### 3.2 Sisi klien (frontend)

| Teknologi | Versi | Peran |
|---|---|---|
| **Blade** | (bawaan Laravel) | *Template engine* — sintaks untuk menulis HTML dengan logika tampilan (`@if`, `@foreach`, `{{ $variabel }}`). Dikompilasi menjadi PHP biasa dan di-cache. |
| **Tailwind CSS** | ^3.1 | Framework CSS *utility-first* — styling ditulis sebagai kelas-kelas kecil (`flex`, `p-4`, `text-gray-700`) langsung di HTML, bukan berkas CSS terpisah per komponen. |
| **Alpine.js** | ^3.4 | Pustaka JavaScript ringan untuk interaktivitas deklaratif di atribut HTML (`x-data`, `x-show`, `@click`) — pengganti jQuery modern untuk pola server-rendered. |
| **Vite** | ^7.0 | *Build tool* / *bundler* — menggabungkan, meminifikasi, dan memberi *fingerprint* (hash pada nama berkas untuk cache-busting) aset JavaScript dan CSS. |
| **CodeMirror** | ^5.65 | Editor kode di browser — dipakai mahasiswa saat mengerjakan exercise pemrograman. |
| **EasyMDE** | ^2.20 | Editor Markdown — dipakai dosen saat menulis materi. |
| **Chart.js** | ^4.4 | Pustaka grafik — visualisasi statistik di dashboard. |
| **highlight.js + marked** | — | Pewarnaan sintaks kode dan konversi Markdown → HTML di sisi klien. |

### 3.3 Infrastruktur

| Teknologi | Peran |
|---|---|
| **Docker + Docker Compose** | Kontainerisasi — setiap komponen (aplikasi, web server, basis data, dst.) berjalan dalam kontainer terisolasi; Compose mendefinisikan dan menjalankan semuanya dari satu berkas YAML. |
| **Caddy 2** | *Reverse proxy* terluar + terminasi **TLS** (HTTPS) dengan sertifikat Let's Encrypt otomatis. |
| **Nginx** | Web server — menyajikan berkas statis dan meneruskan request PHP ke PHP-FPM. |
| **PHP-FPM** | *FastCGI Process Manager* — pool proses PHP yang mengeksekusi kode aplikasi. |
| **Google Cloud Platform (GCP)** | Penyedia komputasi awan tempat VM produksi berjalan. |
| **Jitsi Meet (self-hosted)** | Platform konferensi video open-source, di-host sendiri di VM yang sama. |
| **Piston** | Mesin eksekusi kode ter-sandbox — menjalankan kode mahasiswa dengan aman dan terisolasi. |
| **coturn** | Server **TURN/STUN** — relay lalu lintas media video untuk peserta di balik NAT/firewall ketat. |

---

## 4. Arsitektur Aplikasi: Pola MVC

### 4.1 Apa itu MVC?

**MVC** (*Model–View–Controller*) adalah pola arsitektur perangkat lunak yang memisahkan aplikasi menjadi tiga tanggung jawab:

```
        Request HTTP dari browser
                 │
                 ▼
        ┌─────────────────┐
        │   ROUTING       │  routes/web.php — memetakan URL → Controller
        └────────┬────────┘
                 ▼
        ┌─────────────────┐      ┌──────────────┐
        │   CONTROLLER    │◄────►│    MODEL     │◄────► Basis data MySQL
        │ (logika alur)   │      │ (data+relasi)│
        └────────┬────────┘      └──────────────┘
                 ▼
        ┌─────────────────┐
        │     VIEW        │  Template Blade → HTML
        └────────┬────────┘
                 ▼
        Response HTML ke browser
```

- **Model** (`app/Models/`) — merepresentasikan tabel basis data sebagai kelas PHP melalui **Eloquent ORM**. *ORM* (*Object-Relational Mapping*) adalah teknik memetakan baris tabel menjadi objek, sehingga query ditulis sebagai kode PHP (`Course::where('dosen_id', $id)->get()`) alih-alih SQL mentah. Model juga mendefinisikan **relasi** antar tabel (`hasMany`, `belongsTo`).
- **View** (`resources/views/`) — template Blade yang menerima data dari controller dan menghasilkan HTML. Diorganisasi per peran: `admin/`, `dosen/`, `mahasiswa/`, plus komponen bersama.
- **Controller** (`app/Http/Controllers/`) — menerima request, memvalidasi input, memanggil model, dan mengembalikan view. Diorganisasi per peran dalam *namespace* `Admin\`, `Dosen\`, `Mahasiswa\`.

### 4.2 Lapisan pendukung di sekitar MVC

Laravel menambahkan beberapa lapisan yang dimanfaatkan sistem ini:

- **Middleware** (`app/Http/Middleware/`) — "penyaring" yang dilewati setiap request **sebelum** mencapai controller. Contoh: `auth` (menolak pengguna yang belum login) dan `CheckRole` (middleware kustom `role:dosen` yang menolak pengguna dengan peran salah). Ada juga `CheckAssignmentUnlocked` yang memblokir mahasiswa membuka tugas sebelum prasyarat materinya dibaca.
- **Policy** (`app/Policies/`) — kelas otorisasi per-model. **Otorisasi** berbeda dengan **autentikasi**: autentikasi menjawab "siapa kamu?" (login), otorisasi menjawab "bolehkah kamu melakukan ini?". `CoursePolicy` dan `AssignmentPolicy` memastikan, misalnya, dosen hanya bisa mengedit mata kuliah miliknya sendiri. Konvensi proyek: **selalu pakai Policy, bukan pengecekan role manual di controller**.
- **Service** (`app/Services/`) — kelas untuk logika yang tidak cocok di controller maupun model. Contoh: `JitsiTokenService` yang mencetak JWT untuk konferensi.
- **Notification** (`app/Notifications/`) — kelas notifikasi yang dikirim ke dua channel: `database` (lonceng notifikasi dalam aplikasi) dan `webpush` (notifikasi browser).
- **Validasi inline** — konvensi proyek ini memvalidasi input langsung di controller dengan `$request->validate([...])`, bukan lewat kelas FormRequest terpisah (kecuali dua kelas bawaan Breeze). Ini keputusan sadar demi kesederhanaan.

### 4.3 Struktur direktori inti

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # controller khusus admin
│   │   ├── Dosen/          # controller khusus dosen
│   │   ├── Mahasiswa/      # controller khusus mahasiswa
│   │   └── ...             # controller bersama (profil, notifikasi, eksekusi kode)
│   └── Middleware/         # CheckRole, CheckAssignmentUnlocked
├── Livewire/Discussion/    # satu-satunya komponen Livewire
├── Models/                 # Eloquent model (User, Course, Assignment, ...)
├── Notifications/          # kelas notifikasi database + webpush
├── Policies/               # CoursePolicy, AssignmentPolicy
└── Services/               # JitsiTokenService
resources/views/            # template Blade per peran
routes/web.php + auth.php   # peta URL → controller
database/migrations/        # riwayat perubahan skema basis data
```

---

## 5. Perancangan Sistem Peran (Role)

Setiap pengguna memiliki tepat satu nilai pada kolom `users.role` — sebuah **enum** MySQL (tipe kolom yang hanya menerima daftar nilai tetap): `admin`, `dosen`, atau `mahasiswa`.

Perancangan aksesnya berlapis tiga:

1. **Pemisahan prefix URL** — semua route admin di bawah `/admin`, dosen di bawah `/dosen`, mahasiswa di bawah `/mahasiswa`. Grup route masing-masing dilindungi middleware `auth` + `role:<peran>`.
2. **Middleware `CheckRole`** — memeriksa `users.role` pada setiap request ke grup tersebut; peran yang salah ditolak.
3. **Policy** — lapisan terakhir yang memeriksa kepemilikan objek (bukan sekadar peran). Contoh: sesama dosen sama-sama lolos middleware `role:dosen`, tetapi `CoursePolicy` menolak dosen A mengedit mata kuliah dosen B.

Route root `/` membaca peran pengguna yang login dan mengarahkan (redirect) ke dashboard yang sesuai; tamu diarahkan ke `/login`. Sejumlah route bersifat *auth-only* (dapat diakses semua peran yang sudah login): profil, notifikasi, diskusi, pengumuman, langganan push, dan proxy eksekusi kode.

---

## 6. Perancangan Basis Data

### 6.1 Hierarki akademik

Struktur akademik dirancang sebagai pohon relasi:

```
AcademicYear (tahun ajaran)
    └── Semester (Ganjil/Genap, punya is_active)

Department (jurusan)
    └── StudyProgram (program studi, level D3/D4/S1/S2/S3)
            └── StudentClass (kelas, terikat ke satu semester)
                    ├── User mahasiswa (via student_class_id)
                    └── Course (mata kuliah per kelas)
```

Relasi antar tabel ditegakkan dengan **foreign key** (kunci asing) — kolom yang menunjuk ke *primary key* (kunci utama) tabel lain, sehingga basis data menolak data yatim (misal kelas yang menunjuk program studi yang tidak ada).

### 6.2 Entitas pembelajaran inti

| Entitas | Penjelasan |
|---|---|
| `Course` | Satu baris per tuple (dosen, kode matkul, kelas, semester). |
| `Material` | Materi pembelajaran (Markdown + lampiran berkas). |
| `Assignment` | Penugasan; kolom `type` ∈ `tugas` \| `quiz` \| `exercise`. |
| `QuizQuestion` + `QuizOption` | Soal kuis (pilihan ganda / esai / cuplikan kode) dan opsinya. |
| `Submission` | Pengumpulan mahasiswa — satu tabel untuk ketiga tipe assignment. |
| `Group` + `GroupMember` | Kelompok proyek untuk tugas kelompok. |
| `Conference` | Sesi konferensi video (menyimpan `room_name` Jitsi). |
| `Discussion` + `DiscussionComment` | Forum tanya-jawab per mata kuliah. |
| `Announcement` | Pengumuman dengan target audiens (`all`/`dosen`/`mahasiswa`/`specific`). |
| `enrollments` (pivot) | **Tabel pivot** — tabel penghubung relasi *many-to-many* antara mahasiswa dan mata kuliah. |

### 6.3 Keputusan perancangan penting

**Mata kuliah "sibling" (multi-kelas).** Dosen yang mengajar mata kuliah yang sama ke tiga kelas mendapat **tiga baris `Course`** — sama `dosen_id + kode_matkul + semester_id`, beda `student_class_id`. Baris-baris ini disebut *siblings*. Konsekuensinya:

- Validasi keunikan mata kuliah bersifat **komposit** pada `(kode_matkul, dosen_id, semester_id, student_class_id)` — bukan unik global pada `kode_matkul`.
- Antarmuka dosen mengelompokkan siblings secara visual dengan `course_group_key` (gabungan `dosen_id|nama_matkul|semester_id`).
- Fitur **copy fan-out**: saat dosen membuat materi/tugas/konferensi, form menampilkan checkbox kelas sibling; sistem membuat salinan record untuk setiap kelas terpilih. Demi keamanan, daftar ID yang dikirim form selalu di-*intersect* (diiriskan) dengan daftar sibling sah milik dosen tersebut — mencegah dosen menarget mata kuliah dosen lain lewat request palsu.

**`exercise_config` sebagai kolom JSON.** Konfigurasi exercise (bahasa, kode awal, kunci jawaban, kata kunci wajib, petunjuk) disimpan dalam **satu kolom JSON** dengan *cast* Laravel (otomatis dikonversi array PHP ↔ teks JSON), bukan lima kolom terpisah. Alasannya: struktur ini hanya relevan untuk tipe `exercise` dan bentuknya bisa berevolusi tanpa migrasi skema.

**Query pivot langsung.** Alur mahasiswa membaca `enrollments` dengan *query builder* mentah (`DB::table('enrollments')->where(...)`) alih-alih relasi Eloquent, demi kecepatan (menghindari *hydration* — proses ORM membangun objek model penuh dari tiap baris). Ini konvensi yang disengaja; satu-satunya pengecualian adalah halaman jadwal mahasiswa.

**Migrasi sebagai riwayat skema.** Semua perubahan struktur tabel dicatat sebagai berkas **migration** (`database/migrations/`) — skrip bertanggal yang bisa dijalankan berurutan untuk membangun skema dari nol. Ini membuat skema basis data ikut ter-*version control* bersama kode.

---

## 7. Arsitektur Server dan Infrastruktur

### 7.1 Server produksi

Seluruh sistem berjalan di **satu VM** (*Virtual Machine* — komputer virtual yang disewa dari penyedia awan) di Google Cloud Platform:

| Aspek | Nilai |
|---|---|
| Nama VM | `pjbl-vm` |
| Tipe mesin | `e2-standard-4` — 4 vCPU (inti prosesor virtual), 16 GB RAM |
| Zona | `asia-southeast2-a` (Jakarta) |
| Sistem operasi | Debian 12 (bookworm) |
| Alamat IP statis | `34.50.107.24` |

### 7.2 Kontainerisasi dengan Docker

**Docker** adalah teknologi *kontainerisasi*: setiap komponen dikemas bersama seluruh dependensinya ke dalam **kontainer** — lingkungan terisolasi yang berbagi kernel sistem operasi host tetapi memiliki filesystem, jaringan, dan proses sendiri. Bedanya dengan VM: kontainer jauh lebih ringan karena tidak membawa sistem operasi utuh.

**Docker Compose** mendefinisikan seluruh kontainer dalam berkas `docker-compose.yml`, dan `docker-compose.override.yml` menambahkan kontainer khusus produksi (Caddy dan coturn). Daftar kontainer:

| Kontainer | Image | Peran |
|---|---|---|
| `pjbl-app` | build dari `Dockerfile` | Aplikasi Laravel di atas **PHP-FPM** — *FastCGI Process Manager*, pool proses PHP yang mengeksekusi kode aplikasi (port internal 9000). |
| `pjbl-web` | `nginx:alpine` | **Nginx** — web server yang menyajikan aset statis dan meneruskan request `.php` ke PHP-FPM lewat protokol **FastCGI**. |
| `pjbl-db` | `mysql:8.0` | Basis data MySQL; hanya bisa diakses dari jaringan internal Docker (tidak terekspos ke internet). |
| `pjbl-piston` | `ghcr.io/engineer-man/piston` | **Sandbox** eksekusi kode — menjalankan kode kiriman mahasiswa dalam isolasi ketat agar kode berbahaya tidak bisa menyentuh sistem. |
| `pjbl-caddy` | `caddy:2-alpine` | **Reverse proxy** terluar + terminasi **TLS**. |
| `pjbl-coturn` | `coturn/coturn` | Server **TURN** untuk relay media konferensi video. |
| `jitsi-*` (proyek compose terpisah) | `jitsi/*:stable-9909` | Kontainer Jitsi Meet (web, prosody, jicofo, jvb) untuk konferensi video. |
| `pjbl-phpmyadmin`, `pjbl-mailhog` | — | Alat bantu pengembangan (GUI basis data; penangkap email dev). |

### 7.3 Topologi jaringan produksi

```
                         Internet (browser pengguna)
                                    │  HTTPS (port 443, TLS)
                                    ▼
                     ┌──────────────────────────────┐
                     │  pjbl-caddy  (reverse proxy)  │
                     │  TLS Let's Encrypt otomatis   │
                     └──────┬───────────────┬───────┘
        polimedia.pblworkspace.com    meet.polimedia.pblworkspace.com
                     │                        │
                     ▼                        ▼
              ┌────────────┐          ┌─────────────┐
              │  pjbl-web  │          │  jitsi-web  │
              │  (Nginx)   │          │  (Jitsi)    │
              └─────┬──────┘          └─────────────┘
                    │ FastCGI :9000
                    ▼
              ┌────────────┐   SQL    ┌────────────┐
              │  pjbl-app  │─────────►│  pjbl-db   │
              │ (PHP-FPM)  │          │ (MySQL 8)  │
              └─────┬──────┘          └────────────┘
                    │ HTTP internal
                    ▼
              ┌─────────────┐
              │ pjbl-piston │  (sandbox eksekusi kode)
              └─────────────┘

   Jalur media video (di luar Caddy):
   Browser ──UDP 10000──► jitsi-jvb   (langsung, saat jaringan mengizinkan)
   Browser ──UDP/TCP 3478─► pjbl-coturn ──► jvb   (relay cadangan di balik NAT ketat)
```

Penjelasan komponen kunci:

- **Reverse proxy** — server yang berdiri di depan server-server lain dan meneruskan request ke tujuan yang tepat berdasarkan nama domain. Caddy melayani `polimedia.pblworkspace.com` → Nginx aplikasi, dan `meet.polimedia.pblworkspace.com` → Jitsi. Dengan satu titik masuk, hanya Caddy yang perlu terekspos ke internet.
- **TLS** (*Transport Layer Security*) — protokol enkripsi di balik HTTPS. **Terminasi TLS** artinya Caddy-lah yang mendekripsi lalu lintas HTTPS; komunikasi antar kontainer di belakangnya memakai HTTP polos di jaringan internal Docker yang tidak terjangkau dari luar.
- **Let's Encrypt** — otoritas sertifikat (*Certificate Authority*) gratis; Caddy menerbitkan dan memperpanjang sertifikat TLS-nya sepenuhnya otomatis.
- **HTTP/3** — versi terbaru protokol HTTP di atas UDP; Caddy mengaktifkannya di port 443/udp.
- **NAT traversal, STUN, TURN** — peserta konferensi di balik *NAT* (router yang menyembunyikan alamat IP lokal) kadang tidak bisa menerima lalu lintas video langsung. *STUN* membantu perangkat menemukan alamat publiknya; jika tetap gagal, *TURN* (coturn) menjadi perantara yang me-relay seluruh lalu lintas media.

### 7.4 Alur request PHP: Caddy → Nginx → PHP-FPM

Mengapa ada dua "web server" (Caddy dan Nginx)? Perannya berbeda:

1. **Caddy** menangani urusan internet: TLS, multi-domain, HTTP/3, kompresi gzip.
2. **Nginx** menangani urusan aplikasi: menyajikan aset statis (`/build/*` dengan header cache setahun karena nama berkasnya sudah ber-*fingerprint* Vite), kompresi Brotli, dan aturan `try_files` khas Laravel — setiap URL yang bukan berkas fisik diarahkan ke `index.php`, titik masuk tunggal (*front controller*) aplikasi.
3. **PHP-FPM** mengeksekusi `index.php`, yang mem-bootstrap Laravel dan menghasilkan response.

### 7.5 Keputusan konfigurasi runtime

- **Queue (antrian tugas) = `sync`.** Laravel bisa menunda pekerjaan berat (kirim email, notifikasi massal) ke *queue* yang diproses *worker* terpisah. Produksi saat ini memakai driver `sync` — semua pekerjaan dieksekusi langsung dalam request yang sama, tanpa worker. Ini disengaja: volume notifikasi masih kecil; worker baru diperlukan bila fan-out notifikasi mulai memperlambat request.
- **Broadcasting = `log`.** Tidak ada WebSocket/Pusher — aplikasi tidak butuh pembaruan real-time server→browser di luar Livewire.
- **Cache konfigurasi.** Setiap deploy diakhiri `composer optimize` (`config:cache`, `route:cache`, `view:cache`, `event:cache`) — Laravel mengompilasi konfigurasi dan rute ke berkas tunggal agar tidak diparsing ulang setiap request.
- **Email produksi** memakai relay SMTP **Resend**; **MailHog** hanya untuk menangkap email di pengembangan.

---

## 8. Alur Sebuah Request dari Awal sampai Akhir

Contoh konkret: mahasiswa membuka daftar tugas sebuah mata kuliah.

```
1. Browser meminta GET https://polimedia.pblworkspace.com/mahasiswa/courses/42/assignments
2. Caddy menerima koneksi HTTPS, mendekripsi TLS, meneruskan ke pjbl-web (Nginx)
3. Nginx: URL bukan berkas statis → teruskan ke PHP-FPM (index.php) via FastCGI
4. Laravel bootstrap → Router mencocokkan URL dengan route bernama
5. Middleware berjalan berurutan:
   ├─ auth           → sudah login? (cek session cookie) — jika belum: redirect /login
   └─ role:mahasiswa → users.role == 'mahasiswa'? — jika bukan: tolak (403)
6. Controller Mahasiswa dieksekusi:
   ├─ cek enrollment: DB::table('enrollments') — terdaftar di course 42?
   └─ query Assignment beserta status pengumpulan mahasiswa ini
7. Controller me-render view Blade → HTML utuh
8. Response HTML mengalir balik: PHP-FPM → Nginx → Caddy (dienkripsi TLS) → browser
9. Browser memuat aset /build/*.js dan /build/*.css (di-cache setahun oleh
   header immutable — nama berkas berubah otomatis saat isinya berubah)
```

Untuk aksi tulis (POST, misalnya mengumpulkan tugas), ada satu lapisan tambahan di langkah 5: verifikasi **token CSRF** (lihat Bab 10).

---

## 9. Integrasi Layanan Eksternal

### 9.1 Konferensi video: Jitsi self-hosted + JWT

Jitsi Meet di-host sendiri di subdomain `meet.polimedia.pblworkspace.com` (bukan layanan pihak ketiga berbayar). Kontrol akses memakai **JWT** (*JSON Web Token*) — token berisi klaim (data) yang **ditandatangani secara kriptografis** sehingga tidak bisa dipalsukan:

- Saat pengguna membuka ruang konferensi, `JitsiTokenService::mint()` mencetak JWT bertanda tangan **HS256** (*HMAC-SHA256* — tanda tangan simetris berbasis secret bersama antara aplikasi dan server Jitsi).
- Klaim token memuat identitas pengguna, nama ruang, masa berlaku (2 jam), dan flag **moderator**: `true` untuk dosen/admin, `false` untuk mahasiswa.
- Server Jitsi dikonfigurasi `ENABLE_AUTO_OWNER=0` sehingga status moderator **hanya** datang dari token — tanpa ini, mahasiswa yang bergabung pertama akan otomatis jadi moderator (celah keamanan).

### 9.2 Eksekusi kode: proxy Piston

Fitur exercise memungkinkan mahasiswa menulis dan menjalankan kode dari browser. Perancangan keamanannya:

- Browser **tidak pernah** memanggil Piston langsung. Semua lewat endpoint `POST /execute-code` di aplikasi (pola **proxy** — perantara yang mengontrol akses).
- Proxy memvalidasi bahasa terhadap *allowlist* (daftar bahasa yang diizinkan), lalu meneruskan ke kontainer Piston di jaringan internal.
- **Rate limiting** (pembatasan laju) `throttle:10,1`: maksimal 10 eksekusi per menit per pengguna — mencegah penyalahgunaan sumber daya server.
- Piston menjalankan kode dalam sandbox dengan filesystem sementara (*tmpfs*).
- Catatan perancangan: hasil eksekusi/validasi kode **tidak** menjadi nilai otomatis — sistem hanya menyimpan hasil pencocokan kata kunci sebagai *petunjuk* ("Validasi Mesin") bagi dosen; penilaian tetap manual.

### 9.3 Notifikasi push: Web Push + VAPID

Notifikasi browser memakai standar **Web Push**: browser pengguna berlangganan lewat **Service Worker** (skrip JavaScript yang berjalan di latar belakang browser, terpisah dari halaman), dan server mengirim pesan terenkripsi ke *push service* milik vendor browser. Server diidentifikasi dengan sepasang kunci **VAPID** (*Voluntary Application Server Identification*) — kunci publik/privat yang membuktikan pengirim sah.

### 9.4 Email: SMTP Resend

Email transaksional (OTP registrasi, reset password) dikirim lewat **SMTP** (*Simple Mail Transfer Protocol*) melalui relay **Resend** — layanan pengiriman email yang menjaga reputasi pengiriman (deliverability) sehingga email tidak masuk spam.

---

## 10. Perancangan Keamanan

| Lapisan | Mekanisme |
|---|---|
| **Transport** | Seluruh lalu lintas dienkripsi TLS (HTTPS); cookie sesi diberi flag `secure` (hanya dikirim lewat HTTPS). |
| **Autentikasi** | Laravel Breeze; password di-*hash* dengan **bcrypt** (fungsi satu-arah — password asli tidak pernah disimpan). Registrasi mahasiswa memakai verifikasi **OTP** (*One-Time Password* via email) plus persetujuan admin. |
| **Otorisasi** | Tiga lapis: prefix route per peran → middleware `role:` → Policy per objek (Bab 5). |
| **CSRF** | *Cross-Site Request Forgery* — serangan di mana situs jahat mengirim request diam-diam atas nama korban yang sedang login. Laravel menolak semua request tulis tanpa **token CSRF** yang valid (disisipkan otomatis di setiap form Blade via `@csrf`). |
| **SQL Injection** | Serangan menyisipkan SQL lewat input. Dicegah karena Eloquent dan query builder selalu memakai **prepared statement** (nilai input dikirim terpisah dari teks SQL, tidak pernah disambung sebagai string). |
| **XSS** | *Cross-Site Scripting* — menyisipkan JavaScript jahat lewat konten. Blade meng-*escape* seluruh output `{{ }}` secara default (karakter HTML dinetralkan). |
| **Mass assignment** | Model membatasi kolom yang boleh diisi massal lewat `$fillable`, mencegah request memanipulasi kolom sensitif (misal `role`). |
| **Copy fan-out** | ID kelas target selalu di-intersect dengan daftar sibling sah (Bab 6.3) — otorisasi tidak pernah mempercayai input form. |
| **Isolasi kode** | Kode mahasiswa hanya berjalan di sandbox Piston lewat proxy ber-rate-limit (Bab 9.2). |
| **Isolasi jaringan** | MySQL dan Piston tidak terekspos internet; hanya Caddy yang membuka port publik. |
| **Secret** | Semua kredensial (kunci aplikasi, password DB, secret JWT, kunci VAPID) berada di berkas `.env` yang tidak pernah di-commit ke repositori. |

---

## 11. Perancangan Pengujian

| Lapisan | Alat | Cakupan |
|---|---|---|
| **Unit test** | Pest (di atas PHPUnit) | Fungsi/kelas terisolasi — `tests/Unit`. |
| **Feature test** | Pest | Simulasi request HTTP penuh terhadap aplikasi (routing + middleware + controller + DB) tanpa browser — `tests/Feature`. Titik masuk: `composer test`. |
| **Browser test** | Laravel Dusk | Mengendalikan browser Chrome sungguhan untuk menguji alur end-to-end; memakai basis data SQLite in-memory terpisah. |
| **E2E (WIP)** | Playwright | Suite pengujian lintas-browser yang sedang disiapkan. |

**Pest** adalah framework pengujian PHP dengan sintaks ekspresif; **E2E** (*end-to-end*) berarti menguji sistem persis seperti pengguna memakainya — dari klik di browser sampai data tersimpan.

---

## 12. Glosarium Istilah Teknis

| Istilah | Definisi singkat |
|---|---|
| **Alpine.js** | Pustaka JavaScript ringan untuk interaktivitas langsung di atribut HTML. |
| **API** | *Application Programming Interface* — kontrak yang memungkinkan dua program saling berkomunikasi. |
| **Autentikasi** | Proses memverifikasi identitas pengguna ("siapa kamu?"). |
| **Otorisasi** | Proses memverifikasi hak akses ("bolehkah kamu?"). |
| **bcrypt** | Algoritma hash password satu-arah yang lambat secara sengaja agar sulit dibongkar paksa. |
| **Blade** | Template engine bawaan Laravel untuk menulis view. |
| **Bundler / build tool** | Alat yang menggabungkan dan mengoptimalkan berkas JS/CSS untuk produksi (di sini: Vite). |
| **Cache** | Penyimpanan sementara hasil komputasi agar tidak dihitung ulang. |
| **Cast (Eloquent)** | Konversi otomatis tipe kolom DB ↔ tipe PHP (misal teks JSON ↔ array). |
| **Container (kontainer)** | Lingkungan terisolasi ringan untuk menjalankan aplikasi beserta dependensinya (Docker). |
| **CSRF** | Cross-Site Request Forgery; serangan request palsu atas nama korban — ditangkal dengan token per-form. |
| **Docker Compose** | Alat mendefinisikan dan menjalankan multi-kontainer dari satu berkas YAML. |
| **Eloquent ORM** | ORM bawaan Laravel; memetakan tabel ke kelas dan baris ke objek. |
| **Enum** | Tipe data dengan daftar nilai tetap yang diperbolehkan. |
| **E2E testing** | Pengujian dari ujung ke ujung, meniru pengguna asli di browser. |
| **FastCGI** | Protokol komunikasi antara web server (Nginx) dan prosesor aplikasi (PHP-FPM). |
| **Foreign key** | Kolom yang merujuk primary key tabel lain; menjaga integritas relasi. |
| **Framework** | Kerangka kerja — fondasi kode siap pakai dengan struktur dan aturan main. |
| **Fingerprint (aset)** | Hash pada nama berkas (`app-a1b2c3.js`) agar cache browser otomatis basi saat isi berubah. |
| **HS256** | HMAC-SHA256 — skema tanda tangan JWT simetris berbasis secret bersama. |
| **HTTP/3** | Versi HTTP terbaru di atas protokol UDP (lebih cepat pada jaringan buruk). |
| **JWT** | JSON Web Token — token berisi klaim yang ditandatangani sehingga anti-pemalsuan. |
| **Let's Encrypt** | Otoritas sertifikat TLS gratis dan otomatis. |
| **Livewire** | Pustaka Laravel untuk komponen UI dinamis yang dirender di server. |
| **Middleware** | Penyaring yang memproses request sebelum/atau sesudah controller. |
| **Migration** | Skrip perubahan skema basis data yang ter-version-control. |
| **Monolith** | Arsitektur satu basis kode + satu unit deploy untuk seluruh fitur. |
| **Microservices** | Arsitektur banyak layanan kecil independen yang berkomunikasi lewat jaringan. |
| **MVC** | Model–View–Controller; pemisahan data, tampilan, dan logika alur. |
| **NAT** | Network Address Translation; router yang menyembunyikan IP lokal di balik satu IP publik. |
| **ORM** | Object-Relational Mapping; menulis query lewat objek, bukan SQL mentah. |
| **OTP** | One-Time Password; kode sekali pakai untuk verifikasi (di sini via email). |
| **Pivot table** | Tabel penghubung relasi many-to-many (di sini: `enrollments`). |
| **Policy** | Kelas otorisasi Laravel per-model. |
| **Prepared statement** | Teknik query yang memisahkan SQL dari nilai input — menutup celah SQL injection. |
| **PHP-FPM** | FastCGI Process Manager; pengelola pool proses PHP. |
| **Proxy** | Perantara yang meneruskan request atas nama pihak lain sambil mengontrol akses. |
| **Queue (antrian)** | Mekanisme menunda pekerjaan berat ke proses latar belakang. |
| **Rate limiting** | Pembatasan jumlah request per satuan waktu. |
| **RDBMS** | Sistem basis data relasional (tabel + relasi + SQL); di sini MySQL 8. |
| **Reverse proxy** | Server di depan server lain yang merutekan request masuk berdasarkan domain/path. |
| **Sandbox** | Lingkungan eksekusi terisolasi ketat untuk kode tak tepercaya. |
| **Server-rendered** | HTML dibangun di server, dikirim jadi ke browser (lawan dari SPA). |
| **Service Worker** | Skrip browser yang berjalan di latar belakang, halaman tidak harus terbuka (dipakai Web Push). |
| **Session** | Data status login pengguna yang disimpan server, dirujuk cookie di browser. |
| **SPA** | Single-Page Application; UI dibangun JavaScript di browser, data via API. |
| **SMTP** | Protokol standar pengiriman email antar server. |
| **SQL injection** | Serangan penyisipan perintah SQL lewat input pengguna. |
| **STUN / TURN** | Protokol bantu koneksi peer-to-peer di balik NAT; TURN me-relay penuh bila jalur langsung gagal. |
| **Template engine** | Alat menyusun HTML dinamis dari template + data (di sini: Blade). |
| **TLS** | Protokol enkripsi di balik HTTPS. |
| **Utility-first CSS** | Pendekatan styling dengan kelas-kelas kecil satu-fungsi (Tailwind). |
| **VAPID** | Pasangan kunci identifikasi server aplikasi pada protokol Web Push. |
| **Vite** | Build tool frontend modern untuk bundling dan fingerprinting aset. |
| **VM** | Virtual Machine; komputer virtual di atas perangkat keras fisik bersama. |
| **Web Push** | Standar notifikasi dari server ke browser, bahkan saat situs tidak dibuka. |
| **WebSocket** | Koneksi dua-arah persisten browser↔server (tidak dipakai di sistem ini). |
| **XSS** | Cross-Site Scripting; penyisipan skrip jahat lewat konten — ditangkal escaping Blade. |

---

## Referensi silang

- [architecture.md](architecture.md) — detail arsitektur aplikasi untuk pengembang
- [database.md](database.md) — referensi skema per tabel
- [deployment.md](deployment.md) — topologi produksi dan runbook deploy
- [auth-roles.md](auth-roles.md) — detail middleware, policy, dan alur registrasi
- [frontend.md](frontend.md) — pola frontend Blade/Alpine/Vite
- [ops/jitsi-self-host.md](ops/jitsi-self-host.md) — provisioning Jitsi lengkap
