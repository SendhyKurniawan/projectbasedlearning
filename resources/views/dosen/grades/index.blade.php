@push('styles')
    @vite('resources/css/pages/dosen/grades.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold font-headline tracking-tight text-on-surface">Rekap Nilai Mahasiswa</h2>
                <p class="text-sm font-medium text-on-surface-variant mt-1">Kelola dan pantau seluruh nilai mahasiswa dari berbagai mata kuliah</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-surface border border-outline-variant/30 px-4 py-2 rounded-xl shadow-sm text-center">
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest block mb-0.5">Total Matkul</span>
                    <span class="text-lg font-extrabold text-primary leading-none">{{ $courses->total() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest overflow-hidden shadow-sm border border-outline-variant/30 rounded-2xl relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-secondary"></div>
            
            <!-- Filter Section -->
            <div class="p-6 border-b border-outline-variant/20 bg-surface-container-lowest">
                <form method="GET" action="{{ route('dosen.grades.index') }}" class="flex flex-col md:flex-row md:items-end gap-5">
                    <div class="w-full md:w-64">
                        <label for="search" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Pencarian Mahasiswa</label>
                        <div class="relative focus-within:text-primary text-on-surface-variant">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-[18px]">search</span>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik nama / NIM..." 
                                class="block w-full rounded-xl border border-outline-variant/30 bg-surface pl-10 pr-3 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow placeholder:text-on-surface-variant/50">
                        </div>
                    </div>
                    
                    <div class="w-full md:flex-1">
                        <label for="academic_year_id" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Tahun Akademik</label>
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
                        <label for="semester_id" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Semester</label>
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
                        <a href="{{ route('dosen.grades.index') }}" class="flex items-center justify-center gap-2 h-[42px] px-5 border border-outline-variant/30 bg-surface rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors shadow-sm w-full md:w-auto">
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span> Reset
                        </a>
                        <button type="submit" class="flex items-center justify-center gap-2 h-[42px] px-6 bg-primary hover:bg-primary/90 text-white rounded-xl shadow-md font-bold transition-all w-full md:w-auto hover:-translate-y-0.5 active:translate-y-0">
                            <span class="material-symbols-outlined text-[18px]">filter_list</span> Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="p-6 md:p-8 bg-surface-container-lowest">
                <div class="space-y-10">
                    @forelse($courses as $course)
                        <div class="border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm bg-surface-container-lowest">
                            <!-- Course Header -->
                            <div class="w-full flex justify-between items-center px-6 py-4 bg-surface-container-low/30 border-b border-outline-variant/20">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
                                        <span class="material-symbols-outlined">library_books</span>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-lg font-headline text-on-surface leading-tight">{{ $course->nama_matkul }}</h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs font-bold font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded uppercase">{{ $course->kode_matkul }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-widest bg-primary-container text-on-primary border border-primary/20">
                                                {{ $course->semester->name ?? 'Semua Semester' }} ({{ $course->semester->academicYear->year_start ?? '' }}/{{ $course->semester->academicYear->year_end ?? '' }})
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="hidden sm:flex gap-4">
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-on-surface-variant tracking-widest mb-0.5">Tugas</p>
                                        <p class="text-base font-extrabold text-on-surface leading-none">{{ $course->assignments->count() }}</p>
                                    </div>
                                    <div class="w-px h-8 bg-outline-variant/30"></div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-on-surface-variant tracking-widest mb-0.5">Mhs</p>
                                        <p class="text-base font-extrabold text-on-surface leading-none">{{ $course->students->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-surface-container-lowest">
                                @if($course->students->count() > 0 && $course->assignments->count() > 0)
                                    <div class="overflow-x-auto custom-scrollbar">
                                        <table class="min-w-full divide-y divide-outline-variant/20">
                                            <thead class="bg-surface">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest sticky left-0 bg-surface z-10 w-48 shadow-[1px_0_0_0_rgba(0,0,0,0.05)] border-r border-outline-variant/10">Mahasiswa</th>
                                                    <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest whitespace-nowrap bg-surface">Masuk</th>
                                                    @foreach($course->assignments as $assignment)
                                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest bg-surface relative group" title="{{ $assignment->title }}">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="material-symbols-outlined text-[14px]">assignment</span>
                                                                <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-primary transition-colors truncate max-w-[120px]">
                                                                    {{ Str::limit($assignment->title, 15) }}
                                                                </a>
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                    <th scope="col" class="px-6 py-4 text-right text-[10px] font-bold text-primary uppercase tracking-widest bg-primary/5 border-l border-outline-variant/10">Rata-rata</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-outline-variant/10 bg-surface-container-lowest">
                                                @foreach($course->students as $student)
                                                    <tr class="hover:bg-surface-container-low/50 transition-colors group">
                                                        <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-surface-container-lowest group-hover:bg-surface-container-low/50 shadow-[1px_0_0_0_rgba(0,0,0,0.05)] border-r border-outline-variant/10 transition-colors z-10">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-8 h-8 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-secondary/20">
                                                                    {{ substr($student->name, 0, 2) }}
                                                                </div>
                                                                <div>
                                                                    <div class="text-sm font-bold text-on-surface">{{ $student->name }}</div>
                                                                    <div class="text-[10px] font-mono font-medium text-on-surface-variant">{{ $student->nim }}</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-on-surface-variant">
                                                            <span class="bg-surface border border-outline-variant/30 px-2.5 py-1 rounded-md text-xs">
                                                                {{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('Y') : '-' }} 
                                                                <span class="text-outline-variant/50 mx-1">|</span> 
                                                                <span class="{{ $student->pivot->enrolled_at && \Carbon\Carbon::parse($student->pivot->enrolled_at)->month > 6 ? 'text-primary' : 'text-warning' }}">
                                                                    {{ $student->pivot->enrolled_at && \Carbon\Carbon::parse($student->pivot->enrolled_at)->month > 6 ? 'Ganjil' : 'Genap' }}
                                                                </span>
                                                            </span>
                                                        </td>
                                                        @php $totalScore = 0; $count = 0; @endphp
                                                        @foreach($course->assignments as $assignment)
                                                            @php
                                                                $submission = $student->submissions->where('assignment_id', $assignment->id)->first();
                                                                $score = $submission ? $submission->score : null;
                                                                if($score !== null) {
                                                                    $totalScore += $score;
                                                                    $count++;
                                                                }
                                                            @endphp
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                @if($score !== null)
                                                                    <div class="inline-flex items-center justify-center min-w-[3rem] px-2.5 py-1 bg-secondary-container text-secondary border border-secondary/20 rounded-lg text-sm font-extrabold shadow-sm">
                                                                        {{ $score }}
                                                                    </div>
                                                                @else
                                                                    <span class="text-outline-variant/50 font-bold px-2">-</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                        <td class="px-6 py-4 whitespace-nowrap text-right bg-primary/5 border-l border-outline-variant/10">
                                                            @if($count > 0)
                                                                @php $avg = $totalScore / $count; @endphp
                                                                <span class="inline-flex items-center justify-center min-w-[3.5rem] px-3 py-1.5 {{ $avg >= 80 ? 'bg-primary text-on-primary shadow-md' : ($avg >= 60 ? 'bg-warning-light text-warning border border-warning/30' : 'bg-error-container text-on-error-container border border-error/30') }} rounded-xl text-sm font-extrabold">
                                                                    {{ number_format($avg, 1) }}
                                                                </span>
                                                            @else
                                                                <span class="text-outline-variant/50 font-bold px-2">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif($course->assignments->count() == 0)
                                    <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
                                        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant/50">assignment_late</span>
                                        </div>
                                        <p class="text-sm font-bold text-on-surface">Belum ada tugas</p>
                                        <p class="text-xs text-on-surface-variant mt-1">Tidak ada tugas yang terdaftar untuk mata kuliah ini.</p>
                                    </div>
                                @else
                                    <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
                                        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                                            <span class="material-symbols-outlined text-[32px] text-on-surface-variant/50">search_off</span>
                                        </div>
                                        <p class="text-sm font-bold text-on-surface">Tidak ada hasil</p>
                                        <p class="text-xs text-on-surface-variant mt-1">Tidak ada mahasiswa yang cocok dengan filter pencarian pada kelas ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-20 flex flex-col items-center justify-center text-center bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm">
                            <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-6">
                                <span class="material-symbols-outlined text-[40px] text-on-surface-variant/50">folder_off</span>
                            </div>
                            <h3 class="text-lg font-extrabold font-headline text-on-surface">Tidak ada data matkul</h3>
                            <p class="text-sm text-on-surface-variant max-w-md mt-2">Silakan ubah filter pencarian Anda, atau pastikan Anda memiliki akses pengajaran untuk semester yang dipilih.</p>
                        </div>
                    @endforelse
                </div>

                @if($courses->hasPages())
                    <div class="mt-8">
                        {{ $courses->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Scoped styles to hide default scroll bar on horizontal scrolling container if needed -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.02);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0,0,0,0.2);
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const yearSelect = document.getElementById('academic_year_id');
                const semesterSelect = document.getElementById('semester_id');
                
                // Basic chaining for semester dropdown based on selected year
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
                    
                    // Reset semester if it's no longer visible
                    if (semesterSelect.selectedOptions[0].style.display === 'none') {
                        semesterSelect.value = '';
                    }
                });
                
                // Trigger initially
                if (yearSelect.value) {
                    yearSelect.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
</x-app-layout>
