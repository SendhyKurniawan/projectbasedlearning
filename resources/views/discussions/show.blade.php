@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
@push('head')
    @livewireStyles
@endpush
@push('scripts')
    @livewireScripts
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Breadcrumb --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-3">
                    <a href="{{ route('discussions.index') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">forum</span> Diskusi
                    </a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('discussions.index', ['topic' => $discussion->topic]) }}" class="hover:text-primary transition-colors">{{ $discussion->topic ?? 'Umum' }}</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary truncate max-w-[200px]">{{ $discussion->title }}</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface leading-tight">{{ $discussion->title }}</h1>
                <div class="flex items-center gap-3 mt-2">
                    @if($discussion->topic)
                        <span class="px-2.5 py-0.5 rounded-md bg-primary/8 text-primary text-[10px] font-bold">#{{ $discussion->topic }}</span>
                    @endif
                    <span class="text-xs text-on-surface-variant">{{ $discussion->created_at->format('d M Y, H:i') }}</span>
                    <span class="text-xs text-outline">·</span>
                    <span class="text-xs text-on-surface-variant">{{ $discussion->comments->count() }} balasan</span>
                </div>
            </div>
            <a href="{{ route('discussions.index', ['topic' => $discussion->topic]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition shrink-0">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Original Post --}}
                <article class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="h-11 w-11 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-sm font-bold shrink-0 ring-2 ring-primary/10">
                                {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-on-surface text-sm">{{ $discussion->user->name }}</span>
                                    @if(($discussion->user->role ?? '') === 'dosen')
                                        <span class="px-2 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-[10px]">verified</span> Dosen
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-on-surface-variant mt-0.5">{{ $discussion->created_at->format('d M Y, H:i') }} · {{ $discussion->created_at->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="prose prose-sm max-w-none text-on-surface leading-relaxed">
                            {!! nl2br(e($discussion->content)) !!}
                        </div>

                        @if(auth()->id() === $discussion->user_id)
                            <div class="mt-6 pt-4 border-t border-outline-variant/10 flex items-center gap-4">
                                <a href="{{ route('discussions.edit', $discussion) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-primary hover:bg-primary/5 text-xs font-bold transition-all">
                                    <span class="material-symbols-outlined text-sm">edit</span> Edit
                                </a>
                                <form action="{{ route('discussions.destroy', $discussion) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-error hover:bg-error/5 text-xs font-bold transition-all" onclick="return confirm('Hapus diskusi ini?')">
                                        <span class="material-symbols-outlined text-sm">delete</span> Hapus
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>

                {{-- Comments --}}
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    <header class="px-6 py-4 border-b border-outline-variant/10 flex items-center justify-between bg-surface-container-low/30">
                        <h3 class="font-headline text-base font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">chat</span>
                            Komentar
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-xs font-bold">{{ $discussion->comments->count() }} balasan</span>
                    </header>

                    @livewire('discussion.show', ['discussion' => $discussion], key($discussion->id))
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-4">
                {{-- Discussion Info --}}
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-sm font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base">info</span>
                        Detail Diskusi
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Topik</span>
                            <span class="px-2 py-0.5 rounded-md bg-primary/8 text-primary text-[10px] font-bold">#{{ $discussion->topic ?? 'Umum' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Dibuat</span>
                            <span class="text-xs font-bold text-on-surface">{{ $discussion->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Balasan</span>
                            <span class="text-xs font-bold text-on-surface">{{ $discussion->comments->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Pembuat</span>
                            <span class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-[8px] font-bold">
                                    {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                                </div>
                                {{ $discussion->user->name }}
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Related Topics --}}
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-sm font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-tertiary text-base">explore</span>
                        Jelajahi Topik
                    </h3>
                    <div class="flex flex-wrap gap-1.5">
                        <a href="{{ route('discussions.index', ['topic' => $discussion->topic]) }}" class="px-3 py-1.5 rounded-lg bg-primary/8 text-primary text-[11px] font-bold hover:bg-primary/15 transition-colors">
                            #{{ $discussion->topic ?? 'Umum' }}
                        </a>
                        <a href="{{ route('discussions.index') }}" class="px-3 py-1.5 rounded-lg bg-surface-container text-on-surface-variant text-[11px] font-bold hover:bg-surface-container-high transition-colors">
                            Semua Diskusi →
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>

</x-app-layout>
