# Seed the PoliMedia testing ground (GCP prod)

Wipes and reseeds the production DB with the PoliMedia testing ground (real jurusan/prodi
from PDDIKTI + synthetic accounts and content). **Destructive** — all current rows are dropped.

- VM: `pjbl-vm` (project `pjbl-app-btgs6`, IP `34.50.107.24`), Docker Compose, MySQL service `db`,
  app service `app` (container `pjbl-app`), DB name `pjbl`.
- Deploy model: **git pull** (code is bind-mounted) — no image rebuild, no Vite build, no
  `app_build` volume wipe (this change is PHP + JSON only, no JS).
- App URL: https://polimedia.pblworkspace.com

Run these on the VM (via `gcloud compute ssh pjbl-vm --zone <zone>` or your `!` Git Bash), from the
app directory.

### 1. Back up first (always)
```bash
docker compose exec -T db mysqldump -uroot -p"${DB_ROOT_PASSWORD:-root}" pjbl \
  > ~/pjbl_backup_$(date +%F_%H%M).sql
ls -lh ~/pjbl_backup_*.sql   # confirm non-empty
```
Restore later if needed:
```bash
docker compose exec -T db mysql -uroot -p"${DB_ROOT_PASSWORD:-root}" pjbl < ~/pjbl_backup_YYYY-MM-DD_HHMM.sql
```

### 2. Pull the new code
```bash
git pull            # brings the Polimedia seeders + database/data/polimedia.json
```
`database/data/polimedia.json` is committed, so seeding needs **no network** to PDDIKTI.
(Only regenerate it with `python scripts/pddikti/fetch_polimedia.py` if you want fresh data.)

### 3. Wipe + reseed
```bash
docker compose exec app php artisan migrate:fresh --seed --force
```
Expected console lines (≈75s on sqlite, faster on prod MySQL):
`PoliMedia calendar: 4 academic years, 8 semesters (active: Ganjil 2025/2026).`,
`PoliMedia structure: 4 departments, 16 prodi, 384 classes across 8 semesters.`,
`PoliMedia users: 1 admin, 16 dosen, 3072 mahasiswa.`,
`PoliMedia courses: 768 created with full content across 8 semesters.`

### 4. Clear caches
```bash
docker compose exec app php artisan optimize:clear
```

### 5. Smoke test
- Open https://polimedia.pblworkspace.com and log in as `admin@polimedia.test` / `password`.
- On `/register`, confirm the **Kode Kelas** dropdown lists **active-term** classes only
  (e.g. `Desain Grafis — DG-A 25Gj`), ~48 of them.
- Log in as `mahasiswa@polimedia.test` → a course should show materi, tugas, quiz, exercise, conference.

### 6. Verify self-registration email (Resend)
Sign-up emails a 6-digit OTP (sent synchronously). Register a throwaway mahasiswa against a real
kelas and confirm the code arrives. If it doesn't, the Resend mail config needs attention — handout
accounts still work meanwhile. See the SMTP note in project memory.

---
**Accounts & feature checklist:** `docs/testing/tester-accounts.md`.
**Re-seeding is idempotent-safe only via `migrate:fresh`** — the Polimedia seeders use
`firstOrCreate` for structure/users but `create` for courses, so don't run `db:seed` twice on a
populated DB (it would duplicate courses). Always go through `migrate:fresh`.
