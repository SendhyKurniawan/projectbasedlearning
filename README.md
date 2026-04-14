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

Proyek ini telah dikonfigurasi menggunakan Docker untuk kemudahan pengembangan.

### Prasyarat

- Docker Desktop
- Docker Compose

### Langkah Cepat

1. **Salin file environment**:
    ```bash
    cp .env.example .env
    ```
2. **Setup Konfigurasi Database** (dalam `.env`):
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=pbl
    DB_USERNAME=pbl
    DB_PASSWORD=password
    ```
3. **Jalankan container**:
    ```bash
    docker-compose up -d --build
    ```
4. **Setup Aplikasi** (Pertama kali):
    ```bash
    docker-compose exec app composer install
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate --seed
    docker-compose exec app npm install
    docker-compose exec app npm run build
    ```
5. **Akses**: [http://localhost:8000](http://localhost:8000)

## Kredensial Default

Setelah menjalankan seeder, gunakan akun berikut untuk pengujian:

| Role      | Email            | Password   |
| :-------- | :--------------- | :--------- |
| **Admin** | `admin@pbl.test` | `password` |
| **Dosen** | `dosen@pbl.test` | `password` |

## Lisensi

Aplikasi ini dikembangkan untuk tujuan edukasi dan mengikuti lisensi [MIT](https://opensource.org/licenses/MIT).

