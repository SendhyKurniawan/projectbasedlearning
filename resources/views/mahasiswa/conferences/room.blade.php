@php
    $domain = config('services.jitsi.domain');
    $meetUrl = 'https://' . $domain . '/' . $conference->room_name . '?jwt=' . $jwt;
@endphp

<x-app-layout>
    <div class="max-w-3xl mx-auto" x-data="{ opened: false }">
        <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest mb-3">
            <a href="{{ route('mahasiswa.courses.show', $conference->course) }}" class="hover:text-primary transition-colors">{{ $conference->course->kode_matkul ?? 'Kelas' }}</a>
            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
            <span class="text-primary truncate max-w-[260px]">{{ $conference->title }}</span>
        </nav>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-3xl p-8 md:p-10 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span> Live
                </span>
                <span class="text-xs font-bold text-on-surface-variant/70 uppercase tracking-widest">Peserta</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-on-surface font-headline mb-2">{{ $conference->title }}</h1>
            @if($conference->description)
                <p class="text-on-surface-variant font-medium mb-6">{{ $conference->description }}</p>
            @else
                <div class="mb-6"></div>
            @endif

            <div class="space-y-3">
                <a href="{{ $meetUrl }}"
                   target="_blank"
                   rel="noopener"
                   @click="opened = true"
                   class="w-full inline-flex items-center justify-center gap-3 px-6 py-4 bg-primary text-on-primary rounded-2xl font-bold text-base shadow-sm hover:bg-primary/90 transition-all">
                    <span class="material-symbols-outlined">videocam</span>
                    Gabung Ruang Konferensi
                    <span class="material-symbols-outlined text-[18px] opacity-70">open_in_new</span>
                </a>

            </div>

            <div x-show="opened" x-transition x-cloak class="mt-6 p-4 bg-tertiary-container/40 border border-tertiary/20 rounded-2xl text-sm text-on-surface">
                <p class="font-bold mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">info</span>
                    Ruang konferensi dibuka di tab baru.
                </p>
                <p class="text-on-surface-variant">Izinkan akses kamera & mikrofon saat diminta browser. Tutup tab konferensi setelah sesi selesai.</p>
            </div>

            <div class="mt-8 pt-6 border-t border-outline-variant">
                <a href="{{ route('mahasiswa.courses.show', $conference->course) }}" class="inline-flex items-center gap-2 text-sm font-bold text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke kelas
                </a>
            </div>
        </div>

        <p class="mt-6 text-xs text-on-surface-variant/70 text-center">
            Tip: gunakan browser desktop atau seluler. Aplikasi Jitsi Meet di HP memerlukan konfigurasi server <code class="font-mono">{{ $domain }}</code> di pengaturan aplikasi.
        </p>
    </div>
</x-app-layout>
