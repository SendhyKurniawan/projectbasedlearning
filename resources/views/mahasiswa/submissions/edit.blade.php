<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Submission') }}
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
                        
                        <div class="mt-3 text-sm">
                            <p class="text-gray-600 dark:text-gray-400">
                                <strong>Dikumpulkan:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form action="{{ route('mahasiswa.submissions.update', $submission) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Catatan
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Tambahkan catatan untuk dosen...">{{ old('notes', $submission->notes) }}</textarea>
                            @error('notes')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Submission File/Link -->
                        @if($assignment->submission_format === 'url' && $submission->url_link)
                            <div class="mb-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    <strong>Link URL saat ini:</strong> 
                                    <a href="{{ $submission->url_link }}" 
                                       target="_blank"
                                       class="text-blue-600 dark:text-blue-400 hover:underline break-all">
                                        {{ $submission->url_link }}
                                    </a>
                                </p>
                            </div>
                        @elseif($assignment->submission_format === 'pdf' && $submission->file_path)
                            <div class="mb-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    <strong>File saat ini:</strong> 
                                    <a href="{{ Storage::url($submission->file_path) }}" 
                                       target="_blank"
                                       class="text-blue-600 dark:text-blue-400 hover:underline break-all">
                                        {{ basename($submission->file_path) }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        <!-- New File/Link Upload -->
                        @if($assignment->submission_format === 'url')
                            <div class="mb-6">
                                <label for="url_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Ubah Link URL (Opsional)
                                </label>
                                <input type="url" 
                                       name="url_link" 
                                       id="url_link"
                                       value="{{ old('url_link', $submission->url_link) }}"
                                       placeholder="https://..."
                                       class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Kosongkan jika tidak ingin mengubah link
                                </p>
                                @error('url_link')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div class="mb-6">
                                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Upload File Baru (Opsional, Max 10MB)
                                </label>
                                <input type="file" 
                                       name="file" 
                                       id="file"
                                       class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Kosongkan jika tidak ingin mengubah file
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
                                Update Submission
                            </button>
                            <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}"
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
