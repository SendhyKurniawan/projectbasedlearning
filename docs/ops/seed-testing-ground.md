# Seed testing ground PoliMedia (prod GCP)

Menghapus dan men-seed ulang DB produksi dengan testing ground PoliMedia (jurusan/prodi nyata dari PDDIKTI + akun & konten sintetis). **Destruktif** — semua baris saat ini dihapus.

- VM: `pjbl-vm` (project `pjbl-app-btgs6`, IP `34.50.107.24`), Docker Compose, service MySQL `db`, service aplikasi `app` (kontainer `pjbl-app`), nama DB `pjbl`.
- Model deploy: **git pull** (kode di-bind-mount) — tanpa rebuild image, tanpa build Vite, tanpa wipe volume `app_build` (perubahan ini hanya PHP + JSON, tanpa JS).
- URL aplikasi: https://polimedia.pblworkspace.com

Jalankan ini di VM (via `gcloud compute ssh pjbl-vm --zone <zone>` atau Git Bash `!` Anda), dari direktori aplikasi.

### 1. Backup dulu (selalu)
```bash
docker compose exec -T db mysqldump -uroot -p"${DB_ROOT_PASSWORD:-root}" pjbl \
  > ~/pjbl_backup_$(date +%F_%H%M).sql
ls -lh ~/pjbl_backup_*.sql   # konfirmasi tidak kosong
```
Pulihkan nanti bila perlu:
```bash
docker compose exec -T db mysql -uroot -p"${DB_ROOT_PASSWORD:-root}" pjbl < ~/pjbl_backup_YYYY-MM-DD_HHMM.sql
```

### 2. Tarik kode baru
```bash
git pull            # membawa seeder Polimedia + database/data/polimedia.json
```
`database/data/polimedia.json` di-commit, jadi seeding **tidak perlu jaringan** ke PDDIKTI.
(Regenerasi hanya dengan `python scripts/pddikti/fetch_polimedia.py` bila Anda ingin data segar.)

### 3. Wipe + seed ulang
```bash
docker compose exec app php artisan migrate:fresh --seed --force
```
Baris konsol yang diharapkan (≈75 detik di sqlite, lebih cepat di MySQL prod):
`PoliMedia calendar: 4 academic years, 8 semesters (active: Ganjil 2025/2026).`,
`PoliMedia structure: 4 departments, 16 prodi, 384 classes across 8 semesters.`,
`PoliMedia users: 1 admin, 16 dosen, 3072 mahasiswa.`,
`PoliMedia courses: 768 created with full content across 8 semesters.`

### 4. Bersihkan cache
```bash
docker compose exec app php artisan optimize:clear
```

### 5. Smoke test
- Buka https://polimedia.pblworkspace.com dan login sebagai `admin@polimedia.test` / `password`.
- Di `/register`, konfirmasi dropdown **Kode Kelas** hanya menampilkan kelas **periode-aktif** (mis. `Desain Grafis — DG-A 25Gj`), sekitar 48 buah.
- Login sebagai `mahasiswa@polimedia.test` → sebuah mata kuliah seharusnya menampilkan materi, tugas, quiz, exercise, konferensi.

### 6. Verifikasi email registrasi-mandiri (Resend)
Pendaftaran mengirim OTP 6-digit lewat email (dikirim sinkron). Daftarkan mahasiswa sekali-pakai terhadap kelas nyata dan konfirmasi kodenya tiba. Bila tidak, config mail Resend perlu diperiksa — akun bagian masih berfungsi sementara itu. Lihat catatan SMTP di memori proyek.

---
**Akun & checklist fitur:** `docs/testing/tester-accounts.md`.
**Seed ulang aman-idempoten hanya via `migrate:fresh`** — seeder Polimedia memakai `firstOrCreate` untuk struktur/user tetapi `create` untuk mata kuliah, jadi jangan jalankan `db:seed` dua kali pada DB terisi (akan menduplikasi mata kuliah). Selalu lewat `migrate:fresh`.
