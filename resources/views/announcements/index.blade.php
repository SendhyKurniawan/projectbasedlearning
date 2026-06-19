{{-- Halaman daftar pengumuman. --}}
@push('styles')
    @vite('resources/css/pages/shared/announcements.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Pengumuman</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Informasi penting dari admin pusat dan dosen.</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('announcements.index') }}" class="flex gap-2">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-base">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengumuman..." class="pl-9 pr-3 py-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-lowest text-sm w-56 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </form>
                @if(in_array(Auth::user()->role, ['admin', 'dosen']))
                    <a href="{{ route('announcements.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                        <span class="material-symbols-outlined text-base">add</span> Buat Pengumuman
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="px-5 py-3 bg-secondary-container/30 border-l-4 border-secondary text-secondary rounded-xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Announcement list --}}
            <main class="lg:col-span-8 space-y-4">
                @forelse ($announcements as $a)
                    @php
                        $isPinned = false;
                        $accent = match($a->target_audience ?? '') {
                            'all' => 'primary',
                            'dosen' => 'secondary',
                            'mahasiswa' => 'tertiary',
                            default => 'on-surface-variant',
                        };
                    @endphp
                    <article class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6 {{ $isPinned ? 'border-l-4 border-l-tertiary' : '' }}">
                        <header class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-{{ $accent }}/10 text-{{ $accent }} text-[10px] font-bold uppercase tracking-widest">{{ ucfirst($a->target_audience) }}</span>
                                @if($a->hasAttachment())
                                    <span class="px-2 py-0.5 rounded-md bg-surface-container text-[10px] font-bold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">attach_file</span>
                                        {{ $a->attachmentIsImage() ? 'Gambar' : ($a->attachmentIsPdf() ? 'PDF' : 'Lampiran') }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-on-surface-variant">{{ $a->created_at->diffForHumans() }}</span>
                        </header>
                        <h2 class="font-headline text-xl font-bold mb-2">
                            <a href="{{ route('announcements.show', $a) }}" class="hover:text-primary transition-colors">{{ $a->title }}</a>
                        </h2>
                        <p class="text-sm text-on-surface-variant line-clamp-2">{{ Str::limit(strip_tags($a->content), 180) }}</p>
                        <footer class="flex items-center justify-between mt-4 pt-4 border-t border-outline-variant/10">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-[10px] font-bold">
                                    {{ strtoupper(substr($a->author->name, 0, 2)) }}
                                </div>
                                <span class="text-xs text-on-surface-variant">{{ $a->author->name }} · {{ ucfirst($a->author->role) }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                @if(Auth::id() === $a->user_id || Auth::user()->role === 'admin')
                                    <a href="{{ route('announcements.edit', $a) }}" class="text-xs font-bold text-tertiary">Edit</a>
                                    <form action="{{ route('announcements.destroy', $a) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-error">Hapus</button>
                                    </form>
                                @endif
                                <a href="{{ route('announcements.show', $a) }}" class="text-xs font-bold text-primary hover:underline">Baca selengkapnya →</a>
                            </div>
                        </footer>
                    </article>
                @empty
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-outline mb-3">campaign</span>
                        <h3 class="font-headline text-lg font-bold">Belum ada pengumuman</h3>
                    </div>
                @endforelse

                @if($announcements->hasPages())
                    <div>{{ $announcements->links() }}</div>
                @endif
            </main>

            {{-- RIGHT: Filter sidebar --}}
            <aside class="lg:col-span-4 space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-base font-bold mb-3">Filter Target</h3>
                    <div class="space-y-1">
                        @foreach([['', 'Semua'], ['all', 'Semua Pengguna'], ['mahasiswa', 'Mahasiswa'], ['dosen', 'Dosen'], ['specific', 'Spesifik']] as [$val, $label])
                            <a href="{{ route('announcements.index', ['target' => $val]) }}" class="flex items-center justify-between px-3 py-2 rounded-lg {{ request('target') === $val ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                                <span class="text-xs">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="bg-tertiary-fixed/40 rounded-2xl border border-tertiary/20 p-5">
                    <h3 class="font-headline text-sm font-bold mb-2 flex items-center gap-1.5"><span class="material-symbols-outlined text-base">push_pin</span> Tips</h3>
                    <p class="text-xs text-on-surface-variant">Pengumuman dari Admin Pusat berlaku universitas. Pengumuman dari dosen biasanya terkait mata kuliah tertentu.</p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
