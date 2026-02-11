---
trigger: always_on
---

ENTITAS UTAMA
Users

Menyimpan semua user (mahasiswa, dosen, admin).

users

id (PK)

name

email (unique)

password (nullable jika SSO)

role (enum: mahasiswa, dosen, admin)

nim (unique, nullable)

nip (unique, nullable)

sso_id (nullable, jika pakai SSO kampus)

is_active

created_at

updated_at

Relasi:

1 user (dosen) → banyak courses

1 user (mahasiswa) → banyak enrollments

1 user → banyak submissions

Academic Years

Supaya fleksibel mengikuti sistem kampus.

academic_years

id (PK)

year_start (2025)

year_end (2026)

is_active

Relasi:

1 academic_year → banyak semesters

Semesters

Pisahkan semester dari course agar bisa mengikuti sistem kampus.

semesters

id (PK)

academic_year_id (FK)

name (Ganjil / Genap)

start_date

end_date

is_active

Relasi:

1 semester → banyak courses

Courses (Mata Kuliah)

courses

id (PK)

kode_matkul (unique)

nama_matkul

dosen_id (FK → users)

semester_id (FK → semesters)

sks

description

created_at

Relasi:

1 course → banyak assignments

1 course → banyak enrollments

Enrollments (Pivot Mahasiswa–Course)

enrollments

id (PK)

mahasiswa_id (FK → users)

course_id (FK → courses)

enrolled_at

final_grade (nullable)

Cardinality:

1 mahasiswa → banyak enrollments

1 course → banyak enrollments

Many-to-many resolved di sini.

Assignments

assignments

id (PK)

course_id (FK)

title

description

type (enum: tugas, quiz, project)

due_date

max_score

created_at

Relasi:

1 assignment → banyak submissions

Submissions

submissions

id (PK)

assignment_id (FK)

mahasiswa_id (FK → users)

file_path (nullable)

url_link (nullable)

submitted_at

score (nullable)

feedback (nullable)

status (submitted, late, graded)

Constraint penting:
UNIQUE (assignment_id, mahasiswa_id)
Supaya 1 mahasiswa hanya bisa 1 submission per tugas (kecuali mau multiple attempt).

Materials (Materi Pembelajaran)
materials

id (PK)

course_id (FK)

title

description

file_path / video_url

uploaded_by (FK → users)

created_at

Discussions (Opsional tapi Profesional)

discussions

id

course_id

user_id

title

content

created_at

discussion_comments

id

discussion_id

user_id

content

created_at

CARDINALITY RINGKAS

Users (1) —— (N) Courses (as dosen)
Users (1) —— (N) Enrollments
Courses (1) —— (N) Enrollments
Courses (1) —— (N) Assignments
Assignments (1) —— (N) Submissions
Users (1) —— (N) Submissions
AcademicYears (1) —— (N) Semesters
Semesters (1) —— (N) Courses

ERD DALAM FORMAT STRUKTUR VISUAL (TEXT)
Users
├──< Enrollments >── Courses
│ ├── Assignments ──< Submissions
│ ├── Materials
│ └── Discussions ──< DiscussionComments
│
└── Submissions

AcademicYears ──< Semesters ──< Courses
