<x-app-layout>
    @php
        $allScores = [];
        $totalAssignments = 0;
        foreach($courses as $c) {
            foreach($c->assignments as $a) {
                $totalAssignments++;
                $sub = $a->submissions->first();
                if($sub && $sub->score !== null) $allScores[] = $sub->score;
            }
        }
        $avg = count($allScores) > 0 ? array_sum($allScores) / count($allScores) : 0;
        $maxScore = count($allScores) ? max($allScores) : 0;
        $predikat = $avg >= 85 ? 'Sangat Baik' : ($avg >= 70 ? 'Baik' : ($avg >= 60 ? 'Cukup' : ($avg > 0 ? 'Kurang' : '—')));
    @endphp

    <div class="space-y-8 pb-20 px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Riwayat Nilai</h1>
                <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Rekap penilaian per mata kuliah dan transkrip akademik.</p>
            </div>
        </div>

        {{-- 4-Stat Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Rata-rata Skor', number_format($avg, 1), 'Skala 100', 'analytics', 'primary'],
                ['Selesai Dinilai', count($allScores), 'dari ' . $totalAssignments . ' tugas', 'task_alt', 'secondary'],
                ['Skor Tertinggi', $maxScore ?: '—', 'Pencapaian terbaik', 'workspace_premium', 'tertiary'],
                ['Predikat', $predikat, 'Berdasarkan rata-rata', 'star', 'primary'],
            ] as [$label, $value, $hint, $icon, $accent])
                <div class="bg-surface-container-lowest p-5 rounded-2xl relative overflow-hidden border border-outline-variant/10">
                    <div class="absolute top-0 left-0 w-1 h-full bg-{{ $accent }}"></div>
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-{{ $accent }}/10 text-{{ $accent }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">{{ $label }}</p>
                    <p class="font-headline text-3xl font-extrabold mt-1 leading-none">{{ $value }}</p>
                    <p class="text-[11px] text-on-surface-variant mt-2">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2 border-b border-outline-variant/10 pb-3">
            <span class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary text-xs font-bold">Detail per Mata Kuliah</span>
            <a href="{{ route('mahasiswa.dashboard') }}" class="px-3 py-1.5 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-low">Kembali ke Overview</a>
        </div>

        <!-- Filter Section -->
        <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 border border-outline-variant/10 shadow-sm">
            <form method="GET" action="{{ route('mahasiswa.grades.index') }}" class="flex flex-col md:flex-row md:items-end gap-5">
                <div class="w-full md:w-64">
                    <label for="search" class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-2">Cari Mata Kuliah</label>
                    <div class="relative focus-within:text-primary text-on-surface-variant">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama / kode matkul..."
                            class="block w-full rounded-xl border border-outline-variant/30 bg-surface pl-10 pr-3 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow placeholder:text-on-surface-variant/50">
                    </div>
                </div>

                <div class="w-full md:flex-1">
                    <label for="academic_year_id" class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-2">Tahun Akademik</label>
                    <div class="relative focus-within:text-primary text-on-surface-variant">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        </div>
                        <select name="academic_year_id" id="academic_year_id"
                            class="w-full bg-surface border border-outline-variant/30 rounded-xl pl-10 pr-10 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                            <option value="">Semua Tahun</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_start }}/{{ $year->year_end }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="w-full md:flex-1">
                    <label for="semester_id" class="block text-[10px] font-black uppercase tracking-widest text-on-surface-variant mb-2">Semester</label>
                    <div class="relative focus-within:text-primary text-on-surface-variant">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[18px]">view_timeline</span>
                        </div>
                        <select name="semester_id" id="semester_id"
                            class="w-full bg-surface border border-outline-variant/30 rounded-xl pl-10 pr-10 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                            <option value="">Semua Semester</option>
                            @foreach($availableSemesters as $sem)
                                <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }} data-year="{{ $sem->academic_year_id }}">
                                    {{ $sem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-4 md:mt-0">
                    <a href="{{ route('mahasiswa.grades.index') }}" class="flex items-center justify-center gap-2 h-[42px] px-5 border border-outline-variant/30 bg-surface rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors shadow-sm w-full md:w-auto">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span> Reset
                    </a>
                    <button type="submit" class="flex items-center justify-center gap-2 h-[42px] px-6 bg-primary hover:bg-primary/90 text-on-primary rounded-xl shadow-md font-bold transition-all w-full md:w-auto hover:-translate-y-0.5 active:translate-y-0">
                        <span class="material-symbols-outlined text-[18px]">filter_list</span> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses List -->
        <div class="space-y-4">
            @forelse($courses as $course)
                <div x-data="{ expanded: false }" 
                     class="bg-surface-container-lowest rounded-[2.5rem] border border-outline-variant/10 shadow-sm overflow-hidden group transition-all duration-500"
                     :class="expanded ? 'ring-2 ring-primary/10 shadow-2xl' : ''">
                    
                    <!-- Card Header -->
                    <button @click="expanded = !expanded" 
                            class="w-full flex flex-col md:flex-row md:items-center justify-between p-8 text-left hover:bg-surface-container-low/50 transition-colors gap-6 ">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 bg-surface-container-low rounded-2xl flex items-center justify-center text-primary dark:text-primary-fixed-dim group-hover:scale-110 transition-transform duration-500 shadow-inner">
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
                        <h4 class="text-xl font-black text-on-surface uppercase tracking-tighter">TIDAK ADA HASIL</h4>
                        <p class="text-sm text-on-surface-variant opacity-60 max-w-xs mx-auto">Tidak ada mata kuliah yang cocok dengan filter. Coba ubah kriteria pencarian.</p>
                    </div>
                    <a href="{{ route('mahasiswa.grades.index') }}" class="px-10 py-4 bg-primary text-on-primary rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-xl transition-all">Reset Filter</a>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('academic_year_id');
            const semesterSelect = document.getElementById('semester_id');

            if (yearSelect && semesterSelect) {
                yearSelect.addEventListener('change', function() {
                    const yearId = this.value;
                    const options = semesterSelect.querySelectorAll('option');

                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = '';
                            return;
                        }
                        if (!yearId || option.dataset.year === yearId) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    if (semesterSelect.selectedOptions[0].style.display === 'none') {
                        semesterSelect.value = '';
                    }
                });

                if (yearSelect.value) {
                    yearSelect.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
