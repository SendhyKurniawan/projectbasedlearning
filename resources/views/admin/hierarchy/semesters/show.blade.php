@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex flex-wrap items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.hierarchy.departments.index') }}" class="hover:text-primary transition-colors">Struktur</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('admin.hierarchy.departments.show', $studyProgram->department_id) }}" class="hover:text-primary transition-colors">{{ $studyProgram->department->name }}</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('admin.hierarchy.study-programs.show', $studyProgram) }}" class="hover:text-primary transition-colors">{{ $studyProgram->name }}</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">{{ $semester->name }}</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">{{ $semester->name }} <span class="text-on-surface-variant text-xl">· TA {{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }}</span></h1>
 <p class="mt-1 text-sm text-on-surface-variant">Kelola kelas mahasiswa dan plot mata kuliah pada periode ini.</p>
 </div>
 </div>

 <div class="space-y-6">
 
 {{-- Flash Messages --}}
 @if(session('success'))
 <div class="px-4 py-3 bg-secondary-container border border-secondary text-secondary rounded-lg text-sm">
 {{ session('success') }}
 </div>
 @endif

 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 {{-- Kelas Tersedia --}}
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <div class="flex justify-between items-center mb-4">
 <h3 class="text-lg font-medium text-on-surface">Daftar Kelas</h3>
 <a href="{{ route('admin.student-classes.create') }}" class="text-sm text-primary hover:underline">
 + Kelas Baru
 </a>
 </div>
 
 <div class="space-y-3">
 @forelse($classes as $kelas)
 <a href="{{ route('admin.hierarchy.student-classes.show', $kelas) }}" 
 class="flex items-center justify-between p-4 bg-surface-container-low/50 border border-surface-container-low rounded-lg hover:bg-surface-container-low transition">
 <div>
 <h4 class="font-bold text-on-surface">{{ $kelas->name }}</h4>
 <p class="text-xs text-on-surface-variant">{{ $kelas->students_count }} Mahasiswa Terdaftar</p>
 </div>
 <svg class="w-5 h-5 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
 </svg>
 </a>
 @empty
 <div class="p-4 text-center text-sm text-on-surface-variant bg-surface-container-low/50 rounded-lg border border-dashed border-outline-variant/30">
 Belum ada kelas yang ditentukan untuk prodi dan semester ini.
 </div>
 @endforelse
 </div>
 </div>
 </div>

 {{-- Mata Kuliah Semester (Diwariskan) --}}
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <div class="flex justify-between items-center mb-4">
 <div>
 <h3 class="text-lg font-medium text-on-surface">Mata Kuliah Semester</h3>
 <p class="text-xs text-on-surface-variant">Atur mata kuliah yang akan diikuti oleh semua kelas di semester ini</p>
 </div>
 </div>

 {{-- Form Tambah Mata Kuliah (X-Data Toggle) --}}
 <div x-data="{ open: false }" class="mb-6">
 <button @click="open = !open" class="mb-4 text-sm px-3 py-1.5 bg-primary-container text-on-primary font-semibold rounded-md border border-primary/20 hover:bg-primary-container/80 transition">
 + Tambah Mata Kuliah
 </button>

 <div x-show="open" x-transition class="p-4 bg-surface-container-low/50 rounded-lg border border-surface-container-low mb-4">
 <form action="{{ route('admin.hierarchy.study-programs.semesters.add-course', [$studyProgram, $semester]) }}" method="POST">
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
 <div class="mb-4">
 <x-input-label for="description" value="Deskripsi (Opsional)" />
 <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 shadow-sm text-sm"></textarea>
 </div>
 <div class="flex justify-end gap-2">
 <button type="button" @click="open = false" class="px-3 py-1.5 text-sm text-on-surface-variant bg-surface-container-lowest border border-outline-variant/30 rounded-md hover:bg-surface-bright">Batal</button>
 <x-primary-button class="text-sm">Simpan Course</x-primary-button>
 </div>
 </form>
 </div>
 </div>

 {{-- Daftar Mata Kuliah --}}
 <div class="space-y-3">
 @forelse($semesterCourses as $course)
 <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-surface-container-lowest border-l-4 border-l-primary border-y border-r border-surface-container-low rounded-r-lg shadow-sm">
 <div class="mb-2 sm:mb-0">
 <h4 class="font-bold text-on-surface">{{ $course->nama_matkul }}</h4>
 <div class="text-xs text-on-surface-variant mt-1 flex flex-wrap gap-2">
 <span class="px-2 py-0.5 rounded bg-surface-container-low font-mono">{{ $course->kode_matkul }}</span>
 <span class="px-2 py-0.5 rounded bg-surface-container-low ">{{ $course->sks }} SKS</span>
 <span class="px-2 py-0.5 rounded bg-primary-container text-on-primary">Dosen: {{ $course->dosen->name }}</span>
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
 Belum ada mata kuliah yang di-assign untuk semester ini.
 </div>
 @endforelse
 </div>

 </div>
 </div>
 </div>

 </div>
 </div>
</x-app-layout>
