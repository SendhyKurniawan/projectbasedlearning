<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-12 pb-20">
        <!-- Header & Breadcrumbs -->
        <div class="text-center">
            <nav class="inline-flex items-center gap-2 text-[10px] font-black text-on-surface-variant/60 uppercase tracking-[0.2em] mb-4 ">
                <span>Hasil Evaluasi</span>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span class="text-primary">{{ $assignment->title }}</span>
            </nav>
            <h1 class="text-4xl font-headline font-black text-on-surface uppercase tracking-tighter leading-tight">Analisis Performa</h1>
            <p class="text-on-surface-variant body-md mt-2 opacity-80">Tinjau kembali jawaban anda untuk memperdalam pemahaman materi.</p>
        </div>

        <!-- Score Hero Card -->
        <div class="relative bg-surface-container-lowest rounded-[3rem] p-12 border border-outline-variant/10 shadow-2xl shadow-primary/5 overflow-hidden group">
            <!-- Background Decorative Elements -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-colors duration-1000"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-secondary/5 rounded-full blur-3xl group-hover:bg-secondary/10 transition-colors duration-1000"></div>
            
            <div class="relative flex flex-col items-center text-center space-y-8">
                <div class="space-y-2">
                    <span class="text-[10px] font-black uppercase text-on-surface-variant tracking-[0.3em] opacity-60">SKOR AKHIR ANDA</span>
                    <div class="flex items-baseline justify-center gap-2">
                        <span class="text-8xl font-black tracking-tighter text-primary">{{ $submission->score ?? 0 }}</span>
                        <span class="text-2xl font-bold text-on-surface-variant/40 ">/ {{ $assignment->questions->sum('score_weight') }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full max-w-2xl">
                    <div class="p-5 bg-surface-container-low rounded-3xl border border-outline-variant/5 ">
                        <p class="text-[9px] font-black uppercase text-on-surface-variant opacity-60 mb-1">Status</p>
                        <p class="text-sm font-black text-secondary uppercase flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">verified</span>
                            COMPLETED
                        </p>
                    </div>
                    <div class="p-5 bg-surface-container-low rounded-3xl border border-outline-variant/5 ">
                        <p class="text-[9px] font-black uppercase text-on-surface-variant opacity-60 mb-1">Waktu Selesai</p>
                        <p class="text-sm font-black text-on-surface">{{ $submission->finished_at->format('H:i') }} WIB</p>
                    </div>
                    <div class="p-5 bg-surface-container-low rounded-3xl border border-outline-variant/5 ">
                        <p class="text-[9px] font-black uppercase text-on-surface-variant opacity-60 mb-1">Tanggal</p>
                        <p class="text-sm font-black text-on-surface">{{ $submission->finished_at->format('d M Y') }}</p>
                    </div>
                    <div class="p-5 bg-primary/10 rounded-3xl border border-primary/10 ">
                        <p class="text-[9px] font-black uppercase text-primary mb-1">Akurasi</p>
                        <p class="text-sm font-black text-primary">
                            {{ round(($submission->score / max(1, $assignment->questions->sum('score_weight'))) * 100) }}%
                        </p>
                    </div>
                </div>

                <div class="pt-4">
                    <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="inline-flex items-center gap-3 px-10 py-4 bg-on-surface text-surface rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary transition-all hover:shadow-xl hover:shadow-primary/20 group/btn">
                        <span class="material-symbols-outlined group-hover/btn:-translate-x-1 transition-transform">arrow_back</span>
                        KEMBALI KE DASHBOARD COURSE
                    </a>
                </div>
            </div>
        </div>

        <!-- Review Section -->
        <div class="space-y-10">
            <h3 class="text-xs font-black uppercase text-on-surface-variant tracking-[0.3em] opacity-60 flex items-center gap-3 px-4">
                <span class="w-12 h-1 bg-primary rounded-full"></span>
                TINJAUAN JAWABAN
            </h3>

            <div class="space-y-8">
                @foreach($assignment->questions as $index => $question)
                    @php
                        $userAnswer = $submission->answers[$question->id] ?? null;
                        $isCorrect = false;
                        $correctOption = null;
                        
                        if ($question->question_type === 'pilihan_ganda') {
                            $correctOption = $question->options->where('is_correct', true)->first();
                            $isCorrect = $userAnswer == $correctOption->id;
                        }
                    @endphp

                    <div class="bg-surface-container-lowest rounded-[2rem] p-8 border {{ $question->question_type === 'pilihan_ganda' ? ($isCorrect ? 'border-secondary/20 shadow-secondary/5' : 'border-error/20 shadow-error/5') : 'border-outline-variant/10' }} shadow-xl relative overflow-hidden group">
                        <!-- Label Status -->
                        @if($question->question_type === 'pilihan_ganda')
                            <div class="absolute top-0 right-0 p-6 opacity-10">
                                <span class="material-symbols-outlined text-4xl {{ $isCorrect ? 'text-secondary' : 'text-error' }}">
                                    {{ $isCorrect ? 'check_circle' : 'cancel' }}
                                </span>
                            </div>
                        @endif

                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <span class="w-10 h-10 flex items-center justify-center {{ $question->question_type === 'pilihan_ganda' ? ($isCorrect ? 'bg-secondary/10 text-secondary' : 'bg-error/10 text-error') : 'bg-surface-container-low text-on-surface-variant' }} font-black rounded-xl border border-outline-variant/10 text-lg">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 {{ $isCorrect ? 'text-secondary' : ($question->question_type === 'pilihan_ganda' ? 'text-error' : 'text-on-surface-variant') }}">SOAL {{ $index + 1 }}</span>
                            </div>

                            <h4 class="text-lg font-bold text-on-surface leading-snug pr-12">
                                {{ $question->question_text }}
                            </h4>

                            @if($question->question_type === 'pilihan_ganda')
                                <div class="grid grid-cols-1 gap-3 pt-2">
                                    @foreach($question->options as $optIndex => $option)
                                        @php 
                                            $label = chr(65 + $optIndex);
                                            $isSelected = $userAnswer == $option->id;
                                            $isAnswerCorrect = $option->is_correct;
                                        @endphp
                                        <div class="flex items-center gap-4 p-4 rounded-2xl border transition-all {{ $isSelected ? ($isCorrect ? 'bg-secondary/5 border-secondary/30' : 'bg-error/5 border-error/30') : ($isAnswerCorrect ? 'bg-secondary/5 border-secondary/20 border-dashed' : 'bg-surface-container-low/50 border-outline-variant/5') }}
                                        ">
                                            <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-lg font-black text-xs transition-colors
                                                {{ $isSelected ? ($isCorrect ? 'bg-secondary text-white' : 'bg-error text-white') : ($isAnswerCorrect ? 'bg-secondary/20 text-secondary' : 'bg-surface-container-low text-on-surface-variant/40') }}
                                            ">
                                                {{ $label }}
                                            </div>
                                            
                                            <span class="flex-1 text-sm font-medium {{ $isSelected ? ($isCorrect ? 'text-secondary' : 'text-error') : ($isAnswerCorrect ? 'text-secondary font-bold' : 'text-on-surface-variant/60') }}">
                                                {{ $option->option_text }}
                                            </span>

                                            @if($isAnswerCorrect)
                                                <span class="text-[9px] font-black text-secondary uppercase tracking-widest px-2 py-1 bg-secondary/10 rounded">Kunci Jawaban</span>
                                            @endif
                                            
                                            @if($isSelected && !$isCorrect)
                                                <span class="text-[9px] font-black text-error uppercase tracking-widest px-2 py-1 bg-error/10 rounded">Jawaban Anda</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'essay' || $question->question_type === 'code_snippet')
                                <div class="space-y-4 pt-4">
                                    <div>
                                        <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-2 opacity-40">Jawaban Anda:</p>
                                        <div class="p-6 bg-surface-container-low rounded-2xl border border-outline-variant/5 font-mono text-sm text-on-surface whitespace-pre-wrap leading-relaxed">
                                            {{ $userAnswer ?? 'Media tidak ditemukan' }}
                                        </div>
                                    </div>
                                    @if($question->correct_answer)
                                        <div>
                                            <p class="text-[10px] font-black text-secondary uppercase tracking-widest mb-2 ">Rubrik / Kunci Jawaban:</p>
                                            <div class="p-6 bg-secondary/5 rounded-2xl border border-secondary/10 text-sm text-secondary leading-relaxed">
                                                {{ $question->correct_answer }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
