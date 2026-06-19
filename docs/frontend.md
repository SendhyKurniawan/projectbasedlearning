# Frontend

## Stack

- **Blade** template server-rendered — setiap halaman.
- **Tailwind 3** untuk utilitas, dikonfigurasi di `tailwind.config.js`. Design token berada di `resources/css/design-system.css` dan dirujuk di kelas melalui arbitrary property atau via token warna MD3 (`bg-surface`, `text-on-surface`, dst.).
- **Alpine 3** untuk state sisi klien — dikirim otomatis oleh Livewire 4 (`@livewireScripts`). `app.js` sendiri **tidak** `import 'alpinejs'`.
- **Livewire 4** untuk satu komponen diskusi (sekaligus membawa Alpine).
- **CodeMirror 5** untuk editor exercise dosen dan view pengerjaan mahasiswa.
- **EasyMDE** untuk editor markdown di create/edit materi dosen.
- **marked + highlight.js** untuk render markdown read-only di view materi mahasiswa.
- **Chart.js 4** untuk dashboard.
- **Vite 7** sebagai bundler / dev server, dengan `laravel-vite-plugin` dan `fast-glob` untuk auto-discovery CSS per-halaman.

Versi `package.json` yang perlu dicek sebelum mengubah: `alpinejs ^3.4.2`, `tailwindcss ^3.1.0`, `vite ^7.0.7`, `chart.js ^4.4.0`, `codemirror ^5.65.20`, `easymde ^2.20.0`, `marked ^17.0.1`, `highlight.js ^11.11.1`.

---

## Entry point Vite (`vite.config.js`)

```js
laravel({
  input: [
    'resources/css/app.css',
    'resources/css/design-system.css',
    ...fg.sync('resources/css/pages/**/*.css'),
    'resources/js/app.js',
    'resources/js/code-editor.js',
    'resources/js/markdown-editor.js',
    'resources/js/markdown-renderer.js',
    'resources/js/charts.js'
  ],
  refresh: true,
});
```

| Berkas | Kapan dimuat | Fungsinya |
|---|---|---|
| `resources/css/app.css` | setiap halaman (via `@vite` layout) | Layer Tailwind + override utilitas global |
| `resources/css/design-system.css` | setiap halaman | Token rasa MD3 (`--md-sys-color-*`) dan skala tipografi |
| `resources/css/pages/**/*.css` | per halaman | Style spesifik halaman yang di-glob otomatis; `@vite` hanya di view yang membutuhkannya |
| `resources/js/app.js` | setiap halaman | Saat ini hanya `import './bootstrap';` (axios + header CSRF). Alpine tiba lewat `@livewireScripts`. |
| `resources/js/code-editor.js` | create/edit exercise dosen, pengerjaan exercise mahasiswa | Init CodeMirror dikunci oleh `data-codemirror` |
| `resources/js/markdown-editor.js` | create/edit materi dosen | EasyMDE pada `textarea[data-markdown-editor]` |
| `resources/js/markdown-renderer.js` | show materi mahasiswa | Render Markdown via marked + highlight.js ke elemen yang ikut serta |
| `resources/js/charts.js` | dashboard (admin/dosen/mahasiswa) | Init Chart.js membaca token MD3 dari `getComputedStyle(document.documentElement)` |

**Tidak ada** `conference-jitsi.js`. Ruang konferensi membuka Jitsi di tab baru via tautan biasa `<a href="https://{JITSI_DOMAIN}/{room_name}?jwt={jwt}" target="_blank">` — lihat [features/conferences.md](features/conferences.md).

---

## Layout

### `<x-app-layout>` (`resources/views/layouts/app.blade.php`)

Kerangka untuk pengguna terautentikasi.

```blade
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold">Judul Halaman</h1>
    </x-slot>

    {{-- konten halaman di sini --}}
</x-app-layout>
```

Yang disertakan:

- Head HTML dengan `@vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js'])`, `@livewireStyles`, preload font Inter/Manrope/Material Symbols.
- Toggle sidebar mobile dibungkus `x-data="{ open: false }"` dengan backdrop click-outside.
- `@include('layouts.sidebar')` — sidebar disusun oleh `SidebarComposer`.
- Slot `$header` opsional dirender di dalam top bar yang sticky; jika tidak, lonceng mengambang di kanan atas.
- `@include('layouts.notifications')` — badge lonceng belum dibaca di top bar.
- Slot konten utama.
- `@livewireScripts` (juga membawa Alpine).
- Registrasi service-worker inline yang men-subscribe browser ke WebPush via `/push-subscribe`. Memakai `env('VAPID_PUBLIC_KEY')` (dipanggil langsung di Blade — sadari ini mencegah `config:cache` menutupi kunci yang hilang, tetapi juga berarti front-end diam-diam melewati subscription bila env kosong).

### `<x-guest-layout>` (`resources/views/layouts/guest.blade.php`)

Dipakai untuk login, register, reset password, verifikasi OTP, dll.

### `layouts/sidebar.blade.php`

Disusun oleh `App\Http\View\Composers\SidebarComposer` (didaftarkan di `AppServiceProvider::boot()`). Menyediakan:

| Variabel | Untuk role | Catatan |
|---|---|---|
| `$dosenCourses` | dosen | `Cache::remember("sidebar:dosen:{user.id}", 300, …)` — eager-load `studentClass`, diurut `created_at desc` |
| `$dosenCourseGroups` | dosen | `$dosenCourses->groupBy('course_group_key')` untuk grup matkul yang dapat dilipat |
| `$mahasiswaFirstCourse` | mahasiswa | Mata kuliah terdaftar pertama (query DB mentah demi kecepatan) |

Kunci cache dibersihkan oleh `Course::booted()` setiap create/update/delete.

### `layouts/topbar.blade.php`, `layouts/notifications.blade.php`

`topbar.blade.php` merender pita header halaman. `notifications.blade.php` merender lonceng — jumlah belum dibaca dari `auth()->user()->unreadNotifications->count()` (channel database).

---

## Pola Alpine

Alpine disisipkan inline pada elemen via `x-data` / `x-show` / `x-on` / `x-transition`. Tidak ada store Alpine global; state komponen berskop per blok `x-data`.

Pola umum dalam basis kode:

- **Modal**: `x-data="{ open: false }"` + `@click.outside="open = false"` + kelas transisi (`<x-modal>`, `<x-copy-modal>`).
- **Pergantian tab**: `x-data="{ tab: 'info' }"` pada bagian dashboard.
- **Field form dinamis**: men-toggle visibilitas berdasarkan select (mis. tipe assignment, format pengumpulan, toggle kelompok).
- **Timer kuis**: hitung mundur sisi klien dari `started_at` + `duration_minutes`, auto-submit saat habis.
- **Dialog konfirmasi inline**: `@click="if (confirm('…')) { $refs.endForm.submit(); }"` — dipakai untuk "Akhiri Sesi" di ruang konferensi.

Hindari menambah store Alpine global; bila state perlu melintasi halaman, dorong ke sisi server.

---

## Komponen Blade yang dapat dipakai ulang

`resources/views/components/`:

| Komponen | Tujuan |
|---|---|
| `<x-app-layout>` | Kerangka terautentikasi (dijelaskan di atas) |
| `<x-guest-layout>` | Kerangka tak terautentikasi |
| `<x-application-logo />` | Logo SVG untuk merek |
| `<x-assignment-card :assignment="…" />` | Kartu tugas berulang yang dipakai di daftar |
| `<x-auth-session-status :status="…" />` | Merender `session('status')` untuk flash auth |
| `<x-copy-modal :course :siblings :action />` | Modal Alpine yang mem-POST `sibling_ids` ke endpoint copy |
| `<x-danger-button>`, `<x-primary-button>`, `<x-secondary-button>` | Gaya tombol konsisten |
| `<x-dropdown>`, `<x-dropdown-link>` | Kerangka dropdown Alpine |
| `<x-input-error :messages="…" />` | Pesan error validasi di bawah field |
| `<x-input-label :value="…" />` | Label form |
| `<x-modal>` | Kerangka modal Alpine generik |
| `<x-nav-link>`, `<x-responsive-nav-link>` | Tautan navigasi sidebar / mobile |
| `<x-text-input>` | Pembungkus input teks dengan kelas Tailwind standar |
| `<x-discussion.*>` | Komponen khusus modul diskusi |

Pakai ini demi konsistensi. `<button>` / `<input>` polos boleh untuk kasus sekali pakai, tetapi varian di atas membungkus standar `bg-primary text-on-primary` dll.

### `<x-copy-modal>`

```blade
<x-copy-modal
    :course="$course"
    :siblings="$course->siblings()"
    :action="route('dosen.materials.copy', $material)"
/>
```

Merender form checkbox-per-sibling; endpoint action menerapkan irisan keamanan di sisi server.

### `dosen/partials/sibling-kelas-picker.blade.php`

`@include('dosen.partials.sibling-kelas-picker')` pada form **create** Material / Assignment / Conference / Exercise dosen. Ia merender checkbox sibling yang sama secara inline sehingga handler create dapat fan-out saat pembuatan. Berbeda dengan `<x-copy-modal>` (yang beraksi pada record yang sudah ada).

---

## Editor

### CodeMirror (`code-editor.js`)

Dipakai pada:
- Form create/edit exercise dosen (starter code, solution code).
- Halaman pengerjaan exercise mahasiswa.

Menargetkan elemen bertanda `data-codemirror`, bahasa dibaca dari `data-language` (salah satu dari `html`, `css`, `javascript`, `htmlmixed`, `java`, `php`, `csharp`). Untuk bahasa server-side (`java`, `php`, `csharp`), tombol Run mem-POST ke `/execute-code`. Untuk HTML/CSS/JS preview terjadi di iframe sisi klien.

### EasyMDE (`markdown-editor.js`)

Diinisialisasi pada `textarea[data-markdown-editor]`. Outputnya adalah Markdown mentah yang disimpan di `materials.content` — tidak ada konversi Markdown→HTML sisi server saat menyimpan. Render HTML terjadi saat view via `markdown-renderer.js`.

### `markdown-renderer.js`

Read-only. Dipakai pada `mahasiswa.materials.show`. Membaca sumber markdown dari atribut data dan menyalurkannya melalui `marked` + `highlight.js`.

---

## Chart

`charts.js` menginisialisasi Chart.js pada canvas dengan ID yang dikenal. Warna ditarik dari CSS custom property agar chart tetap selaras dengan design system:

```js
const styles = getComputedStyle(document.documentElement);
const primary = styles.getPropertyValue('--md-sys-color-primary').trim();
```

Dataset dilewatkan dari controller via atribut `data-*` JSON-encoded (atau via blok `<script>` inline yang memancarkan `window.X = @json($payload)`).

Seri spesifik dashboard yang diproduksi controller:

- **Dashboard admin** (`Admin\DashboardController`): seri aktivitas 30 hari (`submissions`, `materials`), donut distribusi role.
- **Dashboard dosen** (`Dosen\DashboardController`): seri pengumpulan 7 hari, jumlah menunggu review.
- **Dashboard mahasiswa** (`Mahasiswa\DashboardController`): seri pengumpulan sendiri 30 hari, histogram nilai (`0–50`, `51–70`, `71–85`, `86–100`).

---

## Design system

`resources/css/design-system.css` mengekspos token bergaya Material You:

```css
:root {
  --md-sys-color-primary: …;
  --md-sys-color-on-primary: …;
  --md-sys-color-surface: …;
  --md-sys-color-on-surface: …;
  --md-sys-color-surface-container-lowest: …;
  /* …shape, tipografi, motion */
}
```

Ini dipetakan ke warna utilitas Tailwind via `tailwind.config.js` (mis. `bg-primary`, `text-on-surface-variant`, `bg-surface-container-lowest`). Saat mengubah warna, jalankan:

```bash
npm run audit:contrast
```

Ia mengeksekusi `scripts/audit-contrast.mjs` untuk memverifikasi rasio kontras WCAG pada palet. Review CI/PR memakai cek ini.

Font: **Inter** (body, kritikal-LCP, dimuat segera), **Manrope** (heading, ditangguhkan), **Material Symbols Outlined** (font ikon, ditangguhkan). Ketiganya ditarik dari Google Fonts via `<link rel="stylesheet">` di layout — tanpa self-hosting.

---

## Push notification (sisi front-end)

Layout menyertakan bootstrap service-worker inline:

```js
if ('serviceWorker' in navigator && 'PushManager' in window) {
  navigator.serviceWorker.register('/sw.js').then(registration => {
    Notification.requestPermission().then(permission => {
      if (permission === 'granted' && vapidPublicKey) {
        registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        }).then(subscription => {
          fetch('/push-subscribe', { /* … */ });
        });
      }
    });
  });
}
```

`/sw.js` adalah service worker yang berada di `public/`. `vapidPublicKey` diinterpolasi dari `env('VAPID_PUBLIC_KEY')` langsung di Blade — bila env kosong front-end melewati subscription diam-diam.

`POST /push-subscribe` dan `POST /push-unsubscribe` menuju `PushSubscriptionController`, yang memanggil `User::updatePushSubscription()` / `User::deletePushSubscription()` dari trait `HasPushSubscriptions`.

---

## Lokasi halaman

- Admin: `resources/views/admin/{akademik,announcements,auth,conferences,courses,dashboard,debug,departments,grades,study-programs,student-classes,semesters,users}/…`
- Dosen: `resources/views/dosen/{assignments,conferences,courses,exercises,grades,materials,partials}/…`
- Mahasiswa: `resources/views/mahasiswa/{conferences,courses,exercises,grades,materials,quizzes,schedule,submissions}/…`
- Bersama: `resources/views/{announcements,auth,discussions,notifications,profile,errors,layouts,livewire,components,vendor}/…`
- View Livewire: `resources/views/livewire/discussion/show.blade.php`
