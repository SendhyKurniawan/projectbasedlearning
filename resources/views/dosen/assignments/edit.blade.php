@push('styles')
    @vite('resources/css/pages/dosen/assignments.css')
@endpush
<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Edit Tugas - {{ $course->nama_matkul }}
 </h2>
 <a href="{{ route('dosen.assignments.index', $course) }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <form action="{{ route('dosen.assignments.update', $assignment) }}" method="POST" x-data="{ type: '{{ old('type', $assignment->type) }}', has_duration: {{ old('has_duration', is_null($assignment->duration_minutes) ? 'false' : 'true') === 'true' ? 'true' : 'false' }}, submission_format: '{{ old('submission_format', $assignment->submission_format ?? 'pdf') }}' }">
 @csrf
 @method('PUT')

 <!-- Assignment Type -->
 <div class="mb-4">
 <label for="type" class="block text-sm font-medium text-on-surface-variant mb-2">
 Tipe Assignment <span class="text-error">*</span>
 </label>
 <select name="type" 
 id="type" 
 x-model="type"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 required>
 <option value="tugas" {{ $assignment->type === 'tugas' ? 'selected' : '' }}>Tugas (Upload File/Link)</option>
 <option value="quiz" {{ $assignment->type === 'quiz' ? 'selected' : '' }}>Quiz (Pilihan Ganda / Essay)</option>
 <option value="exercise" {{ $assignment->type === 'exercise' ? 'selected' : '' }}>Exercise (Coding)</option>
 </select>
 <p class="text-xs text-on-surface-variant mt-1" x-show="type === 'quiz'">
 * Setelah menyimpan, Anda akan diarahkan untuk mengisi pertanyaan.
 </p>
 @error('type')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Title -->
 <div class="mb-4">
 <label for="title" class="block text-sm font-medium text-on-surface-variant mb-2">
 Judul Tugas <span class="text-error">*</span>
 </label>
 <input type="text" 
 name="title" 
 id="title" 
 value="{{ old('title', $assignment->title) }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 required>
 @error('title')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Description -->
 <div class="mb-4">
 <label for="description" class="block text-sm font-medium text-on-surface-variant mb-2">
 Deskripsi Tugas
 </label>
 <textarea name="description" 
 id="description" 
 rows="5"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">{{ old('description', $assignment->description) }}</textarea>
 @error('description')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Tugas Specific Fields -->
 <div x-show="type === 'tugas'" class="mb-4 bg-surface-container-low/50 p-4 rounded-lg border border-surface-container-low">
 <label for="submission_format" class="block text-sm font-medium text-on-surface-variant mb-2">
 Format Pengumpulan <span class="text-error">*</span>
 </label>
 <select name="submission_format" 
 id="submission_format" 
 x-model="submission_format"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 :required="type === 'tugas'"
 :disabled="type !== 'tugas'">
 <option value="pdf">File Upload (PDF, DOCX, ZIP, dll)</option>
 <option value="url">Link URL (Github, GDrive, Youtube, dll)</option>
 </select>
 <p class="text-xs text-on-surface-variant mt-1">
 Pilih apakah mahasiswa mengumpulkan dalam bentuk unggah file (Max 10MB) atau sekedar input kolom teks Link URL.
 </p>
 @error('submission_format')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Quiz Specific Fields -->
 <div x-show="type === 'quiz'" class="mb-4">
 <div>
 <div class="flex items-center justify-between mb-2">
 <label for="duration_minutes" class="block text-sm font-medium text-on-surface-variant">
 Durasi (Menit) <span class="text-error" x-show="has_duration">*</span>
 </label>
 <label class="inline-flex items-center cursor-pointer">
 <input type="checkbox" name="has_duration" value="true" class="w-4 h-4 text-primary dark:text-primary-fixed-dim bg-surface-container-low border-outline-variant/30 rounded focus:ring-primary focus:ring-2" x-model="has_duration" :disabled="type !== 'quiz'">
 <span class="ms-2 text-sm font-medium text-on-surface">Gunakan Batas Waktu</span>
 </label>
 </div>
 <div x-show="has_duration">
 <input type="number" 
 name="duration_minutes" 
 id="duration_minutes" 
 value="{{ old('duration_minutes', $assignment->duration_minutes) }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 :required="type === 'quiz' && has_duration"
 :disabled="type !== 'quiz' || !has_duration">
 @error('duration_minutes')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>
 <p class="text-xs text-on-surface-variant mt-1" x-show="!has_duration">
 Kuis tidak memiliki batasan waktu (Unlimited).
 </p>
 </div>
 </div>

 <!-- Deadline -->
 <div class="mb-4">
 <label for="deadline" class="block text-sm font-medium text-on-surface-variant mb-2">
 Deadline <span class="text-error">*</span>
 </label>
 <input type="datetime-local" 
 name="deadline" 
 id="deadline" 
 value="{{ old('deadline', $assignment->deadline->format('Y-m-d\TH:i')) }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 required>
 @error('deadline')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Max Score -->
 <div class="mb-6">
 <label for="max_score" class="block text-sm font-medium text-on-surface-variant mb-2">
 Nilai Maksimal <span class="text-error">*</span>
 </label>
 <input type="number"
 name="max_score"
 id="max_score"
 value="{{ old('max_score', $assignment->max_score) }}"
 min="1"
 max="100"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 required>
 @error('max_score')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Required Material (Prerequisite Gating) -->
 <div class="mb-6">
 <label for="required_material_id" class="block text-sm font-medium text-on-surface-variant mb-2">
 Prasyarat Materi
 </label>
 <select name="required_material_id"
 id="required_material_id"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 <option value="">Tidak ada prasyarat</option>
 @foreach($materials as $material)
 <option value="{{ $material->id }}" {{ old('required_material_id', $assignment->required_material_id) == $material->id ? 'selected' : '' }}>
 {{ $material->title }}
 </option>
 @endforeach
 </select>
 <p class="text-xs text-on-surface-variant mt-1">
 Jika dipilih, mahasiswa hanya dapat mengerjakan tugas ini setelah membuka materi prasyarat.
 </p>
 @error('required_material_id')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Submit Buttons -->
 <div class="flex gap-3">
 <button type="submit" 
 class="bg-primary hover:bg-primary-hover text-on-primary px-6 py-2 rounded">
 Update Tugas
 </button>
 <a href="{{ route('dosen.assignments.index', $course) }}"
 class="bg-surface-container hover:bg-surface-container-high text-on-surface px-6 py-2 rounded">
 Batal
 </a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
