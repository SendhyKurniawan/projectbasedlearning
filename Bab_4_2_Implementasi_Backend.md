# 4.2 Implementasi Kode Backend (Logika & Keamanan)

Implementasi fungsional _backend_ menggunakan instrumen dari Laravel 12 yang menekankan pemisahan logika terstruktur antara proses permintaan pengguna _(Request)_, aturan bisnis otorisasi, manipulasi persisten _database_, hingga format lemparan jawaban _(Response)_, sesuai prinsip _Separation of Concerns_ (SoC) dan Pola _Model-View-Controller_ (MVC).

### 1. Sistem Routing dan Otorisasi (_Middleware_)

Semua kendali rute pengguna diregistrasikan di `routes/web.php`. Demi menjaga integritas akses laman rahasia (seperti dashboard dosen atau manajemen _superadmin_), aplikasi menyertakan filter penjaga bernama Middleware (`Auth` dan `Role`).

**Struktur Pemisahan Rute (_Route Groups_):**

- **Otomasi _Redirect Session_**: Pada jalur bawaan web (`/`), sistem mengenali keberadaan otentikasi. Melalui sintaks `match()`, jalur dasar ini memecah alur URL berdasarkan parameter properti otorisasi pengguna (`admin` otomatis diarahkan ke `admin.dashboard`, `mahasiswa` ke `mahasiswa.dashboard`, dan pengunjung `guest` dikembalikan otomatis ke jendela `login`).
- **Route khusus Admin**: Tersembunyi pada rute berlapis `admin/` dan diverifikasi ketat secara sekuensial memakai `auth` dipadu `role:admin`. Di sinilah jalur `resource('users', Admin\UserController::class)` tersedia untuk aksi memblokir atau menghapus mahasiswa/dosen.
- **Route khusus Dosen**: Terintegrasi pada rute awalan `dosen/`. Secara fungsional menyajikan REST API lokal penuh (`Route::resource`) bagi entitas `courses`, `assignments`, hingga fungsi khusus `/assignments/{assignment}/submissions/{submission}` untuk validasi manual lembar jawaban kuis berformat esai.
- **Route khusus Mahasiswa**: Membutuhkan lapis penyaring validasi _(Custom Middleware)_ seperti `check.assignment.unlocked` guna menentukan apakah jadwal kuis/tugas belum tenggat dan boleh diakses _(solve)_, melarang penulisan skor fiktif (`auth`, `role:mahasiswa`).

### 2. Arsitektur Controller Bersarang (_Nested Controllers_)

Sistem menghindar dari sebuah _Controller Raksasa_ (_God Class_), mengadopsi standar penamaan kontrol per-aktor:

- **Administrasi Logika (`App\Http\Controllers\Admin\*`)**
    - `UserController.php`: Menangani fungsi modifikasi properti _User_, termasuk metode khusus `toggleActive($id)` yang seketika menutup sesi akun mahasiswa bermasalah tanpa mendelete datanya secara harfiah.
    - `CourseController.php`: Menautkan Mahasiswa ke _Course_ memakai injeksi kelas pivot _(Laravel Attach/Detach relationships)_ guna memastikan tidak ada mahasiswa duplikat di sebuah sesi kelas.
- **Kontrol Evaluasi Pengajar (`App\Http\Controllers\Dosen\*`)**
    - `AssignmentController.php`: Di sisi backend, fungsi ini mengatur logika penambahan detail tugas. Berperan aktif pada fungsi simpanan skoring dari mahasiswa (`grade()`), serta merekam format _Unified Question Management_ (`storeQuestion()`) agar terpisah logikanya dari parameter tugas dasar.
- **Proses Evaluasi Interaktif Edukati (`App\Http\Controllers\Mahasiswa\*`)**
    - `SubmissionController.php` & `ExerciseController.php`: Merupakan bagian vital di backend yang menjembatani penerimaan _POST form-data_, proses mengunggah _file attachment_, validasi ekstensi/link repositori, pencegahan _Multiple-Spam Submission_ dengan cek ke database `submissions()->exists()`, dan konfirmasi timestamp. Pengerjaan kode mahasiswa dieksekusi simulasi manual (_sandboxing_ statis via antarmuka UI tanpa otomatisasi _execution engine_).

### 3. Eksekusi Pengamanan dan Standardisasi (Code Quality)

- **Form Request Validation**: Alih-alih merumpunkan validasi panjang di dalam _Controller_, setiap titik masukan data menggunakan `Illuminate\Foundation\Http\FormRequest`. Ini menyaring agar input yang berupa injeksi silang XSS dan angka fiktif gagal di titik awal. Contoh, metode simpan tugas mewajibkan parameter _DueDate_ berformat `date|after:today`.
- **Sistem _Error Handling_ Terintegrasi**: Mengikuti standar industri, semua percobaan input dan unggah data krusial di backend dibungkus blok eksekusi penanganan kesalahan masif berbasis `try-catch`. Bila sinkronisasi gagal, aplikasi membangkitkan pencatatan log internal logaritma server (`Log::error()`) dan sekadar mengembalikan informasi yang aman buat pengguna UI. (Menghindari bocornya data kueri internal ke publik).
- **Proteksi Pintu Dasar Berupa _Laravel Breeze_**: Bertugas mengamankan _Bcrypt Password Hashing_, penyediaan penanganan pendaftaran, mekanisme _Session Persistence_, serta pelrindungan CSRF bawaan secara menyeluruh pada setiap token navigasi dan rute POST yang dijalankan oleh sistem.
