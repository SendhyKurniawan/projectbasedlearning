<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Header & Breadcrumbs -->
        <div>
            <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest mb-2 italic px-1">
                <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span><a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="hover:text-primary transition-colors">Course</a></span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span class="text-primary italic">Edit Submission</span>
            </nav>
            <h1 class="text-4xl font-headline font-black text-on-surface italic uppercase tracking-tighter leading-tight">Perbarui Pengumpulan</h1>
            <p class="text-on-surface-variant body-md mt-1 italic">Silakan perbarui hasil pengerjaan anda jika diperlukan sebelum batas waktu berakhir.</p>
        </div>

        @if(session('error'))
            <div class="px-6 py-4 bg-error-container text-on-error-container border-l-4 border-error rounded-2xl text-sm font-bold shadow-sm italic transition-all animate-in fade-in slide-in-from-top-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            <!-- Left: Assignment Info Card -->
            <div class="col-span-1 space-y-6">
                <div class="bg-surface-container-lowest rounded-[2rem] p-6 border border-outline-variant/10 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-secondary opacity-50"></div>
                    <div class="relative space-y-6">
                        <div>
                            <span class="text-[10px] font-black uppercase text-secondary tracking-widest italic mb-2 block">Detail Monitoring</span>
                            <h3 class="text-lg font-black italic text-on-surface leading-tight uppercase tracking-tighter">{{ $assignment->title }}</h3>
                        </div>

                        <div class="bg-surface-container-low rounded-2xl p-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest italic leading-none opacity-60">Submitted At</p>
                                    <p class="text-xs font-black italic text-on-surface">{{ $submission->submitted_at->format('d M, H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-tertiary text-[20px]" style="font-variation-settings: 'FILL' 1;">history</span>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest italic leading-none opacity-60">Status</p>
                                    <p class="text-xs font-black italic text-on-surface uppercase">{{ $submission->status ?? 'Submitted' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-outline-variant/10">
                            @if($assignment->submission_format === 'url' && $submission->url_link)
                                <a href="{{ $submission->url_link }}" target="_blank" class="flex items-center gap-2 text-[10px] font-black text-primary uppercase hover:underline decoration-primary/50 decoration-2 italic transition-all">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span> LIHAT SUBMISI SAAT INI
                                </a>
                            @elseif($assignment->submission_format === 'pdf' && $submission->file_path)
                                <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="flex items-center gap-2 text-[10px] font-black text-primary uppercase hover:underline decoration-primary/50 decoration-2 italic transition-all">
                                    <span class="material-symbols-outlined text-sm">visibility</span> LIHAT FILE SAAT INI
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Edit Form Section -->
            <div class="col-span-1 md:col-span-2">
                <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 md:p-10 border border-outline-variant/10 shadow-xl shadow-surface-dim/20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <span class="material-symbols-outlined text-[120px] rotate-[15deg]">edit_note</span>
                    </div>

                    <form action="{{ route('mahasiswa.submissions.update', $submission) }}" method="POST" enctype="multipart/form-data" class="relative z-10 space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Form Field: Notes -->
                        <div class="space-y-3">
                            <label for="notes" class="flex items-center gap-2 text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] italic ml-1 opacity-70">
                                <span class="material-symbols-outlined text-sm">description</span>
                                Catatan Perubahan
                            </label>
                            <textarea name="notes" id="notes" rows="4"
                                class="w-full bg-surface-container-low border-outline-variant/10 text-on-surface text-sm rounded-2xl p-4 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-inner italic"
                                placeholder="Tuliskan catatan singkat jika ada hal khusus mengenai perubahan ini...">{{ old('notes', $submission->notes) }}</textarea>
                            @error('notes')
                                <p class="text-error text-[11px] font-bold italic mt-1 ml-2 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if($assignment->submission_format === 'url')
                            <!-- Form Field: Update URL -->
                            <div class="space-y-3">
                                <label for="url_link" class="flex items-center gap-2 text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] italic ml-1 opacity-70">
                                    <span class="material-symbols-outlined text-sm">alternate_email</span>
                                    Perbarui Tautan (Opsional)
                                </label>
                                <div class="relative group">
                                    <input type="url" name="url_link" id="url_link" value="{{ old('url_link', $submission->url_link) }}"
                                        class="w-full bg-surface-container-low border-outline-variant/10 text-on-surface text-sm rounded-2xl p-4 pl-12 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-inner font-bold placeholder:font-normal placeholder:opacity-50"
                                        placeholder="https://...">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">link</span>
                                    </div>
                                </div>
                                <p class="text-[9px] text-on-surface-variant/50 font-bold italic ml-2">Kosongkan jika tidak ingin mengubah link yang sudah dikirim.</p>
                                @error('url_link')
                                    <p class="text-error text-[11px] font-bold italic mt-1 ml-2 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @else
                            <!-- Form Field: Update File -->
                            <div class="space-y-3">
                                <label class="flex items-center gap-2 text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] italic ml-1 opacity-70">
                                    <span class="material-symbols-outlined text-sm">sync_alt</span>
                                    Perbarui Dokumen (Opsional)
                                </label>
                                <div class="relative group cursor-pointer">
                                    <input type="file" name="file" id="file"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                                    <div class="w-full bg-surface-container-low border-2 border-dashed border-outline-variant/20 rounded-[2rem] p-8 text-center group-hover:border-primary/40 group-hover:bg-primary/5 transition-all relative z-10 overflow-hidden">
                                        <div class="relative z-10">
                                            <div class="w-14 h-14 bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4 border border-white/60 group-hover:bg-primary group-hover:text-on-primary transition-all duration-300">
                                                <span class="material-symbols-outlined text-[28px]">file_upload_off</span>
                                            </div>
                                            <p class="text-xs font-black italic text-on-surface group-hover:text-primary transition-colors">Ganti File Submission</p>
                                            <p class="text-[9px] font-bold text-on-surface-variant italic mt-1 opacity-50">Biarkan kosong jika tidak ingin mengubah file lama</p>
                                        </div>
                                    </div>
                                </div>
                                @error('file')
                                    <p class="text-error text-[11px] font-bold italic mt-1 ml-2 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex flex-col md:flex-row items-center gap-4 pt-6">
                            <button type="submit" class="w-full md:flex-1 bg-secondary text-on-secondary font-black text-xs uppercase tracking-[0.2em] py-4 rounded-2xl hover:shadow-2xl hover:shadow-secondary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-2 italic shadow-lg">
                                UPDATE SUBMISSION
                                <span class="material-symbols-outlined text-sm">save</span>
                            </button>
                            <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="w-full md:w-auto px-10 bg-surface-container text-on-surface-variant font-black text-xs uppercase tracking-widest py-4 rounded-2xl hover:bg-surface-container-high transition-all text-center italic">
                                BATAL
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
