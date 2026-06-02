# PBL Workspace — Tester Guide & Accounts

Test instance built on the **real Politeknik Negeri Media Kreatif (PoliMedia) Jakarta**
academic structure (jurusan & program studi pulled from PDDIKTI).

- **URL:** https://polimedia.pblworkspace.com
- **Shared password (all pre-made accounts):** `password`
- Login is by **email + password**. All handout accounts are pre-activated (no OTP step).

## Handout accounts

| Role | Email | Password | Notes |
|------|-------|----------|-------|
| Admin | `admin@polimedia.test` | `password` | Manages jurusan/prodi/kelas, courses, users; approves dosen sign-ups. |
| Dosen | `dosen@polimedia.test` | `password` | Lecturer for prodi **Desain Grafis (D3)**. Teaches the demo courses; has student submissions ready to grade. |
| Mahasiswa | `mahasiswa@polimedia.test` | `password` | Student in kelas **DG-A 25Gj** (Desain Grafis, active term). Enrolled in the demo courses. Land here first for the richest view. |

**More accounts also exist** (same password `password`):
- One dosen per prodi: `dosen.<kode_prodi>@polimedia.test` (e.g. `dosen.90345@polimedia.test` for Animasi).
- ~8 mahasiswa per kelas per term: `<nim>@student.polimedia.test` (NIM shown on the admin user list).

## What's in the data
- A **4-year calendar**: 4 academic years × Ganjil/Genap = **8 semesters**; only **Ganjil 2025/26 is active**.
- **16 program studi** (Jakarta campus) across **4 jurusan**, **3 kelas each (A–C) per semester** =
  384 kelas total (**48 in the active term**), ~**3,072 mahasiswa**.
- **768 courses** — each mata kuliah runs across a term's kelas as *siblings* (same dosen, code &
  semester); titles carry the term's study level, e.g. "Studio Desain VII". The active term holds **96**.
- Every course ships: 2 materi, 5 tugas/quiz/exercise (PDF tugas, URL group project, MC quiz with
  timer, coding exercise, essay quiz), and 1 scheduled conference.
- The active-term demo kelas (**DG-A 25Gj**) also has **sample submissions** (graded / submitted /
  late) on the first tugas.
- Past terms (2022–2025) are fully populated too — visible to admin and to students holding those
  accounts — but the handout accounts and new sign-ups live in the **active** term.
- Global: 3 pengumuman (announcements) + 3 diskusi threads.

## Feature checklist (try everything)

**As mahasiswa (`mahasiswa@polimedia.test`):**
1. Dashboard → open a course → read a **Materi**.
2. Submit the **Tugas 1** (PDF) and the **Project Akhir** (URL).
3. Take the **Kuis Tengah Semester** (MC, 60-min timer) → see the auto-scored MC result.
4. Do the **Exercise** (coding) → run code in the editor → submit.
5. Join/create a **Group** on the group project.
6. Open the **Conference** (Jitsi) from Jadwal/course.
7. Post in a **Diskusi**, read **Pengumuman**, check **Jadwal**, enable **push notifications**.

**As dosen (`dosen@polimedia.test`):**
1. Open a course → create a Materi / Assignment / Conference → **fan out to sibling kelas** (B/C/D).
2. Use **copy-to-kelas** on existing content.
3. Open **Submissions** for Tugas 1 → **grade** a submission (sample data is there).
4. Review the quiz auto-scoring and grade the essay/exercise manually.

**As admin (`admin@polimedia.test`):**
1. Browse jurusan → prodi → kelas → courses.
2. Create/edit a user; **approve a self-registered dosen** (see below).
3. Post an Announcement targeted to mahasiswa/dosen.

**Self-registration flow (optional):**
- `/register` → choose role + a real **Kode Kelas** (the dropdown lists only **active-term** classes,
  e.g. `Desain Grafis — DG-A 25Gj`) → a 6-digit **OTP** is emailed. Mahasiswa activate on OTP;
  **dosen also need admin approval** afterward (use `admin@` to approve).
- On signup a mahasiswa is **auto-enrolled into their kelas's mata kuliah**, so the dashboard is
  populated on first login. The **Courses** page is **scoped to their kelas** (+ semester-wide
  courses), not the whole catalog.
- Requires email delivery (Resend) to be live — see `docs/ops/seed-testing-ground.md`.
