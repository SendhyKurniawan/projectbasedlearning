{{-- Form edit exercise (latihan koding) (dosen). --}}
@push('styles')
    @vite('resources/css/pages/dosen/exercises.css')
@endpush
<x-app-layout>
    @vite(['resources/js/code-editor.js'])

    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-primary transition-colors">Assignments</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary truncate max-w-[160px]">Edit {{ $assignment->title }}</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Edit Latihan Kode</h1>
                <p class="mt-1 text-sm text-on-surface-variant">{{ $course->nama_matkul }} · {{ $assignment->title }}</p>
            </div>
            <a href="{{ route('dosen.assignments.index', $course) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6 lg:p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-secondary"></div>

            <form action="{{ route('dosen.exercises.update', $assignment) }}" method="POST" id="exercise-form">
                @csrf
                @method('PUT')

                <div class="space-y-8">
                    <!-- Title & Description Container -->
                    <div class="space-y-6 bg-surface-container-low/30 p-6 rounded-2xl border border-outline-variant/20">
                        <!-- Title -->
                        <div>
                            <label for="title" class="text-xs font-bold font-headline uppercase tracking-widest text-primary mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">title</span> Judul Latihan <span class="text-error">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $assignment->title) }}" required
                                class="w-full bg-surface border border-outline-variant/30 rounded-xl px-4 py-3 text-base font-bold text-on-surface focus:ring-2 focus:ring-primary shadow-inner transition-shadow placeholder:text-on-surface-variant/50 placeholder:font-medium"
                                placeholder="Contoh: HTML - Struktur Dasar Website">
                            @error('title')
                                <p class="text-error text-xs font-bold mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Deskripsi & Instruksi
                            </label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full bg-surface border border-outline-variant/30 rounded-xl px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary shadow-inner transition-shadow placeholder:text-on-surface-variant/50"
                                placeholder="Jelaskan apa yang harus dikerjakan mahasiswa...">{{ old('description', $assignment->description) }}</textarea>
                            @error('description')
                                <p class="text-error text-xs font-bold mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Configuration Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Language -->
                        <div>
                            <label for="exercise_language" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Bahasa Pemrograman <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                @php $lang = old('exercise_language', $assignment->exercise_config['language'] ?? 'htmlmixed'); @endphp
                                <select name="exercise_language" id="exercise_language" required
                                    class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl pl-4 pr-10 py-3 text-sm font-bold text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                                    <option value="htmlmixed" {{ $lang === 'htmlmixed' ? 'selected' : '' }}>HTML (Mixed)</option>
                                    <option value="html" {{ $lang === 'html' ? 'selected' : '' }}>HTML Only</option>
                                    <option value="css" {{ $lang === 'css' ? 'selected' : '' }}>CSS</option>
                                    <option value="javascript" {{ $lang === 'javascript' ? 'selected' : '' }}>JavaScript</option>
                                    <option value="java" {{ $lang === 'java' ? 'selected' : '' }}>Java</option>
                                    <option value="php" {{ $lang === 'php' ? 'selected' : '' }}>PHP</option>
                                    <option value="csharp" {{ $lang === 'csharp' ? 'selected' : '' }}>C#</option>
                                </select>
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div>
                            <label for="deadline" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Tenggat Waktu (Deadline) <span class="text-error">*</span>
                            </label>
                            <input type="datetime-local" name="deadline" id="deadline"
                                value="{{ old('deadline', $assignment->deadline->format('Y-m-d\TH:i')) }}" required
                                class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl px-4 py-3 text-sm font-bold text-on-surface focus:ring-2 focus:ring-primary shadow-inner">
                        </div>

                        <!-- Max Score -->
                        <div>
                            <label for="max_score" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Nilai Maksimum <span class="text-error">*</span>
                            </label>
                            <input type="number" name="max_score" id="max_score"
                                value="{{ old('max_score', $assignment->max_score) }}" min="1" max="100" required
                                class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl px-4 py-3 text-sm font-bold text-on-surface focus:ring-2 focus:ring-primary shadow-inner">
                        </div>
                    </div>

                    <!-- Code Editors -->
                    <div class="space-y-6">
                        <!-- Starter Code -->
                        <div class="border border-outline-variant/30 rounded-xl overflow-hidden bg-surface shadow-sm focus-within:ring-2 focus-within:ring-primary transition-shadow">
                            <div class="bg-surface-container-low px-4 py-2 border-b border-outline-variant/20 flex justify-between items-center">
                                <label class="text-xs font-bold uppercase tracking-widest text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-primary">data_object</span> Template Kode Awal <span class="text-error">*</span>
                                </label>
                                <span class="text-[10px] text-on-surface-variant font-medium">Bisa diedit oleh mahasiswa</span>
                            </div>
                            <textarea id="starter-code-editor" name="starter_code">{{ old('starter_code', $assignment->exercise_config['starter_code'] ?? '') }}</textarea>
                            @error('starter_code')
                                <p class="text-error text-xs font-bold px-4 py-2 flex items-center gap-1 bg-error-container/30 border-t border-outline-variant/20"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Solution Code -->
                        <div class="border border-outline-variant/30 rounded-xl overflow-hidden bg-surface shadow-sm focus-within:ring-2 focus-within:ring-primary transition-shadow">
                            <div class="bg-surface-container-low px-4 py-2 border-b border-outline-variant/20 flex justify-between items-center">
                                <label class="text-xs font-bold uppercase tracking-widest text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-secondary">verified</span> Referensi Solusi Kode (Opsional)
                                </label>
                                <span class="text-[10px] text-on-surface-variant font-medium">Hanya untuk referensi Dosen</span>
                            </div>
                            <textarea id="solution-code-editor" name="solution_code">{{ old('solution_code', $assignment->exercise_config['solution_code'] ?? '') }}</textarea>
                            @error('solution_code')
                                <p class="text-error text-xs font-bold px-4 py-2 flex items-center gap-1 bg-error-container/30 border-t border-outline-variant/20"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Evaluation Tools -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-surface-container-low/30 p-6 rounded-2xl border border-outline-variant/20">
                        <!-- Required Keywords -->
                        <div>
                            <label for="required_keywords" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Kata Kunci Wajib (Auto-grading)
                            </label>
                            <input type="text" name="required_keywords" id="required_keywords"
                                value="{{ old('required_keywords', implode(', ', $assignment->exercise_config['required_keywords'] ?? [])) }}"
                                class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl px-4 py-3 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary shadow-inner"
                                placeholder="Contoh: <h1>, <p>, <div>, class=, id=">
                            <p class="text-[10px] font-medium text-on-surface-variant mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">info</span> Pisahkan dengan koma. (Contoh: header, footer)
                            </p>
                        </div>

                        <!-- Hints -->
                        <div>
                            <label for="hints" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Petunjuk Mahasiswa (Hints)
                            </label>
                            <textarea name="hints" id="hints" rows="3"
                                class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl px-4 py-3 text-sm font-medium text-on-surface focus:ring-2 focus:ring-primary shadow-inner"
                                placeholder="Tulis hints per baris&#10;Gunakan tag heading h1 untuk judul&#10;Jangan lupa closing tag">{{ old('hints', implode("\n", $assignment->exercise_config['hints'] ?? [])) }}</textarea>
                            <p class="text-[10px] font-medium text-on-surface-variant mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">info</span> Satu hint per baris pengetikan
                            </p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-6 border-t border-outline-variant/20 flex justify-end gap-3">
                        <a href="{{ route('dosen.assignments.index', $course) }}" class="px-6 py-3 border border-outline-variant/30 text-on-surface-variant font-bold rounded-xl hover:bg-surface-container transition-colors text-center">Batal</a>
                        <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary/90 text-on-primary font-bold rounded-xl shadow-md flex items-center justify-center gap-2 transition-transform hover:scale-105 active:scale-95">
                            <span class="material-symbols-outlined text-[20px]">save</span> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .CodeMirror {
            height: 300px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 14px;
            padding: 10px 0;
            background: var(--surface, #fff);
            color: var(--on-surface, #1a1c25);
        }
        .CodeMirror-gutters {
            background-color: var(--surface-container-low, #f8fafc);
            border-right: 1px solid var(--outline-variant, rgba(0,0,0,0.05));
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initCodeEditor === 'function') {
                const currentLang = document.getElementById('exercise_language').value;
                const currentMode = cmModeMap[currentLang] || currentLang;

                const starterEditor = initCodeEditor('starter-code-editor', {
                    mode: currentMode,
                    lineNumbers: true,
                });

                const solutionEditor = initCodeEditor('solution-code-editor', {
                    mode: currentMode,
                    lineNumbers: true,
                });

                document.getElementById('exercise_language').addEventListener('change', function() {
                    const mode = cmModeMap[this.value] || this.value;
                    starterEditor.setOption('mode', mode);
                    solutionEditor.setOption('mode', mode);
                });

                document.getElementById('exercise-form').addEventListener('submit', function() {
                    starterEditor.save();
                    solutionEditor.save();
                });
            } else {
                console.warn("initCodeEditor function not found. Please ensure code-editor.js is loaded.");
            }
        });
    </script>
</x-app-layout>
