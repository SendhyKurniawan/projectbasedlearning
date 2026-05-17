@push('styles')
    @vite('resources/css/pages/dosen/grades.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Manajemen Nilai</h1>
                <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Kelola dan pantau seluruh nilai mahasiswa dari mata kuliah yang Anda ampu.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="bg-surface-container-lowest border border-outline-variant/20 px-4 py-2 rounded-xl text-center">
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest block mb-0.5">Total Matkul</span>
                    <span class="font-headline text-lg font-extrabold text-primary leading-none">{{ $courseGroups->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest overflow-hidden shadow-sm border border-outline-variant/30 rounded-2xl relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-secondary"></div>

            <!-- Filter Section -->
            <div class="p-6 border-b border-outline-variant/20 bg-surface-container-lowest">
                <form method="GET" action="{{ route('dosen.grades.index') }}" class="space-y-4">
                    {{-- Row 1: search + academic year + semester --}}
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="w-full md:w-56">
                            <label for="search" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Pencarian Mahasiswa</label>
                            <div class="relative focus-within:text-primary text-on-surface-variant">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-[18px]">search</span>
                                </div>
                                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama / NIM..."
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
                                        <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
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
                                        <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }} data-year="{{ $sem->academic_year_id }}">
                                            {{ $sem->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: department → study program → kelas + action buttons --}}
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="w-full md:flex-1">
                            <label for="department_id" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Departemen</label>
                            <div class="relative focus-within:text-primary text-on-surface-variant">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-[18px]">corporate_fare</span>
                                </div>
                                <select name="department_id" id="department_id"
                                    class="w-full bg-surface border border-outline-variant/30 rounded-xl pl-10 pr-10 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                                    <option value="">Semua Departemen</option>
                                    @foreach($availableDepartments as $dept)
                                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="w-full md:flex-1">
                            <label for="study_program_id" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Program Studi</label>
                            <div class="relative focus-within:text-primary text-on-surface-variant">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-[18px]">school</span>
                                </div>
                                <select name="study_program_id" id="study_program_id"
                                    class="w-full bg-surface border border-outline-variant/30 rounded-xl pl-10 pr-10 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                                    <option value="">Semua Prodi</option>
                                    @foreach($availablePrograms as $prog)
                                        <option value="{{ $prog->id }}"
                                                data-department="{{ $prog->department_id }}"
                                                {{ $studyProgramId == $prog->id ? 'selected' : '' }}>
                                            {{ $prog->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="w-full md:flex-1">
                            <label for="student_class_id" class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Kelas</label>
                            <div class="relative focus-within:text-primary text-on-surface-variant">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-[18px]">meeting_room</span>
                                </div>
                                <select name="student_class_id" id="student_class_id"
                                    class="w-full bg-surface border border-outline-variant/30 rounded-xl pl-10 pr-10 py-2.5 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                                    <option value="">Semua Kelas</option>
                                    @foreach($availableClasses as $kelas)
                                        <option value="{{ $kelas->id }}"
                                                data-program="{{ $kelas->study_program_id }}"
                                                {{ $studentClassId == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-4 md:mt-0 shrink-0">
                            <a href="{{ route('dosen.grades.index') }}" class="flex items-center justify-center gap-2 h-[42px] px-5 border border-outline-variant/30 bg-surface rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors shadow-sm w-full md:w-auto">
                                <span class="material-symbols-outlined text-[18px]">restart_alt</span> Reset
                            </a>
                            <button type="submit" class="flex items-center justify-center gap-2 h-[42px] px-6 bg-primary hover:bg-primary/90 text-white rounded-xl shadow-md font-bold transition-all w-full md:w-auto hover:-translate-y-0.5 active:translate-y-0">
                                <span class="material-symbols-outlined text-[18px]">filter_list</span> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-6 md:p-8 bg-surface-container-lowest">
                <div class="space-y-10">
                    @forelse($courseGroups as $group)
                        <div class="border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm bg-surface-container-lowest">
                            <!-- Group Header -->
                            <div class="w-full flex justify-between items-center px-6 py-4 bg-surface-container-low/30 border-b border-outline-variant/20">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
                                        <span class="material-symbols-outlined">library_books</span>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-lg font-headline text-on-surface leading-tight">{{ $group['nama_matkul'] }}</h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs font-bold font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded uppercase">{{ $group['kode_matkul'] }}</span>
                                            @if($group['semester'])
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-widest bg-primary-container text-on-primary border border-primary/20">
                                                    {{ $group['semester']->name }} ({{ $group['semester']->academicYear?->year_start ?? '' }}/{{ $group['semester']->academicYear?->year_end ?? '' }})
                                                </span>
                                            @endif
                                            @if($group['courses']->count() > 1)
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-widest bg-secondary-container text-secondary border border-secondary/20">
                                                    {{ $group['courses']->count() }} kelas
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="hidden sm:flex gap-4">
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-on-surface-variant tracking-widest mb-0.5">Tugas</p>
                                        <p class="text-base font-extrabold text-on-surface leading-none">{{ $group['total_assignments'] }}</p>
                                    </div>
                                    <div class="w-px h-8 bg-outline-variant/30"></div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-on-surface-variant tracking-widest mb-0.5">Mhs</p>
                                        <p class="text-base font-extrabold text-on-surface leading-none">{{ $group['total_students'] }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Per-kelas tabs (Alpine) --}}
                            <div x-data="{ activeKelas: {{ $group['courses']->first()->id }} }">
                                @if($group['courses']->count() > 1)
                                    <div class="flex gap-0 px-6 pt-3 border-b border-outline-variant/20 overflow-x-auto">
                                        @foreach($group['courses'] as $kelasCourse)
                                            <button @click="activeKelas = {{ $kelasCourse->id }}"
                                                    :class="activeKelas === {{ $kelasCourse->id }}
                                                        ? 'border-primary text-primary bg-primary/5'
                                                        : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                                                    class="px-4 py-2.5 text-xs font-bold border-b-2 transition-colors whitespace-nowrap shrink-0 -mb-px">
                                                {{ $kelasCourse->studentClass?->name ?? 'Tanpa Kelas' }}
                                                <span class="ml-1 text-[10px] opacity-70">({{ $kelasCourse->students->count() }})</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                @foreach($group['courses'] as $kelasCourse)
                                    <div x-show="activeKelas === {{ $kelasCourse->id }}" x-cloak>
                                        @include('dosen.grades._kelas_table', ['course' => $kelasCourse])
                                    </div>
                                @endforeach
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
            document.addEventListener('DOMContentLoaded', function () {
                const yearSelect    = document.getElementById('academic_year_id');
                const semesterSelect = document.getElementById('semester_id');
                const deptSelect    = document.getElementById('department_id');
                const progSelect    = document.getElementById('study_program_id');
                const classSelect   = document.getElementById('student_class_id');

                // ── Year → Semester chaining ──────────────────────────────────────
                yearSelect.addEventListener('change', function () {
                    const yearId  = this.value;
                    semesterSelect.querySelectorAll('option').forEach(opt => {
                        if (!opt.value) { opt.style.display = ''; return; }
                        opt.style.display = (!yearId || opt.dataset.year === yearId) ? '' : 'none';
                    });
                    if (semesterSelect.selectedOptions[0]?.style.display === 'none') {
                        semesterSelect.value = '';
                    }
                });

                // ── Department → StudyProgram → StudentClass chaining ─────────────
                deptSelect.addEventListener('change', function () {
                    const deptId = this.value;
                    // Filter prodi options
                    progSelect.querySelectorAll('option').forEach(opt => {
                        if (!opt.value) { opt.style.display = ''; return; }
                        opt.style.display = (!deptId || opt.dataset.department === deptId) ? '' : 'none';
                    });
                    if (progSelect.selectedOptions[0]?.style.display === 'none') progSelect.value = '';
                    // Cascade to kelas
                    progSelect.dispatchEvent(new Event('change'));
                });

                progSelect.addEventListener('change', function () {
                    const progId = this.value;
                    classSelect.querySelectorAll('option').forEach(opt => {
                        if (!opt.value) { opt.style.display = ''; return; }
                        opt.style.display = (!progId || opt.dataset.program === progId) ? '' : 'none';
                    });
                    if (classSelect.selectedOptions[0]?.style.display === 'none') classSelect.value = '';
                });

                // Trigger initially in case page was re-rendered with active filters
                if (yearSelect.value) yearSelect.dispatchEvent(new Event('change'));
                if (deptSelect.value) deptSelect.dispatchEvent(new Event('change'));
            });
        </script>
    @endpush
</x-app-layout>
