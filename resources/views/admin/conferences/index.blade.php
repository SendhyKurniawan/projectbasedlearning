{{-- Halaman daftar konferensi (kelas virtual) (admin). --}}
<x-app-layout>
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest">
                    <span><a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary">Kelas Virtual</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline">Live Conferences</h1>
                <p class="text-on-surface-variant max-w-lg font-medium">Monitor sesi live & terjadwal lintas mata kuliah. Admin masuk sebagai moderator.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="px-5 py-4 bg-tertiary-fixed border-l-4 border-tertiary text-on-tertiary-fixed-variant rounded-xl text-sm font-bold mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-5 py-4 bg-error-container border-l-4 border-error text-on-error-container rounded-xl text-sm font-bold mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div>
            <h2 class="text-lg font-extrabold text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">sensors</span> Sesi Aktif & Terjadwal
            </h2>
            <div class="space-y-4">
                @forelse($activeConferences as $conference)
                    <div class="bg-surface-container-lowest rounded-2xl relative overflow-hidden shadow-sm border border-outline-variant/10 hover:shadow-md transition-shadow">
                        @if($conference->status === 'live')
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-error animate-pulse"></div>
                        @else
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
                        @endif
                        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-6 pl-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    @if($conference->status === 'live')
                                        <span class="px-2 py-1 bg-error-container text-on-error-container text-[10px] font-black uppercase tracking-widest rounded flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span> Live Now
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-primary-container text-on-primary-container text-[10px] font-black uppercase tracking-widest rounded">Dijadwalkan</span>
                                    @endif
                                    <div class="flex items-center gap-1.5 text-xs text-on-surface font-semibold bg-surface px-2 py-1 rounded border border-outline-variant/20">
                                        <span class="material-symbols-outlined text-[16px] text-tertiary">calendar_clock</span>
                                        {{ $conference->scheduled_at->format('d M Y, H:i') }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-on-surface-variant font-semibold">
                                        <span class="material-symbols-outlined text-[16px]">menu_book</span>
                                        {{ $conference->course->kode_matkul }} &mdash; {{ $conference->course->nama_matkul }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-on-surface-variant font-semibold">
                                        <span class="material-symbols-outlined text-[16px]">person</span>
                                        {{ $conference->dosen->name ?? '-' }}
                                    </div>
                                </div>
                                <h3 class="font-extrabold text-lg text-on-surface leading-tight mb-1">{{ $conference->title }}</h3>
                                @if($conference->description)
                                    <p class="text-sm text-on-surface-variant line-clamp-1">{{ $conference->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 md:justify-end shrink-0">
                                @if($conference->status === 'live')
                                    <a href="{{ route('admin.conferences.room', $conference) }}" class="px-5 py-2.5 bg-error text-white font-bold text-sm rounded-xl hover:bg-error/90 transition-colors flex items-center gap-2 shadow-sm">
                                        <span class="material-symbols-outlined text-[18px]">meeting_room</span> Masuk Room
                                    </a>
                                    <form method="POST" action="{{ route('admin.conferences.end', $conference) }}" onsubmit="return confirm('Akhiri sesi ini untuk semua peserta?')">
                                        @csrf
                                        <button type="submit" class="w-10 h-10 bg-surface-container hover:bg-error-container hover:text-error text-on-surface-variant rounded-xl flex items-center justify-center transition-colors" title="Akhiri Sesi">
                                            <span class="material-symbols-outlined text-[20px]">stop_circle</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 bg-surface border border-outline-variant/20 rounded-2xl text-center">
                        <p class="text-sm font-medium text-on-surface-variant">Tidak ada sesi live atau terjadwal saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($endedConferences->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-lg font-bold text-on-surface-variant mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-on-surface-variant/70">history</span> Riwayat Sesi Selesai
                </h2>
                <div class="space-y-3">
                    @foreach($endedConferences as $conference)
                        <div class="bg-surface border border-outline-variant/20 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 bg-surface-container-high text-on-surface-variant text-[10px] font-black uppercase tracking-widest rounded">Selesai</span>
                                    <span class="text-xs text-on-surface-variant font-medium">{{ $conference->scheduled_at->format('d M Y') }}@if($conference->ended_at) (Berakhir: {{ $conference->ended_at->format('H:i') }})@endif</span>
                                    <span class="text-xs text-on-surface-variant font-semibold">{{ $conference->course->kode_matkul ?? '' }}</span>
                                    <span class="text-xs text-on-surface-variant">&middot; {{ $conference->dosen->name ?? '-' }}</span>
                                </div>
                                <h3 class="font-bold text-on-surface">{{ $conference->title }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($endedConferences->hasPages())
                    <div class="mt-8">{{ $endedConferences->links() }}</div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
