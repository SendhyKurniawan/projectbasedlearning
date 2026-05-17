# Discussions

## Access

The `/discussions` route is shared — all three roles (admin, dosen, mahasiswa) can access it. It's registered under the shared `auth` middleware group, not under a role prefix.

---

## Models

### `Discussion`

| Column | Notes |
|---|---|
| `title` | string |
| `body` | text |
| `topic` | string — categorizes discussion (e.g. `general`, `academic`, or custom values) |
| `user_id` | FK → users — the author |

`DiscussionController` handles CRUD via the standard `discussions` resource route.

### `DiscussionComment`

| Column | Notes |
|---|---|
| `discussion_id` | FK |
| `user_id` | FK → users |
| `body` | text |

---

## The Livewire Component

`app/Livewire/Discussion/Show.php` — the only real Livewire component in the app.

It renders on the discussion show view (`resources/views/livewire/discussion/show.blade.php`) and handles reactive comment addition:

```php
// Livewire\Discussion\Show::addComment()
public function addComment(): void
{
    $this->validate(['body' => 'required|string|max:5000']);

    DiscussionComment::create([
        'discussion_id' => $this->discussion->id,
        'user_id'       => auth()->id(),
        'body'          => $this->body,
    ]);

    $this->body = '';
    $this->loadComments();
}
```

The comment list updates without a full page reload. This is the reason Livewire is used here — Alpine.js alone can't do a server-round-trip-and-re-render without a full form submit.

**Don't add more Livewire components** for other features. If you need interactivity elsewhere, use Alpine for local state or a regular Blade form + redirect. See [contributing.md](../contributing.md).

---

## Sidebar Count Cache

The discussion count shown in the sidebar is cached. On Discussion create, update, or delete, the relevant cache key is flushed so the count stays current. If the sidebar count seems stale, check for a cache flush in `DiscussionController` or the `Discussion` model's `booted()` hook.
