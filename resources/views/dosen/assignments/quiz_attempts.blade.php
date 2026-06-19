{{-- Halaman daftar percobaan quiz mahasiswa pada sebuah quiz (dosen). --}}
@push('styles')
    @vite('resources/css/pages/dosen/assignments.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-primary transition-colors">Assignments</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary truncate max-w-[180px]">{{ $assignment->title }}</span>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">Hasil Kuis</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Hasil Kuis</h1>
                <p class="mt-1 text-sm text-on-surface-variant">{{ $assignment->title }} · Daftar percobaan dan skor.</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-surface-container-lowest px-4 py-2 border border-outline-variant/30 rounded-xl shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">groups</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest block mb-0.5">Total Dikerjakan</span>
                        <span class="text-lg font-extrabold text-on-surface leading-none">{{ $submissions->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden border border-outline-variant/30">
            @if($submissions->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <span class="material-symbols-outlined text-[64px] text-primary/20 mb-4">folder_open</span>
                    <h3 class="text-lg font-bold font-headline text-on-surface">Belum Ada Percobaan</h3>
                    <p class="text-on-surface-variant text-sm mt-1 max-w-sm">Daftar kosong, mahasiwa belum memulai evaluasi ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low/50 border-b border-outline-variant/20">
                                <th class="px-6 py-4 text-xs font-extrabold text-on-surface-variant uppercase tracking-widest font-headline">Mahasiswa</th>
                                <th class="px-6 py-4 text-xs font-extrabold text-on-surface-variant uppercase tracking-widest font-headline">Waktu Selesai</th>
                                <th class="px-6 py-4 text-xs font-extrabold text-on-surface-variant uppercase tracking-widest font-headline">Skor Akhir</th>
                                <th class="px-6 py-4 text-xs font-extrabold text-on-surface-variant uppercase tracking-widest font-headline text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10 bg-surface-container-lowest">
                            @foreach($submissions as $submission)
                                <tr class="hover:bg-surface-container-low transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-tertiary text-white flex items-center justify-center font-bold font-headline shrink-0 uppercase shadow-inner">
                                                {{ substr($submission->mahasiswa->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ $submission->mahasiswa->name }}</div>
                                                <div class="text-xs text-on-surface-variant">{{ $submission->mahasiswa->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($submission->finished_at)
                                            <div class="flex items-center gap-2 text-sm text-on-surface font-medium">
                                                <span class="material-symbols-outlined text-[16px] text-secondary">check_circle</span>
                                                {{ $submission->finished_at->format('d M Y, H:i') }}
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-sm text-on-warning font-bold bg-warning-light px-2 py-1 rounded inline-flex">
                                                <span class="material-symbols-outlined text-[16px]">sync</span>
                                                Sedang Dikerjakan
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($submission->finished_at)
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-sm font-extrabold bg-surface-container text-on-surface shadow-inner border border-outline-variant/20">
                                                {{ $submission->score ?? '0' }}
                                            </span>
                                        @else
                                            <span class="text-on-surface-variant text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($submission->finished_at)
                                            <a href="{{ route('dosen.assignments.submissions.show', [$assignment, $submission]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container-lowest hover:bg-primary/5 border border-outline-variant/30 hover:border-primary/30 text-primary dark:text-primary-fixed-dim rounded-xl text-sm font-bold transition-all shadow-sm">
                                                <span>Detail</span>
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface-variant border border-transparent rounded-xl text-sm font-bold cursor-not-allowed">
                                                <span>Detail</span>
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
