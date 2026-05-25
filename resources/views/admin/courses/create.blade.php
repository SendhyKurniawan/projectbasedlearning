@push('styles')
    @vite('resources/css/pages/admin/courses.css')
@endpush
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('admin.courses.index') }}" class="hover:text-primary transition-colors">Mata Kuliah</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Tambah</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Tambah Mata Kuliah</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Buat mata kuliah baru dan tetapkan dosen pengampu.</p>
 </div>
 <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 
 <form method="POST" action="{{ route('admin.courses.store') }}">
 @csrf

 <!-- Code -->
 <div class="form-group">
 <label class="form-label" for="kode_matkul">Course Code</label>
 <input class="form-input" id="kode_matkul" type="text" name="kode_matkul" value="{{ old('kode_matkul') }}" placeholder="e.g. WEB101" required autofocus>
 @error('kode_matkul')
 <span class="form-error">{{ $message }}</span>
 @enderror
 </div>

 <!-- Name -->
 <div class="form-group">
 <label class="form-label" for="nama_matkul">Course Name</label>
 <input class="form-input" id="nama_matkul" type="text" name="nama_matkul" value="{{ old('nama_matkul') }}" placeholder="e.g. Web Development Basics" required>
 @error('nama_matkul')
 <span class="form-error">{{ $message }}</span>
 @enderror
 </div>

 <!-- Description -->
 <div class="form-group">
 <label class="form-label" for="description">Description</label>
 <textarea class="form-textarea" id="description" name="description">{{ old('description') }}</textarea>
 @error('description')
 <span class="form-error">{{ $message }}</span>
 @enderror
 </div>

 <!-- Dosen -->
 <div class="form-group">
 <label class="form-label" for="dosen_id">Assign Lecturer</label>
 <select class="form-select" id="dosen_id" name="dosen_id" required>
 <option value="" disabled selected>Select Lecturer</option>
 @foreach($dosens as $dosen)
 <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
 {{ $dosen->name }} ({{ $dosen->email }})
 </option>
 @endforeach
 </select>
 @error('dosen_id')
 <span class="form-error">{{ $message }}</span>
 @enderror
 </div>

 <!-- Class (Optional) -->
 <div class="form-group">
 <label class="form-label" for="student_class_id">Kelas (Opsional)</label>
 <select class="form-select" id="student_class_id" name="student_class_id">
 <option value="">Pilih Kelas</option>
 @foreach($classes as $class)
 <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
 {{ $class->name }} ({{ $class->studyProgram->name }})
 </option>
 @endforeach
 </select>
 @error('student_class_id')
 <span class="form-error">{{ $message }}</span>
 @enderror
 </div>

 <!-- Semester -->
 <div class="form-group">
 <label class="form-label" for="semester_id">Semester</label>
 <select class="form-select" id="semester_id" name="semester_id" required>
 <option value="" disabled selected>Pilih Semester</option>
 @foreach($semesters as $semester)
 <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
 {{ $semester->name }} ({{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }})
 </option>
 @endforeach
 </select>
 @error('semester_id')
 <span class="form-error">{{ $message }}</span>
 @enderror
 </div>

 <div class="flex items-center justify-end mt-4">
 <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors mr-3">
 Cancel
 </a>
 <button type="submit" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">
 Create Course
 </button>
 </div>
 </form>

 </div>
 </div>
 </div>
 </div>
</x-app-layout>
