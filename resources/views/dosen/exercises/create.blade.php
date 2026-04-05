<x-app-layout>
 @vite(['resources/js/code-editor.js'])
 
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Buat Code Exercise - {{ $course->nama_matkul }}
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <form action="{{ route('dosen.exercises.store', $course) }}" method="POST" id="exercise-form">
 @csrf

 <!-- Title -->
 <div class="mb-4">
 <label for="title" class="block text-sm font-medium text-on-surface-variant mb-2">
 Judul Exercise <span class="text-red-500">*</span>
 </label>
 <input type="text" 
 name="title" 
 id="title" 
 value="{{ old('title') }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 placeholder="Contoh: HTML - Struktur Dasar Website"
 required>
 @error('title')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <!-- Description -->
 <div class="mb-4">
 <label for="description" class="block text-sm font-medium text-on-surface-variant mb-2">
 Deskripsi & Instruksi
 </label>
 <textarea name="description" 
 id="description" 
 rows="3"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 placeholder="Jelaskan apa yang harus dikerjakan mahasiswa...">{{ old('description') }}</textarea>
 @error('description')
 <p class="text-error text-sm mt-1">{{ $message }}</p>
 @enderror
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
 <!-- Language -->
 <div>
 <label for="exercise_language" class="block text-sm font-medium text-on-surface-variant mb-2">
 Bahasa <span class="text-red-500">*</span>
 </label>
 <select name="exercise_language" 
 id="exercise_language"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 required>
 <option value="htmlmixed" {{ old('exercise_language') === 'htmlmixed' ? 'selected' : '' }}>HTML (Mixed)</option>
 <option value="html" {{ old('exercise_language') === 'html' ? 'selected' : '' }}>HTML Only</option>
 <option value="css" {{ old('exercise_language') === 'css' ? 'selected' : '' }}>CSS</option>
 <option value="javascript" {{ old('exercise_language') === 'javascript' ? 'selected' : '' }}>JavaScript</option>
 </select>
 </div>

 <!-- Deadline -->
 <div>
 <label for="deadline" class="block text-sm font-medium text-on-surface-variant mb-2">
 Deadline <span class="text-red-500">*</span>
 </label>
 <input type="datetime-local" 
 name="deadline" 
 id="deadline" 
 value="{{ old('deadline') }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 required>
 </div>

 <!-- Max Score -->
 <div>
 <label for="max_score" class="block text-sm font-medium text-on-surface-variant mb-2">
 Nilai Max <span class="text-red-500">*</span>
 </label>
 <input type="number" 
 name="max_score" 
 id="max_score" 
 value="{{ old('max_score', 100) }}"
 min="1"
 max="100"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 required>
 </div>
 </div>

 <!-- Starter Code -->
 <div class="mb-4">
 <label class="block text-sm font-medium text-on-surface-variant mb-2">
 Starter Code (Template untuk mahasiswa) <span class="text-red-500">*</span>
 </label>
 <div class="border rounded-md overflow-hidden">
 <textarea id="starter-code-editor" name="starter_code" required>{{ old('starter_code', '<!DOCTYPE html>
<html>
<head>
 <title>My First Website</title>
</head>
<body>
 <!-- TODO: Lengkapi kode di sini -->
 
</body>
</html>') }}</textarea>
 </div>
 <p class="text-xs text-on-surface-variant mt-1">
 Kode awal yang akan dilihat mahasiswa
 </p>
 </div>

 <!-- Solution Code -->
 <div class="mb-4">
 <label class="block text-sm font-medium text-on-surface-variant mb-2">
 Solution Code (Reference - Optional)
 </label>
 <div class="border rounded-md overflow-hidden">
 <textarea id="solution-code-editor" name="solution_code">{{ old('solution_code') }}</textarea>
 </div>
 <p class="text-xs text-on-surface-variant mt-1">
 Kode solusi untuk referensi Anda
 </p>
 </div>

 <!-- Required Keywords for Validation -->
 <div class="mb-4">
 <label for="required_keywords" class="block text-sm font-medium text-on-surface-variant mb-2">
 Required Keywords (untuk auto-grading)
 </label>
 <input type="text" 
 name="required_keywords" 
 id="required_keywords" 
 value="{{ old('required_keywords') }}"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 placeholder="Contoh: <h1>, <p>, <div>, class=, id=">
 <p class="text-xs text-on-surface-variant mt-1">
 Pisahkan dengan koma. Sistem akan cek apakah keywords ini ada di kode mahasiswa.
 </p>
 </div>

 <!-- Hints -->
 <div class="mb-6">
 <label for="hints" class="block text-sm font-medium text-on-surface-variant mb-2">
 Hints (Petunjuk untuk mahasiswa)
 </label>
 <textarea name="hints" 
 id="hints" 
 rows="3"
 class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
 placeholder="Tulis hints per baris&#10;Gunakan tag heading h1 untuk judul&#10;Jangan lupa closing tag">{{ old('hints') }}</textarea>
 <p class="text-xs text-on-surface-variant mt-1">
 Satu hint per baris
 </p>
 </div>

 <!-- Submit Buttons -->
 <div class="flex gap-3">
 <button type="submit" 
 class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
 Simpan Exercise
 </button>
 <a href="{{ route('dosen.assignments.index', $course) }}"
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
 // Initialize CodeMirror editors
 const starterEditor = initCodeEditor('starter-code-editor', {
 mode: 'htmlmixed',
 lineNumbers: true,
 });

 const solutionEditor = initCodeEditor('solution-code-editor', {
 mode: 'htmlmixed',
 lineNumbers: true,
 });

 // Update mode when language changes
 document.getElementById('exercise_language').addEventListener('change', function() {
 const mode = this.value;
 starterEditor.setOption('mode', mode);
 solutionEditor.setOption('mode', mode);
 });
 });
 </script>
</x-app-layout>
