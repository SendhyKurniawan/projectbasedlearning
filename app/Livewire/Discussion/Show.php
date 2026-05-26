<?php

namespace App\Livewire\Discussion;

use App\Models\Discussion;
use App\Models\DiscussionComment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

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
            'content' => $this->newComment,
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
