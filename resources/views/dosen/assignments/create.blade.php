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
                    <form action="{{ route('dosen.assignments.store', $course) }}" method="POST" x-data="{ type: '{{ old('type', 'tugas') }}', has_duration: {{ old('has_duration', 'true') === 'true' ? 'true' : 'false' }}, submission_format: '{{ old('submission_format', 'pdf') }}' }">
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
                                <option value="tugas">Tugas (Upload File/Link)</option>
                                <option value="quiz">Quiz (Pilihan Ganda)</option>
                                <option value="quiz">Quiz (Essay)</option>
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

                        <!-- Tugas Specific Fields -->
                        <div x-show="type === 'tugas'" class="mb-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <label for="submission_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Format Pengumpulan <span class="text-red-500">*</span>
                            </label>
                            <select name="submission_format" 
                                    id="submission_format" 
                                    x-model="submission_format"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :required="type === 'tugas'"
                                    :disabled="type !== 'tugas'">
                                <option value="pdf">File Upload (PDF, DOCX, ZIP, dll)</option>
                                <option value="url">Link URL (Github, GDrive, Youtube, dll)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Pilih apakah mahasiswa mengumpulkan dalam bentuk unggah file (Max 10MB) atau sekedar input kolom teks Link URL.
                            </p>
                            @error('submission_format')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quiz Specific Fields -->
                        <div x-show="type === 'quiz'" class="mb-4">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Durasi (Menit) <span class="text-red-500" x-show="has_duration">*</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="has_duration" value="true" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" x-model="has_duration" :disabled="type !== 'quiz'">
                                        <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Gunakan Batas Waktu</span>
                                    </label>
                                </div>
                                <div x-show="has_duration">
                                    <input type="number" 
                                           name="duration_minutes" 
                                           id="duration_minutes" 
                                           value="{{ old('duration_minutes') }}"
                                           class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           :required="type === 'quiz' && has_duration"
                                           :disabled="type !== 'quiz' || !has_duration">
                                    @error('duration_minutes')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-show="!has_duration">
                                    Kuis tidak memiliki batasan waktu (Unlimited).
                                </p>
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
