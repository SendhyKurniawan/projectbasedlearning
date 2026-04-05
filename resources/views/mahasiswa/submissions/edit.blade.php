<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Edit Submission') }}
 </h2>
 <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali ke Course
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <!-- Assignment Info -->
 <div class="mb-6">
 <h3 class="text-xl font-bold text-on-surface">{{ $assignment->title }}</h3>
 <p class="text-sm text-on-surface-variant">{{ $assignment->course->name }}</p>
 
 <div class="mt-3 text-sm">
 <p class="text-on-surface-variant">
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
 <label for="notes" class="block text-sm font-medium text-on-surface-variant mb-2">
 Catatan
 </label>
 <textarea name="notes" 
 id="notes" 
 rows="4"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 placeholder="Tambahkan catatan untuk dosen...">{{ old('notes', $submission->notes) }}</textarea>
 @error('notes')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Current Submission File/Link -->
 @if($assignment->submission_format === 'url' && $submission->url_link)
 <div class="mb-4">
 <p class="text-sm text-on-surface-variant mb-2">
 <strong>Link URL saat ini:</strong> 
 <a href="{{ $submission->url_link }}" 
 target="_blank"
 class="text-blue-600 hover:underline break-all">
 {{ $submission->url_link }}
 </a>
 </p>
 </div>
 @elseif($assignment->submission_format === 'pdf' && $submission->file_path)
 <div class="mb-4">
 <p class="text-sm text-on-surface-variant mb-2">
 <strong>File saat ini:</strong> 
 <a href="{{ Storage::url($submission->file_path) }}" 
 target="_blank"
 class="text-blue-600 hover:underline break-all">
 {{ basename($submission->file_path) }}
 </a>
 </p>
 </div>
 @endif

 <!-- New File/Link Upload -->
 @if($assignment->submission_format === 'url')
 <div class="mb-6">
 <label for="url_link" class="block text-sm font-medium text-on-surface-variant mb-2">
 Ubah Link URL (Opsional)
 </label>
 <input type="url" 
 name="url_link" 
 id="url_link"
 value="{{ old('url_link', $submission->url_link) }}"
 placeholder="https://..."
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
 <p class="text-xs text-on-surface-variant mt-1">
 Kosongkan jika tidak ingin mengubah link
 </p>
 @error('url_link')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>
 @else
 <div class="mb-6">
 <label for="file" class="block text-sm font-medium text-on-surface-variant mb-2">
 Upload File Baru (Opsional, Max 10MB)
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
 @endif

 <!-- Submit Buttons -->
 <div class="flex gap-3">
 <button type="submit" 
 class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
 Update Submission
 </button>
 <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}"
 class="bg-gray-600 hover:bg-surface-container-high text-white px-6 py-2 rounded">
 Batal
 </a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
