<x-app-layout>
 <x-slot name="header">
 <div class="flex items-center gap-4">
 <a href="{{ route('admin.student-classes.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Edit Kelas') }}
 </h2>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form action="{{ route('admin.student-classes.update', $studentClass) }}" method="POST">
 @csrf
 @method('PUT')
 <div class="mb-4">
 <x-input-label for="study_program_id" :value="__('Program Studi')" />
 <select id="study_program_id" name="study_program_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>
 <option value="">Pilih Prodi</option>
 @foreach($studyPrograms as $prodi)
 <option value="{{ $prodi->id }}" {{ old('study_program_id', $studentClass->study_program_id) == $prodi->id ? 'selected' : '' }}>{{ $prodi->name }} ({{ $prodi->level }})</option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('study_program_id')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="semester_id" :value="__('Semester Aktif')" />
 <select id="semester_id" name="semester_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>
 <option value="">Pilih Semester</option>
 @foreach($semesters as $sem)
 <option value="{{ $sem->id }}" {{ old('semester_id', $studentClass->semester_id) == $sem->id ? 'selected' : '' }}>{{ $sem->name }} ({{ $sem->academicYear->year_start }}/{{ $sem->academicYear->year_end }})</option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('semester_id')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="name" :value="__('Nama Kelas')" />
 <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $studentClass->name)" required placeholder="Contoh: TI-1A" />
 <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button class="ml-4">
 {{ __('Perbarui') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
