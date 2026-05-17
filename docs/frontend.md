# Frontend

## Vite Entry Points

Defined in `vite.config.js`:

| File | Purpose |
|---|---|
| `resources/css/app.css` | Tailwind base + global utility overrides |
| `resources/css/design-system.css` | MD3-token design system variables (colors, spacing, radii) |
| `resources/css/pages/**/*.css` | Per-page scoped styles, auto-glob'd |
| `resources/js/app.js` | Alpine.js init, global components, notification badge |
| `resources/js/code-editor.js` | CodeMirror 6 — code input for dosen exercise create/edit and mahasiswa solve |
| `resources/js/markdown-editor.js` | EasyMDE — markdown input for dosen material create/edit |
| `resources/js/markdown-renderer.js` | `marked` + `highlight.js` — read-only render for student material show view |
| `resources/js/conference-jitsi.js` | Jitsi Meet External API embed — used in conference room views |
| `resources/js/charts.js` | Chart.js with MD3 color tokens — used on dashboard views |

`@vite(['resources/css/app.css', 'resources/js/app.js'])` in the layout loads app-wide bundles. Feature-specific bundles are `@vite`'d only on the views that need them.

---

## Layout Components

### `<x-app-layout>`

`resources/views/layouts/app.blade.php` — authenticated shell with sidebar. Slots:

```blade
<x-app-layout>
    <x-slot name="header">Page Title</x-slot>
    
    {{-- main content --}}
</x-app-layout>
```

The sidebar renders via `SidebarComposer` (view composer registered in `AppServiceProvider`), which resolves the current user's role and injects the correct nav links and course list.

### `<x-guest-layout>`

`resources/views/layouts/guest.blade.php` — unauthenticated shell for login pages.

---

## Alpine.js Patterns

Alpine is initialized in `app.js` and sprinkled onto elements via `x-data`, `x-show`, `x-on`. It handles:

- **Modals**: `x-show="open"` with `@click.outside="open = false"` — used for confirm dialogs and copy modals
- **Tab switching**: `x-data="{ tab: 'info' }"` on dashboard sections
- **Dynamic form fields**: toggling visibility based on assignment type or submission format selects
- **Notification badge**: reactive unread count in the nav bar

There's no global Alpine store. Component state is scoped to each `x-data` block.

---

## Reusable Blade Components

### `<x-copy-modal>`

`resources/views/components/copy-modal.blade.php` — Alpine modal that renders checkboxes for sibling kelas and POSTs to the copy endpoint.

```blade
<x-copy-modal
    :course="$course"
    :siblings="$course->siblings()"
    :action="route('dosen.materials.copy', $material)"
/>
```

The modal handles its own Alpine state (`x-data="{ open: false }"`). The form submission goes to `material/assignment/conference.copy` which applies the sibling security intersect server-side.

### `<x-sibling-kelas-picker>`

`resources/views/dosen/partials/sibling-kelas-picker.blade.php` — included on dosen create forms (Material, Assignment, Conference) to show checkboxes for fan-out to sibling kelas at create time. Different from `<x-copy-modal>` which is for existing records.

### Form Inputs

`resources/views/components/` contains: `text-input`, `textarea`, `select`, `checkbox`, `input-error`, `primary-button`, `secondary-button`, `danger-button`. Use these for consistent styling rather than raw HTML.

---

## Editors

### CodeMirror (`code-editor.js`)

Used on: dosen exercise create/edit (starter code, solution code), mahasiswa exercise solve page. Initialized by targeting elements with `data-codemirror` attribute. Language is set via `data-language` — supported values come from the exercise's `exercise_config.language`.

### EasyMDE (`markdown-editor.js`)

Used on: dosen material create/edit. Targets `textarea[data-markdown-editor]`. Produces Markdown which is stored in `materials.content` as HTML (converted server-side via `str()->markdown()` or equivalent — confirm in `MaterialController::store`).

### `markdown-renderer.js`

Read-only. Used on: mahasiswa material show view. Reads `data-markdown` attribute from the element, runs it through `marked` + `highlight.js` for syntax highlighting. This is client-side rendering of stored content.

---

## Design System

`resources/css/design-system.css` defines CSS custom properties (MD3 tokens):

```css
--md-sys-color-primary: ...
--md-sys-color-surface: ...
--md-sys-typescale-body-large-size: ...
```

`charts.js` reads these tokens via `getComputedStyle(document.documentElement)` when building Chart.js configs — keeps chart colors in sync with the design system without hardcoding hex values.

Run `npm run audit:contrast` to check WCAG contrast ratios on the design token palette. Run it when changing any color token in `design-system.css`.
