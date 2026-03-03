# BAB 4: IMPLEMENTASI SISTEM

Bab ini menguraikan tahapan implementasi sistem dari E-Learning yang telah dikembangkan. Pembahasan mencakup rincian arsitektur yang digunakan, struktur database, implementasi di sisi _Backend_ dan _Frontend_, fungsionalitas utama tiap hak akses, serta tahapan pembuatan proyek aplikasi secara keseluruhan.

---

## 4.1 Arsitektur Sistem Secara Umum

Sistem E-Learning ini dibangun menggunakan fondasi arsitektur **MVC (Model-View-Controller)** yang disediakan oleh _Framework_ **Laravel 12**. Pemilihan arsitektur MVC ditujukan guna memisahkan antara logika aplikasi (Controller), manipulasi dan representasi data dari database (Model), serta antarmuka yang akan ditampilkan kepada pengguna (View).

### Spesifikasi Teknologi:

- **Backend**: PHP 8.x dengan Framework Laravel 12.
- **Frontend**: Blade Template Engine, terintegrasi dengan **Tailwind CSS** untuk desain tata letak (_styling_) dan **Alpine.js** untuk interaksi _client-side_ yang reaktif.
- **Database**: MySQL.
- **Autentikasi**: Laravel Breeze.
- **Keamanan Pendukung**: Middleware Role-Based Access Control (RBAC), CSRF (Cross-Site Request Forgery) Protection, dan Enkripsi Password (Bcrypt/Argon2).

---

## 4.2 Arsitektur Database dan Relasi (Model)

Database dirancang agar terintegrasi dan konsisten untuk mengelola berbagai entitas utama dalam sistem pembelajaran. Implementasi tabel dikelola menggunakan fitur _Migrations_ bawaan Laravel.

Berikut adalah struktur entitas utama beserta fungsinya:

1. **`users`**: Tabel sentral otentikasi. Menyimpan seluruh data pengguna beserta atribut `role` (Admin, Dosen, atau Mahasiswa), NIM/NIP, email, dan status aktif.
2. **`academic_years` & `semesters`**: Menyimpan master data Tahun Akademik (misalnya 2025/2026) dan Semester (Ganjil/Genap) untuk mendukung fleksibilitas historis perkuliahan.
3. **`courses`**: Tabel mata kuliah yang memuat relasi terhadap Dosen Pengampu (`dosen_id`) dan Semester (`semester_id`).
4. **`enrollments`**: Merupakan _Pivot Table_ (tabel perantara) yang memetakan relasi _Many-to-Many_ antara entitas `users` (dengan role Mahasiswa) dan entitas `courses`.
5. **`materials`**: Tempat menyimpan data materi pembelajaran, dapat berupa _file path_ maupun tautan (_video URL_). Dilengkapi juga entitas pendukung `material_views` untuk melacak keterbacaan materi oleh mahasiswa.
6. **`assignments`**: Menampung komponen evaluasi, bisa berupa tugas reguler, _project_, ataupun _quiz_.
7. **`submissions`**: Menampung hasil pengerjaan mahasiswa atas `assignments`. Tabel ini menyimpan skor (_grade_), _file/url_ pengumpulan, status keterlambatan, dan riwayat waktu pengerjaan.
8. **Quiz Data (`quiz_questions`, `quiz_options`)**: Digunakan secara khusus untuk menampung format soal kuis objektif, baik itu opsi pilihan ganda maupun esai/kode program.
9. **`discussions` & `discussion_comments`**: Memfasilitasi komunikasi dua arah antar pengguna di dalam suatu forum berjenjang.
10. **`groups` & `group_members`**: Mengakomodasi skenario pengerjaan tugas dalam bentuk kelompok evaluasi.

---

## 4.3 Implementasi Backend (Logika & Routing)

Di sisi server, aplikasi menerapkan pengelolaan kontrol aliran _(routing)_ secara persisi menggunakan perlindungan _middleware_.

### 1. Manajemen Routing (`routes/web.php`)

Routing telah dipisahkan ke dalam beberapa grup _(Route Groups)_ yang diikat dengan _middleware_ spesifik (`role:admin`, `role:dosen`, `role:mahasiswa`).
Konfigurasi login utama akan secara otomatis mengarahkan akses ke _dashboard_ masing-masing (_Redirect Match_) sesuai dengan nilai role _user_ yang diautentikasi.

### 2. Implementasi Controllers

Tanggung jawab logika bisnis dipecah ke masing-masing _namespace_ spesifik yang bertingkat:

- **`App\Http\Controllers\Admin`**: Memiliki _Controller_ untuk `DashboardController` khusus Admin, `UserController` untuk manajemen pengguna (CRUD, matikan/aktifkan status akun), dan `CourseController` untuk menangani _enrollment_ mahasiswa. Termasuk manipulasi _master data_ semester/tahun akademik.
- **`App\Http\Controllers\Dosen`**: Menampung fungsionalitas pengajar seperti `MaterialController` (Unggah file ajar), `AssignmentController` (Pembuatan tugas/kuis dan perekaman pertanyaannya), serta memberikan koreksi atau skor di `submissions`.
- **`App\Http\Controllers\Mahasiswa`**: Berfokus pada penyerahan dan interaksi mahasiswa, diantaranya `CourseController` untuk melihat matkul yang diampu, `SubmissionController` mengumpulkan tugas, serta pengontrol terpusat `QuizController` dan `ExerciseController` untuk mengerjakan latihan kode secara interaktif.
- **`DiscussionController`**: Controller publik (yang dijaga _Middleware Auth_) memungkinkan Dosen maupun Mahasiswa dapat berbagi pengetahuan bersama.

---

## 4.4 Implementasi Frontend (Antarmuka Pengguna)

Sisi antarmuka direalisasikan menggunakan perpaduan **Laravel Blade** dan kerangka CSS **Tailwind CSS**.

1. **Struktur Direktori Komponen (`resources/views`)**:
   Penempatan berkas tampilan (_view_) dikategorikan rapi ke dalam beberapa map utama diantaranya: `admin/`, `dosen/`, `mahasiswa/`, `auth/` (untuk autentikasi spesifik), dan `layouts/` (template utama). Selain itu terdapat folder `components/` untuk perancangan elemen desain komponen berulang (form input, tombol, kartu mata kuliah, dsb).
2. **Desain Interaktif**:
   Integrasi **Alpine.js** dipakai untuk mengatur state _dropdown_ nav-bars, _modal_ pengumuman, dan panel hapus data (konfirmasi delete), yang meminimalkan perlunya membuat _script_ Vanilla JS manual. UI/UX didesain agar _responsive_ (tampil optimal melintasi perangkat seluler dan desktop), dan siap dikembangkan dalam opsi _dark-mode_ ke depannya.

---

## 4.5 Alur Sistem dan Fungsionalitas Hak Akses

Aplikasi memiliki batas otorisasi ketat yang membedakan alur pekerjaan tiga jenis pengguna utama:

1. **Supervisi Administrator**:
    - Mampu mengakses `/admin/login` independen ataupun gabung dari login standar.
    - Punya hak istimewa melakukan pemutusan akses aktif (_toggle active status_) pada suatu entitas mahasiswa atau dosen, yang dapat langsung mengakhiri sesi pengguna bersangkutan.
    - Bertanggung jawab melakukan pendaftaran masal (_enrollment_) atau manuver pencabutan mahasiswa (`unenroll`) pada kelas.
2. **Aktivitas Dosen (Pengajar)**:
    - Membuat mata acara materi di silabus dengan integrasi lampiran tautan maupun berkas bacaan.
    - Membangun berbagai tipe penugasan evaluasi reguler ke dalam sistem.
    - Menyediakan soal-soal kuis melalui fitur _Unified Question Management_.
    - Mengendalikan mutu _submission_ dari mahasiswa (memberikan _grading_ / penilaian beserta reviu komentar _feedback_).
3. **Aktivitas Mahasiswa (Peserta Didik)**:
    - Terbatas hanya pada akses materi di mata kuliah terdaftar (_enrolled courses_).
    - Melihat prasyarat suatu tugas, dapat memulai _take quiz_ atau membedah logika lewat halaman eksekusi spesifik latihan program (`/exercises/{assignment}/solve`).
    - Dapat mengetahui detail status _submission_ mereka (_Late_, _Graded_, atau _Submitted_).

---

## 4.6 Tahapan Pembuatan Aplikasi

Aplikasi proyek e-learning ini dibangun melalui metodologi siklus hidup bertahap sebagai berikut:

- **Tahap 1: Inisialisasi Proyek dan Lingkungan Kerja**:
  Pembuatan skema environment `.env`, penetapan standar koneksi database (MySQL). Pembuatan struktur _repository git_, konvensi commit, dan instalasi _Library_ esensial Laravel 12.
- **Tahap 2: Skema Data dan _Seeders_**:
  Translasi ERD konseptual ke dalam bentuk _Laravel Migrations_ beserta definisi foreign keys (`constrained`, `cascade`, dll). Pembuatan skrip `DatabaseSeeder` sebagai pabrik inisialisasi _dummy data_ untuk masa pegujian awal.
- **Tahap 3: Implementasi Autentikasi**:
  Instalasi _Laravel Breeze_. Kemudian adaptasi masif pada sistem _Route Session_ untuk membuat validasi jalur ganda (Berdasarkan _Role_) guna memisahkan dashboard Admin, Dosen, serta Mahasiswa pasca login.
- **Tahap 4: _Backend MVC Architecture Pipeline_**:
  Menulis relasi Eloquent dalam _Model_ secara ekstensif (seperti `$course->enrollments`). Penyelesaian _Business Logic_ dalam setiap _Controller_ yang menyambungkan sistem ke _database_ secara aman. Pengadaan _Validation Form Request_ pada saat Create/Update data guna mencegah _malicious input_.
- **Tahap 5: Pengembangan Antarmuka (Frontend UX/UI)**:
  Memecah struktur statis _HTML/Tailwind_ ke bentuk dinamis _Blade Directive_ (`@foreach`, `@if`). Menyempurnakan navigasi bilah sisi (_Sidebar Navigation_) berdasarkan komponen otorisasi (`@role`).
- **Tahap 6: Integrasi Fitur Dinamis Tambahan**:
  Membangun fungsionalitas tingkat menengah mencakup: Logika sistem waktu tunggu _Quiz (Duration logic)_, fitur Forum Diskusi Komentar Dinamis (_Discussions_), hingga algoritma _Course Enrollment Checking_.
- **Tahap 7: Testing dan Optimalisasi**:
  Verifikasi aliran fungsi (`Manual Testing`), pemeriksaan batasan izin hak akses dari _URL Hijacking_ (percobaan mahasiswa mengakses `/admin`), serta validasi integritas struktur komponen untuk optimalkan _loading cache_ di halaman presentasi.
