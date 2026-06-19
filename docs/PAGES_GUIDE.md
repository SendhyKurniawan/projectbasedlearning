# 📄 Panduan Halaman PBL E-Learning

Dokumentasi ini merinci seluruh halaman yang tersedia dalam sistem E-Learning berbasis **Project-Based Learning (PBL)**, dikelompokkan berdasarkan hak akses pengguna dan fitur utama.

---

## 🔐 Halaman Publik & Autentikasi

Halaman yang dapat diakses sebelum atau sesaat setelah login dasar.

- **Login (`/login`):** Gerbang utama masuk ke sistem menggunakan email dan password.
- **Registrasi (`/register`):** Pendaftaran akun baru bagi mahasiswa.
- **Lupa Password (`/forgot-password`):** Alur pemulihan akun melalui email.
- **Verifikasi Email (`/verify-email`):** Keamanan tambahan untuk memastikan email pengguna valid.

---

## 👤 Fitur Umum (Semua Pengguna)

Halaman yang tersedia untuk Admin, Dosen, dan Mahasiswa.

- **Profil Saya (`/profile`):** Mengelola informasi pribadi, mengubah password, dan menghapus akun.
- **Notifikasi (`/notifications`):** Daftar pemberitahuan terkait tugas baru, pengumuman, atau nilai yang telah dirilis.
- **Forum Diskusi (`/discussions`):** Tempat interaksi tanya jawab antara dosen dan mahasiswa terkait materi atau proyek.
- **Pengumuman (`/announcements`):** Informasi penting yang disiarkan oleh Admin atau Dosen.

---

## ⚡ Dashboard Admin (`/admin`)

Halaman khusus untuk manajemen infrastruktur akademik dan pengguna.

- **Dashboard Utama:** Ringkasan statistik (jumlah user, dosen, mahasiswa, dan mata kuliah aktif).
- **Manajemen User:** CRUD (Create, Read, Update, Delete) akun pengguna dan aktivasi/deaktivasi akun.
- **Manajemen Mata Kuliah:** Pengelolaan data mata kuliah dan ploting mahasiswa ke kelas.
- **Struktur Akademik:**
    - **Tahun Akademik & Semester:** Mengatur periode aktif pembelajaran.
    - **Jurusan & Program Studi:** Manajemen hierarki institusi.
    - **Kelas Mahasiswa:** Pengorganisasian mahasiswa ke dalam grup kelas.
- **Hierarki Akademik (Drill-down):** Navigasi visual dari Jurusan → Prodi → Semester → Mata Kuliah.
- **Manajemen Nilai Keseluruhan:** Memantau rekapitulasi nilai seluruh mahasiswa di semua mata kuliah.
- **Debug Push Notification:** Alat teknis untuk menguji pengiriman notifikasi ke browser.

---

## 👨‍🏫 Dashboard Dosen (`/dosen`)

Halaman untuk mengelola proses belajar mengajar.

- **Dashboard Utama:** Overview aktivitas kelas dan statistik mahasiswa yang diajar.
- **Manajemen Materi:**
    - Unggah dokumen (PDF/File).
    - Pengaturan urutan materi (Drag & Drop).
- **Manajemen Tugas & Kuis:**
    - Pembuatan tugas proyek atau kuis pilihan ganda/essay.
    - **Bank Soal:** Mengelola pertanyaan untuk kuis tertentu.
- **Review & Penilaian:**
    - Melihat daftar pengumpulan mahasiswa.
    - Memberikan nilai dan feedback pada tugas atau kuis.
- **Kelas Virtual (Conference):**
    - Penjadwalan sesi live conference.
    - **Ruang Meeting:** Virtual room berbasis **Jitsi self-hosted** (dimuat via `external_api.js` dengan token JWT HS256) untuk tatap muka daring.

---

## 🎓 Dashboard Mahasiswa (`/mahasiswa`)

Halaman untuk aktivitas pembelajaran mahasiswa.

- **Dashboard Utama:** Ringkasan progres belajar, tugas mendatang, dan pengumuman terbaru.
- **Mata Kuliah Saya:**
    - Daftar mata kuliah yang diikuti.
    - **Halaman Kelas:** Mengakses urutan materi dan daftar tugas per mata kuliah.
- **Materi Belajar:** Viewer khusus untuk membaca PDF atau melihat materi yang diberikan dosen.
- **Pengerjaan Tugas:**
    - **Upload Tugas:** Mengirimkan file atau link proyek.
    - **Kuis Interaktif:** Mengerjakan kuis online dengan tampilan yang user-friendly.
    - **Code Exercise:** Editor kode terintegrasi (CodeMirror) untuk menyelesaikan tantangan pemrograman langsung di browser.
- **Riwayat Nilai:** Melihat rekapitulasi nilai per mata kuliah beserta feedback dari dosen.
- **Join Conference:** Masuk ke sesi live meeting yang sedang berlangsung.

---

## 🛠️ Fitur Teknis Tambahan

- **Mode Gelap (Dark Mode):** Seluruh halaman mendukung optimasi visual untuk kenyamanan mata.
- **Web Push Notifications:** Menerima pesan instan bahkan saat browser tidak membuka website.
- **Integrasi Jitsi Self-Hosted:** Engine video conference yang di-host sendiri (lihat `JITSI_DOMAIN`) untuk kapasitas kelas besar; moderator ditentukan via klaim JWT.
