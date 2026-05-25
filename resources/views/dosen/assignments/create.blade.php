@push('styles')
    @vite('resources/css/pages/dosen/assignments.css')
@endpush
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-primary transition-colors">Tugas</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Baru</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Tambah Tugas</h1>
 <p class="mt-1 text-sm text-on-surface-variant">{{ $course->nama_matkul }} ({{ $course->kode_matkul }})</p>
 </div>
 <a href="{{ route('dosen.assignments.index', $course) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <form action="{{ route('dosen.assignments.store', $course) }}" method="POST" x-data="{ type: '{{ old('type', 'tugas') }}', has_duration: {{ old('has_duration', 'true') === 'true' ? 'true' : 'false' }}, submission_format: '{{ old('submission_format', 'pdf') }}', is_group: {{ old('is_group') ? 'true' : 'false' }}, grading_mode: '{{ old('grading_mode', 'equal') }}' }">
 @csrf

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
 <option value="tugas">Tugas (Upload File/Link)</option>
 <option value="quiz">Quiz (Pilihan Ganda / Essay)</option>
 <option value="exercise">Exercise (Coding)</option>
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
 value="{{ old('title') }}"
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
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 placeholder="Jelaskan detail tugas...">{{ old('description') }}</textarea>
 @error('description')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Tugas Specific Fields -->
 <div x-show="type === 'tugas'" class="mb-4 bg-surface-container-low/50 p-4 rounded-lg border border-surface-container-low space-y-4">
 <div>
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

 <div class="border-t border-outline-variant/20 pt-4">
 <label class="inline-flex items-center cursor-pointer">
 <input type="checkbox" name="is_group" value="1" class="w-4 h-4 text-primary bg-surface-container-low border-outline-variant/30 rounded focus:ring-primary focus:ring-2" x-model="is_group" :disabled="type !== 'tugas'">
 <span class="ms-2 text-sm font-medium text-on-surface">Tugas Kelompok</span>
 </label>
 <p class="text-xs text-on-surface-variant mt-1 ml-6">
 Mahasiswa dapat menambahkan teman sekelas saat mengumpulkan.
 </p>

 <div x-show="is_group" class="mt-4 space-y-3 pl-6">
 <div>
 <label for="max_group_size" class="block text-sm font-medium text-on-surface-variant mb-1">
 Maksimal Anggota per Kelompok
 </label>
 <input type="number" name="max_group_size" id="max_group_size"
 value="{{ old('max_group_size', 5) }}" min="2" max="20"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary"
 :required="is_group" :disabled="!is_group">
 <p class="text-xs text-on-surface-variant mt-1">Termasuk pembuat kelompok.</p>
 @error('max_group_size')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <div>
 <label class="block text-sm font-medium text-on-surface-variant mb-2">
 Mode Penilaian
 </label>
 <div class="space-y-2">
 <label class="flex items-start gap-2 cursor-pointer p-3 rounded-md border border-outline-variant/30 hover:bg-surface-container-low" :class="grading_mode === 'equal' ? 'border-primary bg-primary/5' : ''">
 <input type="radio" name="grading_mode" value="equal" x-model="grading_mode" class="mt-1 text-primary focus:ring-primary" :disabled="!is_group">
 <span>
 <span class="block text-sm font-medium text-on-surface">Sama Rata</span>
 <span class="block text-xs text-on-surface-variant">Semua anggota mendapat nilai yang sama.</span>
 </span>
 </label>
 <label class="flex items-start gap-2 cursor-pointer p-3 rounded-md border border-outline-variant/30 hover:bg-surface-container-low" :class="grading_mode === 'individual' ? 'border-primary bg-primary/5' : ''">
 <input type="radio" name="grading_mode" value="individual" x-model="grading_mode" class="mt-1 text-primary focus:ring-primary" :disabled="!is_group">
 <span>
 <span class="block text-sm font-medium text-on-surface">Per Individu</span>
 <span class="block text-xs text-on-surface-variant">Nilai bisa berbeda untuk tiap anggota.</span>
 </span>
 </label>
 </div>
 @error('grading_mode')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>
 </div>
 </div>
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
 value="{{ old('duration_minutes') }}"
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
 value="{{ old('deadline') }}"
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
 value="{{ old('max_score', 100) }}"
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
 <option value="{{ $material->id }}" {{ old('required_material_id') == $material->id ? 'selected' : '' }}>
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

 @include('dosen.partials.sibling-kelas-picker')

 <!-- Submit Buttons -->
 <div class="flex gap-3 mt-4">
 <button type="submit"
 class="bg-primary hover:bg-primary-hover text-on-primary px-6 py-2 rounded">
 Simpan Tugas
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
