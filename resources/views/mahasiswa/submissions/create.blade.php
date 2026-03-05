<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kumpulkan Tugas') }}
            </h2>
            <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali ke Course
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Assignment Info -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $assignment->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $assignment->course->name }}</p>
                        
                        @if($assignment->description)
                            <p class="text-gray-700 dark:text-gray-300 mt-3">{{ $assignment->description }}</p>
                        @endif
                        
                        <div class="mt-3 text-sm">
                            <p class="text-gray-600 dark:text-gray-400">
                                <strong>Deadline:</strong> {{ $assignment->deadline->format('d M Y, H:i') }}
                            </p>
                            <p class="text-gray-600 dark:text-gray-400">
                                <strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}
                            </p>
                        </div>
                    </div>

                    @if($existing)
                        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded p-4 mb-6">
                            <p class="text-yellow-800 dark:text-yellow-200">
                                Anda sudah mengumpulkan tugas ini pada {{ $existing->submitted_at->format('d M Y, H:i') }}.
                                Anda dapat mengedit atau menghapus submission dari halaman course.
                            </p>
                        </div>
                        
                        <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}"
                           class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                            Kembali ke Course
                        </a>
                    @else
                        <!-- Submission Form -->
                        <form action="{{ route('mahasiswa.submissions.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                            <!-- Notes -->
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Catatan (Opsional)
                                </label>
                                <textarea name="notes" 
                                          id="notes" 
                                          rows="4"
                                          class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Tambahkan catatan untuk dosen...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($assignment->submission_format === 'url')
                                <!-- URL Link Input -->
                                <div class="mb-6">
                                    <label for="url_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Link URL (Wajib)
                                    </label>
                                    <input type="url" 
                                           name="url_link" 
                                           id="url_link"
                                           value="{{ old('url_link') }}"
                                           placeholder="https://..."
                                           required
                                           class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Masukkan link hasil pengerjaan (Misal: Google Drive, Github, Youtube). Pastikan link dapat diakses.
                                    </p>
                                    @error('url_link')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <!-- File Upload -->
                                <div class="mb-6">
                                    <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Upload File (Wajib, Max 10MB)
                                    </label>
                                    <input type="file" 
                                           name="file" 
                                           id="file"
                                           required
                                           class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Format yang didukung: PDF, DOC, DOCX, ZIP, RAR, JPG, PNG
                                    </p>
                                    @error('file')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <!-- Submit Buttons -->
                            <div class="flex gap-3">
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                                    Kumpulkan Tugas
                                </button>
                                <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}"
                                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded">
                                    Batal
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
