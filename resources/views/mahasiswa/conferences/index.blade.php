<x-app-layout>
    <div class="space-y-12 pb-20 px-4 sm:px-6 lg:px-8">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-4">
                <nav class="flex items-center gap-2 text-[10px] font-black text-on-surface-variant/60 uppercase tracking-[0.2em] mb-2 px-1">
                    <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span><a href="{{ route('mahasiswa.courses.show', $course->id) }}" class="hover:text-primary transition-colors">Course</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary text-[11px]">Kelas Virtual</span>
                </nav>
                <h1 class="text-4xl font-headline font-black text-on-surface uppercase tracking-tighter leading-none">Ruang Konferensi</h1>
                <p class="text-on-surface-variant body-md mt-1 opacity-80">Sesi tatap muka virtual interaktif untuk mata kuliah <span class="text-on-surface font-bold">{{ $course->nama_matkul }}</span>.</p>
            </div>
            
            <div class="flex items-center gap-4 bg-surface-container-lowest p-4 rounded-3xl border border-outline-variant/10 shadow-sm ">
                <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">videocam</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[9px] font-black uppercase text-on-surface-variant opacity-60 leading-none mb-1">Total Sesi</span>
                    <span class="text-lg font-black text-on-surface">{{ $conferences->count() }} Sesi Terjadwal</span>
                </div>
            </div>
        </div>

        @php
            $liveConferences = $conferences->where('status', 'live');
            $scheduledConferences = $conferences->where('status', 'scheduled');
            $endedConferences = $conferences->where('status', 'ended');
        @endphp

        <!-- 🔴 LIVE SESSIONS -->
        @if($liveConferences->count() > 0)
        <div class="space-y-6">
            <h3 class="text-xs font-black uppercase text-primary tracking-[0.3em] px-1 flex items-center gap-3">
                <span class="w-10 h-1 bg-primary rounded-full"></span>
                LIVE SEKARANG
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
            </h3>

            <div class="grid grid-cols-1 gap-4">
                @foreach($liveConferences as $conference)
                    <div class="group relative bg-surface-container-lowest rounded-[2rem] border-2 border-primary/20 p-8 flex flex-col md:flex-row items-center justify-between gap-8 hover:shadow-2xl hover:shadow-primary/5 transition-all duration-500 overflow-hidden">
                        <div class="absolute top-0 left-0 w-2 h-full bg-primary "></div>
                        
                        <div class="flex items-center gap-8 flex-1">
                            <div class="w-16 h-16 bg-primary/5 text-primary rounded-[1.5rem] flex items-center justify-center group-hover:scale-110 transition-transform duration-500 shadow-inner">
                                <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">cell_tower</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-2xl font-black text-on-surface tracking-tighter uppercase">{{ $conference->title }}</h4>
                                    <span class="px-2 py-0.5 bg-error text-white text-[8px] font-black rounded uppercase tracking-widest animate-pulse">ACTIVE SESSION</span>
                                </div>
                                <p class="text-sm text-on-surface-variant leading-relaxed opacity-80">{{ $conference->description ?? 'Tidak ada deskripsi sesi.' }}</p>
                                <div class="flex items-center gap-4 pt-2">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-on-surface-variant uppercase tracking-widest ">
                                        <span class="material-symbols-outlined text-sm opacity-50">person</span>
                                        {{ $conference->dosen->name }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] font-black text-primary uppercase tracking-widest ">
                                        <span class="material-symbols-outlined text-sm opacity-50">schedule</span>
                                        Started {{ $conference->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('mahasiswa.conferences.room', $conference) }}" 
                           class="w-full md:w-auto px-10 py-5 bg-primary text-on-primary font-black text-xs uppercase tracking-[0.2em] rounded-2xl flex items-center justify-center gap-3 hover:shadow-2xl hover:shadow-primary/30 active:scale-[0.98] transition-all ">
                            MASUK RUANG KELAS
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">door_open</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 📅 SCHEDULED SESSIONS -->
        @if($scheduledConferences->count() > 0)
        <div class="space-y-6">
            <h3 class="text-xs font-black uppercase text-on-surface-variant tracking-[0.3em] px-1 flex items-center gap-3 opacity-60">
                <span class="w-10 h-1 bg-outline-variant/30 rounded-full"></span>
                SESI MENDATANG
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($scheduledConferences as $conference)
                    <div class="bg-surface-container-lowest rounded-[2rem] border border-outline-variant/10 p-6 flex items-start gap-5 hover:border-primary/20 transition-all duration-300">
                        <div class="w-12 h-12 bg-surface-container-low text-on-surface-variant/40 rounded-2xl flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined">event</span>
                        </div>
                        <div class="space-y-3 flex-1">
                            <div class="space-y-1">
                                <h4 class="text-lg font-black text-on-surface leading-tight uppercase tracking-tighter">{{ $conference->title }}</h4>
                                <p class="text-[10px] font-medium text-on-surface-variant opacity-60">{{ $conference->scheduled_at->format('d M Y') }} &bull; {{ $conference->scheduled_at->format('H:i') }} WIB</p>
                            </div>
                            <p class="text-xs text-on-surface-variant/60 line-clamp-2">{{ $conference->description }}</p>
                            <div class="pt-2 flex items-center justify-between">
                                <span class="bg-surface-container px-3 py-1 rounded-full text-[9px] font-black text-on-surface-variant/40 uppercase tracking-widest ">DIJADWALKAN</span>
                                <span class="text-[10px] font-bold text-on-surface-variant opacity-40">{{ $conference->scheduled_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 🏁 ENDED SESSIONS -->
        @if($endedConferences->count() > 0)
        <div class="space-y-6 opacity-60 grayscale-[0.5]">
            <h3 class="text-xs font-black uppercase text-on-surface-variant tracking-[0.3em] px-1 flex items-center gap-3 opacity-40">
                <span class="w-10 h-1 bg-outline-variant/30 rounded-full"></span>
                SESI BERAKHIR
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($endedConferences as $conference)
                    <div class="bg-surface-container-low/50 rounded-2xl border border-outline-variant/5 p-5 flex flex-col gap-3 ">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black text-on-surface-variant/40 uppercase tracking-widest">SESSION ENDED</span>
                            <span class="material-symbols-outlined text-sm text-on-surface-variant/20">check_circle</span>
                        </div>
                        <h4 class="text-sm font-black text-on-surface uppercase tracking-tight line-clamp-1">{{ $conference->title }}</h4>
                        <div class="flex items-center justify-between text-[9px] font-bold text-on-surface-variant/40 uppercase">
                            <span>{{ $conference->scheduled_at->format('d M y') }}</span>
                            <span>{{ $conference->ended_at?->format('H:i') ?? 'Selesai' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($conferences->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center space-y-6">
            <div class="w-24 h-24 bg-surface-container-low rounded-[2rem] flex items-center justify-center text-on-surface-variant/20 shadow-inner">
                <span class="material-symbols-outlined text-[48px]">videocam_off</span>
            </div>
            <div class="space-y-2 ">
                <h4 class="text-xl font-black text-on-surface uppercase tracking-tighter">BELUM ADA SESI KELAS</h4>
                <p class="text-sm text-on-surface-variant opacity-60 max-w-xs mx-auto">Dosen anda belum menjadwalkan atau memulai sesi kelas virtual untuk mata kuliah ini.</p>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
