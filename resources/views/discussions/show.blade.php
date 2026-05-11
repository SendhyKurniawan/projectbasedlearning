@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('discussions.index') }}" class="hover:text-primary transition-colors">Diskusi</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('discussions.index', ['topic' => $discussion->topic]) }}" class="hover:text-primary transition-colors">{{ $discussion->topic ?? 'Umum' }}</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary truncate max-w-[180px]">{{ $discussion->title }}</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">{{ $discussion->title }}</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Topik: <span class="font-semibold text-primary">{{ $discussion->topic ?? 'Umum' }}</span></p>
            </div>
            <a href="{{ route('discussions.index', ['topic' => $discussion->topic]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition shrink-0">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
        </div>

        {{-- Original Post --}}
        <article class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-on-surface text-sm truncate">{{ $discussion->user->name }}</div>
                        <div class="text-xs text-on-surface-variant">{{ $discussion->created_at->format('d M Y H:i') }}</div>
                    </div>
                    @if(($discussion->user->role ?? '') === 'dosen')
                        <span class="ml-auto px-2 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold">📌 Dosen</span>
                    @endif
                </div>

                <div class="prose prose-sm max-w-none text-on-surface leading-relaxed">
                    {!! nl2br(e($discussion->content)) !!}
                </div>

                @if(auth()->id() === $discussion->user_id)
                    <div class="mt-5 pt-4 border-t border-outline-variant/10 flex items-center gap-4">
                        <a href="{{ route('discussions.edit', $discussion) }}" class="inline-flex items-center gap-1 text-primary hover:text-primary/80 text-sm font-semibold">
                            <span class="material-symbols-outlined text-base">edit</span> Edit
                        </a>
                        <form action="{{ route('discussions.destroy', $discussion) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 text-error hover:text-error/80 text-sm font-semibold" onclick="return confirm('Hapus diskusi ini?')">
                                <span class="material-symbols-outlined text-base">delete</span> Hapus
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </article>

        {{-- Comments --}}
        <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
            <header class="px-6 py-4 border-b border-outline-variant/10 flex items-center justify-between">
                <h3 class="font-headline text-base font-bold text-on-surface">Komentar</h3>
                <span class="text-xs text-on-surface-variant">{{ $discussion->comments->count() }} balasan</span>
            </header>

            <div class="divide-y divide-outline-variant/10">
                @forelse($discussion->comments as $comment)
                    <div class="px-6 py-4 flex items-start gap-3">
                        <div class="h-8 w-8 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="font-semibold text-sm text-on-surface truncate">{{ $comment->user->name }}</div>
                                <div class="text-[11px] text-on-surface-variant shrink-0">{{ $comment->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="text-sm text-on-surface leading-relaxed">
                                {!! nl2br(e($comment->content)) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">chat_bubble_outline</span>
                        <p class="text-sm text-on-surface-variant">Belum ada komentar. Jadilah yang pertama.</p>
                    </div>
                @endforelse
            </div>

            {{-- Comment Form --}}
            <div class="px-6 py-5 bg-surface-container-low/40 border-t border-outline-variant/10">
                @livewire('discussion.show', ['discussion' => $discussion], key($discussion->id))
            </div>
        </section>
    </div>
</x-app-layout>
