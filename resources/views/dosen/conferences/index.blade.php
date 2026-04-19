@push('styles')
    @vite('resources/css/pages/dosen/conferences.css')
@endpush
<x-app-layout>
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest">
                    <span><a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary truncate max-w-[200px]">{{ $course->kode_matkul }}</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline">Live Conference</h1>
                <p class="text-on-surface-variant max-w-lg font-medium">{{ $course->nama_matkul }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('dosen.conferences.create', $course) }}" class="px-6 py-3 bg-primary text-on-primary rounded-xl font-bold flex items-center gap-2 shadow-sm hover:bg-primary/90 transition-all">
                    <span class="material-symbols-outlined">video_call</span>
                    Jadwalkan Sesi Baru
                </a>
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

        @php
            $activeConferences = $conferences->filter(fn($c) => $c->status !== 'ended');
            $endedConferences = $conferences->filter(fn($c) => $c->status === 'ended');
        @endphp

        <!-- Upcoming & Live Conferences List -->
        <div>
            <h2 class="text-lg font-extrabold text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">sensors</span> Sesi Aktif & Terjadwal
            </h2>
            <div class="space-y-4">
                @forelse($activeConferences as $conference)
                    <div class="bg-surface-container-lowest rounded-2xl relative overflow-hidden group shadow-sm border border-outline-variant/10 hover:shadow-md transition-shadow">
                        
                        @if($conference->status === 'live')
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-error animate-pulse"></div>
                        @else
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
                        @endif

                        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-6 pl-6">
                            <!-- Left Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    @if($conference->status === 'live')
                                        <span class="px-2 py-1 bg-error-container text-on-error-container text-[10px] font-black uppercase tracking-widest rounded flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span> Live Now
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-primary-container text-on-primary-container text-[10px] font-black uppercase tracking-widest rounded flex items-center gap-1">
                                            Dijadwalkan
                                        </span>
                                    @endif
                                    
                                    <div class="flex items-center gap-1.5 text-xs text-on-surface font-semibold bg-surface px-2 py-1 rounded border border-outline-variant/20">
                                        <span class="material-symbols-outlined text-[16px] text-tertiary">calendar_clock</span>
                                        {{ $conference->scheduled_at->format('l, d M Y') }} &bull; {{ $conference->scheduled_at->format('H:i') }}
                                    </div>
                                </div>
                                
                                <h3 class="font-extrabold text-lg text-on-surface leading-tight mb-1">
                                    {{ $conference->title }}
                                </h3>
                                
                                @if($conference->description)
                                    <p class="text-sm text-on-surface-variant line-clamp-1">{{ $conference->description }}</p>
                                @endif
                            </div>

                            <!-- Right Actions -->
                            <div class="flex items-center gap-3 md:justify-end shrink-0 border-t md:border-t-0 border-outline-variant/10 pt-4 md:pt-0 mt-4 md:mt-0">
                                @if($conference->status === 'scheduled')
                                    <div class="flex items-center gap-1 mr-3 border-r border-outline-variant/20 pr-4">
                                        <a href="{{ route('dosen.conferences.edit', $conference) }}" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Edit Sesi">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('dosen.conferences.destroy', $conference) }}" class="inline" onsubmit="return confirm('Hapus konferensi ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container rounded-lg transition-colors" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('dosen.conferences.start', $conference) }}">
                                        @csrf
                                        <button type="submit" class="px-5 py-2.5 bg-primary/10 text-primary font-bold text-sm rounded-xl hover:bg-primary hover:text-white transition-colors flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px]">play_arrow</span> Mulai Sesi
                                        </button>
                                    </form>
                                @elseif($conference->status === 'live')
                                    <a href="{{ route('dosen.conferences.room', $conference) }}" class="px-5 py-2.5 bg-error text-white font-bold text-sm rounded-xl hover:bg-error/90 transition-colors flex items-center gap-2 shadow-sm">
                                        <span class="material-symbols-outlined text-[18px]">meeting_room</span> Masuk Room
                                    </a>
                                    <form method="POST" action="{{ route('dosen.conferences.end', $conference) }}" onsubmit="return confirm('Akhiri sesi ini untuk semua peserta?')">
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
                    @if($endedConferences->isEmpty())
                        <div class="py-16 bg-surface border-2 border-dashed border-outline-variant/30 rounded-3xl flex flex-col items-center justify-center text-center">
                            <div class="w-20 h-20 bg-surface-container mx-auto rounded-2xl flex items-center justify-center mb-4 transform -rotate-6">
                                <span class="material-symbols-outlined text-outline-variant text-4xl">videocam_off</span>
                            </div>
                            <h3 class="text-xl font-bold text-on-surface font-headline mb-2">Belum Ada Sesi Kelas Virtual</h3>
                            <p class="text-on-surface-variant font-medium text-sm mb-6 max-w-sm">Anda dapat menjadwalkan kelas interaktif tatap muka (video conference) bersama mahasiswa di sini.</p>
                            <a href="{{ route('dosen.conferences.create', $course) }}" class="px-6 py-3 bg-primary text-on-primary font-bold rounded-xl flex items-center gap-2 hover:bg-primary/90 transition-colors shadow-sm">
                                <span class="material-symbols-outlined">add</span>
                                Jadwalkan Sesi Pertama
                            </a>
                        </div>
                    @else
                        <div class="p-6 bg-surface border border-outline-variant/20 rounded-2xl text-center">
                            <p class="text-sm font-medium text-on-surface-variant">Tidak ada sesi kelas yang berlangsung atau dijadwalkan.</p>
                        </div>
                    @endif
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
                        <div class="bg-surface border border-outline-variant/20 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 opacity-80 hover:opacity-100 transition-opacity">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="px-2 py-0.5 bg-surface-container-high text-on-surface-variant text-[10px] font-black uppercase tracking-widest rounded flex items-center gap-1">
                                        Selesai
                                    </span>
                                    <span class="text-xs text-on-surface-variant font-medium">
                                        {{ $conference->scheduled_at->format('d M Y') }} 
                                        @if($conference->ended_at)
                                            (Berakhir: {{ $conference->ended_at->format('H:i') }})
                                        @endif
                                    </span>
                                </div>
                                <h3 class="font-bold text-on-surface">{{ $conference->title }}</h3>
                            </div>
                            <!-- Aksi tambahan jika perlu (misal: lihat statistik kehadiran) -->
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($conferences->hasPages())
            <div class="mt-8">
                {{ $conferences->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
