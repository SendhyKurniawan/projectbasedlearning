# Struktur Database Baru (Student Hierarchy)

Untuk mendukung pemisahan mahasiswa berdasarkan struktur akademik yang lebih terperinci, telah ditambahkan hirarki dari tingkat Jurusan hingga Kelas. Berikut adalah skema dan relasi terbaru:

## 1. Tabel `departments` (Jurusan)

Menyimpan data jurusan.

- `id` (PK)
- `name` (Nama Jurusan, e.g., Teknik Informatika dan Komputer)
- `code` (Kode Jurusan, e.g., TIK)
- `created_at`, `updated_at`

## 2. Tabel `study_programs` (Program Studi)

Menyimpan data program studi yang berada di bawah suatu jurusan.

- `id` (PK)
- `department_id` (FK ke `departments`)
- `name` (Nama Program Studi, e.g., D4 Teknik Informatika)
- `code` (Kode Prodi, e.g., TI)
- `level` (Enum: D3, D4, S1, S2, S3)
- `created_at`, `updated_at`

## 3. Tabel `student_classes` (Kelas)

Menyimpan data kelas berdasar program studi dan semester aktif.

- `id` (PK)
- `study_program_id` (FK ke `study_programs`)
- `semester_id` (FK ke `semesters`)
- `name` (Nama Kelas, e.g., TI-1A)
- `created_at`, `updated_at`

## 4. Perubahan pada `users`

Tabel `users` mendapat tambahan satu kolom baru agar setiap mahasiswa dapat ditempatkan pada kelas tertentu.

- `student_class_id` (FK ke `student_classes`, nullable)
  _Catatan: Hanya diisi untuk user dengan role `mahasiswa`._

## 5. Perubahan pada `courses`

Tabel `courses` (Mata Kuliah) juga mendapat tambahan struktur kelas, di mana setiap mata kuliah telah disusun per semesternya dan per kelasnya oleh admin.

- `student_class_id` (FK ke `student_classes`, nullable)
  _Catatan: Mahasiswa hanya akan bisa mengakses courses (mata kuliah) yang memiliki `student_class_id` sesuai dengan kelas mereka._

## Relasi Lengkap (Hierarchy)

`Department (1)` -> `(N) StudyPrograms`
`StudyProgram (1)` -> `(N) StudentClasses`
`Semester (1)` -> `(N) StudentClasses`
`StudentClass (1)` -> `(N) Users (Mahasiswa)`
`StudentClass (1)` -> `(N) Courses`

Dengan struktur ini, sistem sekarang mendukung pengelompokan Mahasiswa dan Mata Kuliah ke dalam Kelas, Program Studi, dan Jurusan secara terorganisir.
