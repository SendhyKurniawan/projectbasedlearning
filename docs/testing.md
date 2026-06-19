# Pengujian

## Tata letak test

| Path | Runner | Status |
|---|---|---|
| `tests/Feature/` | Pest + `RefreshDatabase` | aktif, suite utama |
| `tests/Unit/` | Pest | aktif (kecil, mayoritas factory/model) |
| `tests/Browser/` | Pest dengan binding Dusk (`DuskTestCase`) | scaffolded — direktori ada (`Admin/`, `Dosen/`, `Mahasiswa/`, `Auth/`, `Components/`, `Concerns/`, `Pages/`, `Shared/`, `console/`, `fixtures/`, `screenshots/`, `source/`) tetapi belum ada berkas spec yang di-commit |
| `tests/playwright/` | Playwright | WIP — kerangka `assets/`, `helpers/` ada; **belum ada `playwright.config.ts`**, belum ada spec. Skrip `package.json` (`test:pw`, `test:pw:headed`, `test:pw:ui`, `test:pw:report`) sudah disiapkan. |

---

## Pest (Feature + Unit)

```bash
composer test         # config:clear → php artisan test
```

`composer test` adalah entrypoint kanonik. Ia memanggil `php artisan config:clear --ansi` dulu agar cache config lama tidak menimpa `.env.testing`.

### Binding (`tests/Pest.php`)

```php
pest()->extend(Tests\DuskTestCase::class)
      ->beforeEach(function () {
          // Seed hanya saat DB kosong untuk menghindari konflik seeder paralel.
          // Jalankan `php artisan migrate:fresh --seeder=DuskSeeder` untuk memaksa seed bersih.
          if (\App\Models\User::count() === 0) {
              $this->seed(\Database\Seeders\DuskSeeder::class);
          }
      })
      ->in('Browser');

pest()->extend(Tests\TestCase::class)
      ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
      ->in('Feature');
```

Test `Feature` mereset DB tiap test dengan `RefreshDatabase`. Test `Browser` berbagi state dan seed hanya pada run pertama.

> **Perhatian**: `DuskSeeder` dirujuk tetapi **belum** ada di `database/seeders/` saat ini. Bila Anda mulai menulis test browser, buat dulu sebelum menjalankan `composer test` terhadap `tests/Browser/`. Seeder yang ada (`UserSeeder`, `CourseSeeder`, dst.) tetap bisa dipakai untuk seeding ad-hoc.

### Berkas yang saat ini di-commit

```
tests/Feature/
├── AssignmentModelTest.php
├── ConferenceTest.php
├── CourseModelTest.php
├── DosenAssignmentTest.php
├── DosenMaterialTest.php
├── ExampleTest.php
├── MahasiswaSubmissionTest.php
├── MiddlewareTest.php
├── ProfileTest.php
├── ProfileUpdateTest.php
├── SubmissionModelTest.php
└── Auth/
    ├── AuthenticationTest.php
    ├── EmailVerificationTest.php
    ├── PasswordConfirmationTest.php
    ├── PasswordResetTest.php
    ├── PasswordUpdateTest.php
    └── RegistrationTest.php

tests/Unit/
├── ExampleTest.php
└── UserModelTest.php
```

### Pola umum

**Gerbang auth role** — mayoritas test fitur menegaskan bahwa role salah atau tamu mendapat 403/redirect:

```php
test('mahasiswa cannot reach dosen dashboard', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    $this->actingAs($mahasiswa)->get(route('dosen.dashboard'))->assertRedirect(route('mahasiswa.dashboard'));
});
```

(Catatan: middleware `role` me-redirect ke dashboard milik user, bukan 403 — lihat [auth-roles.md](auth-roles.md).)

**Cek kepemilikan** — dosen tak bisa menyentuh mata kuliah dosen lain:

```php
test('dosen cannot view another dosens course', function () {
    $other = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->for($other, 'dosen')->create();
    $this->actingAs($this->dosen)->get(route('dosen.materials.index', $course))->assertForbidden();
});
```

**Redirect login** (`Auth/AuthenticationTest.php` sudah ada di suite):

```php
test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertAuthenticated();
    $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
});
```

### Hook factory

| Factory | Catatan |
|---|---|
| `UserFactory` | Role default `mahasiswa`; timpa dengan `['role' => 'dosen' \| 'admin']`. `is_active` default true. |
| `CourseFactory` | Menyediakan `dosen`, `semester`, `student_class` bila relasi tidak diset. |
| `AssignmentFactory` | Default `type=tugas`. Timpa `type=quiz` atau `type=exercise` (dan sediakan `exercise_config`). |
| `MaterialFactory`, `SubmissionFactory`, `ConferenceFactory` | Factory standar yang sadar relasi. |
| Factory akademik: `AcademicYearFactory`, `SemesterFactory`, `DepartmentFactory`, `StudyProgramFactory`, `StudentClassFactory`. | |

---

## Laravel Dusk

Suite memakai **Pest dengan binding Dusk**. Dusk menjalankan Chrome sungguhan lewat ChromeDriver.

```bash
php artisan dusk              # headless
php artisan dusk --browse     # headed
```

`laravel/dusk` **tidak** ada di `composer.json` saat ini. Untuk mulai memakai Dusk: `composer require --dev laravel/dusk` lalu `php artisan dusk:install` (membuat `tests/DuskTestCase.php` plus template `.env.dusk.local`).

Saran `.env.dusk.local`:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
JITSI_DOMAIN=meet.local.test
JITSI_JWT_APP_ID=test
JITSI_JWT_APP_SECRET=00000000000000000000000000000000
```

Paksa keadaan bersih kapan saja:

```bash
php artisan migrate:fresh --seeder=DuskSeeder
```

Direktori page-object `tests/Browser/Pages/` ada tetapi kosong. Direktori `tests/Browser/console/`, `screenshots/`, dan `source/` dilacak tetapi outputnya di-`.gitignore`.

---

## Playwright (WIP)

`tests/playwright/` adalah rumah yang direncanakan untuk spec Playwright tetapi belum ada `playwright.config.ts` yang di-commit. Sampai Anda menambah config + spec, skrip npm akan keluar dengan `No tests found.`

```bash
npm run test:pw         # headless
npm run test:pw:headed  # browser headed
npm run test:pw:ui      # mode UI Playwright (interaktif)
npm run test:pw:report  # buka laporan HTML terakhir
```

Saat menyambungkan ini:

1. `npm i -D @playwright/test` sudah ada di `devDependencies` (`^1.59.1`).
2. Tambahkan `playwright.config.ts` di root repo.
3. Set `testDir: './tests/playwright'` dan blok `webServer` yang menjalankan `php artisan serve` terhadap `.env.testing`.
4. Pakai akun hasil seed (tabel di bawah) — jangan mendaftarkan user baru di test.
5. Untuk form berbasis Livewire (komentar diskusi), gunakan pengetikan lambat — `page.locator('textarea').pressSequentially(text, { delay: 30 })` — bukan `.fill()`. `wire:model` tidak commit saat `fill()` melompati urutan event input; form akan ter-submit kosong.

---

## Akun test hasil seed

`database/seeders/UserSeeder.php` membuat:

| Role | Email | Catatan |
|---|---|---|
| Admin | `admin@pjbl.test` | password `password` |
| Dosen (utama) | `dosen@pjbl.test` | |
| Dosen | `budi.dosen@pjbl.test` | |
| Dosen | `siti.dosen@pjbl.test` | |
| Mahasiswa (utama) | `mahasiswa@pjbl.test` | `student_class_id = 1` |
| Mahasiswa | `ahmad.mhs@pjbl.test`, `dewi.mhs@…`, `cahya.mhs@…`, `rina.mhs@…`, `fajar.mhs@…` | `student_class_id` acak ∈ {1,2} |

Semua password adalah `password`. Pakai `mahasiswa@pjbl.test` untuk alur mahasiswa; itu yang berada di `student_class_id=1` yang ter-enroll di mata kuliah hasil seed.

`DatabaseSeeder::run()` memanggil (berurutan): `AcademicYearSeeder`, `SemesterSeeder`, `DepartmentSeeder`, `StudyProgramSeeder`, `StudentClassSeeder`, `UserSeeder`, `CourseSeeder`, `DummyDataSeeder`, `AssignmentSeeder`. Jalankan dengan:

```bash
php artisan migrate:fresh --seed
```

---

## Analisis statis & gaya

```bash
vendor/bin/pint         # gaya kode PHP — PSR-12 + preset Laravel
```

Belum ada konfigurasi PHPStan atau Psalm di repo ini saat ini. CI mengandalkan Pint + Pest saja.

---

## Sandbox eksekusi kode dalam test

`POST /execute-code` dibatasi `10/menit` per IP. Test yang menjalankan proxy sebaiknya:

- mock `Illuminate\Support\Facades\Http::fake([…])` di test fitur; atau
- mengganti `services.piston.url` ke fake lokal sebelum dijalankan.

Jangan benar-benar memanggil endpoint publik `emkc.org` dari CI.
