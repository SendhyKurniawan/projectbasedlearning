@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
    @php
        $currentSort = request('sort', 'latest');
    @endphp

    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Forum Diskusi</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Tempat tanya-jawab dan berbagi pengetahuan antar mahasiswa dan dosen.</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('discussions.index') }}" class="flex">
                    @if(request('topic'))
                        <input type="hidden" name="topic" value="{{ request('topic') }}">
                    @endif
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-base">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari topik..." class="pl-9 pr-3 py-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-lowest text-sm w-56 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary/30 transition-all">
                    </div>
                </form>
                <a href="{{ route('discussions.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm hover:shadow-md hover:bg-primary-hover transition-all duration-200">
                    <span class="material-symbols-outlined text-base">add</span> Buat Topik Baru
                </a>
            </div>
        </div>

        {{-- Stats Banner --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Total Diskusi', $totalCount, 'forum', 'primary'],
                ['Topik Aktif', $allTopics->count(), 'tag', 'tertiary'],
                ['Kontributor', $topContributors->count(), 'group', 'secondary'],
                ['Diskusi Terbaru', $discussions->count(), 'schedule', 'primary'],
            ] as [$label, $value, $icon, $accent])
                <div class="bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/10 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-{{ $accent }}"></div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-{{ $accent }}/10 text-{{ $accent }} flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">{{ $label }}</p>
                            <p class="font-headline text-2xl font-extrabold text-on-surface leading-none mt-0.5">{{ $value }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Channels --}}
            <aside class="lg:col-span-3 space-y-4">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-4">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant px-3 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs">tag</span> Channel
                    </h3>
                    <div class="space-y-0.5">
                        <a href="{{ route('discussions.index', array_filter(['search' => request('search'), 'sort' => request('sort')])) }}"
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ !request('topic') ? 'bg-primary text-on-primary font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                            <span class="text-xs flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">forum</span> Semua Diskusi
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ !request('topic') ? 'bg-on-primary/20' : 'bg-surface-container' }}">{{ $totalCount }}</span>
                        </a>
                        @foreach($allTopics as $t)
                            <a href="{{ route('discussions.index', array_filter(['topic' => $t->topic, 'search' => request('search'), 'sort' => request('sort')])) }}"
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ request('topic') === $t->topic ? 'bg-primary text-on-primary font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                                <span class="text-xs flex items-center gap-2">
                                    <span class="text-on-surface-variant/60">#</span> {{ Str::limit($t->topic, 20) }}
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full {{ request('topic') === $t->topic ? 'bg-on-primary/20' : 'bg-surface-container' }}">{{ $t->count }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                {{-- Forum Rules --}}
                <section class="bg-tertiary-fixed/40 rounded-2xl border border-tertiary/20 p-5">
                    <h3 class="font-headline text-sm font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-tertiary text-base">gavel</span> Aturan Forum
                    </h3>
                    <ul class="space-y-2">
                        @foreach([
                            'Hormati sesama pengguna',
                            'Gunakan tag/topik yang tepat',
                            'Tandai pertanyaan terjawab',
                            'Hindari spam atau promosi',
                        ] as $rule)
                            <li class="flex items-start gap-2 text-xs text-on-surface-variant">
                                <span class="material-symbols-outlined text-tertiary text-xs mt-0.5 shrink-0">check_circle</span>
                                {{ $rule }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>

            {{-- CENTER: List --}}
            <main class="lg:col-span-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    {{-- Sort Tabs --}}
                    <div class="flex items-center gap-1 px-4 py-3 border-b border-outline-variant/10 bg-surface-container-low/30">
                        @foreach([
                            ['latest', '🕐 Terbaru', 'schedule'],
                            ['popular', '🔥 Populer', 'whatshot'],
                        ] as [$sortKey, $sortLabel, $sortIcon])
                            <a href="{{ route('discussions.index', array_filter(['sort' => $sortKey, 'topic' => request('topic'), 'search' => request('search')])) }}"
                               class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 {{ $currentSort === $sortKey ? 'bg-primary/10 text-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                                {{ $sortLabel }}
                            </a>
                        @endforeach

                        @if(request('search'))
                            <div class="ml-auto flex items-center gap-1.5">
                                <span class="text-[10px] text-on-surface-variant">Hasil untuk:</span>
                                <span class="px-2 py-0.5 rounded-md bg-primary/10 text-primary text-[10px] font-bold">{{ request('search') }}</span>
                                <a href="{{ route('discussions.index', array_filter(['topic' => request('topic'), 'sort' => request('sort')])) }}" class="text-on-surface-variant hover:text-error transition-colors">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </a>
                            </div>
                        @endif

                        @if(request('topic'))
                            <div class="ml-auto flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold">#{{ request('topic') }}</span>
                                <a href="{{ route('discussions.index', array_filter(['search' => request('search'), 'sort' => request('sort')])) }}" class="text-on-surface-variant hover:text-error transition-colors">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Discussion List --}}
                    <div class="divide-y divide-outline-variant/10">
                        @forelse($discussions as $d)
                            <a href="{{ route('discussions.show', $d) }}" class="flex items-start gap-3.5 p-5 hover:bg-surface-bright/60 transition-all duration-200 group">
                                {{-- Avatar --}}
                                <div class="w-10 h-10 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-xs font-bold shrink-0 ring-2 ring-primary/10 group-hover:ring-primary/30 transition-all">
                                    {{ strtoupper(substr($d->user->name, 0, 2)) }}
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Tags Row --}}
                                    <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                        @if($d->topic)
                                            <span class="px-2 py-0.5 rounded-md bg-primary/8 text-primary text-[10px] font-bold">#{{ Str::limit($d->topic, 16) }}</span>
                                        @endif
                                        @if(($d->user->role ?? '') === 'dosen')
                                            <span class="px-2 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-[10px]">verified</span> Dosen
                                            </span>
                                        @endif
                                        @if($d->created_at->diffInHours(now()) < 24)
                                            <span class="px-2 py-0.5 rounded-md bg-secondary/10 text-secondary text-[10px] font-bold">Baru</span>
                                        @endif
                                    </div>

                                    {{-- Title --}}
                                    <h3 class="text-sm font-bold text-on-surface line-clamp-1 group-hover:text-primary transition-colors">{{ $d->title }}</h3>

                                    {{-- Preview --}}
                                    <p class="text-[11px] text-on-surface-variant mt-1 line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($d->content), 120) }}</p>

                                    {{-- Meta --}}
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-[10px] text-on-surface-variant font-medium">{{ $d->user->name }}</span>
                                        <span class="text-[10px] text-outline">·</span>
                                        <span class="text-[10px] text-on-surface-variant">{{ $d->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                {{-- Reply count --}}
                                <div class="flex flex-col items-center gap-0.5 text-on-surface-variant shrink-0 pt-1">
                                    <div class="w-9 h-9 rounded-xl bg-surface-container flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-all">
                                        <span class="material-symbols-outlined text-base">chat_bubble</span>
                                    </div>
                                    <span class="text-[10px] font-bold">{{ $d->comments_count }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="p-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-surface-container mx-auto flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-4xl text-outline">forum</span>
                                </div>
                                <h3 class="font-headline text-lg font-bold text-on-surface mb-1">Belum ada diskusi</h3>
                                <p class="text-sm text-on-surface-variant mb-4">Jadilah yang pertama memulai percakapan.</p>
                                <a href="{{ route('discussions.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm hover:shadow-md transition-all">
                                    <span class="material-symbols-outlined text-base">add</span> Buat Diskusi Pertama
                                </a>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($discussions->hasPages())
                        <div class="p-4 border-t border-outline-variant/10 bg-surface-container-low/20">
                            {{ $discussions->links() }}
                        </div>
                    @endif
                </section>
            </main>

            {{-- RIGHT: Sidebar --}}
            <aside class="lg:col-span-3 space-y-4">
                {{-- Top Contributors --}}
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-base font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base">leaderboard</span>
                        Top Kontributor
                    </h3>
                    <div class="space-y-3">
                        @forelse($topContributors as $contributor)
                            <div class="flex items-center gap-3 group">
                                {{-- Rank Badge --}}
                                @if($loop->iteration <= 3)
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0
                                        {{ $loop->iteration === 1 ? 'bg-warning/20 text-warning' : ($loop->iteration === 2 ? 'bg-on-surface-variant/10 text-on-surface-variant' : 'bg-tertiary/10 text-tertiary') }}">
                                        {{ $loop->iteration === 1 ? '🥇' : ($loop->iteration === 2 ? '🥈' : '🥉') }}
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-full bg-surface-container flex items-center justify-center text-[10px] font-bold text-on-surface-variant shrink-0">
                                        {{ $loop->iteration }}
                                    </div>
                                @endif

                                {{-- User Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate">{{ $contributor->name }}</p>
                                    <p class="text-[10px] text-on-surface-variant">{{ $contributor->discussions_count }} diskusi</p>
                                </div>

                                {{-- Avatar --}}
                                <div class="w-7 h-7 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($contributor->name, 0, 2)) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant text-center py-2">Belum ada kontributor.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Quick Create --}}
                <section class="bg-primary/5 rounded-2xl border border-primary/15 p-5">
                    <h3 class="font-headline text-sm font-bold mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base">edit_note</span>
                        Punya pertanyaan?
                    </h3>
                    <p class="text-xs text-on-surface-variant mb-3">Jangan ragu untuk bertanya. Komunitas siap membantu!</p>
                    <a href="{{ route('discussions.create') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold shadow-sm hover:shadow-md transition-all duration-200">
                        <span class="material-symbols-outlined text-sm">add</span> Mulai Diskusi
                    </a>
                </section>
            </aside>
        </div>
    </div>

</x-app-layout>
