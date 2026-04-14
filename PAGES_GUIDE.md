# Panduan Halaman PBL Workspace

Dokumentasi ini merinci seluruh halaman yang tersedia dalam sistem **PBL Workspace** berbasis Project-Based Learning (PBL), dikelompokkan berdasarkan hak akses pengguna dan fitur utama.

---

## Halaman Publik & Autentikasi
Halaman yang dapat diakses sebelum atau sesaat setelah login dasar.

*   **Masuk (`/login`):** Gerbang utama masuk ke sistem menggunakan email dan kata sandi.
*   **Registrasi (`/register`):** Pendaftaran akun baru bagi mahasiswa dan dosen.
*   **Lupa Kata Sandi (`/forgot-password`):** Alur pemulihan akun melalui email.
*   **Verifikasi Email (`/verify-email`):** Keamanan tambahan untuk memastikan email pengguna valid.

---

## Fitur Umum (Semua Pengguna)
Halaman yang tersedia untuk Admin, Dosen, dan Mahasiswa.

*   **Profil Saya (`/profile`):** Mengelola informasi pribadi, mengubah kata sandi, dan menghapus akun.
*   **Notifikasi (`/notifications`):** Daftar pemberitahuan terkait tugas baru, pengumuman, atau nilai yang telah dirilis.
*   **Forum Diskusi (`/discussions`):** Tempat interaksi tanya jawab antara dosen dan mahasiswa terkait materi atau proyek.
*   **Pengumuman (`/announcements`):** Informasi penting yang disiarkan oleh Admin atau Dosen.

---

## Dasbor Admin (`/admin`)
Halaman khusus untuk manajemen infrastruktur akademik dan pengguna.

*   **Dasbor Utama:** Ringkasan statistik (jumlah pengguna, dosen, mahasiswa, dan mata kuliah aktif).
*   **Manajemen Pengguna:** CRUD (Buat, Lihat, Ubah, Hapus) akun pengguna dan aktivasi/deaktivasi akun.
*   **Manajemen Mata Kuliah:** Pengelolaan data mata kuliah dan penugasan mahasiswa ke kelas.
*   **Struktur Akademik:**
    *   **Tahun Akademik & Semester:** Mengatur periode aktif pembelajaran.
    *   **Jurusan & Program Studi:** Manajemen hierarki institusi.
    *   **Kelas Mahasiswa:** Pengorganisasian mahasiswa ke dalam grup kelas.
*   **Hierarki Akademik (Drill-down):** Navigasi visual dari Jurusan -> Prodi -> Semester -> Mata Kuliah.
*   **Manajemen Nilai Keseluruhan:** Memantau rekapitulasi nilai seluruh mahasiswa di semua mata kuliah.
*   **Debug Push Notification:** Alat teknis untuk menguji pengiriman notifikasi ke browser.

---

## Dasbor Dosen (`/dosen`)
Halaman untuk mengelola proses belajar mengajar.

*   **Dasbor Utama:** Ikhtisar aktivitas kelas dan statistik mahasiswa yang diajar.
*   **Manajemen Materi:**
    *   Unggah dokumen (PDF/File).
    *   Pengaturan urutan materi (Drag & Drop).
*   **Manajemen Tugas & Kuis:**
    *   Pembuatan tugas proyek atau kuis pilihan ganda/essay.
    *   **Bank Soal:** Mengelola pertanyaan untuk kuis tertentu.
*   **Penilaian & Umpan Balik:**
    *   Melihat daftar pengumpulan mahasiswa.
    *   Memberikan nilai dan komentar pada tugas atau kuis.
*   **Kelas Virtual (Conference):**
    *   Penjadwalan sesi live conference.
    *   **Ruang Meeting:** Virtual room berbasis LiveKit untuk tatap muka daring (SFU Architecture).

---

## Dasbor Mahasiswa (`/mahasiswa`)
Halaman untuk aktivitas pembelajaran mahasiswa.

*   **Dasbor Utama:** Ringkasan progres belajar, tugas mendatang, dan pengumuman terbaru.
*   **Mata Kuliah Saya:**
    *   Daftar mata kuliah yang diikuti.
    *   **Halaman Kelas:** Mengakses urutan materi dan daftar tugas per mata kuliah.
*   **Materi Belajar:** Viewer khusus untuk membaca PDF atau melihat materi yang diberikan dosen.
*   **Pengerjaan Tugas:**
    *   **Kumpulkan Tugas:** Mengirimkan file atau link proyek.
    *   **Kuis Interaktif:** Mengerjakan kuis online dengan tampilan yang ramah pengguna.
    *   **Latihan Kode:** Editor kode terintegrasi (CodeMirror) untuk menyelesaikan tantangan pemrograman langsung di browser.
*   **Riwayat Nilai:** Melihat rekapitulasi nilai per mata kuliah beserta umpan balik dari dosen.
*   **Bergabung ke Conference:** Masuk ke sesi live meeting yang sedang berlangsung.

---

## Fitur Teknis Tambahan
*   **Mode Gelap (Dark Mode):** Seluruh halaman mendukung optimasi visual untuk kenyamanan mata.
*   **Web Push Notifications:** Menerima pesan instan bahkan saat browser tidak membuka website.
*   **LiveKit Integration:** Engine video conference stabil untuk kapasitas kelas besar.
