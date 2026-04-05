<x-app-layout>
 @vite(['resources/js/markdown-editor.js'])
 
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Edit Materi - {{ $course->nama_matkul }}
 </h2>
 <a href="{{ route('dosen.materials.index', $course) }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <form action="{{ route('dosen.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
 @csrf
 @method('PUT')

 <!-- Title -->
 <div class="mb-4">
 <label for="title" class="block text-sm font-medium text-on-surface-variant mb-2">
 Judul Materi <span class="text-red-500">*</span>
 </label>
 <input type="text" 
 name="title" 
 id="title" 
 value="{{ old('title', $material->title) }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 required>
 @error('title')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>


 <!-- Content with Markdown Editor -->
 <div class="mb-4">
 <label for="content" class="block text-sm font-medium text-on-surface-variant mb-2">
 Konten Materi (Markdown)
 </label>
 <textarea name="content" 
 id="content-editor">{{ old('content', $material->content) }}</textarea>
 
 <div class="mt-2 text-xs text-on-surface-variant bg-surface-container-low p-3 rounded">
 <strong class="text-on-surface-variant">Tips Markdown:</strong>
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
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Current File -->
 @if($material->file_path)
 <div class="mb-4">
 <p class="text-sm text-on-surface-variant mb-2">
 <strong>File saat ini:</strong> 
 <a href="{{ Storage::url($material->file_path) }}" 
 target="_blank"
 class="text-blue-600 hover:underline">
 {{ basename($material->file_path) }}
 </a>
 </p>
 </div>
 @endif

 <!-- New File Upload -->
 <div class="mb-6">
 <label for="file" class="block text-sm font-medium text-on-surface-variant mb-2">
 Upload File Baru (Opsional, Max 20MB)
 </label>
 <input type="file" 
 name="file" 
 id="file"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
 <p class="text-xs text-on-surface-variant mt-1">
 Kosongkan jika tidak ingin mengubah file
 </p>
 @error('file')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Submit Buttons -->
 <div class="flex gap-3">
 <button type="submit" 
 class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
 Update Materi
 </button>
 <a href="{{ route('dosen.materials.index', $course) }}"
 class="bg-gray-600 hover:bg-surface-container-high text-white px-6 py-2 rounded">
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
