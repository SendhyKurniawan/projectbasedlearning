<?php

namespace App\Livewire\Discussion;

use App\Models\Discussion;
use App\Models\DiscussionComment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

// Satu-satunya komponen Livewire di aplikasi: menampilkan & mengelola daftar komentar
// sebuah topik diskusi, sekaligus form tambah komentar (render mandiri, tanpa reload halaman).
class Show extends Component
{
    public Discussion $discussion;
    public Collection $comments;
    public string $newComment = '';

    // Aturan validasi untuk komentar baru.
    protected $rules = [
        'newComment' => 'required|string|max:1000',
    ];

    // Inisialisasi komponen dengan topik diskusi terkait, lalu muat komentarnya.
    public function mount(Discussion $discussion)
    {
        $this->discussion = $discussion;
        $this->loadComments();
    }

    // Validasi & simpan komentar baru, kosongkan input, lalu muat ulang daftar komentar.
    public function addComment()
    {
        $this->validate();

        DiscussionComment::create([
            'discussion_id' => $this->discussion->id,
            'user_id' => auth()->id(),
            'content' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->loadComments();
    }

    // Ambil komentar (beserta penulisnya) urut dari terlama ke terbaru.
    protected function loadComments(): void
    {
        $this->comments = $this->discussion->comments()->with('user')->oldest()->get();
    }

    public function render()
    {
        return view('livewire.discussion.show');
    }
}
