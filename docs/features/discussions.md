# Discussions

## Access

`/discussions` is shared across all three roles. Registered as a resource route under the auth-only middleware group (no role prefix):

```php
Route::middleware('auth')->group(function () {
    Route::resource('discussions', App\Http\Controllers\DiscussionController::class);
    // ...
});
```

So admin, dosen, and mahasiswa share the same discussion forum.

---

## Models

### `Discussion` (`app/Models/Discussion.php`, table `discussions`)

| Column | Notes |
|---|---|
| `id` | PK |
| `user_id` | FK users cascade — the author |
| `topic` | string — free-text category (e.g. `general`, `akademik`, `tugas`); aggregated for the sidebar popular-topics list |
| `title` | string |
| `content` | text |
| timestamps | |

> Originally there was a `course_id` FK; migration `2026_03_12_065022_alter_discussions_table_replace_course_with_topic.php` dropped it in favour of the free-text `topic` column.

Relations: `user()` belongsTo `User`, `comments()` hasMany `DiscussionComment`.

### `DiscussionComment` (`app/Models/DiscussionComment.php`, table `discussion_comments`)

| Column | Notes |
|---|---|
| `id` | PK |
| `discussion_id` | FK discussions cascade |
| `user_id` | FK users cascade |
| `content` | text — **the field name is `content`, not `body`** |
| timestamps | |

Relations: `discussion()`, `user()`.

---

## Routes (`DiscussionController`)

| Method + URL | Action |
|---|---|
| `GET /discussions` | `index` — list with search, topic filter, sort (`latest`/`popular`), pagination |
| `GET /discussions/create` | `create` — accepts optional `?topic=` to prefill |
| `POST /discussions` | `store` — validates `topic`, `title`, `content`; sets `user_id = auth()->id()` |
| `GET /discussions/{discussion}` | `show` — eager-loads `user` + `comments.user`, renders `discussions.show` which embeds the Livewire component |
| `GET /discussions/{discussion}/edit` | `edit` — authorize `update` |
| `PUT /discussions/{discussion}` | `update` — `title`, `content` (topic isn't editable post-create) |
| `DELETE /discussions/{discussion}` | `destroy` — authorize `delete` |

Authorization for `edit`/`update`/`destroy` uses Laravel's policy auto-discovery (`$this->authorize('update'|'delete', $discussion)`). The default rule should restrict to the author + admin; if you need a `DiscussionPolicy`, add one — none is committed yet, so currently any auth user can edit/delete (verify before assuming).

---

## Sidebar cache

The discussion-index sidebar (popular topics, total count, top contributors) is cached for 10 minutes:

```php
$sidebar = Cache::remember('discussions:index:sidebar', 600, function () {
    return [
        'allTopics' => Discussion::selectRaw('topic, COUNT(*) as count')
            ->whereNotNull('topic')->where('topic', '!=', '')
            ->groupBy('topic')->orderByDesc('count')->limit(12)->get(),
        'totalCount' => Discussion::count(),
        'topContributors' => User::select('users.*')
            ->selectRaw('COUNT(discussions.id) as discussions_count')
            ->join('discussions', 'users.id', '=', 'discussions.user_id')
            ->groupBy('users.id')->orderByDesc('discussions_count')->limit(5)->get(),
    ];
});
```

The `Discussion::booted()` hook invalidates the key on every create/update/delete:

```php
protected static function booted(): void
{
    $flush = fn () => Cache::forget('discussions:index:sidebar');
    static::created($flush);
    static::updated($flush);
    static::deleted($flush);
}
```

If the sidebar counts are stale after a discussion change, the model hook should have fired — check the cache driver.

---

## Index filtering / sorting

`GET /discussions` accepts:

- `search` — matches `title` OR `content` with `LIKE %…%`
- `topic` — exact match on `topic`
- `sort` — `latest` (default) or `popular`. `popular` reorders by `comments_count` descending.

Pagination: 10 per page, `withQueryString()` so filters survive page links.

---

## Livewire component — the lone exception

`app/Livewire/Discussion/Show.php` is the **only** Livewire component in the app. It exists to handle comment submission with a reactive comment-list re-render (without a full page reload).

```php
class Show extends Component
{
    public Discussion $discussion;
    public Collection $comments;
    public string $newComment = '';

    protected $rules = [
        'newComment' => 'required|string|max:1000',
    ];

    public function mount(Discussion $discussion)
    {
        $this->discussion = $discussion;
        $this->loadComments();
    }

    public function addComment()
    {
        $this->validate();

        DiscussionComment::create([
            'discussion_id' => $this->discussion->id,
            'user_id' => auth()->id(),
            'content' => $this->newComment,           // ← writes to `content`, not `body`
        ]);

        $this->newComment = '';
        $this->loadComments();
    }

    protected function loadComments(): void
    {
        $this->comments = $this->discussion->comments()->with('user')->oldest()->get();
    }

    public function render()
    {
        return view('livewire.discussion.show');
    }
}
```

The view (`resources/views/livewire/discussion/show.blade.php`) renders:

- The thread starter (title + content + author + timestamps).
- The reactive `$comments` list.
- A `wire:model="newComment"` textarea and a "Kirim" button that calls `wire:click="addComment"`.

It is embedded inside `resources/views/discussions/show.blade.php` via `<livewire:discussion.show :discussion="$discussion" />`. The parent Blade view shouldn't render `$discussion->comments` itself — the Livewire component owns the comment list.

### Constraints

- **Don't add more Livewire components** for other features. If you need interactivity, use Alpine. If you need a server round-trip with redirect, use a regular Blade form. Only add a Livewire component when reactive server state is genuinely required.
- **Livewire 4 redirect-to-same-URL quirk**: redirecting to the same URL within a component doesn't refresh Blade state outside the component. Use `loadX()` for in-component refresh, or `redirect(..., navigate: false)` for a hard reload.
- **Playwright tip**: `wire:model` does not commit when `.fill()` is used because it short-circuits the input event sequence. Use slow typing — `page.locator(...).pressSequentially(text, { delay: 30 })`.

---

## Adding policies

If you want to lock discussion editing/deleting to the author + admin (current behaviour relies on Laravel's auto-discovery, which without a `DiscussionPolicy` is permissive), add:

```php
// app/Policies/DiscussionPolicy.php
class DiscussionPolicy
{
    public function update(User $user, Discussion $discussion): bool
    {
        return $user->isAdmin() || $user->id === $discussion->user_id;
    }

    public function delete(User $user, Discussion $discussion): bool
    {
        return $user->isAdmin() || $user->id === $discussion->user_id;
    }
}
```

Laravel auto-discovers it via the `Model` → `ModelPolicy` naming.
