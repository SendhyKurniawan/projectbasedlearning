<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Kumpulkan Tugas') }}
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
 
 @if($assignment->description)
 <p class="text-on-surface-variant mt-3">{{ $assignment->description }}</p>
 @endif
 
 <div class="mt-3 text-sm">
 <p class="text-on-surface-variant">
 <strong>Deadline:</strong> {{ $assignment->deadline->format('d M Y, H:i') }}
 </p>
 <p class="text-on-surface-variant">
 <strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}
 </p>
 </div>
 </div>

 @if($existing)
 <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
 <p class="text-amber-800">
 Anda sudah mengumpulkan tugas ini pada {{ $existing->submitted_at->format('d M Y, H:i') }}.
 Anda dapat mengedit atau menghapus submission dari halaman course.
 </p>
 </div>
 
 <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}"
 class="inline-block bg-gray-600 hover:bg-surface-container-high text-white px-4 py-2 rounded">
 Kembali ke Course
 </a>
 @else
 <!-- Submission Form -->
 <form action="{{ route('mahasiswa.submissions.store') }}" method="POST" enctype="multipart/form-data">
 @csrf
 <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

 <!-- Notes -->
 <div class="mb-4">
 <label for="notes" class="block text-sm font-medium text-on-surface-variant mb-2">
 Catatan (Opsional)
 </label>
 <textarea name="notes" 
 id="notes" 
 rows="4"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 placeholder="Tambahkan catatan untuk dosen...">{{ old('notes') }}</textarea>
 @error('notes')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 @if($assignment->submission_format === 'url')
 <!-- URL Link Input -->
 <div class="mb-6">
 <label for="url_link" class="block text-sm font-medium text-on-surface-variant mb-2">
 Link URL (Wajib)
 </label>
 <input type="url" 
 name="url_link" 
 id="url_link"
 value="{{ old('url_link') }}"
 placeholder="https://..."
 required
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
 <p class="text-xs text-on-surface-variant mt-1">
 Masukkan link hasil pengerjaan (Misal: Google Drive, Github, Youtube). Pastikan link dapat diakses.
 </p>
 @error('url_link')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>
 @else
 <!-- File Upload -->
 <div class="mb-6">
 <label for="file" class="block text-sm font-medium text-on-surface-variant mb-2">
 Upload File (Wajib, Max 10MB)
 </label>
 <input type="file" 
 name="file" 
 id="file"
 required
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
 <p class="text-xs text-on-surface-variant mt-1">
 Format yang didukung: PDF, DOC, DOCX, ZIP, RAR, JPG, PNG
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
 Kumpulkan Tugas
 </button>
 <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}"
 class="bg-gray-600 hover:bg-surface-container-high text-white px-6 py-2 rounded">
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
