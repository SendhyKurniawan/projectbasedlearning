@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
    @php
        $topics = $discussions->pluck('topic')->filter()->unique()->take(8);
    @endphp

    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Forum Diskusi</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Tempat tanya-jawab antara mahasiswa dan dosen.</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('discussions.index') }}" class="flex">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-base">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari topik..." class="pl-9 pr-3 py-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-lowest text-sm w-56 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </form>
                <a href="{{ route('discussions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                    <span class="material-symbols-outlined text-base">add</span> Buat Topik Baru
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Channels --}}
            <aside class="lg:col-span-3 space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-4">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant px-3 mb-2">Channel</h3>
                    <div class="space-y-0.5">
                        <a href="{{ route('discussions.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg {{ !request('topic') ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                            <span class="text-xs"># Semua</span>
                            <span class="text-[10px]">{{ $discussions->total() }}</span>
                        </a>
                        @foreach($topics as $t)
                            <a href="{{ route('discussions.index', ['topic' => $t]) }}" class="flex items-center justify-between px-3 py-2 rounded-lg {{ request('topic') === $t ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                                <span class="text-xs"># {{ Str::limit($t, 18) }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            </aside>

            {{-- CENTER: List --}}
            <main class="lg:col-span-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    <div class="flex gap-2 px-4 py-3 border-b border-outline-variant/10">
                        <span class="px-3 py-1 rounded-lg bg-primary/10 text-primary text-xs font-bold">🔥 Hot</span>
                        <span class="px-3 py-1 rounded-lg text-on-surface-variant text-xs font-bold">🆕 Baru</span>
                        <span class="px-3 py-1 rounded-lg text-on-surface-variant text-xs font-bold">📌 Pinned</span>
                    </div>
                    <div class="divide-y divide-outline-variant/10">
                        @forelse($discussions as $d)
                            <a href="{{ route('discussions.show', $d) }}" class="flex items-start gap-3 p-4 hover:bg-surface-bright transition-colors">
                                <div class="w-10 h-10 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($d->user->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        @if($d->topic)
                                            <span class="px-2 py-0.5 rounded-md bg-surface-container text-[10px] font-bold">#{{ Str::limit($d->topic, 14) }}</span>
                                        @endif
                                        @if(($d->user->role ?? '') === 'dosen')
                                            <span class="px-2 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold">📌 Dosen</span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-bold text-on-surface line-clamp-1">{{ $d->title }}</h3>
                                    <p class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-1">{{ Str::limit(strip_tags($d->content), 100) }}</p>
                                    <p class="text-[10px] text-on-surface-variant mt-1">{{ $d->user->name }} · {{ $d->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1 text-[10px] text-on-surface-variant">
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">chat_bubble</span>{{ $d->comments_count }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="p-12 text-center">
                                <span class="material-symbols-outlined text-5xl text-outline mb-3">forum</span>
                                <p class="text-sm text-on-surface-variant">Belum ada diskusi.</p>
                            </div>
                        @endforelse
                    </div>
                    @if($discussions->hasPages())
                        <div class="p-4 border-t border-outline-variant/10">{{ $discussions->links() }}</div>
                    @endif
                </section>
            </main>

            {{-- RIGHT: Sidebar --}}
            <aside class="lg:col-span-3 space-y-4">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-base font-bold mb-3">Top Kontributor</h3>
                    <div class="space-y-2">
                        @foreach($discussions->take(3) as $d)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center text-[10px] font-bold">{{ $loop->iteration }}</div>
                                <span class="text-xs flex-1 truncate">{{ $d->user->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
                <section class="bg-tertiary-fixed/40 rounded-2xl border border-tertiary/20 p-5">
                    <h3 class="font-headline text-sm font-bold mb-2">📝 Aturan Forum</h3>
                    <p class="text-xs text-on-surface-variant">Hormati sesama, gunakan tag yang tepat, dan tandai pertanyaan terjawab.</p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
