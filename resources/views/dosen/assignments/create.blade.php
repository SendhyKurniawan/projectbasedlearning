<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tambah Tugas - {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('dosen.assignments.index', $course) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('dosen.assignments.store', $course) }}" method="POST" x-data="{ type: '{{ old('type', 'tugas') }}' }">
                        @csrf

                        <!-- Assignment Type -->
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipe Assignment <span class="text-red-500">*</span>
                            </label>
                            <select name="type" 
                                    id="type" 
                                    x-model="type"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>
                                <option value="tugas">Tugas (Upload File)</option>
                                <option value="quiz">Quiz (Pilihan Ganda)</option>
                                <option value="quiz">Quiz (Essay)</option>
                                <option value="project">Project (Upload File/Link)</option>
                                <option value="exercise">Exercise (Coding)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1" x-show="type === 'quiz'">
                                * Setelah menyimpan, Anda akan diarahkan untuk mengisi pertanyaan.
                            </p>
                            @error('type')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Judul Tugas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   value="{{ old('title') }}"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('title')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Deskripsi Tugas
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="5"
                                      class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Jelaskan detail tugas...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quiz Specific Fields -->
                        <div x-show="type === 'quiz'" class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="quiz_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nomor Quiz <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="quiz_number" 
                                       id="quiz_number" 
                                       value="{{ old('quiz_number') }}"
                                       class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       :required="type === 'quiz'">
                                @error('quiz_number')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Durasi (Menit) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="duration_minutes" 
                                       id="duration_minutes" 
                                       value="{{ old('duration_minutes') }}"
                                       class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       :required="type === 'quiz'">
                                @error('duration_minutes')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div class="mb-4">
                            <label for="deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Deadline <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" 
                                   name="deadline" 
                                   id="deadline" 
                                   value="{{ old('deadline') }}"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('deadline')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Score -->
                        <div class="mb-6">
                            <label for="max_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nilai Maksimal <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="max_score" 
                                   id="max_score" 
                                   value="{{ old('max_score', 100) }}"
                                   min="1"
                                   max="100"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('max_score')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-3">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                                Simpan Tugas
                            </button>
                            <a href="{{ route('dosen.assignments.index', $course) }}"
                               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
