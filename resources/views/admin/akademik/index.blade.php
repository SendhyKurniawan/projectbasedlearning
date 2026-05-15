<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-on-surface leading-tight">Manajemen Akademik</h2>
    </x-slot>

    <div class="py-6 space-y-6" x-data="akademikPage()">

        @include('admin.akademik.partials.flash')

        {{-- ══════════════════════════════════════════
             KARTU 1: PERIODE (Tahun Akademik + Semester)
        ══════════════════════════════════════════ --}}
        <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">calendar_today</span>
                    <h3 class="font-bold text-on-surface">Periode Akademik</h3>
                </div>
                <button @click="open('ay','create',{})"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-medium hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-sm">add</span> Tahun Akademik
                </button>
            </div>

            {{-- Tahun Akademik tabs --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @forelse($academicYears as $ay)
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.akademik.index', array_merge(request()->query(), ['ay' => $ay->id, 'sem' => null])) }}"
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                               {{ $selectedAy?->id === $ay->id
                                  ? 'bg-primary text-on-primary'
                                  : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                            {{ $ay->year_start }}/{{ $ay->year_end }}
                            @if($ay->is_active)
                                <span class="ml-1 text-[10px] bg-white/20 px-1 rounded">Aktif</span>
                            @endif
                        </a>
                        <div class="flex items-center gap-0.5">
                            <button @click="open('ay','edit',{{ json_encode(['id'=>$ay->id,'year_start'=>$ay->year_start,'year_end'=>$ay->year_end,'is_active'=>$ay->is_active]) }})"
                                    class="p-1 rounded text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            @if(!$ay->is_active)
                            <form action="{{ route('admin.akademik.academic-years.activate', $ay) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" title="Set Aktif"
                                        class="p-1 rounded text-on-surface-variant hover:text-tertiary hover:bg-surface-container transition-colors">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                            </form>
                            <button @click="deleteConfirm={url:'/admin/akademik/academic-years/{{ $ay->id }}',label:'Tahun Akademik {{ $ay->year_start }}/{{ $ay->year_end }}'};open('delete')"
                                    class="p-1 rounded text-on-surface-variant hover:text-error hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-on-surface-variant">Belum ada tahun akademik.</p>
                @endforelse
            </div>

            {{-- Semester chips --}}
            @if($selectedAy)
            <div class="border-t border-outline-variant/30 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wide">
                        Semester — {{ $selectedAy->year_start }}/{{ $selectedAy->year_end }}
                    </span>
                    <button @click="open('sem','create',{academic_year_id:{{ $selectedAy->id }}})"
                            class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container text-on-surface-variant text-xs font-medium hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-sm">add</span> Semester
                    </button>
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse($semesters as $sem)
                        <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-container">
                            <a href="{{ route('admin.akademik.index', array_merge(request()->query(), ['ay' => $selectedAy->id, 'sem' => $sem->id])) }}"
                               class="px-3 py-1 rounded-lg text-sm font-medium transition-colors
                                   {{ $selectedSem?->id === $sem->id
                                      ? 'bg-secondary text-on-secondary'
                                      : 'text-on-surface-variant hover:text-on-surface' }}">
                                {{ $sem->name }}
                                @if($sem->is_active)
                                    <span class="ml-1 px-1 text-[10px] {{ $selectedSem?->id === $sem->id ? 'bg-white/20' : 'bg-primary/10 text-primary' }} rounded">Aktif</span>
                                @endif
                            </a>
                            <button @click="open('sem','edit',{{ json_encode(['id'=>$sem->id,'academic_year_id'=>$sem->academic_year_id,'name'=>$sem->name,'start_date'=>$sem->start_date?->format('Y-m-d'),'end_date'=>$sem->end_date?->format('Y-m-d'),'is_active'=>$sem->is_active]) }})"
                                    class="p-1 rounded text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            @if(!$sem->is_active)
                            <form action="{{ route('admin.akademik.semesters.activate', $sem) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" title="Set Aktif" class="p-1 rounded text-on-surface-variant hover:text-tertiary transition-colors">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                            </form>
                            <button @click="deleteConfirm={url:'/admin/akademik/semesters/{{ $sem->id }}',label:'Semester {{ $sem->name }}'};open('delete')"
                                    class="p-1 rounded text-on-surface-variant hover:text-error transition-colors">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-on-surface-variant">Belum ada semester untuk tahun akademik ini.</p>
                    @endforelse
                </div>
            </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════
             KARTU 2: JURUSAN & PRODI
        ══════════════════════════════════════════ --}}
        <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">account_balance</span>
                <h3 class="font-bold text-on-surface">Jurusan &amp; Program Studi</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Kolom Jurusan --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wide">Jurusan</span>
                        <button @click="open('dep','create',{})"
                                class="flex items-center gap-1 px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant text-xs font-medium hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined text-sm">add</span> Jurusan
                        </button>
                    </div>
                    <div class="space-y-1">
                        @forelse($departments as $dep)
                            @php $depActive = $selectedDep?->id === $dep->id; @endphp
                            <div class="flex items-center gap-1 rounded-xl border-l-[3px] transition-all
                                {{ $depActive
                                    ? 'border-l-primary bg-primary/[0.08]'
                                    : 'border-l-transparent bg-surface-container-high/60 hover:bg-surface-container-high' }}">
                                <a href="{{ route('admin.akademik.index', array_merge(request()->query(), ['dep' => $dep->id, 'prog' => null])) }}"
                                   class="flex-1 flex items-center gap-2 px-3 py-2.5 rounded-l-xl">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold
                                        {{ $depActive ? 'bg-primary text-on-primary' : 'bg-on-surface/10 text-on-surface-variant' }}">{{ $dep->code }}</span>
                                    <div>
                                        <p class="text-sm font-medium {{ $depActive ? 'text-primary' : 'text-on-surface' }}">{{ $dep->name }}</p>
                                        <p class="text-xs {{ $depActive ? 'text-primary/70' : 'text-on-surface-variant' }}">{{ $dep->study_programs_count }} prodi</p>
                                    </div>
                                </a>
                                <div class="flex items-center gap-0.5 pr-2">
                                    <button @click="open('dep','edit',{{ json_encode(['id'=>$dep->id,'name'=>$dep->name,'code'=>$dep->code]) }})"
                                            class="p-1 rounded text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <button @click="deleteConfirm={url:'/admin/akademik/departments/{{ $dep->id }}',label:'Jurusan {{ addslashes($dep->name) }}'};open('delete')"
                                            class="p-1 rounded text-on-surface-variant hover:text-error transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-on-surface-variant px-3 py-2">Belum ada jurusan.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Kolom Prodi --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wide">
                            Program Studi{{ $selectedDep ? ' — '.$selectedDep->name : '' }}
                        </span>
                        @if($selectedDep)
                        <button @click="open('prog','create',{department_id:{{ $selectedDep->id }}})"
                                class="flex items-center gap-1 px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant text-xs font-medium hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined text-sm">add</span> Prodi
                        </button>
                        @endif
                    </div>
                    <div class="space-y-1">
                        @forelse($studyPrograms as $prog)
                            @php $progActive = $selectedProg?->id === $prog->id; @endphp
                            <div class="flex items-center gap-1 rounded-xl border-l-[3px] transition-all
                                {{ $progActive
                                    ? 'border-l-tertiary bg-tertiary/[0.08]'
                                    : 'border-l-transparent bg-surface-container-high/60 hover:bg-surface-container-high' }}">
                                <a href="{{ route('admin.akademik.index', array_merge(request()->query(), ['dep' => $selectedDep->id, 'prog' => $prog->id])) }}"
                                   class="flex-1 flex items-center gap-2 px-3 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                                        {{ $progActive ? 'bg-tertiary text-white' : 'bg-on-surface/10 text-on-surface-variant' }}">{{ $prog->level }}</span>
                                    <div>
                                        <p class="text-sm font-medium {{ $progActive ? 'text-tertiary' : 'text-on-surface' }}">{{ $prog->name }}</p>
                                        <p class="text-xs {{ $progActive ? 'text-tertiary/70' : 'text-on-surface-variant' }}">{{ $prog->student_classes_count }} kelas · {{ $prog->code }}</p>
                                    </div>
                                </a>
                                <div class="flex items-center gap-0.5 pr-2">
                                    <button @click="open('prog','edit',{{ json_encode(['id'=>$prog->id,'department_id'=>$prog->department_id,'name'=>$prog->name,'code'=>$prog->code,'level'=>$prog->level]) }})"
                                            class="p-1 rounded text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <button @click="deleteConfirm={url:'/admin/akademik/study-programs/{{ $prog->id }}',label:'Prodi {{ addslashes($prog->name) }}'};open('delete')"
                                            class="p-1 rounded text-on-surface-variant hover:text-error transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-on-surface-variant px-3 py-2">
                                {{ $selectedDep ? 'Belum ada prodi untuk jurusan ini.' : 'Pilih jurusan terlebih dahulu.' }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             KARTU 3: KELAS
        ══════════════════════════════════════════ --}}
        @if($selectedProg && $selectedSem)
        <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary" style="font-variation-settings:'FILL' 1;">groups</span>
                    <h3 class="font-bold text-on-surface">Kelas</h3>
                </div>
                <button @click="open('class','create',{study_program_id:{{ $selectedProg->id }},semester_id:{{ $selectedSem->id }}})"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-tertiary text-white text-xs font-medium hover:bg-tertiary/90 transition-colors">
                    <span class="material-symbols-outlined text-sm">add</span> Kelas
                </button>
            </div>
            <p class="text-xs text-on-surface-variant mb-4">
                {{ $selectedProg->name }} — Semester {{ $selectedSem->name }}
                ({{ $selectedSem->academicYear->year_start ?? '' }}/{{ $selectedSem->academicYear->year_end ?? '' }})
            </p>

            <div class="space-y-3">
                @forelse($classes as $kelas)
                <div x-data="{ expanded: false }" class="border border-outline-variant/40 rounded-xl overflow-hidden">
                    {{-- Header Kelas --}}
                    <div class="flex items-center gap-3 px-4 py-3 bg-surface-container cursor-pointer"
                         @click="expanded = !expanded">
                        <span class="material-symbols-outlined text-sm text-on-surface-variant transition-transform duration-200"
                              :class="expanded ? 'rotate-90' : ''">chevron_right</span>
                        <span class="material-symbols-outlined text-tertiary text-base" style="font-variation-settings:'FILL' 1;">meeting_room</span>
                        <span class="font-semibold text-on-surface">{{ $kelas->name }}</span>
                        <span class="ml-auto flex items-center gap-2 text-xs text-on-surface-variant">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">person</span>
                                {{ $kelas->students->count() }} mahasiswa
                            </span>
                        </span>
                        <div class="flex items-center gap-1 ml-2" @click.stop>
                            <button @click="open('class','edit',{{ json_encode(['id'=>$kelas->id,'name'=>$kelas->name,'study_program_id'=>$kelas->study_program_id,'semester_id'=>$kelas->semester_id]) }})"
                                    class="p-1 rounded text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button @click="deleteConfirm={url:'/admin/akademik/classes/{{ $kelas->id }}',label:'Kelas {{ addslashes($kelas->name) }}'};open('delete')"
                                    class="p-1 rounded text-on-surface-variant hover:text-error transition-colors">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>

                    {{-- Expandable: Mahasiswa only --}}
                    <div x-show="expanded" x-transition class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wide">Mahasiswa</span>
                            <button @click="open('assign','create',{class_id:{{ $kelas->id }},class_name:'{{ addslashes($kelas->name) }}'})"
                                    class="flex items-center gap-1 px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant text-xs font-medium hover:bg-surface-container-high transition-colors">
                                <span class="material-symbols-outlined text-sm">person_add</span> Assign
                            </button>
                        </div>
                        @if($kelas->students->isEmpty())
                            <p class="text-xs text-on-surface-variant">Belum ada mahasiswa di kelas ini.</p>
                        @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($kelas->students as $student)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-xs">
                                <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </span>
                                <span class="text-on-surface font-medium">{{ $student->name }}</span>
                                <form action="{{ route('admin.akademik.classes.unassign-student', [$kelas, $student]) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-error transition-colors ml-0.5" title="Unassign">
                                        <span class="material-symbols-outlined text-xs">close</span>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                    <div class="text-center py-10 text-on-surface-variant">
                        <span class="material-symbols-outlined text-4xl block mb-2">meeting_room</span>
                        <p class="text-sm">Belum ada kelas untuk prodi dan semester ini.</p>
                        <button @click="open('class','create',{study_program_id:{{ $selectedProg->id }},semester_id:{{ $selectedSem->id }}})"
                                class="mt-3 px-4 py-2 rounded-lg bg-tertiary text-on-tertiary text-sm font-medium hover:bg-tertiary/90 transition-colors">
                            + Tambah Kelas Pertama
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             KARTU 4: MATA KULIAH
        ══════════════════════════════════════════ --}}
        <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">auto_stories</span>
                <h3 class="font-bold text-on-surface">Mata Kuliah</h3>
            </div>
            <p class="text-xs text-on-surface-variant mb-4">
                {{ $selectedProg->name }} — Semester {{ $selectedSem->name }}
                ({{ $selectedSem->academicYear->year_start ?? '' }}/{{ $selectedSem->academicYear->year_end ?? '' }})
            </p>

            {{-- Section 1: MK Semester --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wide flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-secondary">hub</span>
                        MK Semester (diwarisi semua kelas)
                    </span>
                    <button @click="open('course','create',{scope:'semester',semester_id:{{ $selectedSem->id }}})"
                            class="flex items-center gap-1 px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant text-xs font-medium hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-sm">add</span> MK Semester
                    </button>
                </div>
                @if($semesterCourses->isEmpty())
                    <p class="text-xs text-on-surface-variant px-3 py-2 bg-surface-container rounded-lg">Belum ada MK semester.</p>
                @else
                <div class="overflow-x-auto rounded-xl border border-outline-variant/30">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container">
                            <tr>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Kode</th>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Nama MK</th>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Dosen</th>
                                <th class="text-center px-4 py-2 text-xs text-on-surface-variant font-bold">SKS</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @foreach($semesterCourses as $course)
                            <tr class="hover:bg-surface-container/50">
                                <td class="px-4 py-2 font-mono text-xs text-primary">{{ $course->kode_matkul }}</td>
                                <td class="px-4 py-2 font-medium">{{ $course->nama_matkul }}</td>
                                <td class="px-4 py-2 text-on-surface-variant">{{ $course->dosen->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">{{ $course->sks }}</td>
                                <td class="px-4 py-2 text-right">
                                    <button @click="deleteConfirm={url:'/admin/akademik/courses/{{ $course->id }}',label:'MK {{ addslashes($course->nama_matkul) }}'};open('delete')"
                                            class="p-1 rounded text-on-surface-variant hover:text-error transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Section 2: MK Khusus Kelas --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wide flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-tertiary">class</span>
                        MK Khusus Kelas
                    </span>
                    @if($classes->isNotEmpty())
                    <button @click="open('course','create',{scope:'class',semester_id:{{ $selectedSem->id }}})"
                            class="flex items-center gap-1 px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant text-xs font-medium hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-sm">add</span> MK Khusus
                    </button>
                    @endif
                </div>
                @if($classes->isEmpty())
                    <p class="text-xs text-on-surface-variant px-3 py-2 bg-surface-container rounded-lg">
                        Buat kelas terlebih dahulu untuk menambahkan MK khusus kelas.
                    </p>
                @elseif($classCourses->isEmpty())
                    <p class="text-xs text-on-surface-variant px-3 py-2 bg-surface-container rounded-lg">Belum ada MK khusus kelas.</p>
                @else
                <div class="overflow-x-auto rounded-xl border border-outline-variant/30">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container">
                            <tr>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Kode</th>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Nama MK</th>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Kelas</th>
                                <th class="text-left px-4 py-2 text-xs text-on-surface-variant font-bold">Dosen</th>
                                <th class="text-center px-4 py-2 text-xs text-on-surface-variant font-bold">SKS</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @foreach($classCourses as $course)
                            <tr class="hover:bg-surface-container/50">
                                <td class="px-4 py-2 font-mono text-xs text-primary">{{ $course->kode_matkul }}</td>
                                <td class="px-4 py-2 font-medium">{{ $course->nama_matkul }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-tertiary/10 text-tertiary">
                                        {{ $course->studentClass->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-on-surface-variant">{{ $course->dosen->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">{{ $course->sks }}</td>
                                <td class="px-4 py-2 text-right">
                                    <button @click="deleteConfirm={url:'/admin/akademik/courses/{{ $course->id }}',label:'MK {{ addslashes($course->nama_matkul) }}'};open('delete')"
                                            class="p-1 rounded text-on-surface-variant hover:text-error transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        @elseif(!$selectedProg || !$selectedSem)
        <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm">
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl block mb-2">info</span>
                <p class="text-sm">Pilih <strong>Prodi</strong> dan <strong>Semester</strong> untuk melihat kelas &amp; mata kuliah.</p>
            </div>
        </div>
        @endif

        {{-- ══════ MODALS ══════ --}}
        @include('admin.akademik.modals.academic-year-form')
        @include('admin.akademik.modals.semester-form')
        @include('admin.akademik.modals.department-form')
        @include('admin.akademik.modals.study-program-form')
        @include('admin.akademik.modals.class-form')
        @include('admin.akademik.modals.course-form')
        @include('admin.akademik.modals.assign-students')
        @include('admin.akademik.modals.confirm-delete')

    </div>

    <script>
    function akademikPage() {
        return {
            activeModal: null,
            modalMode: 'create',
            modalPayload: {},
            deleteConfirm: { url: '', label: '' },

            open(modal, mode = 'create', payload = {}) {
                this.activeModal = modal;
                this.modalMode   = mode;
                this.modalPayload = payload;
            },
            close() { this.activeModal = null; this.modalPayload = {}; },
        };
    }
    </script>
</x-app-layout>
