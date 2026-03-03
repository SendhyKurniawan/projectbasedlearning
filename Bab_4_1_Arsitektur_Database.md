# 4.1 Arsitektur Database dan Model Relasional

Untuk mendukung ekosistem _e-learning_ yang komprehensif, arsitektur _database_ telah dirancang dengan memperhatikan aspek integritas data (melalui _Foreign Keys_ bersyarat seperti `cascade` dan `set null`), fleksibilitas historis semester, serta batasan interaksi _(constraints)_ spesifik. Sistem dikembangkan di atas RDBMS **MySQL** menggunakan fitur **Laravel Migrations** dan **Eloquent ORM**.

Berikut adalah rincian detail setiap entitas/tabel yang didefinisikan dalam skema _database_, beserta perannya dalam kelangsungan logika aplikasi:

### 1. Entitas Pengguna dan Autentikasi

1. **`users`**
    - **Tujuan**: Merupakan tabel induk untuk semua akses masuk (_login_) ke dalam sistem. Tabel ini menggabungkan semua jenis entitas manusia ke dalam satu tabel terpusat menggunakan atribut `role`.
    - **Aturan**: Memiliki Enum untuk membedakan otorisasi: `admin`, `dosen`, dan `mahasiswa`. Kolom `nim` (unik dan opsional) diperuntukkan khusus mahasiswa, sementara `nip` (juga unik dan opsional) khusus bagi dosen. Digabungkan dengan mekanisme _Laravel Breeze_ untuk _password hashing_ bcrypt dan token sesi otomatis yang diamankan. Dilengkapi kolom `is_active` sebagai saklar dari _Admin_ jika sewaktu-waktu ingin memblokir akses _user_.
    - **Relasi Eloquent**: Memiliki relasi _One-to-Many_ dengan entitas `courses` (Dosen memiliki banyak mata kuliah, Mahasiswa mendaftar/enroll ke banyak mata kuliah melalui _pivot table_), `submissions`, dan `discussions`.

### 2. Entitas Master Data Waktu Akademik

Data ini diperlukan guna menghindari pencampuran riwayat perkuliahan pada semester atau tahun ajar yang berbeda.

1. **`academic_years`**
    - Menyimpan daftar tahun akademik (Contoh: 2025/2026). Tabel ini dikendalikan _Admin_.
2. **`semesters`**
    - Terikat langsung dengan _Academic Year_. Menyimpan nama _term_ spesifik ("Ganjil" atau "Genap") serta melingkupi validitas bulan/tanggal (_start_date_, _end_date_). Relasinya mengendalikan masa _live_ atas suatu Kelas/Mata Kuliah.

### 3. Entitas Mata Kuliah dan Peserta (KBM)

1. **`courses`**
    - **Tujuan**: Mendefinisikan rincian kelas/mata kuliah spesifik, seperti `kode_matkul`, nama mata kuliah, `sks`, serta narasi deskriptif pengenalan (_description_).
    - **Relasi**: Entitas ini berakar dari satu Dosen (_owner_) lewat `dosen_id`, dan bernaung di bawah satu periode `semester_id`. Sebuah Course mewadahi banyak data (Materi, Assignment, Kuiz).
2. **`enrollments` (Tabel Perantara / Pivot)**
    - **Tujuan**: Memetakan relasi _Many-to-Many_ yang menjembatani hubungan antara Mahasiswa (`mahasiswa_id`) dan Kelas (`course_id`).
    - Melalui tabel ini, mahasiswa memperoleh otoritas (izin gerak) membaca materi suatu sesi atau berpartisipasi pada ujian (_quizzes_/_assignments_). Tabel ini dapat menyimpan nilai akhir absolut (`final_grade`) sebagai rangkuman hasil pengerjaan.

### 4. Entitas Konten Pengajaran (Materi & Forum)

1. **`materials`**
    - Dikelola oleh dosen pengampu (_Foreign Key_ ke `courses`). Tabel ini mengakomodir penyampaian konten statis ke mahasiswa. Memiliki rujukan untuk berkas dokumen (`file_path`) atau langsung _embed link_ video (`video_url`).
2. **`material_views`** _(Opsional)_
    - Terpasang guna melacak _engagement_ atau durasi sentuh/pembacaan dokumen materispesifik oleh mahasiswa per individunya.
3. **`discussions` & `discussion_comments`**
    - Mengendalikan wadah asinkron bagi mahasiswa dan dosen. `discussions` menjadi akar _(thread)_ percakapan pada cakupan kelas (`course_id`), di mana balasan dinamis yang terkait diikat melalui `discussion_comments`.

### 5. Entitas Evaluasi, Penugasan, dan Ujian

Sub-sistem ini adalah bagian kritikal dari _e-learning_ guna merekam parameter penugasan dan nilainya.

1. **`assignments`**
    - Memiliki parameter kompleks meliputi: tenggat waktu (`due_date`), bobot skor dominan (`max_score`), dan tipe penugasan (enum: `tugas`, `quiz`, `project`).
    - Menyimpan opsi unggah yang membolehkan mahasiswa menyetor _file_ tunggal, dokumen pdf, atau repositori _URL_ (misalnya tautan GitHub).
2. **`submissions`**
    - Media pengumpulan hasil tugas. Direlasikan kuat ke _Assignment_ dan _Mahasiswa_ (User_id).
    - **Kendali Kritis / Constraint**: Tabel ini diikat _UNIQUE (assignment_id, mahasiswa_id)_ di level skema MySQL. Tujuannya adalah memastikan perlindungan absolut agar satu mahasiswa tidak bisa memicu penyerahan (_resubmission_) ganda atas satu tugas yang sama, melainkan hanya _update_ data sebelum tenggat habis (kecuali untuk ujian berbasis percobaan majemuk).
    - Menyimpan _timestamp_ waktu setor (`submitted_at`), angka nilai dari dosen (`score`), hingga umpan balik manual teks (`feedback`). Dilengkapi metadata status penyelesaian enum: _submitted, late, graded_.

### 6. Sub-Sistem Penilaian Canggih (Quiz dan Kelompok)

1. **`groups` & `group_members`** _(Opsional Berdasarkan Tipe Tugas)_
    - Diturunkan dari penugasan dengan format kerja sama berkelompok. Mengorganisasil pembagian anggota (`mahasiswa_id`) agar nilai dari suatu `submission` dapat dikalibrasi pada _group_ bukan per orangan (`group_id`).
2. **`quiz_questions` & `quiz_options`** _(Unified Question Management)_
    - Merupakan implementasi soal detail apabila sebuah `assignment` memiliki tipe sebagai _Quiz_. Sebuah `quiz_questions` menyimpan isi soal, tipenya (pilihan ganda, esai singkat, ataupun logika rentetan kode), serta pembobotan persentil per titik (`score_weight`).
    - Jika berbentuk _Multiple Choice_, pilihan A-E disimpan pada tabel rekursif `quiz_options` lengkap dengan indikator `is_correct` untuk kebutuhan otomatisasi sistem penilaian (skoring mesin).

Seluruh sistem _Foreign Key Constraint_ dirancang menggunakan strategi **Cascade Delete** di beberapa bagian struktural (misal: jika sebuah `course` terhapus, maka seluruh `materials`, `assignments`, dan `enrollments`-nya otomatis terhapus untuk mencegah penumpukan relasi buta/ _Orphaned Data_ pada basis data MySQL).
