<div>
    <div class="divide-y divide-outline-variant/10">
        @forelse($comments as $comment)
            <div class="px-6 py-5 flex items-start gap-3" wire:key="comment-{{ $comment->id }}">
                <div class="h-9 w-9 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-xs font-bold shrink-0 ring-2 ring-primary/5">
                    {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-on-surface">{{ $comment->user->name }}</span>
                            @if(($comment->user->role ?? '') === 'dosen')
                                <span class="px-1.5 py-0.5 rounded bg-tertiary/10 text-tertiary text-[9px] font-bold">Dosen</span>
                            @endif
                            @if($comment->user_id === $discussion->user_id)
                                <span class="px-1.5 py-0.5 rounded bg-primary/10 text-primary text-[9px] font-bold">OP</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-on-surface-variant shrink-0">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-sm text-on-surface leading-relaxed">
                        {!! nl2br(e($comment->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-surface-container mx-auto flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-3xl text-outline">chat_bubble_outline</span>
                </div>
                <p class="text-sm font-bold text-on-surface mb-1">Belum ada komentar</p>
                <p class="text-xs text-on-surface-variant">Jadilah yang pertama memberikan tanggapan.</p>
            </div>
        @endforelse
    </div>

    <div class="px-6 py-5 bg-surface-container-low/40 border-t border-outline-variant/10">
        <form wire:submit.prevent="addComment" class="mt-0">
            <div>
                <x-input-label for="newComment" :value="__('Tambahkan Komentar')" />
                <textarea wire:model="newComment" id="newComment" rows="3" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm"></textarea>
                @error('newComment') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Kirim Komentar') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
