<x-app-layout>
    @vite(['resources/js/markdown-editor.js'])
    
    <div class="space-y-8 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest">
                    <span><a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span><a href="{{ route('dosen.materials.index', $course) }}" class="hover:text-primary transition-colors">Materials</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary">Baru</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline">Tambah Materi Baru</h1>
                <p class="text-on-surface-variant max-w-lg font-medium">{{ $course->nama_matkul }} ({{ $course->kode_matkul }})</p>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/10 overflow-hidden relative">
            <!-- Top brand strip -->
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-primary to-tertiary"></div>
            
            <div class="p-8">
                <form action="{{ route('dosen.materials.store', $course) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="title" class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-[18px] text-primary">title</span>
                            Judul Materi <span class="text-error">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title') }}"
                               placeholder="Contoh: Pertemuan 1 - Pengenalan Web"
                               class="w-full bg-surface-container text-on-surface border-none rounded-xl focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all shadow-inner px-4 py-3"
                               required>
                        @error('title')
                            <p class="text-error text-xs font-bold flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content with Markdown Editor -->
                    <div class="space-y-2">
                        <label for="content-editor" class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-[18px] text-secondary">subject</span>
                            Konten Materi (Markdown)
                        </label>
                        
                        <div class="rounded-xl overflow-hidden border border-outline-variant/20 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all shadow-inner">
                            <textarea name="content" id="content-editor">{{ old('content') }}</textarea>
                        </div>
                        
                        <!-- Markdown Tips Snippet -->
                        <div class="mt-3 flex items-start gap-3 bg-surface-container-low p-4 rounded-xl border border-outline-variant/10">
                            <span class="material-symbols-outlined text-secondary mt-0.5">tips_and_updates</span>
                            <div class="text-xs text-on-surface-variant font-medium space-y-1">
                                <strong class="text-on-surface block mb-1">Tips Markdown Cepat:</strong>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2">
                                    <span><code># Judul</code></span>
                                    <span><code>**tebal**</code></span>
                                    <span><code>[tautan](url)</code></span>
                                    <span><code>- Butir list</code></span>
                                    <span><code>`kode`</code></span>
                                    <span><code>![Gambar](url)</code></span>
                                </div>
                            </div>
                        </div>
                        
                        @error('content')
                            <p class="text-error text-xs font-bold flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Upload -->
                    <div class="space-y-2">
                        <label for="file" class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-[18px] text-tertiary">attach_file</span>
                            Lampiran File <span class="text-on-surface-variant/60 font-medium">(Opsional)</span>
                        </label>
                        
                        <div class="relative group">
                            <input type="file" name="file" id="file" class="hidden" onchange="document.getElementById('file-name').textContent = this.files.length ? this.files[0].name : 'Belum ada file dipilih'">
                            <label for="file" class="w-full flex items-center justify-between bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/20 border-dashed rounded-xl cursor-pointer transition-all px-4 py-4 shadow-inner">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-surface flex items-center justify-center text-on-surface-variant group-hover:text-primary transition-colors shadow-sm">
                                        <span class="material-symbols-outlined">upload_file</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm" id="file-name">Pilih file untuk diunggah...</span>
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant/80">PDF, DOCX, ZIP (Max 20MB)</span>
                                    </div>
                                </div>
                                <span class="px-4 py-1.5 bg-surface rounded-lg text-xs font-bold text-on-surface-variant shadow-sm border border-outline-variant/10 group-hover:bg-primary/10 group-hover:text-primary transition-colors">Browse</span>
                            </label>
                        </div>
                        @error('file')
                            <p class="text-error text-xs font-bold flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-6 mt-6 border-t border-outline-variant/10 flex justify-end gap-3">
                        <a href="{{ route('dosen.materials.index', $course) }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:bg-primary/90 flex items-center gap-2 shadow-sm transition-all focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Simpan Materi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initMarkdownEditor('content-editor');
        });
    </script>
</x-app-layout>
