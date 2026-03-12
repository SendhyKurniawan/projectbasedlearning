<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.student-classes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Kelas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.student-classes.update', $studentClass) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-input-label for="study_program_id" :value="__('Program Studi')" />
                            <select id="study_program_id" name="study_program_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Pilih Prodi</option>
                                @foreach($studyPrograms as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('study_program_id', $studentClass->study_program_id) == $prodi->id ? 'selected' : '' }}>{{ $prodi->name }} ({{ $prodi->level }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('study_program_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="semester_id" :value="__('Semester Aktif')" />
                            <select id="semester_id" name="semester_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
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
