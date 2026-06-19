# Diskusi (Discussions)

## Akses

`/discussions` dibagikan ke ketiga role. Didaftarkan sebagai route resource di bawah grup middleware auth-only (tanpa prefiks role):

```php
Route::middleware('auth')->group(function () {
    Route::resource('discussions', App\Http\Controllers\DiscussionController::class);
    // ...
});
```

Jadi admin, dosen, dan mahasiswa berbagi forum diskusi yang sama.

---

## Model

### `Discussion` (`app/Models/Discussion.php`, tabel `discussions`)

| Kolom | Catatan |
|---|---|
| `id` | PK |
| `user_id` | FK users cascade — penulis |
| `topic` | string — kategori teks-bebas (mis. `general`, `akademik`, `tugas`); diagregasi untuk daftar topik-populer di sidebar |
| `title` | string |
| `content` | text |
| timestamps | |

> Semula ada FK `course_id`; migrasi `2026_03_12_065022_alter_discussions_table_replace_course_with_topic.php` menghapusnya demi kolom teks-bebas `topic`.

Relasi: `user()` belongsTo `User`, `comments()` hasMany `DiscussionComment`.

### `DiscussionComment` (`app/Models/DiscussionComment.php`, tabel `discussion_comments`)

| Kolom | Catatan |
|---|---|
| `id` | PK |
| `discussion_id` | FK discussions cascade |
| `user_id` | FK users cascade |
| `content` | text — **nama field adalah `content`, bukan `body`** |
| timestamps | |

Relasi: `discussion()`, `user()`.

---

## Route (`DiscussionController`)

| Method + URL | Aksi |
|---|---|
| `GET /discussions` | `index` — daftar dengan pencarian, filter topik, sortir (`latest`/`popular`), paginasi |
| `GET /discussions/create` | `create` — menerima `?topic=` opsional untuk prefill |
| `POST /discussions` | `store` — memvalidasi `topic`, `title`, `content`; menyetel `user_id = auth()->id()` |
| `GET /discussions/{discussion}` | `show` — eager-load `user` + `comments.user`, render `discussions.show` yang menyematkan komponen Livewire |
| `GET /discussions/{discussion}/edit` | `edit` — authorize `update` |
| `PUT /discussions/{discussion}` | `update` — `title`, `content` (topic tak bisa diedit pasca-create) |
| `DELETE /discussions/{discussion}` | `destroy` — authorize `delete` |

Otorisasi untuk `edit`/`update`/`destroy` memakai auto-discovery policy Laravel (`$this->authorize('update'|'delete', $discussion)`). Aturan default seharusnya membatasi ke penulis + admin; bila Anda perlu `DiscussionPolicy`, tambahkan satu — belum ada yang di-commit, jadi saat ini user auth mana pun bisa edit/delete (verifikasi sebelum berasumsi).

---

## Cache sidebar

Sidebar index diskusi (topik populer, total hitungan, kontributor teratas) di-cache selama 10 menit:

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

Hook `Discussion::booted()` membatalkan kunci pada tiap create/update/delete:

```php
protected static function booted(): void
{
    $flush = fn () => Cache::forget('discussions:index:sidebar');
    static::created($flush);
    static::updated($flush);
    static::deleted($flush);
}
```

Bila hitungan sidebar basi setelah perubahan diskusi, hook model seharusnya terpicu — cek driver cache.

---

## Filter / sortir index

`GET /discussions` menerima:

- `search` — mencocokkan `title` ATAU `content` dengan `LIKE %…%`
- `topic` — kecocokan persis pada `topic`
- `sort` — `latest` (default) atau `popular`. `popular` mengurutkan ulang berdasarkan `comments_count` menurun.

Paginasi: 10 per halaman, `withQueryString()` agar filter bertahan di tautan halaman.

---

## Komponen Livewire — satu-satunya pengecualian

`app/Livewire/Discussion/Show.php` adalah **satu-satunya** komponen Livewire di aplikasi. Ia ada untuk menangani pengiriman komentar dengan render ulang daftar-komentar reaktif (tanpa reload halaman penuh).

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
            'content' => $this->newComment,           // ← menulis ke `content`, bukan `body`
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

View (`resources/views/livewire/discussion/show.blade.php`) merender:

- Pembuka thread (judul + konten + penulis + timestamps).
- Daftar `$comments` yang reaktif.
- Textarea `wire:model="newComment"` dan tombol "Kirim" yang memanggil `wire:click="addComment"`.

Ia disematkan di dalam `resources/views/discussions/show.blade.php` via `<livewire:discussion.show :discussion="$discussion" />`. View Blade induk tidak boleh merender `$discussion->comments` sendiri — komponen Livewire memiliki daftar komentar.

### Batasan

- **Jangan menambah komponen Livewire lagi** untuk fitur lain. Bila perlu interaktivitas, pakai Alpine. Bila perlu round-trip server dengan redirect, pakai form Blade biasa. Tambahkan komponen Livewire hanya saat state server reaktif benar-benar diperlukan.
- **Kuirk Livewire 4 redirect-ke-URL-sama**: redirect ke URL yang sama dalam komponen tidak menyegarkan state Blade di luar komponen. Pakai `loadX()` untuk refresh dalam-komponen, atau `redirect(..., navigate: false)` untuk reload keras.
- **Tip Playwright**: `wire:model` tidak commit saat `.fill()` dipakai karena ia melompati urutan event input. Pakai pengetikan lambat — `page.locator(...).pressSequentially(text, { delay: 30 })`.

---

## Menambah policy

Bila Anda ingin mengunci edit/hapus diskusi ke penulis + admin (perilaku saat ini mengandalkan auto-discovery Laravel, yang tanpa `DiscussionPolicy` bersifat permisif), tambahkan:

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

Laravel otomatis menemukannya via penamaan `Model` → `ModelPolicy`.
