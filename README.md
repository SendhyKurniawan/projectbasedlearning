# PBL Workspace

Sistem E-Learning berbasis Project Based Learning (PBL) yang dirancang untuk mendukung proses pembelajaran pada mata kuliah pengembangan website. Sistem ini menyediakan fasilitas pengelolaan materi, tugas, dan interaksi antara dosen dan mahasiswa secara terstruktur.

## Fitur Utama

### Administrator

- **Manajemen Pengguna**: Registrasi, aktivasi, dan kontrol akses akun (Admin, Dosen, Mahasiswa).
- **Manajemen Kursus**: Pembuatan mata kuliah, pengaturan semester, dan tahun akademik.
- **Enrollment**: Pendaftaran mahasiswa ke dalam mata kuliah secara massal atau individual.
- **Supervisi Sistem**: Monitoring aktivitas dan manajemen data master.

### Dosen

- **Manajemen Materi**: Unggah bahan ajar dalam bentuk file (PDF) atau tautan video.
- **Manajemen Tugas**: Pembuatan tugas reguler, proyek, dan kuis.
- **Evaluasi & Grading**: Penilaian hasil pengerjaan mahasiswa disertai feedback komentar.
- **Manajemen Soal**: Unified management untuk pertanyaan kuis dan latihan kode.

### Mahasiswa

- **Akses Pembelajaran**: Mengakses materi pada mata kuliah yang diikuti.
- **Pengumpulan Tugas**: Submit tugas secara online dengan status pelacakan (Submitted, Late, Graded).
- **Interaksi Kuis**: Mengerjakan kuis dan latihan pemrograman secara interaktif.
- **Forum Diskusi**: Berinteraksi dengan dosen dan sesama mahasiswa dalam forum diskusi terintegrasi.

## Stack Teknologi

Sistem ini dibangun dengan teknologi modern untuk performa dan skalabilitas:

- **Framework**: Laravel 12 (PHP 8.x)
- **Frontend**: Blade Engine, Tailwind CSS, Alpine.js
- **State Management**: Livewire
- **Database**: MySQL 8.0
- **Autentikasi**: Laravel Breeze (Starter Kit)
- **Testing**: Pest Framework
- **Environment**: Docker & Laravel Sail

## Penggunaan Docker

Proyek ini telah dikonfigurasi agar bisa dijalankan dengan satu perintah pada
mesin manapun yang memiliki Docker.

### Prasyarat

- [Docker Desktop](https://www.docker.com/products/docker-desktop) (Windows/macOS) atau Docker Engine + Docker Compose v2 (Linux)

### Langkah Cepat (One-Click)

```bash
git clone <repository-url> pjbl
cd pjbl
docker compose up -d --build
```

Itu saja. Pada boot pertama, container `app` akan otomatis:

- menyalin `.env.example` menjadi `.env`
- menjalankan `php artisan key:generate`
- menunggu MySQL siap, lalu `php artisan migrate --seed`
- membuat storage symlink dan membersihkan cache

Front-end (CSS/JS) sudah dibuild sebelumnya di dalam image, jadi tidak perlu
menjalankan `npm install` secara manual.

### Layanan & URL

| Layanan        | URL                                              |
| :------------- | :----------------------------------------------- |
| Aplikasi       | [http://localhost:8000](http://localhost:8000)   |
| MailHog (mail) | [http://localhost:8025](http://localhost:8025)   |
| phpMyAdmin     | [http://localhost:8081](http://localhost:8081)   |
| LiveKit (WS)   | `ws://localhost:7880`                            |
| Piston (code)  | `http://localhost:2000` (internal `piston:2000`) |

### Perintah Berguna

```bash
# Lihat log aplikasi
docker compose logs -f app

# Masuk ke container app
docker compose exec app bash

# Reset database (hapus data)
docker compose down -v && docker compose up -d --build
```

## Kredensial Default

Setelah menjalankan seeder, gunakan akun berikut untuk pengujian.
Seluruh akun aktif (`is_active = true`) sehingga bisa langsung login tanpa OTP,
dengan password `password`.

| Role          | Email                   | Password   |
| :------------ | :---------------------- | :--------- |
| **Admin**     | `admin@pjbl.test`       | `password` |
| **Dosen**     | `dosen@pjbl.test`       | `password` |
| **Dosen**     | `budi.dosen@pjbl.test`  | `password` |
| **Dosen**     | `siti.dosen@pjbl.test`  | `password` |
| **Mahasiswa** | `mahasiswa@pjbl.test`   | `password` |
| **Mahasiswa** | `ahmad.mhs@pjbl.test`   | `password` |
| **Mahasiswa** | `dewi.mhs@pjbl.test`    | `password` |
| **Mahasiswa** | `cahya.mhs@pjbl.test`   | `password` |
| **Mahasiswa** | `rina.mhs@pjbl.test`    | `password` |
| **Mahasiswa** | `fajar.mhs@pjbl.test`   | `password` |

## Pengujian (Playwright)

Suite QA end-to-end ditulis dalam TypeScript menggunakan Playwright dan
menjalankan skenario untuk semua peran (Admin, Dosen, Mahasiswa) terhadap
container Docker yang sudah berjalan.

### Prasyarat

- Stack Docker aktif (`docker compose up -d`) — container `pjbl-app` harus ada
- Node.js 18+ dan npm
- Install dependensi & browser Chromium:
    ```bash
    npm install
    npx playwright install chromium
    ```

> Sebelum setiap run, global setup akan mengeksekusi
> `php artisan migrate:fresh --seed` di dalam container `pjbl-app`,
> sehingga **seluruh data di database akan di-reset**.

### Menjalankan Tes

```bash
# Jalankan seluruh suite (headless)
npm run test:pw

# Mode headed (lihat browser)
npm run test:pw:headed

# Mode UI interaktif
npm run test:pw:ui

# Buka laporan HTML hasil run terakhir
npm run test:pw:report
```

Override base URL bila aplikasi berjalan di host/port lain:

```bash
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8080 npm run test:pw
```

Artefak (trace, screenshot, video) untuk tes yang gagal tersimpan di
`.playwright-mcp/test-results/`, dan laporan HTML di `playwright-report/`.

## Lisensi

Aplikasi ini dikembangkan untuk tujuan edukasi dan mengikuti lisensi [MIT](https://opensource.org/licenses/MIT).

