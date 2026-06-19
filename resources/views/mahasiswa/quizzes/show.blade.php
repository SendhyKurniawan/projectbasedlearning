{{-- Halaman info quiz sebelum dikerjakan (mahasiswa). --}}
<x-app-layout>
    @php
        $submission = $existingSubmission;
        $isFinished = $submission && $submission->finished_at;
    @endphp

    <div class="max-w-4xl mx-auto space-y-10 pb-20">
        <!-- Header & Breadcrumbs -->
        <div>
            <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest mb-2 px-1">
                <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span><a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="hover:text-primary transition-colors">Course</a></span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span class="text-primary ">Detail Kuis</span>
            </nav>
            <h1 class="text-4xl font-headline font-black text-on-surface uppercase tracking-tighter leading-tight">Persiapan Evaluasi</h1>
            <p class="text-on-surface-variant body-md mt-1 opacity-80">Pastikan anda telah memahami materi terkait sebelum memulai sesi kuis ini.</p>
        </div>

        @if(session('error'))
            <div class="px-6 py-4 bg-error-container text-on-error-container border-l-4 border-error rounded-2xl text-sm font-bold shadow-sm transition-all animate-in fade-in slide-in-from-top-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            <!-- Left: Quiz Stats & Summary -->
            <div class="md:col-span-5 space-y-6">
                <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 border border-outline-variant/10 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-primary opacity-50"></div>
                    <div class="relative space-y-8">
                        <div>
                            <span class="text-[10px] font-black uppercase text-primary tracking-[0.2em] mb-3 block">Informasi Kuis</span>
                            <h2 class="text-2xl font-black text-on-surface leading-tight uppercase tracking-tighter">{{ $assignment->title }}</h2>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-4 bg-surface-container-low rounded-2xl border border-outline-variant/5">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">help</span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest opacity-60 leading-none mb-1">Jumlah Soal</p>
                                    <p class="text-sm font-black text-on-surface">{{ $assignment->questions->count() }} Pertanyaan</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-4 bg-surface-container-low rounded-2xl border border-outline-variant/5">
                                <div class="w-10 h-10 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">timer</span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest opacity-60 leading-none mb-1">Durasi</p>
                                    <p class="text-sm font-black text-on-surface">{{ $assignment->duration_minutes ?? '-' }} Menit</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-4 bg-surface-container-low rounded-2xl border border-outline-variant/5">
                                <div class="w-10 h-10 rounded-xl bg-tertiary/10 text-tertiary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest opacity-60 leading-none mb-1">Nilai Maksimal</p>
                                    <p class="text-sm font-black text-on-surface">{{ $assignment->max_score }} Poin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($isFinished)
                    <div class="bg-secondary/5 rounded-[2rem] p-8 border border-secondary/20 flex flex-col items-center text-center space-y-4 shadow-sm shadow-secondary/5">
                        <div class="w-16 h-16 bg-secondary/10 text-secondary rounded-full flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-on-surface uppercase tracking-tighter">Hasil Tersedia</h3>
                            <p class="text-xs text-on-surface-variant mt-1 opacity-80">Anda telah menyelesaikan kuis ini.</p>
                        </div>
                        <div class="pt-2">
                             <div class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1 opacity-60">CORRECT SCORE</div>
                             <div class="text-3xl font-black text-secondary">{{ $submission->score ?? '0' }}</div>
                        </div>
                        <a href="{{ route('mahasiswa.quizzes.result', $assignment) }}" class="w-full py-3 bg-secondary text-on-secondary font-black text-[10px] uppercase tracking-widest rounded-xl hover:shadow-lg transition-all"> LIHAT REVIEW HASIL </a>
                    </div>
                @endif
            </div>

            <!-- Right: Instructions & Action -->
            <div class="md:col-span-7 space-y-8">
                <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 md:p-10 border border-outline-variant/10 shadow-sm space-y-10">
                    <section>
                        <h3 class="text-xs font-black uppercase text-on-surface-variant tracking-[0.3em] mb-4 opacity-60 flex items-center gap-3">
                            <span class="w-8 h-1 bg-primary rounded-full"></span>
                            PERATURAN & INSTRUKSI
                        </h3>
                        <div class="prose prose-slate prose-sm max-w-none text-on-surface-variant leading-loose space-y-4">
                            <p>{{ $assignment->description ?? 'Kuis ini dirancang untuk menguji pemahaman anda mengenai topik yang telah dipelajari dalam modul ini. Harap kerjakan dengan jujur dan teliti.' }}</p>
                            <ul class="list-none p-0 space-y-3 font-bold text-xs text-on-surface">
                                <li class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-primary text-sm mt-0.5">check_circle</span>
                                    <span>Waktu akan terus berjalan setelah anda menekan tombol "Mulai Kuis".</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-primary text-sm mt-0.5">check_circle</span>
                                    <span>Pastikan koneksi internet anda stabil selama sesi kuis berlangsung.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-primary text-sm mt-0.5">check_circle</span>
                                    <span>Anda tidak diperbolehkan untuk menutup halaman atau me-refresh tab browser.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-primary text-sm mt-0.5">check_circle</span>
                                    <span>Jawaban akan tersimpan secara otomatis setiap kali anda berpindah soal.</span>
                                </li>
                            </ul>
                        </div>
                    </section>
                    
                    @if(!$isFinished)
                        <div class="pt-10 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center gap-6">
                            <form action="{{ route('mahasiswa.quizzes.start', $assignment) }}" method="POST" class="w-full sm:w-auto flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-primary text-on-primary font-black text-xs uppercase tracking-[0.2em] py-4 rounded-2xl hover:shadow-2xl hover:shadow-primary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-3 ">
                                    MULAI KUIS SEKARANG
                                    <span class="material-symbols-outlined text-[20px]">play_circle</span>
                                </button>
                            </form>
                            <p class="text-[10px] font-bold text-on-surface-variant opacity-60 sm:max-w-[150px]">
                                Menekan tombol mulai berarti anda menyetujui seluruh peraturan kuis.
                            </p>
                        </div>
                    @else
                        <div class="pt-10 border-t border-outline-variant/10">
                            <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="inline-flex items-center gap-2 p-4 px-8 bg-surface-container text-on-surface-variant rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-surface-container-high transition-all ">
                                <span class="material-symbols-outlined">arrow_back</span>
                                KEMBALI KE COURSE
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
