# Frontend

## Stack

- **Blade** server-rendered templates — every page.
- **Tailwind 3** for utilities, configured in `tailwind.config.js`. The design tokens live in `resources/css/design-system.css` and are referenced in classes through arbitrary properties or via the MD3 colour tokens (`bg-surface`, `text-on-surface`, etc.).
- **Alpine 3** for client-side state — shipped automatically by Livewire 4 (`@livewireScripts`). `app.js` itself does **not** `import 'alpinejs'`.
- **Livewire 4** for the single discussion component (and brings Alpine with it).
- **CodeMirror 5** for the dosen exercise editor and the mahasiswa solve view.
- **EasyMDE** for the markdown editor on dosen material create/edit.
- **marked + highlight.js** for read-only markdown rendering on mahasiswa material view.
- **Chart.js 4** for dashboards.
- **Vite 7** as bundler / dev server, with `laravel-vite-plugin` and `fast-glob` for per-page CSS auto-discovery.

`package.json` versions to check before changing: `alpinejs ^3.4.2`, `tailwindcss ^3.1.0`, `vite ^7.0.7`, `chart.js ^4.4.0`, `codemirror ^5.65.20`, `easymde ^2.20.0`, `marked ^17.0.1`, `highlight.js ^11.11.1`.

---

## Vite entry points (`vite.config.js`)

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

| File | When loaded | What it does |
|---|---|---|
| `resources/css/app.css` | every page (via layout `@vite`) | Tailwind layers + global utility overrides |
| `resources/css/design-system.css` | every page | MD3-flavoured tokens (`--md-sys-color-*`) and typography scale |
| `resources/css/pages/**/*.css` | per page | Auto-globbed page-specific styles; `@vite` them only on the views that need them |
| `resources/js/app.js` | every page | Currently just `import './bootstrap';` (axios + CSRF header). Alpine arrives through `@livewireScripts`. |
| `resources/js/code-editor.js` | dosen exercise create/edit, mahasiswa exercise solve | CodeMirror init keyed by `data-codemirror` |
| `resources/js/markdown-editor.js` | dosen material create/edit | EasyMDE on `textarea[data-markdown-editor]` |
| `resources/js/markdown-renderer.js` | mahasiswa material show | Renders Markdown via marked + highlight.js into elements that opt in |
| `resources/js/charts.js` | dashboards (admin/dosen/mahasiswa) | Chart.js init reading MD3 tokens from `getComputedStyle(document.documentElement)` |

There is **no** `conference-jitsi.js`. Conference rooms open Jitsi in a new tab via a plain `<a href="https://{JITSI_DOMAIN}/{room_name}?jwt={jwt}" target="_blank">` link — see [features/conferences.md](features/conferences.md).

---

## Layouts

### `<x-app-layout>` (`resources/views/layouts/app.blade.php`)

The authenticated shell.

```blade
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold">Page Title</h1>
    </x-slot>

    {{-- page content here --}}
</x-app-layout>
```

What it includes:

- HTML head with `@vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js'])`, `@livewireStyles`, Inter/Manrope/Material Symbols font preloads.
- A mobile sidebar toggle wrapped in `x-data="{ open: false }"` with a click-outside backdrop.
- `@include('layouts.sidebar')` — sidebar is composed by `SidebarComposer`.
- Optional `$header` slot rendered inside a sticky top bar; otherwise the bell floats top-right.
- `@include('layouts.notifications')` — unread bell badge in the top bar.
- Main content slot.
- `@livewireScripts` (also brings Alpine).
- Inline service-worker registration that subscribes the browser to WebPush via `/push-subscribe`. Uses `env('VAPID_PUBLIC_KEY')` (called directly in Blade — be aware this prevents `config:cache` from masking missing keys, but also means the front-end silently skips subscription if the env var is empty).

### `<x-guest-layout>` (`resources/views/layouts/guest.blade.php`)

Used for login, register, password reset, OTP verify, etc.

### `layouts/sidebar.blade.php`

Composed by `App\Http\View\Composers\SidebarComposer` (registered in `AppServiceProvider::boot()`). Provides:

| Variable | For role | Notes |
|---|---|---|
| `$dosenCourses` | dosen | `Cache::remember("sidebar:dosen:{user.id}", 300, …)` — eager-loads `studentClass`, ordered by `created_at desc` |
| `$dosenCourseGroups` | dosen | `$dosenCourses->groupBy('course_group_key')` for collapsible matkul groups |
| `$mahasiswaFirstCourse` | mahasiswa | The first enrolled course (raw DB query for speed) |

The cache key is flushed by `Course::booted()` on every create/update/delete.

### `layouts/topbar.blade.php`, `layouts/notifications.blade.php`

`topbar.blade.php` renders the page header band. `notifications.blade.php` renders the bell — unread count comes from `auth()->user()->unreadNotifications->count()` (database channel).

---

## Alpine patterns

Alpine is sprinkled inline on elements via `x-data` / `x-show` / `x-on` / `x-transition`. There is no global Alpine store; component state is scoped per `x-data` block.

Common patterns in the codebase:

- **Modals**: `x-data="{ open: false }"` + `@click.outside="open = false"` + transition classes (`<x-modal>`, `<x-copy-modal>`).
- **Tab switching**: `x-data="{ tab: 'info' }"` on dashboard sections.
- **Dynamic form fields**: toggling visibility based on selects (e.g. assignment type, submission format, group toggle).
- **Quiz timer**: client-side countdown from `started_at` + `duration_minutes`, auto-submit on expiry.
- **Inline confirm dialogs**: `@click="if (confirm('…')) { $refs.endForm.submit(); }"` — used for "Akhiri Sesi" in the conference room.

Avoid adding global Alpine stores; if state needs to cross pages, push it server-side.

---

## Reusable Blade components

`resources/views/components/`:

| Component | Purpose |
|---|---|
| `<x-app-layout>` | Authenticated shell (described above) |
| `<x-guest-layout>` | Unauthenticated shell |
| `<x-application-logo />` | SVG logo for the brand mark |
| `<x-assignment-card :assignment="…" />` | Repeated assignment card used in lists |
| `<x-auth-session-status :status="…" />` | Renders `session('status')` for auth flashes |
| `<x-copy-modal :course :siblings :action />` | Alpine modal that POSTs `sibling_ids` to a copy endpoint |
| `<x-danger-button>`, `<x-primary-button>`, `<x-secondary-button>` | Consistent button styling |
| `<x-dropdown>`, `<x-dropdown-link>` | Alpine dropdown shell |
| `<x-input-error :messages="…" />` | Validation error message under a field |
| `<x-input-label :value="…" />` | Form label |
| `<x-modal>` | Generic Alpine modal shell |
| `<x-nav-link>`, `<x-responsive-nav-link>` | Sidebar / mobile navigation links |
| `<x-text-input>` | Text input wrapper with standard Tailwind classes |
| `<x-discussion.*>` | Components specific to the discussion module |

Use these for consistency. Plain `<button>` / `<input>` are fine for one-offs but the variants above wrap the standard `bg-primary text-on-primary` etc.

### `<x-copy-modal>`

```blade
<x-copy-modal
    :course="$course"
    :siblings="$course->siblings()"
    :action="route('dosen.materials.copy', $material)"
/>
```

Renders a checkbox-per-sibling form; the action endpoint applies the security intersect server-side.

### `dosen/partials/sibling-kelas-picker.blade.php`

`@include('dosen.partials.sibling-kelas-picker')` on the dosen Material / Assignment / Conference / Exercise **create** forms. It renders the same sibling checkboxes inline so the create handler can fan out at creation time. Different from `<x-copy-modal>` (which acts on an existing record).

---

## Editors

### CodeMirror (`code-editor.js`)

Used on:
- Dosen exercise create/edit forms (starter code, solution code).
- Mahasiswa exercise solve page.

Targets elements marked with `data-codemirror`, language is read from `data-language` (one of `html`, `css`, `javascript`, `htmlmixed`, `java`, `php`, `csharp`). For server-side languages (`java`, `php`, `csharp`), the Run button posts to `/execute-code`. For HTML/CSS/JS the preview happens in a client-side iframe.

### EasyMDE (`markdown-editor.js`)

Initialised on `textarea[data-markdown-editor]`. Output is the raw Markdown stored in `materials.content` — there is no server-side Markdown→HTML conversion at save time. HTML rendering happens at view time via `markdown-renderer.js`.

### `markdown-renderer.js`

Read-only. Used on `mahasiswa.materials.show`. Reads markdown source from a data attribute and pipes it through `marked` + `highlight.js`.

---

## Charts

`charts.js` initialises Chart.js on canvases with known IDs. Colours are pulled from CSS custom properties so charts stay in sync with the design system:

```js
const styles = getComputedStyle(document.documentElement);
const primary = styles.getPropertyValue('--md-sys-color-primary').trim();
```

Datasets are passed from the controller via JSON-encoded `data-*` attributes (or via inline `<script>` blocks emitting `window.X = @json($payload)`).

Dashboard-specific series produced by controllers:

- **Admin dashboard** (`Admin\DashboardController`): 30-day activity series (`submissions`, `materials`), role distribution donut.
- **Dosen dashboard** (`Dosen\DashboardController`): 7-day submission series, pending-review count.
- **Mahasiswa dashboard** (`Mahasiswa\DashboardController`): 30-day own-submission series, score histogram (`0–50`, `51–70`, `71–85`, `86–100`).

---

## Design system

`resources/css/design-system.css` exposes Material You-style tokens:

```css
:root {
  --md-sys-color-primary: …;
  --md-sys-color-on-primary: …;
  --md-sys-color-surface: …;
  --md-sys-color-on-surface: …;
  --md-sys-color-surface-container-lowest: …;
  /* …shape, typography, motion */
}
```

These map to Tailwind utility colours through `tailwind.config.js` (e.g. `bg-primary`, `text-on-surface-variant`, `bg-surface-container-lowest`). When changing colours, run:

```bash
npm run audit:contrast
```

It executes `scripts/audit-contrast.mjs` to verify WCAG contrast ratios on the palette. CI/PR review uses this check.

Fonts: **Inter** (body, LCP-critical, loaded eagerly), **Manrope** (headings, deferred), **Material Symbols Outlined** (icon font, deferred). All three are pulled from Google Fonts via `<link rel="stylesheet">` in the layout — no self-hosting.

---

## Push notifications (front-end side)

The layout includes an inline service-worker bootstrap:

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

`/sw.js` is the service worker living in `public/`. `vapidPublicKey` is interpolated from `env('VAPID_PUBLIC_KEY')` directly in Blade — if the env var is empty the front-end skips subscription silently.

`POST /push-subscribe` and `POST /push-unsubscribe` go to `PushSubscriptionController`, which calls `User::updatePushSubscription()` / `User::deletePushSubscription()` from the `HasPushSubscriptions` trait.

---

## Where pages live

- Admin: `resources/views/admin/{akademik,announcements,auth,conferences,courses,dashboard,debug,departments,grades,study-programs,student-classes,semesters,users}/…`
- Dosen: `resources/views/dosen/{assignments,conferences,courses,exercises,grades,materials,partials}/…`
- Mahasiswa: `resources/views/mahasiswa/{conferences,courses,exercises,grades,materials,quizzes,schedule,submissions}/…`
- Shared: `resources/views/{announcements,auth,discussions,notifications,profile,errors,layouts,livewire,components,vendor}/…`
- Livewire view: `resources/views/livewire/discussion/show.blade.php`
