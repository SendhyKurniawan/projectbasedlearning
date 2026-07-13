# Proses Perancangan dan Pengembangan PBL Workspace

Dokumen ini menjelaskan **proses** perancangan sistem — bagaimana sistem ini dirancang tahap demi tahap, keputusan apa yang diambil di setiap tahap, dan mengapa. Dokumen ini ditulis sebagai bahan penjelasan kepada dosen penguji: fokusnya adalah *alur berpikir* dan *justifikasi keputusan*, bukan detail teknis hasil akhirnya. Untuk hasil rancangan secara rinci, lihat [arsitektur-dan-perancangan.md](arsitektur-dan-perancangan.md); untuk detail implementasi, lihat [bab4-implementasi.md](bab4-implementasi.md).

---

## Daftar Isi

1. [Metodologi Pengembangan](#1-metodologi-pengembangan)
2. [Tahap 1 — Analisis Kebutuhan](#2-tahap-1--analisis-kebutuhan)
3. [Tahap 2 — Perancangan Sistem](#3-tahap-2--perancangan-sistem)
4. [Tahap 3 — Implementasi Iteratif](#4-tahap-3--implementasi-iteratif)
5. [Tahap 4 — Pengujian](#5-tahap-4--pengujian)
6. [Tahap 5 — Deployment dan Operasional](#6-tahap-5--deployment-dan-operasional)
7. [Justifikasi Keputusan Perancangan (Antisipasi Pertanyaan Penguji)](#7-justifikasi-keputusan-perancangan-antisipasi-pertanyaan-penguji)

---

## 1. Metodologi Pengembangan

Sistem dikembangkan dengan pendekatan **iteratif–inkremental** (turunan praktik *Agile*): fungsionalitas dibangun bertahap dalam iterasi, setiap iterasi menghasilkan sistem yang berjalan dan dapat diuji, lalu hasil evaluasinya menjadi masukan iterasi berikutnya.

Pendekatan ini dipilih — alih-alih *waterfall* murni — karena:

1. **Kebutuhan tidak seluruhnya diketahui di awal.** Fitur seperti konferensi video dan notifikasi push baru dapat dirancang final setelah fondasi LMS berjalan dan dicoba.
2. **Satu pengembang.** Iterasi pendek memungkinkan koreksi arah cepat tanpa biaya koordinasi.
3. **Risiko teknis terkonsentrasi di beberapa fitur** (konferensi video, eksekusi kode ter-sandbox). Fitur berisiko tinggi ini divalidasi lebih dulu dengan prototipe berjalan sebelum dipoles.

Bukti proses iteratif ini terekam pada riwayat *version control* (Git, Februari–Juli 2026):

| Periode | Iterasi | Hasil |
|---|---|---|
| Feb 2026 | **Fondasi** | Inisiasi proyek Laravel, autentikasi, struktur peran, entitas inti LMS (mata kuliah, materi, tugas, pengumpulan), eksperimen pengujian awal |
| Mar 2026 (awal) | **Konferensi video v1** | Integrasi konferensi video, manajemen nilai, lokalisasi antarmuka ke Bahasa Indonesia |
| Mar 2026 (akhir) | **Notifikasi** | Notifikasi dalam aplikasi + Web Push (VAPID, Service Worker) |
| Apr 2026 | **Rework UI & design system** | Perombakan antarmuka dua tahap; standardisasi *design token*, branding, dan konsistensi bahasa di dashboard admin/dosen |
| Mei 2026 | **Jitsi self-hosted & pembersihan arsitektur** | Migrasi konferensi ke Jitsi self-hosted dengan JWT HS256; penghapusan kode mati (model `Role`, `NotificationService`); overhaul dokumentasi |
| Jun 2026 (awal) | **Testing ground produksi** | Seeding data riil PoliMedia (kurikulum 4 tahun via PDDIKTI), akun penguji, katalog mata kuliah per kelas + *auto-enroll*, perbaikan progres dashboard |
| Jun–Jul 2026 | **Stabilisasi** | SEO (meta terpusat, sitemap dinamis), perbaikan hasil pengujian, dokumentasi akhir |

Pola yang terlihat: **fitur inti dulu → fitur berisiko divalidasi → kualitas (UI/keamanan) → data & pengujian nyata → stabilisasi**. Ini pola inkremental yang disengaja, bukan kebetulan.

---

## 2. Tahap 1 — Analisis Kebutuhan

### 2.1 Identifikasi masalah

Pembelajaran berbasis proyek (*Project-Based Learning*, PBL) di perguruan tinggi vokasi membutuhkan lebih dari sekadar LMS pengumpul tugas: mahasiswa bekerja dalam kelompok, mengerjakan kode program, berdiskusi, dan mengikuti sesi sinkron (kelas daring). LMS umum (mis. Moodle) bisa dikonfigurasi ke arah itu, tetapi berat, generik, dan tidak mencerminkan struktur akademik Indonesia (jurusan → program studi → kelas, `nim`/`nip`, SKS) secara natif.

Dari situ dirumuskan kebutuhan: **satu platform terpadu untuk alur PBL** — materi, tiga jenis penugasan (tugas berkas, kuis, latihan pemrograman), kelompok, diskusi, konferensi video, dan notifikasi — dengan struktur data yang memodelkan hierarki akademik Indonesia apa adanya.

### 2.2 Identifikasi aktor dan kebutuhan fungsional

Analisis menghasilkan **tiga aktor** dengan tanggung jawab saling lepas:

| Aktor | Kebutuhan fungsional utama |
|---|---|
| **Admin** | Mengelola hierarki akademik (tahun ajaran, semester, jurusan, prodi, kelas), data pengguna (termasuk persetujuan registrasi dosen), penugasan mata kuliah, pemantauan nilai |
| **Dosen** | CRUD materi/tugas/kuis/latihan/konferensi pada mata kuliah miliknya; menilai pengumpulan; mengelola kelompok; menyalin konten antar kelas paralel |
| **Mahasiswa** | Melihat materi (dengan prasyarat baca), mengumpulkan tugas, mengerjakan kuis dan latihan kode, melihat nilai, jadwal, bergabung konferensi |

Ditambah kebutuhan lintas aktor: forum diskusi per mata kuliah, pengumuman bertarget, notifikasi (dalam aplikasi + push browser), dan profil.

Satu temuan analisis yang berdampak besar pada perancangan data: **dosen di politeknik lazim mengajar mata kuliah yang sama ke beberapa kelas paralel** (kelas A, B, C). Kebutuhan ini melahirkan konsep *sibling course* dan fitur *copy fan-out* (§3.2).

### 2.3 Kebutuhan non-fungsional

| Kategori | Kebutuhan | Konsekuensi rancangan |
|---|---|---|
| Keamanan | Isolasi antar peran; kode mahasiswa tidak boleh menyentuh server | Otorisasi tiga lapis; sandbox Piston di belakang proxy ber-*rate-limit* |
| Kinerja | Responsif untuk skala satu institusi (ratusan–ribuan pengguna) | Monolith server-rendered, cache konfigurasi/route/view, query pivot langsung |
| Biaya | Berjalan pada satu VM cloud kelas menengah | Semua layanan (aplikasi, DB, Jitsi, Piston) dikontainerkan di satu VM |
| Ketersediaan konferensi | Tidak bergantung kuota layanan pihak ketiga | Jitsi Meet di-*self-host* |
| Kemudahan pemeliharaan | Dapat dipahami dan dilanjutkan pengembang lain | Konvensi Laravel standar, dokumentasi `docs/`, migrasi ter-*version-control* |

---

## 3. Tahap 2 — Perancangan Sistem

Perancangan dilakukan pada lima aspek: arsitektur, basis data, proses/alur, antarmuka, dan keamanan.

### 3.1 Perancangan arsitektur

Keputusan arsitektural pertama dan paling menentukan: **monolith server-rendered**, bukan microservices dan bukan SPA (*Single-Page Application*).

Alur pengambilan keputusannya:

1. **Skala target** — satu institusi, bukan multi-tenant jutaan pengguna → kompleksitas microservices (orkestrasi, *service discovery*, konsistensi terdistribusi) tidak sebanding manfaatnya.
2. **Konsistensi data** — fitur-fitur saling terkait erat (hapus mata kuliah harus ikut menghapus tugas, pengumpulan, diskusi) → satu basis data dengan *foreign key cascade* jauh lebih sederhana dan lebih aman daripada transaksi terdistribusi.
3. **Sifat aplikasi** — dominan formulir dan tampilan data, bukan aplikasi *real-time* → HTML yang dirender server (Blade) memadai; JavaScript cukup sebagai "bumbu" (Alpine.js), dengan satu komponen Livewire hanya di halaman diskusi yang memang butuh interaksi tanpa muat ulang.

Kerangka kerja **Laravel 12** dipilih karena menyediakan hampir seluruh kebutuhan sistem sebagai fitur bawaan yang matang: autentikasi (Breeze), ORM (Eloquent), otorisasi (Policy), validasi, notifikasi multi-channel, dan migrasi — sehingga usaha pengembangan terpusat pada logika domain, bukan infrastruktur aplikasi. Pola arsitekturnya **MVC** dengan pengorganisasian *namespace* per peran (`Admin\`, `Dosen\`, `Mahasiswa\`) yang mencerminkan langsung tiga aktor hasil analisis.

### 3.2 Perancangan basis data

Perancangan data dimulai dari pemodelan **hierarki akademik** sebagai pohon relasi berkunci asing:

```
AcademicYear → Semester
Department → StudyProgram → StudentClass → { User(mahasiswa), Course }
```

lalu entitas pembelajaran (`Course`, `Material`, `Assignment`, `Submission`, `Group`, `Conference`, `Discussion`, `Announcement`) digantung pada `Course`, dan relasi mahasiswa↔mata kuliah dimodelkan sebagai tabel pivot `enrollments`.

Tiga keputusan perancangan data yang paling penting untuk dijelaskan:

**(a) Sibling course — satu baris `Course` per kelas.** Untuk kebutuhan multi-kelas (§2.2), ada dua alternatif: (i) satu `Course` menampung banyak kelas lewat pivot, atau (ii) satu baris `Course` per kelas dengan atribut sama. Dipilih alternatif (ii) karena setiap kelas nyatanya berjalan independen — materi bisa berbeda kecepatan, tenggat tugas bisa berbeda, nilai jelas berbeda — sehingga memaksakan satu record bersama justru menuntut *override* per kelas di mana-mana. Konsekuensinya ditangani secara eksplisit: keunikan mata kuliah dibuat **komposit** `(kode_matkul, dosen_id, semester_id, student_class_id)`, antarmuka mengelompokkan siblings secara visual, dan duplikasi konten diringankan lewat fitur *copy fan-out* (dosen membuat materi/tugas sekali, sistem menyalinkannya ke kelas sibling terpilih).

**(b) Satu tabel `Submission` untuk tiga tipe penugasan.** Tugas berkas, kuis, dan latihan kode memiliki daur hidup yang sama (dikumpulkan → dinilai) tetapi *payload* berbeda. Dipilih satu tabel dengan kolom fleksibel (JSON `answers` untuk kuis, `code` + `validation_result` untuk latihan) daripada tiga tabel terpisah — menyederhanakan buku nilai, rekap, dan notifikasi yang memperlakukan ketiganya seragam.

**(c) `exercise_config` sebagai kolom JSON.** Konfigurasi latihan pemrograman (bahasa, kode awal, kata kunci wajib, petunjuk) hanya relevan untuk tipe `exercise` dan bentuknya masih berevolusi → disimpan sebagai satu kolom JSON ber-*cast*, bukan lima kolom yang akan kosong untuk dua tipe lainnya.

Seluruh skema dicatat sebagai **migration** sehingga riwayat perubahan struktur ikut ter-*version-control* — termasuk jejak revisi rancangan (mis. migrasi yang melonggarkan keunikan `kode_matkul` dari global menjadi komposit, bukti bahwa rancangan dievaluasi ulang saat kebutuhan multi-kelas dipahami lebih dalam).

### 3.3 Perancangan proses dan alur

Alur-alur utama dirancang mengikuti aktor:

- **Registrasi**: mahasiswa mendaftar → verifikasi OTP email → aktif (dan otomatis ter-*enroll* ke mata kuliah kelasnya); dosen mendaftar → OTP → **menunggu persetujuan admin**. Dua jalur ini dirancang berbeda karena akun dosen memiliki hak tulis yang jauh lebih luas.
- **Alur penugasan**: dosen membuat tugas (opsional dengan prasyarat materi yang harus dibaca dulu — ditegakkan middleware) → mahasiswa mengumpulkan → penilaian. Kuis pilihan ganda dinilai otomatis; kuis campuran dan latihan kode **sengaja** menunggu penilaian manual dosen (§7, pertanyaan 6).
- **Alur konferensi**: dosen menjadwalkan → sistem membuat nama ruang unik → saat mulai, setiap peserta menerima JWT yang mengunci identitas dan status moderatornya → ruang Jitsi dibuka di tab baru.
- **Alur eksekusi kode**: browser → proxy aplikasi (validasi bahasa + *rate limit*) → sandbox Piston → hasil kembali ke browser. Browser tidak pernah menyentuh Piston langsung.

### 3.4 Perancangan antarmuka

Antarmuka dirancang **per peran dengan kerangka bersama**: satu layout aplikasi (sidebar + konten) yang isinya dikomposisi menurut peran pengguna. Perancangan visual tidak dibuat sekali jadi — iterasi April 2026 memperkenalkan **design token** (warna, spasi, tipografi terpusat di konfigurasi Tailwind) setelah iterasi awal menunjukkan gaya yang mulai tidak konsisten antar halaman. Seluruh antarmuka berbahasa Indonesia, konsisten dengan istilah domain di basis data.

Alat bantu penulisan disesuaikan kontennya: dosen menulis materi dengan editor Markdown (EasyMDE), mahasiswa mengerjakan latihan dengan editor kode (CodeMirror) — keduanya pustaka klien ringan yang tidak mengubah sifat server-rendered aplikasi.

### 3.5 Perancangan keamanan

Keamanan dirancang **berlapis** (defense in depth), bukan satu gerbang:

1. **Prefix route per peran** (`/admin`, `/dosen`, `/mahasiswa`) + middleware `auth` dan `role:` — menolak peran yang salah sebelum controller tersentuh.
2. **Policy per objek** — dosen A dan dosen B sama-sama lolos `role:dosen`, tetapi Policy menolak dosen A menyunting mata kuliah dosen B.
3. **Validasi kepemilikan pada input massal** — daftar kelas target *copy fan-out* selalu di-*intersect* dengan daftar sibling sah di server; input form tidak pernah dipercaya.
4. **Perlindungan bawaan framework** — CSRF token, *prepared statement* (anti SQL injection), *escaping* Blade (anti XSS), `$fillable` (anti *mass assignment*), *hash* bcrypt.
5. **Isolasi infrastruktur** — kode mahasiswa hanya berjalan di sandbox Piston; MySQL dan Piston tidak terekspos internet; satu-satunya pintu publik adalah reverse proxy TLS.

---

## 4. Tahap 3 — Implementasi Iteratif

Implementasi mengikuti urutan risiko dan ketergantungan, bukan urutan menu:

1. **Fondasi dulu** — autentikasi, peran, hierarki akademik, CRUD inti. Tanpa ini tidak ada yang bisa diuji.
2. **Fitur berisiko tinggi divalidasi awal** — konferensi video diintegrasikan sejak Maret (iterasi ke-2) justru karena paling berisiko; versi awalnya kemudian **diganti** dengan Jitsi self-hosted + JWT pada Mei setelah evaluasi biaya dan kontrol (§7, pertanyaan 5). Ini contoh nyata siklus iteratif: bangun → evaluasi → rancang ulang.
3. **Refactoring sebagai bagian proses** — kode yang tidak lagi dipakai (model `Role` terpisah, kelas `NotificationService`) dihapus pada iterasi Mei; menyisakan rancangan yang lebih sederhana (enum kolom `role`, pemanggilan notifikasi langsung). Menghapus rancangan lama yang keliru adalah bagian sah dari proses perancangan.
4. **Konvensi ditegakkan sepanjang implementasi** — validasi inline di controller, Policy (bukan cek peran manual), istilah domain Bahasa Indonesia, banyak berkas kecil daripada sedikit berkas raksasa.

---

## 5. Tahap 4 — Pengujian

Pengujian dirancang berlapis, dari yang paling cepat/murah ke yang paling menyerupai kondisi nyata:

| Lapisan | Alat / metode | Yang diverifikasi |
|---|---|---|
| Unit & feature test | Pest (`composer test`) | Logika terisolasi + simulasi request HTTP penuh (routing, middleware, otorisasi, DB) |
| Browser test | Laravel Dusk | Alur end-to-end pada Chrome sungguhan dengan basis data terpisah |
| Uji fungsional manual | Skenario konferensi lintas-peran | Laporan uji konferensi (31 Mei 2026): moderator/peserta, token, daur hidup ruang |
| Uji beban | Bot peserta konferensi | Uji 20-bot (1 Juni) lalu **uji kapasitas 100 peserta dalam satu ruang** (5 Juni) pada VM produksi — memvalidasi kebutuhan non-fungsional kapasitas kelas besar |
| Uji penerimaan pengguna | **74 akun penguji** dengan alur registrasi + OTP nyata | Sistem diuji pengguna sungguhan di lingkungan produksi dengan data riil PoliMedia (kurikulum 4 tahun) |

Poin yang layak ditekankan ke penguji: pengujian tidak berhenti di *test suite* — sistem di-*seed* dengan **data akademik riil** dan diuji **pengguna nyata melalui alur registrasi sebenarnya**, sehingga temuan (mis. progres dashboard yang tidak akurat) benar-benar ditemukan dan diperbaiki sebelum tahap akhir. Laporan-laporan uji tersimpan sebagai artefak di [docs/testing/](testing/).

---

## 6. Tahap 5 — Deployment dan Operasional

Perancangan deployment mengikuti kebutuhan non-fungsional biaya: **seluruh sistem pada satu VM** Google Cloud (4 vCPU, region Jakarta), dengan setiap komponen dikontainerkan Docker Compose:

- **Caddy** — reverse proxy tunggal yang terekspos internet; TLS Let's Encrypt otomatis untuk dua domain (aplikasi dan Jitsi).
- **Nginx + PHP-FPM** — melayani aplikasi Laravel.
- **MySQL, Piston, Jitsi (web/prosody/jicofo/jvb), coturn** — semuanya di jaringan internal Docker, tidak terekspos langsung.

Prosedur deploy dibakukan (sinkronisasi berkas + pembersihan cache + `composer optimize`), dan keputusan operasional didokumentasikan sebagai *runbook* di [docs/ops/](ops/) agar sistem dapat dioperasikan ulang oleh orang lain — bagian dari kebutuhan non-fungsional kemudahan pemeliharaan.

---

## 7. Justifikasi Keputusan Perancangan (Antisipasi Pertanyaan Penguji)

Ringkasan tanya-jawab atas keputusan yang paling mungkin dipertanyakan:

**1. "Mengapa Laravel, bukan framework lain / bukan buat sendiri?"**
Kebutuhan sistem (autentikasi, otorisasi, ORM, validasi, notifikasi, migrasi) semuanya tersedia sebagai fitur bawaan Laravel yang teruji komunitas besar. Membangun sendiri komponen-komponen itu menambah risiko keamanan tanpa nilai akademik maupun praktis; framework lain (Django, Express, Spring) sekelas kemampuannya, tetapi ekosistem PHP/Laravel paling selaras dengan kebutuhan hosting murah dan penguasaan penulis.

**2. "Mengapa monolith, bukan microservices?"**
Skala target satu institusi. Microservices menyelesaikan masalah *organisasi banyak tim* dan *skala jutaan pengguna* — dua masalah yang tidak ada di sini — dengan harga kompleksitas operasional besar. Monolith yang terstruktur baik (namespace per peran, Policy, Service) tetap mudah dipelihara. Catatan: infrastrukturnya tetap terpisah per kontainer; yang monolitik adalah kode aplikasinya.

**3. "Mengapa server-rendered Blade, bukan React/SPA?"**
Aplikasi didominasi formulir dan tampilan data, bukan interaksi real-time. SPA menuntut API terpisah, duplikasi validasi, dan manajemen state klien — biaya yang tidak dibayar oleh manfaat apa pun di kasus ini. Interaktivitas kecil dicukupi Alpine.js; satu-satunya kebutuhan tanpa-muat-ulang (komentar diskusi) dicukupi satu komponen Livewire.

**4. "Mengapa MySQL?"**
Data sistem sepenuhnya relasional (hierarki akademik berkunci asing, pivot enrollment) dengan kebutuhan integritas referensial kuat — persis kekuatan RDBMS. MySQL 8 dipilih atas familiaritas, dukungan hosting universal, dan kecukupan fitur (enum, kolom JSON untuk kasus `exercise_config`).

**5. "Mengapa Jitsi self-hosted, bukan Zoom/Google Meet/layanan berbayar?"**
Tiga alasan: (i) **biaya** — layanan konferensi berbayar per-menit/per-peserta tidak berkelanjutan untuk institusi; (ii) **kontrol** — integrasi identitas (nama, peran moderator) langsung dari sistem lewat JWT, tanpa akun terpisah; (iii) **data** — lalu lintas media tetap di infrastruktur sendiri. Konsekuensi bebannya diuji nyata: satu ruang 100 peserta terbukti tertangani VM produksi.

**6. "Mengapa latihan kode tidak dinilai otomatis penuh?"**
Keputusan pedagogis yang disengaja. Penilaian otomatis berbasis pencocokan kata kunci mudah dikelabui dan menghukum solusi kreatif; pada konteks PBL, kualitas kode dinilai dosen. Sistem memberi dosen *petunjuk* hasil validasi mesin ("Validasi Mesin"), tetapi angka akhir tetap keputusan manusia. Kuis pilihan ganda — yang jawabannya deterministik — tetap dinilai otomatis.

**7. "Bagaimana keamanan kode mahasiswa yang dieksekusi server?"**
Kode tidak pernah dieksekusi di server aplikasi. Ia dikirim lewat proxy (validasi bahasa + batas 10 eksekusi/menit) ke **Piston**, mesin sandbox terisolasi dalam kontainer terpisah dengan filesystem sementara, di jaringan internal yang tidak terekspos.

**8. "Mengapa registrasi dosen perlu persetujuan admin, sedangkan mahasiswa tidak?"**
Analisis risiko: akun dosen dapat membuat konten dan melihat data mahasiswa lintas kelas, sehingga verifikasi OTP saja tidak cukup — perlu verifikasi identitas oleh admin. Akun mahasiswa cakupannya terbatas pada kelasnya sendiri dan datanya sendiri.

**9. "Apa kelemahan rancangan ini / apa yang akan dilakukan berbeda?"**
(a) Antrian tugas masih `sync` — cukup untuk volume sekarang, tetapi fan-out notifikasi kelas sangat besar kelak butuh *queue worker*; rancangannya sudah siap (driver `database` tinggal diaktifkan). (b) Satu VM adalah *single point of failure* — dapat diterima untuk skala institusi dengan backup, tetapi ketersediaan tinggi menuntut pemisahan basis data. (c) Duplikasi konten sibling adalah *trade-off* sadar — independensi per kelas dibayar dengan penyimpanan ganda.

---

## Referensi silang

- [arsitektur-dan-perancangan.md](arsitektur-dan-perancangan.md) — hasil rancangan secara rinci + glosarium istilah
- [bab4-implementasi.md](bab4-implementasi.md) — referensi implementasi untuk BAB 4 skripsi
- [lampiran-snippet-kode.md](lampiran-snippet-kode.md) — cuplikan kode inti sebagai lampiran
- [testing/](testing/) — laporan-laporan pengujian sebagai artefak bukti
- [database.md](database.md) — referensi skema per tabel
- [deployment.md](deployment.md) — topologi produksi dan runbook
