<x-app-layout>
    @vite(['resources/js/markdown-editor.js'])
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Materi - {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('dosen.materials.index', $course) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('dosen.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Judul Materi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   value="{{ old('title', $material->title) }}"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('title')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Content with Markdown Editor -->
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Konten Materi (Markdown)
                            </label>
                            <textarea name="content" 
                                      id="content-editor">{{ old('content', $material->content) }}</textarea>
                            
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 p-3 rounded">
                                <strong class="text-gray-700 dark:text-gray-300">Tips Markdown:</strong>
                                <ul class="list-disc ml-5 mt-1 space-y-1">
                                    <li>Heading: <code># Judul</code> atau <code>## Sub Judul</code></li>
                                    <li>Bold: <code>**text tebal**</code> atau Italic: <code>*text miring*</code></li>
                                    <li>Code inline: <code>`kode`</code></li>
                                    <li>Code block: <code>```html</code> (enter) kode (enter) <code>```</code></li>
                                    <li>Link: <code>[text](url)</code> | Gambar: <code>![alt](url)</code></li>
                                    <li>List: <code>- item</code> atau <code>1. item</code></li>
                                </ul>
                            </div>
                            
                            @error('content')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current File -->
                        @if($material->file_path)
                            <div class="mb-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    <strong>File saat ini:</strong> 
                                    <a href="{{ Storage::url($material->file_path) }}" 
                                       target="_blank"
                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ basename($material->file_path) }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        <!-- New File Upload -->
                        <div class="mb-6">
                            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Upload File Baru (Opsional, Max 20MB)
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

                        <!-- Submit Buttons -->
                        <div class="flex gap-3">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                                Update Materi
                            </button>
                            <a href="{{ route('dosen.materials.index', $course) }}"
                               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initMarkdownEditor('content-editor');
        });
    </script>
</x-app-layout>
