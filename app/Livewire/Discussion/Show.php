<?php

namespace App\Livewire\Discussion;

use App\Models\Discussion;
use App\Models\DiscussionComment;
use Livewire\Component;

class Show extends Component
{
    public $discussion;
    public $newComment;

    protected $rules = [
        'newComment' => 'required|string|max:1000',
    ];

    public function mount(Discussion $discussion)
    {
        $this->discussion = $discussion;
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
        
        // Refresh the discussion to show new comment? 
        // Or just emit event. simpler to just refresh the page or component.
        // But the view uses $discussion->comments loop which is not reactive here unless we fetch comments separately.
        // Let's reload.
        return redirect()->route('discussions.show', $this->discussion);
    }

    public function render()
    {
        return view('livewire.discussion.show');
    }
}
