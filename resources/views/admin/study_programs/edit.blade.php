<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.study-programs.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Program Studi') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.study-programs.update', $studyProgram) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-input-label for="department_id" :value="__('Jurusan')" />
                            <select id="department_id" name="department_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Pilih Jurusan</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $studyProgram->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="level" :value="__('Jenjang')" />
                            <select id="level" name="level" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                @foreach(['D3', 'D4', 'S1', 'S2', 'S3'] as $lvl)
                                    <option value="{{ $lvl }}" {{ old('level', $studyProgram->level) == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('level')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="code" :value="__('Kode Prodi')" />
                            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $studyProgram->code)" required />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nama Program Studi')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $studyProgram->name)" required />
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
