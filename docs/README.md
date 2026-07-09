# PBL Workspace — Indeks Dokumentasi

LMS Laravel 12 (server-rendered) untuk Project-Based Learning. Tiga role (`admin`, `dosen`, `mahasiswa`) di atas hierarki akademik (jurusan → program studi → kelas → mata kuliah). Blade + Tailwind + Alpine; satu komponen Livewire (`Discussion\Show`). Konferensi memakai Jitsi self-hosted (JWT HS256, peluncur tab baru). Notifikasi dikirim ke channel `database` + `webpush`.

Mulai dari **getting-started.md** bila Anda menyiapkan lingkungan lokal. Mulai dari **architecture.md** bila Anda baru mengenal basis kode ini.

---

## Tingkat atas

| Dokumen | Cakupan |
|---|---|
| [getting-started.md](getting-started.md) | Prasyarat, setup native + Docker, referensi variabel env, akun hasil seed, perintah harian, troubleshooting awal |
| [arsitektur-dan-perancangan.md](arsitektur-dan-perancangan.md) | Penjelasan menyeluruh & edukatif — pola monolith, stack, MVC, perancangan DB, server/infrastruktur, keamanan, plus glosarium istilah teknis |
| [architecture.md](architecture.md) | Sistem tiga role, hierarki akademik, siblings + `course_group_key`, copy fan-out, alur request, eksekusi kode Piston, konvensi |
| [auth-roles.md](auth-roles.md) | Enum `role`, middleware route, `CheckRole` + `CheckAssignmentUnlocked`, policy, login admin, alur registrasi + OTP + persetujuan admin |
| [database.md](database.md) | Referensi skema per tabel, composite key, konvensi (query mentah `DB::table('enrollments')`), gotcha |
| [frontend.md](frontend.md) | Entry point Vite, layout, pola Alpine, komponen Blade, editor (CodeMirror/EasyMDE), Chart.js, design system, bootstrap push subscription |
| [testing.md](testing.md) | Pest (Feature + Unit), scaffolding Dusk, Playwright (WIP), akun hasil seed |
| [deployment.md](deployment.md) | Topologi produksi (Caddy + Docker), service, checklist produksi, variabel env, provisioning Jitsi/VAPID, queue worker, mail, penyimpanan berkas, rollback, logging |
| [contributing.md](contributing.md) | Model branch, konvensi kode, keamanan copy fan-out, menambah tipe assignment, batasan Livewire, template PR |

## Dokumen fitur

| Dokumen | Cakupan |
|---|---|
| [features/courses.md](features/courses.md) | Model Course, siblings, `course_group_key`, enrollment, cache sidebar, learning path mahasiswa, copy fan-out, fallback URL polos |
| [features/materials.md](features/materials.md) | Model Material, route penulisan oleh dosen, EasyMDE/CodeMirror, reorder, penanganan berkas & fan-out, tampilan mahasiswa + pencatatan `MaterialView` |
| [features/assignments.md](features/assignments.md) | Tiga tipe (`tugas / quiz / exercise`), route penulisan dosen, tipe soal kuis, `exercise_config`, prasyarat materi, copy fan-out, notifikasi |
| [features/submissions.md](features/submissions.md) | Daur hidup, pengumpulan tugas (individu + kelompok), semantik submit kuis, submit exercise + `validation_result`, penilaian kelompok, tampilan nilai mahasiswa |
| [features/conferences.md](features/conferences.md) | Model `Conference`, Jitsi self-hosted via JWT HS256, `JitsiTokenService::mint`, daur hidup ruang, perilaku per role, view ruang (peluncur tab baru), copy fan-out |
| [features/notifications.md](features/notifications.md) | Channel `database` + `webpush`, katalog notifikasi, lokasi dispatch, perilaku queue, bentuk payload, alur subscription WebPush, debug admin |
| [features/discussions.md](features/discussions.md) | Model (`Discussion.content`, `DiscussionComment.content`), route, cache sidebar, satu-satunya komponen Livewire, kuirk Livewire 4 |
| [features/announcements.md](features/announcements.md) | Resource bersama (grup auth-only), enum target_audience (`all/dosen/mahasiswa/specific`), lampiran, gerbang visibilitas per role, dispatch `AnnouncementNotification` |
| [features/admin-akademik.md](features/admin-akademik.md) | Halaman terpadu `/admin/akademik`, sub-aksi `AkademikController` untuk tahun/semester/jurusan/prodi/kelas/matkul, assign/unassign kelas, route resource lama, CRUD user admin |
| [features/grades.md](features/grades.md) | Tiga tampilan nilai — daftar filter admin, buku nilai dosen + ekspor CSV + quick-grade, tampilan nilai per matkul mahasiswa |
| [features/code-execution.md](features/code-execution.md) | Proxy `POST /execute-code`, allowlist bahasa + mapping Piston, kontainer docker bawaan, bentuk request/response, throttle + penanganan error, runtime iframe sisi klien |
| [features/profile.md](features/profile.md) | CRUD swalayan `/profile`, `ProfileUpdateRequest` (salah satu dari dua FormRequest), alur ganti password + hapus akun, catatan cascade penghapusan |
| [features/schedule.md](features/schedule.md) | `/mahasiswa/jadwal` — linimasa per hari, isi yang ditampilkan, pewarnaan urgensi, pengecualian konvensi (relasi Eloquent, bukan query mentah) |

## Operasional

| Dokumen | Cakupan |
|---|---|
| [ops/jitsi-self-host.md](ops/jitsi-self-host.md) | Runbook lengkap provisioning Jitsi self-hosted di VM GCP — DNS, firewall, Caddy (kontainer), TURN coturn, `docker-jitsi-meet`, secret JWT, redirect SSO, verifikasi, rollback |
| [ops/seed-testing-ground.md](ops/seed-testing-ground.md) | Runbook seeding produksi untuk testing ground PoliMedia |

## Pengujian & laporan

| Dokumen | Cakupan |
|---|---|
| [testing/tester-accounts.md](testing/tester-accounts.md) | Kredensial akun penguji dan panduannya |
| [testing/conference-test-report-2026-05-31.md](testing/conference-test-report-2026-05-31.md) | Laporan uji konferensi end-to-end manual (Jitsi self-hosted) |
| [testing/jitsi-stress-test-report-2026-06-05.md](testing/jitsi-stress-test-report-2026-06-05.md) | Uji beban Jitsi produksi — kapasitas ruang 100 peserta di VM GCP |
| [testing/loadtest/](testing/loadtest/) | Harness uji beban, rencana, log, dan laporan 20-bot (`loadtest-report-2026-06-01.md`) |

> Laporan uji & log load-test dibiarkan apa adanya sebagai artefak historis (Bahasa Inggris).

## Sumber / khusus

| Dokumen | Cakupan |
|---|---|
| [bab4-implementasi.md](bab4-implementasi.md) | Referensi implementasi rinci (Indonesia) — panduan pengembangan & implementasi akurat-produksi untuk bab BAB 4 skripsi |
| [lampiran-snippet-kode.md](lampiran-snippet-kode.md) | Lampiran cuplikan kode inti (skripsi): model siblings, middleware role, policy, JWT Jitsi, proxy eksekusi, OTP, auto-grading, copy fan-out |
| [PAGES_GUIDE.md](PAGES_GUIDE.md) | Referensi per halaman untuk layar UI tiap role |

---

## Penunjuk di dalam repo (di luar docs/)

- [CLAUDE.md](../CLAUDE.md) — panduan proyek ringkas yang dipakai sebagai memori agen; mencerminkan arsitektur tingkat tinggi & gotcha pada kumpulan dokumen ini (dibiarkan dalam Bahasa Inggris)
- `routes/web.php` + `routes/auth.php` — sumber kebenaran tunggal pemetaan URL → controller
- `app/Notifications/` — katalog kelas notifikasi
- `database/migrations/` — linimasa skema
- `config/code_execution.php`, `config/services.php` — bahasa Piston, kunci env Jitsi
