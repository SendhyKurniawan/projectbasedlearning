---
trigger: always_on
---

Rancangan Website E-Learning

Berbasis Laravel 12

1. Deskripsi Umum Sistem

Website e-learning ini merupakan media pembelajaran berbasis web yang dirancang untuk mendukung proses pembelajaran pada mata kuliah dasar pengembangan website. Sistem ini menyediakan fasilitas pengelolaan materi, tugas berbasis proyek, serta interaksi antara dosen dan mahasiswa secara terstruktur dan terintegrasi.

Website dikembangkan menggunakan framework Laravel 12 dengan pendekatan Model-View-Controller (MVC) untuk memastikan pengembangan sistem yang terorganisir, mudah dipelihara, dan scalable.

2. Tujuan Pengembangan Sistem

Tujuan dari pengembangan website e-learning ini adalah:

Menyediakan media pembelajaran berbasis website yang terstruktur dan mudah diakses.

Membantu dosen dalam menyampaikan materi dan mengelola tugas pembelajaran.

Memfasilitasi mahasiswa dalam mengakses materi dan mengumpulkan tugas secara daring.

Menghasilkan website e-learning yang layak digunakan sebagai media pembelajaran.

3. Ruang Lingkup Sistem

Ruang lingkup pengembangan sistem ini dibatasi pada:

Pengelolaan materi pembelajaran

Pengelolaan tugas dan pengumpulan tugas

Autentikasi dan otorisasi pengguna

Tampilan antarmuka website (UI/UX)

Pengujian kelayakan website

Sistem tidak mencakup penilaian otomatis tugas dan integrasi sistem akademik eksternal.

4. Pengguna Sistem

Sistem memiliki tiga jenis pengguna dengan hak akses yang berbeda, yaitu:

Pengguna Hak Akses
Admin Mengelola akun pengguna dan data mata kuliah
Dosen Mengelola materi pembelajaran dan tugas
Mahasiswa Mengakses materi dan mengumpulkan tugas 5. Fitur Utama Sistem

Fitur utama yang dikembangkan dalam website e-learning ini meliputi:

Sistem autentikasi dan manajemen akun pengguna

Manajemen materi pembelajaran

Manajemen tugas dan pengumpulan tugas

Dashboard pengguna sesuai hak akses

Antarmuka responsif dengan dukungan mode gelap (dark mode)

6. Arsitektur Sistem

Website dikembangkan menggunakan arsitektur MVC (Model-View-Controller) yang diterapkan oleh framework Laravel.

Model berfungsi untuk mengelola data dan interaksi dengan database.

View menggunakan Blade Template dan Tailwind CSS untuk menampilkan antarmuka pengguna.

Controller berperan sebagai penghubung antara Model dan View.

Untuk interaksi dinamis tanpa pemuatan ulang halaman, sistem memanfaatkan Livewire yang terintegrasi dengan Blade dan Alpine.js.

7. Desain Database

Sistem menggunakan MySQL sebagai basis data. Tabel utama yang dirancang dalam sistem antara lain:

users

courses

materials

assignments

submissions

Struktur database dirancang untuk mendukung pengelolaan data pembelajaran secara terintegrasi dan konsisten.

8. Keamanan Sistem

Keamanan sistem diterapkan melalui:

Autentikasi dan manajemen sesi menggunakan Laravel Breeze

Pengaturan hak akses pengguna (role-based access)

Perlindungan terhadap CSRF

Enkripsi kata sandi menggunakan hashing

9. Teknologi yang Digunakan

Teknologi yang digunakan dalam pengembangan website e-learning ini adalah:

Framework backend: Laravel 12

Database: MySQL

Frontend: Blade Template, Tailwind CSS

Interaksi dinamis: Livewire dan Alpine.js

Autentikasi: Laravel Breeze

Pengujian: Pest Testing Framework

Containerization: Docker

10. Pengujian Sistem

Pengujian sistem dilakukan untuk menilai kelayakan website e-learning sebagai media pembelajaran. Pengujian meliputi:

Pengujian fungsional untuk memastikan seluruh fitur berjalan dengan baik.

Pengujian kelayakan melalui evaluasi pengguna terhadap kemudahan penggunaan dan tampilan sistem.

Hasil pengujian digunakan sebagai dasar penilaian kelayakan website e-learning yang dikembangkan.
