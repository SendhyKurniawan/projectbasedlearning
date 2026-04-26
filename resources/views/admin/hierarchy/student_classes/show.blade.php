@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
 <x-slot name="header">
 <div class="flex items-center gap-2 text-sm sm:text-base flex-wrap">
 <a href="{{ route('admin.hierarchy.departments.index') }}" class="text-primary hover:text-primary-hover">
 Data Akademik
 </a>
 <span class="text-on-surface-variant">/</span>
 <a href="{{ route('admin.hierarchy.departments.show', $studentClass->studyProgram->department_id) }}" class="text-primary hover:text-primary-hover">
 {{ $studentClass->studyProgram->department->name }}
 </a>
 <span class="text-on-surface-variant">/</span>
 <a href="{{ route('admin.hierarchy.study-programs.show', $studentClass->study_program_id) }}" class="text-primary hover:text-primary-hover">
 {{ $studentClass->studyProgram->name }}
 </a>
 <span class="text-on-surface-variant">/</span>
 <a href="{{ route('admin.hierarchy.study-programs.semesters.show', [$studentClass->study_program_id, $studentClass->semester_id]) }}" class="text-primary hover:text-primary-hover">
 {{ $studentClass->semester->name }}
 </a>
 <span class="text-on-surface-variant">/</span>
 <h2 class="font-semibold text-on-surface leading-tight">
 Kelas {{ $studentClass->name }}
 </h2>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto space-y-6">
 
 {{-- Flash Messages --}}
 @if(session('success'))
 <div class="px-4 py-3 bg-secondary-container border border-secondary text-secondary rounded-lg text-sm">
 {{ session('success') }}
 </div>
 @endif

 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 {{-- Mahasiswa Terdaftar --}}
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <div class="flex justify-between items-center mb-4">
 <h3 class="text-lg font-medium text-on-surface">Daftar Mahasiswa</h3>
 <span class="text-sm px-2.5 py-1 bg-primary-container text-on-primary rounded-full font-semibold">
 Total: {{ $students->count() }}
 </span>
 </div>
 
 <div class="space-y-2">
 @forelse($students as $student)
 <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-surface-container-low/50 border border-surface-container-low rounded-lg">
 <div class="flex items-center gap-3">
 <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary font-bold text-sm">
 {{ substr($student->name, 0, 1) }}
 </div>
 <div>
 <h4 class="font-bold text-on-surface text-sm">{{ $student->name }}</h4>
 <p class="text-xs text-on-surface-variant font-mono">{{ $student->nim ?? $student->email }}</p>
 </div>
 </div>
 <a href="{{ route('admin.users.edit', $student) }}" class="text-xs text-primary hover:underline mt-2 sm:mt-0">Detail</a>
 </div>
 @empty
 <div class="p-4 text-center text-sm text-on-surface-variant bg-surface-container-low/50 rounded-lg border border-dashed border-outline-variant/30">
 Belum ada mahasiswa yang masuk di kelas ini. <br> Assign mahasiswa ke kelas ini dari <a href="{{ route('admin.users.index') }}" class="text-primary underline">Manajemen User</a>.
 </div>
 @endforelse
 </div>
 </div>
 </div>

 {{-- Mata Kuliah Kelas --}}
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 space-y-6">
 
 {{-- Mata Kuliah Diwariskan (Inherited from Semester) --}}
 <div>
 <div class="flex justify-between items-center mb-4">
 <div>
 <h3 class="text-lg font-medium text-on-surface">Mata Kuliah Semester</h3>
 <p class="text-xs text-on-surface-variant">Diwarisi dari pengaturan Semester. Berlaku untuk seluruh kelas di semester ini.</p>
 </div>
 </div>

 <div class="space-y-2">
 @forelse($inheritedCourses as $course)
 <div class="p-3 bg-surface-container-low/50 border-l-4 border-l-outline-variant border-y border-r border-surface-container-low rounded-r-lg opacity-80">
 <h4 class="font-bold text-on-surface text-sm">{{ $course->nama_matkul }}</h4>
 <div class="text-xs text-on-surface-variant mt-1 flex flex-wrap gap-2">
 <span class="px-2 py-0.5 rounded bg-surface-container-high font-mono">{{ $course->kode_matkul }}</span>
 <span class="px-2 py-0.5 rounded bg-surface-container-high">{{ $course->sks }} SKS</span>
 <span>Dosen: {{ $course->dosen->name }}</span>
 </div>
 </div>
 @empty
 <div class="text-xs text-on-surface-variant ">Tidak ada mata kuliah warisan semester.</div>
 @endforelse
 </div>
 </div>

 <hr class="border-surface-container-low">

 {{-- Mata Kuliah Spesifik Kelas --}}
 <div>
 <div class="flex justify-between items-center mb-4">
 <div>
 <h3 class="text-lg font-medium text-on-surface">Mata Kuliah Khusus Kelas</h3>
 <p class="text-xs text-on-surface-variant">Hanya berlaku untuk kelas {{ $studentClass->name }}</p>
 </div>
 </div>

 {{-- Form Tambah Mata Kuliah Kelas (X-Data Toggle) --}}
 <div x-data="{ open: false }" class="mb-4">
 <button @click="open = !open" class="mb-4 text-xs px-2.5 py-1.5 bg-secondary-container text-secondary font-semibold rounded-md border border-secondary/30 hover:bg-secondary-container/80 transition">
 + Tambah MK Khusus
 </button>

 <div x-show="open" x-transition class="p-4 bg-surface-container-low/50 rounded-lg border border-surface-container-low mb-4">
 <form action="{{ route('admin.hierarchy.student-classes.add-course', $studentClass) }}" method="POST">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
 <div>
 <x-input-label for="kode_matkul" value="Kode MK" />
 <x-text-input id="kode_matkul" name="kode_matkul" type="text" class="mt-1 block w-full text-sm" required />
 </div>
 <div>
 <x-input-label for="nama_matkul" value="Nama Mata Kuliah" />
 <x-text-input id="nama_matkul" name="nama_matkul" type="text" class="mt-1 block w-full text-sm" required />
 </div>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
 <div>
 <x-input-label for="sks" value="SKS" />
 <x-text-input id="sks" name="sks" type="number" class="mt-1 block w-full text-sm" min="1" required />
 </div>
 <div>
 <x-input-label for="dosen_id" value="Dosen Pengampu" />
 <select id="dosen_id" name="dosen_id" class="mt-1 block w-full rounded-md border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 shadow-sm text-sm" required>
 <option value="" disabled selected>-- Pilih Dosen --</option>
 @foreach($dosens as $dosen)
 <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
 @endforeach
 </select>
 </div>
 </div>
 <div class="flex justify-end gap-2 mt-4">
 <button type="button" @click="open = false" class="px-3 py-1.5 text-xs font-semibold text-on-surface-variant bg-surface-container-lowest border border-outline-variant/30 rounded-md hover:bg-surface-bright">Batal</button>
 <button type="submit" class="px-3 py-1.5 bg-secondary border border-transparent rounded-md font-semibold text-xs text-on-secondary uppercase tracking-widest hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2 transition ease-in-out duration-150">Simpan Course</button>
 </div>
 </form>
 </div>
 </div>

 {{-- Daftar Mata Kuliah Spesifik Kelas --}}
 <div class="space-y-2">
 @forelse($classCourses as $course)
 <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-surface-container-lowest border-l-4 border-l-secondary border-y border-r border-surface-container-low rounded-r-lg shadow-sm">
 <div class="mb-2 sm:mb-0">
 <h4 class="font-bold text-on-surface text-sm">{{ $course->nama_matkul }}</h4>
 <div class="text-xs text-on-surface-variant mt-1 flex flex-wrap gap-2">
 <span class="px-2 py-0.5 rounded bg-surface-container-low font-mono">{{ $course->kode_matkul }}</span>
 <span class="px-2 py-0.5 rounded bg-surface-container-low ">{{ $course->sks }} SKS</span>
 <span class="font-semibold text-secondary">Dosen: {{ $course->dosen->name }}</span>
 </div>
 </div>
 <div class="flex items-center gap-2">
 <form action="{{ route('admin.hierarchy.courses.destroy', $course) }}" method="POST">
 @csrf
 @method('DELETE')
 <button type="submit" onclick="return confirm('Hapus mata kuliah {{ addslashes($course->nama_matkul) }}?')" class="text-xs px-2 py-1 text-error hover:text-error/80 border border-error/20 rounded hover:bg-error-container/30 transition">
 Hapus
 </button>
 </form>
 </div>
 </div>
 @empty
 <div class="p-4 text-center text-sm text-on-surface-variant bg-surface-container-low/50 rounded-lg border border-dashed border-outline-variant/30">
 Belum ada mata kuliah khusus untuk kelas ini.
 </div>
 @endforelse
 </div>
 </div>

 </div>
 </div>
 </div>

 </div>
 </div>
</x-app-layout>
