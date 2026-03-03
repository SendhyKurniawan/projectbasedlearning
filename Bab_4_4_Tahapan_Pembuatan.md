# 4.4 Tahapan Implementasi & Pembuatan Aplikasi

Aplikasi dibangun bertahap melalui fondasi alur _Continuous/Agile Development_ berdasarkan kompilat kebutuhan silabus dan instruksi standar teknis. Pengembangan mematuhi metode pemecahan fungsi skala kecil _(Component-Level Checklists)_ guna menyusun pilar e-learning.

### Fase 1: Pra-Penulisan Kode dan Pembacaan Arsitektur (Pre-Coding)

Tahapan esensial di titik nol. Pekerjaan diawali dengan merancang alur pengerjaan logika murni:

- Pembuatan hierarki relasional (ERD - _Entity Relationship Diagram_) agar kerangka perbandingan Mahasiswa dan Dosen saling beririsan pada skenario silabus (_Course_) atau jadwal semester tak bentrok.
- Penerapan komitmen infrastruktur kerangka sistem mengadopsi struktur sejati Model-View-Controller (MVC) yang melekat secara organik melalui Laravel 12. Menentukan alat dan konfigurasi koneksi skema data yang aman melalui lokalisasi parameter di fail `.env` (Memisahkan kunci database _password localhost_).

### Fase 2: Implementasi Struktur Database & Pabrik Data Dasar (Seeding)

Setelah skema matang, tahapan transisi database dimulai:

- Menulis puluhan berkas `Migration` menggunakan konstruktor _Laravel Schema Builder_. Aturan tegas dari skema (_Constraints_, indeks, dan panjang karakter) dieksekusi agar MySQL bekerja optimal (contoh constraint perlindungan relasi kaskade untuk menghapus nilai `submissions` seandainya `assignments` ditiadakan).
- Penciptaan replikasi akun percobaan (_Dummy Data Creation_) memakai _Factories_ dan `DatabaseSeeder`. Pada mulanya dibuatlah sampel `SuperAdmin`, beberapa pengguna `Dosen` dan akun `Mahasiswa` palsu komplit dengan sampel nama dan otentikasinya, sehingga pengembang berwenang dapat langung berlatih login sebagai berbagai jenis _Role_.

### Fase 3: Penciptaan Bisnis Logik Pusat (Backend) & Sistem Otentikasi Lapis Ganda

Ini adalah tahapan eksekusi logaritma dan jalur tersembunyi aplikasi server:

- Penanaman mesin gerbang _Laravel Breeze_, yang menyumbangkan alat registrasi terenskripsi, manajemen penanganan pengetik _password_ berulang serta sesi peramban.
- Modifikasi berlapis pada skrip `web.php` merombak rute agar bercabang memisahkan kendali Dashboard masing-masing aktor pengguna lewat sistem pelindung _(Custom Middleware)_. Di Fase ini pula penulisan algoritma CRUD pada Model dan Controller dieksekusi padat (_seperti fungsi upload materi .pdf dan integrasi URL pada penyimpanan storage public lokal_).
- Menanamkan instruktsi standar pengelolaan penyuplai `Form Request` serta logik pertahanan gagal-try `try-catch block` di tiap celah data transaksi masuk dari klien _(pengumpulan jawaban Quiz / Koding)_.

### Fase 4: Restrukturisasi Layar Frontend & Perbaikan UX (User Experience)

Kode aplikasi tidak relevan apabila di layar klien berantakan. Tahapan kosmetik mulai meramu presentasi:

- Mengubah tampilan standar dari kerangka MVC dasar menjadi bentuk presentasi modern _(premium aesthetics)_ menggunakan kerangka kompilasi elemen `Tailwind CSS`. Penanaman tipografi modern dan pewarnaan interaktif gradasi latar belakang mempresentasikan sistem layaknya institusi modern.
- Membangun komponen antarmuka yang seragam seperti pemakaian `<x-button>` yang mengefisienkan panggilan skrip. Penerapan instruksi _Alpine.js_ dipakai secara efisien untuk menciptakan interaksi layar yang ringkas (navigasi sisi _Foldable Menu_, modal konfirmasi hapus data tugas tanpa harus berpindah dimensi halaman web).

### Fase 5: Modul Fungsionalitas Lanjut & Forum Integratif

Pada masa jenuh koding aplikasi utama ini, pengembangan menjurus kepada integrasi interaksi sistem antar-manusia:

- Pembangunan Modul Forum Diskusi, supaya mahasiswa dapat memberikan untaian pertanyaan kepada dosen, layaknya sub-kategori pengarsipan forum Reddit.
- Menyusun modul latihan kode _(Exercises)_ dimana mahasiswa bisa bertatap muka pada antarmuka penulisan teks kode secara _highlighted_ untuk dipaparkan fungsionalitasnya langsung pada layar (Simulasi IDE ringan bagi basis penilaian tugas dasar web developer).

### Fase 6: Siklus Pengujian Validasi Kelayakan (Quality Assurance - Testing)

Tidak ada perangkat lunak siap muat bila tidak dihakimi, fase akhir sebelum laporan memuat:

- Pemeriksaan komprehensif manual melalui peramban uji (sebuah _session_ berperan sebagai Dosen mempublikasikan file, lalu berganti _tabs mode incognito_ ke _session_ Mahasiswa apakah naskah tugas tersebut nampak saat itu juga).
- Pencobaan intrusi URL dari pihak non-hak _Role Hijacking_, dengan memaksa Mahasiswa masuk menuju tautan URL eksklusif penulisan `/admin/users`; yang mana sistem telah sempurna diwajibkan mengembalikanya kepada layar cegahan 403 (_Unauthorized Access_).
- Semua _source code_ secara berkala digabung dalam pangkalan manajemen Git (_Git Version Control System_) menurut pakem histori _Conventional Commits_ seperti (_feat: penambahan routing quiz, refactor: perampingan desain blade submission_) demi mencegah kode yang hancur memengaruhi file produksi aslinya.
