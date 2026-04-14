<x-app-layout>
    <div class="space-y-12 pb-20 px-4 sm:px-6 lg:px-8">
        <!-- Header & Academic Summary -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
            <div class="space-y-4">
                <nav class="flex items-center gap-2 text-[10px] font-black text-on-surface-variant/60 uppercase tracking-[0.2em] mb-2 px-1">
                    <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary text-[11px]">Laporan Nilai</span>
                </nav>
                <h1 class="text-5xl font-headline font-black text-on-surface uppercase tracking-tighter leading-tight">Transkrip Evaluasi</h1>
                <p class="text-on-surface-variant body-md mt-1 opacity-80">Pantau progres akademik dan pencapaian kompetensi anda di seluruh mata kuliah.</p>
            </div>

            <div class="flex gap-4">
                <div class="bg-surface-container-lowest p-6 rounded-[2.5rem] border border-outline-variant/10 shadow-sm flex flex-col items-center min-w-[140px]">
                    <span class="text-[9px] font-black uppercase text-on-surface-variant opacity-60 mb-2">Rata-rata Skor</span>
                    <span class="text-4xl font-black text-primary tracking-tighter">
                        @php
                            $allScores = [];
                            foreach($courses as $c) {
                                foreach($c->assignments as $a) {
                                    $sub = $a->submissions->first();
                                    if($sub && $sub->score !== null) $allScores[] = $sub->score;
                                }
                            }
                            $avg = count($allScores) > 0 ? array_sum($allScores) / count($allScores) : 0;
                        @endphp
                        {{ number_format($avg, 1) }}
                    </span>
                </div>
                <div class="bg-primary p-6 rounded-[2.5rem] shadow-xl shadow-primary/20 flex flex-col items-center min-w-[140px] text-on-primary">
                    <span class="text-[9px] font-black uppercase opacity-60 mb-2">Selesai Dinilai</span>
                    <span class="text-4xl font-black tracking-tighter">{{ count($allScores) }}</span>
                </div>
            </div>
        </div>

        <!-- Courses List -->
        <div class="space-y-6">
            <h3 class="text-xs font-black uppercase text-on-surface-variant tracking-[0.3em] px-1 flex items-center gap-3 opacity-60">
                <span class="w-12 h-1 bg-primary rounded-full"></span>
                DETAIL PER MATA KULIAH
            </h3>

            @forelse($courses as $course)
                <div x-data="{ expanded: false }" 
                     class="bg-surface-container-lowest rounded-[2.5rem] border border-outline-variant/10 shadow-sm overflow-hidden group transition-all duration-500"
                     :class="expanded ? 'ring-2 ring-primary/10 shadow-2xl' : ''">
                    
                    <!-- Card Header -->
                    <button @click="expanded = !expanded" 
                            class="w-full flex flex-col md:flex-row md:items-center justify-between p-8 text-left hover:bg-surface-container-low/50 transition-colors gap-6 ">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 bg-surface-container-low rounded-2xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform duration-500 shadow-inner">
                                <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $course->kode_matkul }}</span>
                                    <span class="px-2 py-0.5 bg-secondary-container text-on-secondary-container text-[8px] font-black rounded uppercase tracking-widest">{{ $course->semester->name ?? 'Regular' }}</span>
                                </div>
                                <h4 class="text-2xl font-black text-on-surface tracking-tighter uppercase">{{ $course->nama_matkul }}</h4>
                            </div>
                        </div>

                        <div class="flex items-center gap-10">
                            <div class="hidden md:flex flex-col items-end">
                                <span class="text-[9px] font-black text-on-surface-variant/40 uppercase tracking-widest mb-1">Rata-rata MK</span>
                                @php
                                    $courseScores = [];
                                    foreach($course->assignments as $a) {
                                        $sub = $a->submissions->first();
                                        if($sub && $sub->score !== null) $courseScores[] = $sub->score;
                                    }
                                    $courseAvg = count($courseScores) > 0 ? array_sum($courseScores) / count($courseScores) : null;
                                @endphp
                                <span class="text-xl font-black text-on-surface">{{ $courseAvg ? number_format($courseAvg, 1) : '-' }}</span>
                            </div>
                            
                            <div class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant transition-transform duration-500"
                                 :class="expanded ? 'rotate-180 bg-primary/10 text-primary' : ''">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </button>

                    <!-- Card Body: Grades Table -->
                    <div x-show="expanded" x-collapse x-cloak>
                        <div class="px-8 pb-10">
                            @if($course->assignments->count() > 0)
                                <div class="overflow-hidden rounded-[2rem] border border-outline-variant/10">
                                    <table class="w-full text-left ">
                                        <thead>
                                            <tr class="bg-surface-container-low/50 text-[10px] font-black text-on-surface-variant uppercase tracking-widest">
                                                <th class="px-8 py-5">Tugas / Evaluasi</th>
                                                <th class="px-8 py-5 hidden md:table-cell">Jenis</th>
                                                <th class="px-8 py-5">Status</th>
                                                <th class="px-8 py-5 text-right">Nilai Akhir</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-outline-variant/5">
                                            @foreach($course->assignments->sortBy('assignment_number') as $assignment)
                                                @php
                                                    $submission = $assignment->submissions->first();
                                                    $score = $submission ? $submission->score : null;
                                                @endphp
                                                <tr class="hover:bg-primary/5 transition-colors group/row">
                                                    <td class="px-8 py-6">
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-black text-on-surface group-hover/row:text-primary transition-colors uppercase tracking-tight">{{ $assignment->title }}</span>
                                                            <span class="text-[10px] font-bold text-on-surface-variant opacity-40">#{{ $assignment->assignment_number }} &bull; {{ $assignment->deadline?->format('d M Y') ?? '-' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-8 py-6 hidden md:table-cell">
                                                        <span class="text-[9px] font-black text-on-surface-variant uppercase tracking-widest opacity-60">{{ $assignment->type }}</span>
                                                    </td>
                                                    <td class="px-8 py-6">
                                                        @if($submission && $score !== null)
                                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-secondary/10 text-secondary rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm shadow-secondary/5">
                                                                <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                                                Graded
                                                            </span>
                                                        @elseif($submission && $submission->status === 'submitted')
                                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary rounded-full text-[9px] font-black uppercase tracking-widest">
                                                                <span class="material-symbols-outlined text-[12px] animate-pulse">pending</span>
                                                                Review
                                                            </span>
                                                        @elseif($submission && $submission->status === 'late')
                                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-error/10 text-error rounded-full text-[9px] font-black uppercase tracking-widest">Late</span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-surface-container text-on-surface-variant/40 rounded-full text-[9px] font-black uppercase tracking-widest">Missing</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-8 py-6 text-right">
                                                        <div class="flex flex-col items-end">
                                                            <span class="text-lg font-black {{ $score !== null ? 'text-on-surface' : 'text-on-surface-variant opacity-20' }}">
                                                                {{ $score !== null ? $score : '-' }}
                                                            </span>
                                                            <span class="text-[9px] font-bold text-on-surface-variant/40 uppercase tracking-widest">Max {{ $assignment->max_score ?? 100 }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-12 text-center space-y-4 bg-surface-container-low/30 rounded-[2rem] border border-dashed border-outline-variant/20 ">
                                    <span class="material-symbols-outlined text-4xl text-on-surface-variant/20">inbox</span>
                                    <p class="text-xs text-on-surface-variant opacity-60">Belum ada tugas atau kuis yang terdata untuk mata kuliah ini.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-24 text-center space-y-6 bg-surface-container-lowest rounded-[3rem] border border-outline-variant/10 shadow-sm ">
                    <div class="w-24 h-24 bg-surface-container-low rounded-[2rem] flex items-center justify-center text-on-surface-variant/20 shadow-inner">
                        <span class="material-symbols-outlined text-[48px]">school</span>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-xl font-black text-on-surface uppercase tracking-tighter">BELUM TERDAFTAR</h4>
                        <p class="text-sm text-on-surface-variant opacity-60 max-w-xs mx-auto">Anda belum mendaftar di mata kuliah apapun pada semester ini.</p>
                    </div>
                    <a href="{{ route('mahasiswa.dashboard') }}" class="px-10 py-4 bg-primary text-on-primary rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-xl transition-all">Kembali ke Dashboard</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
