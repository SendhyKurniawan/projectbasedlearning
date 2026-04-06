<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dosen.assignments.index', $quiz->course) }}" class="p-2.5 w-10 h-10 flex items-center justify-center bg-white border border-outline-variant/30 rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-on-surface">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-extrabold font-headline tracking-tight text-on-surface">Kuis: {{ $quiz->title }}</h2>
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mt-1">Daftar Percobaan / Hasil Evaluasi</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="bg-surface-container-lowest px-4 py-2 border border-outline-variant/30 rounded-xl shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">groups</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest block mb-0.5">Total Dikerjakan</span>
                        <span class="text-lg font-extrabold text-on-surface leading-none">{{ $attempts->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden border border-outline-variant/30">
            @if($attempts->isEmpty())
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
                        <tbody class="divide-y divide-outline-variant/10 bg-white">
                            @foreach($attempts as $attempt)
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-tertiary text-white flex items-center justify-center font-bold font-headline shrink-0 uppercase shadow-inner">
                                                {{ substr($attempt->mahasiswa->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ $attempt->mahasiswa->name }}</div>
                                                <div class="text-xs text-on-surface-variant">{{ $attempt->mahasiswa->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($attempt->finished_at)
                                            <div class="flex items-center gap-2 text-sm text-on-surface font-medium">
                                                <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>
                                                {{ $attempt->finished_at->format('d M Y, H:i') }}
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-sm text-amber-700 font-bold bg-amber-50 px-2 py-1 rounded inline-flex">
                                                <span class="material-symbols-outlined text-[16px]">sync</span>
                                                Sedang Dikerjakan
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($attempt->finished_at)
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-sm font-extrabold bg-surface-container text-on-surface shadow-inner border border-outline-variant/20">
                                                {{ $attempt->total_score ?? '0' }}
                                            </span>
                                        @else
                                            <span class="text-on-surface-variant text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($attempt->finished_at)
                                            <a href="{{ route('dosen.quizzes.attempts.show', [$quiz, $attempt]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-primary/5 border border-outline-variant/30 hover:border-primary/30 text-primary rounded-xl text-sm font-bold transition-all shadow-sm">
                                                <span>Detail</span>
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container text-outline border border-transparent rounded-xl text-sm font-bold cursor-not-allowed">
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
