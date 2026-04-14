<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Header & Breadcrumbs -->
        <div>
            <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest mb-2 px-1">
                <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span><a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="hover:text-primary transition-colors">Course</a></span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span class="text-primary ">Submit Assignment</span>
            </nav>
            <h1 class="text-4xl font-headline font-black text-on-surface uppercase tracking-tighter leading-tight">Pengumpulan Tugas</h1>
            <p class="text-on-surface-variant body-md mt-1 ">Silakan lengkapi form di bawah ini untuk mengirimkan hasil pengerjaan anda.</p>
        </div>

        @if(session('error'))
            <div class="px-6 py-4 bg-error-container text-on-error-container border-l-4 border-error rounded-2xl text-sm font-bold shadow-sm transition-all animate-in fade-in slide-in-from-top-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            <!-- Left: Assignment Info Card -->
            <div class="col-span-1 space-y-6">
                <div class="bg-surface-container-lowest rounded-[2rem] p-6 border border-outline-variant/10 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-primary opacity-50"></div>
                    <div class="relative space-y-6">
                        <div>
                            <span class="text-[10px] font-black uppercase text-primary tracking-widest mb-2 block">Daftar Mata Kuliah</span>
                            <h3 class="text-lg font-black text-on-surface leading-tight uppercase tracking-tighter">{{ $assignment->course->nama_matkul }}</h3>
                        </div>

                        <div class="bg-surface-container-low rounded-2xl p-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-secondary text-[20px]" style="font-variation-settings: 'FILL' 1;">timer</span>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none opacity-60">Deadline</p>
                                    <p class="text-xs font-black text-on-surface">{{ $assignment->deadline->format('d M, H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-tertiary text-[20px]" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none opacity-60">Nilai Maksimal</p>
                                    <p class="text-xs font-black text-on-surface">{{ $assignment->max_score }} Poin</p>
                                </div>
                            </div>
                        </div>

                        @if($assignment->description)
                            <div>
                                <h4 class="text-[10px] font-black uppercase text-on-surface-variant tracking-widest mb-2 opacity-60">Instruksi:</h4>
                                <div class="text-xs text-on-surface-variant leading-relaxed opacity-90 line-clamp-6">
                                    {{ $assignment->description }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="p-6 bg-yellow-500/5 border border-yellow-500/20 rounded-[2rem] flex items-start gap-4">
                    <span class="material-symbols-outlined text-yellow-600 mt-0.5">info</span>
                    <p class="text-[11px] font-bold text-yellow-800 leading-relaxed ">Pastikan seluruh file atau tautan yang dikirimkan dapat diakses oleh dosen pengampu.</p>
                </div>
            </div>

            <!-- Right: Form Section -->
            <div class="col-span-1 md:col-span-2">
                @if($existing)
                    <div class="bg-surface-container-lowest rounded-[2.5rem] p-10 border border-outline-variant/10 shadow-sm text-center space-y-6 ">
                        <div class="w-16 h-16 bg-secondary/10 text-secondary rounded-full flex items-center justify-center mx-auto shadow-inner">
                            <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-on-surface uppercase tracking-tighter">Sudah Dikumpulkan</h3>
                            <p class="text-sm text-on-surface-variant mt-2 opacity-80">Anda telah mengumpulkan tugas ini pada <span class="text-on-surface font-black">{{ $existing->submitted_at->format('d M Y, H:i') }}</span>.</p>
                        </div>
                        <div class="flex gap-4 justify-center">
                            <a href="{{ route('mahasiswa.dashboard') }}" class="px-8 py-3 bg-surface-container text-on-surface-variant font-black text-xs uppercase tracking-widest rounded-xl hover:bg-surface-container-high transition-all"> DASHBOARD </a>
                            <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="px-8 py-3 bg-primary text-on-primary font-black text-xs uppercase tracking-widest rounded-xl hover:shadow-lg transition-all"> LIHAT COURSE </a>
                        </div>
                    </div>
                @else
                    <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 md:p-10 border border-outline-variant/10 shadow-xl shadow-surface-dim/20 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5">
                            <span class="material-symbols-outlined text-[120px] rotate-12">send</span>
                        </div>

                        <form action="{{ route('mahasiswa.submissions.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10 space-y-8">
                            @csrf
                            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                            <!-- Form Field: Notes -->
                            <div class="space-y-3">
                                <label for="notes" class="flex items-center gap-2 text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] ml-1 opacity-70">
                                    <span class="material-symbols-outlined text-sm">sticky_note_2</span>
                                    Catatan Pengerjaan (Opsional)
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full bg-surface-container-low border-outline-variant/10 text-on-surface text-sm rounded-2xl p-4 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-inner "
                                    placeholder="Tuliskan catatan singkat jika ada hal khusus yang ingin disampaikan kepada dosen...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-error text-[11px] font-bold mt-1 ml-2 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            @if($assignment->submission_format === 'url')
                                <!-- Form Field: URL -->
                                <div class="space-y-3">
                                    <label for="url_link" class="flex items-center gap-2 text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] ml-1 opacity-70">
                                        <span class="material-symbols-outlined text-sm">link</span>
                                        Tautan Hasil Pengerjaan (Wajib)
                                    </label>
                                    <div class="relative group">
                                        <input type="url" name="url_link" id="url_link" value="{{ old('url_link') }}" required
                                            class="w-full bg-surface-container-low border-outline-variant/10 text-on-surface text-sm rounded-2xl p-4 pl-12 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-inner font-bold placeholder:font-normal placeholder:opacity-50"
                                            placeholder="https://github.com/... atau https://drive.google.com/...">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">public</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-on-surface-variant/60 font-bold ml-2">Gunakan Github, Google Drive, atau platform lain yang representatif.</p>
                                    @error('url_link')
                                        <p class="text-error text-[11px] font-bold mt-1 ml-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            @else
                                <!-- Form Field: File -->
                                <div class="space-y-3" x-data="{ fileName: '', fileSize: '', hasFile: false }">
                                    <label class="flex items-center gap-2 text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] ml-1 opacity-70">
                                        <span class="material-symbols-outlined text-sm">upload_file</span>
                                        Upload Dokumen (PDF/ZIP, Max 10MB)
                                    </label>
                                    <div class="relative group cursor-pointer">
                                        <input type="file" name="file" id="file" required
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                                            @change="
                                                if ($event.target.files.length > 0) {
                                                    fileName = $event.target.files[0].name;
                                                    fileSize = ($event.target.files[0].size / 1024 / 1024).toFixed(2);
                                                    hasFile = true;
                                                } else {
                                                    fileName = ''; fileSize = ''; hasFile = false;
                                                }
                                            ">
                                        <div class="w-full bg-surface-container-low border-2 rounded-[2rem] p-8 text-center transition-all relative z-10 overflow-hidden"
                                            :class="hasFile
                                                ? 'border-solid border-secondary/60 bg-secondary/5'
                                                : 'border-dashed border-outline-variant/20 group-hover:border-primary/40 group-hover:bg-primary/5'">
                                            <div class="relative z-10">
                                                <!-- State: belum ada file -->
                                                <template x-if="!hasFile">
                                                    <div>
                                                        <div class="w-16 h-16 bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4 border border-white/60 group-hover:scale-110 group-hover:bg-primary group-hover:text-on-primary transition-all duration-500 shadow-sm">
                                                            <span class="material-symbols-outlined text-[32px]">cloud_upload</span>
                                                        </div>
                                                        <p class="text-sm font-black text-on-surface group-hover:text-primary">Klik atau seret file ke sini</p>
                                                        <p class="text-[10px] text-on-surface-variant font-bold mt-1 opacity-60">Sistem mendukung format PDF, ZIP, RAR, atau Gambar</p>
                                                    </div>
                                                </template>
                                                <!-- State: file sudah dipilih -->
                                                <template x-if="hasFile">
                                                    <div>
                                                        <div class="w-16 h-16 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-secondary/30 shadow-sm">
                                                            <span class="material-symbols-outlined text-[32px] text-secondary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                                        </div>
                                                        <p class="text-sm font-black text-secondary">File Siap Dikirim</p>
                                                        <p class="text-xs font-bold text-on-surface mt-2 truncate max-w-xs mx-auto" x-text="fileName"></p>
                                                        <p class="text-[10px] text-on-surface-variant font-bold mt-1 opacity-60" x-text="fileSize + ' MB'"></p>
                                                        <p class="text-[10px] text-primary font-bold mt-3 cursor-pointer hover:underline">Klik untuk ganti file</p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    @error('file')
                                        <p class="text-error text-[11px] font-bold mt-1 ml-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex flex-col md:flex-row items-center gap-4 pt-6">
                                <button type="submit" class="w-full md:flex-1 bg-primary text-on-primary font-black text-xs uppercase tracking-[0.2em] py-4 rounded-2xl hover:shadow-2xl hover:shadow-primary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-2 ">
                                    KUMPULKAN TUGAS
                                    <span class="material-symbols-outlined text-sm">send</span>
                                </button>
                                <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="w-full md:w-auto px-10 bg-surface-container text-on-surface-variant font-black text-xs uppercase tracking-widest py-4 rounded-2xl hover:bg-surface-container-high transition-all text-center ">
                                    BATAL
                                </a>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
