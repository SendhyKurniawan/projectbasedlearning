{{-- Form tambah kelas (admin). --}}
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.student-classes.index') }}" class="hover:text-primary transition-colors">Kelas</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Tambah</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Tambah Kelas</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Buat kelas baru pada prodi dan semester.</p>
 </div>
 <a href="{{ route('admin.student-classes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form action="{{ route('admin.student-classes.store') }}" method="POST">
 @csrf
 <div class="mb-4">
 <x-input-label for="study_program_id" :value="__('Program Studi')" />
 <select id="study_program_id" name="study_program_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>
 <option value="">Pilih Prodi</option>
 @foreach($studyPrograms as $prodi)
 <option value="{{ $prodi->id }}" {{ old('study_program_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->name }} ({{ $prodi->level }})</option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('study_program_id')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="semester_id" :value="__('Semester Aktif')" />
 <select id="semester_id" name="semester_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>
 <option value="">Pilih Semester</option>
 @foreach($semesters as $sem)
 <option value="{{ $sem->id }}" {{ old('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->name }} ({{ $sem->academicYear->year_start }}/{{ $sem->academicYear->year_end }})</option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('semester_id')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="name" :value="__('Nama Kelas')" />
 <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required placeholder="Contoh: TI-1A" />
 <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button class="ml-4">
 {{ __('Simpan') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
