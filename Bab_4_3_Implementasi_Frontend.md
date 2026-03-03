# 4.3 Implementasi Frontend dan Presentasi Antarmuka

Bagian Frontend aplikasi berfokus pada penyajian informasi yang jelas, dinamis, intuitif, dan _responsive_ di berbagai platform klien. Ekosistem implementasi frontend seutuhnya disusun dari tiga instrumen dominan: **Blade Template Engine** (kerangka presentasi dinamis bawaan Laravel), framework penyusunan visual murni **Tailwind CSS**, serta kerangka JavaScript minimalis **Alpine.js** untuk fungsi reaktif parsial (reaktivitas di level komponen).

### 1. Struktur Organisasi Template Layar (Blade Views)

Proyek e-learning mengikuti susunan modular (`resources/views/`) yang sangat ketat:

- **Sistem Layout Pewarisan (_Template Inheritance_)**: Alih-alih mengatur _Tag HTML Head_ dan _Navigasi_ di tiap file presentasi, aplikasi mempergunakan arsitektur layout di bawah payung `/layouts/` (seperti `app.blade.php`, `guest.blade.php`). Layout induk ini memancarkan wadah luaran utuh (`<main>`) dimana konten anak di-"slot" masuk (_yield_ & _extends_).
- **Blade Components (`/components/`)**: Mematuhi kaidah `DRY` (_Don't Repeat Yourself_), elemen kecil repetitif yang sering dibaca oleh mata dirangkai di dalam folder _components_. Di antaranya: desain seragam _input text form_, kartu indikator materi pelajaran (_Card UI_), modal konfirmasi hapus data, lonceng notifikasi, badge/label status keterlambatan warna-warni (_Pill Badge_), serta desain format spesifik unggah tombol (_button primitives_). Hal ini membuat pengerjaan antar aktor berbeda (Dosen vs Mahasiswa) menggunakan keseragaman _vibe_ desain visual korporat e-learning secara menyeluruh.
- **Separasi Domain (Admin/Dosen/Mahasiswa)**: Sub-direktori views terpisah (contoh `views/dosen/assignments` atau `views/mahasiswa/courses`). Ini memberikan pengembang tingkat fokus tajam mencegah kesalahan teknis kebocoran presentasi tombol (_Button Mismatch_) di layer salah satu otorisasi pengguna.

### 2. Utilisasi Styling Otomatis Tailwind CSS

Dalam arsitektur _frontend_ ini, pengembang mematuhi gaya _Utility-First CSS_. Pengkodean CSS biasa (_Standard Stylesheets_) hampir dihapus total yang ditandai dengan tidak adanya file `.css` khusus fungsional besar.

- **Sistem Warna Berlapis dan _Dark Mode_**: Mengandalkan kemanfaatan kelas perintis milik Tailwind, antarmuka ini terkonfigurasi pada palet warna serasi, gradien halus di bagian _Banner Dashboard_ (`bg-gradient-to-r`). Melalui parameter selektor `dark:`, hampir semua kanvas putih diubah otomatis dan dirender rapi saat mendeteksi preferensi _Dark Mode_ di OS/Browser _(misal perpaduan `text-gray-900 dark:text-gray-100` dan bingkai luaran kelam `dark:bg-gray-800`)_.
- **Interaksi _Responsive_ & Estetika Visi**: Tatanan grid multi-kolom mendasari tampilan katalog mata kuliah. Tampilan _desktop_ (`md:grid-cols-2 lg:grid-cols-3`) dapat melipat otomatis menjadi satu urutan kolom _(Stack)_ saat tertangkap pada rasio layar mungil _(Mobile View)_ berkat kueri _Breakpoint_ Tailwind. Tambahan pendar transisi `transition-all duration-300 hover:shadow-lg hover:scale-105` menonjolkan detail gaya kelas premium saat elemen _hovered_.

### 3. Reaktivitas Micro-Interactions lewat Alpine.js

Untuk meminimalisir kendala lambat memori dari kerangka JavaScript berskala korporat raksasa (misal: React/Vue), serta supaya tidak melakukan penulisan skrip terpisah berceceran jQuery, e-learning memakai alat mungil bawaan ekosistem Laravel bernama Alpine.js (`x-data`, `x-show`, `x-bind`).

- **Penanganan Modal & _Accordion_**: Elemen interaktif rumit semacam layar penyorot persetujuan "Yakin Menghapus Tugas Ini?" / modal pop-up sepenuhnya diorkestra langsung di tingkat HTML melalu inisiasi _state x-data="{ open: false }"_ kemudian bereaksi membuka hanya apabila _event listener click_ terjadi (`@click="open = true"`).
- **Efisiensi Notifikasi**: Pop-up pita balasan status pengunggahan berhasil (Toast _Session Flash_) atau hilap kode juga dirawat oleh Alpine, berotasi mati sendirinya via implementasi penunda skrip _setTimeout() `x-init="setTimeout(() => show = false, 3000)"`_.
- **Menu Profil Pengguna Menyesuaikan Diri (_Dropdown Profile_)**: Elemen sub-navigasi _Log Out_, serta transisi buka-tutup silabus pelajaran (panel sisi lipat / _sidebar fold_) direkayasa instan pada presentasi, memangkas proses pertukaran siklus siklus data tanpa mengharuskan muatan _(reload)_ baru dari _server controller_.
