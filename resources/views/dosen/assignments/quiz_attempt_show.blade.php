<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dosen.quizzes.attempts.index', $quiz) }}" class="p-2.5 w-10 h-10 flex items-center justify-center bg-white border border-outline-variant/30 rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-on-surface">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-extrabold font-headline tracking-tight text-on-surface">Kuis: {{ $quiz->title }}</h2>
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mt-1">Detail Percobaan Mahasiswa</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Student Detail Card -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Info Mahasiswa -->
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center font-bold font-headline text-2xl uppercase shadow-inner">
                            {{ substr($attempt->mahasiswa->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold font-headline text-lg text-on-surface">{{ $attempt->mahasiswa->name }}</h3>
                            <p class="text-xs text-on-surface-variant">{{ $attempt->mahasiswa->email }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-surface-container-highest flex items-center justify-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[18px]">event</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold tracking-widest uppercase text-on-surface-variant">Diselesaikan</p>
                                <p class="text-sm font-semibold text-on-surface">{{ $attempt->finished_at ? $attempt->finished_at->format('d M Y, H:i') : 'Belum Selesai' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <span class="material-symbols-outlined absolute -right-6 -bottom-6 text-[120px] text-primary/[0.03] pointer-events-none">account_circle</span>
                </div>

                <!-- Score Card -->
                <div class="bg-primary-container p-8 rounded-2xl shadow-sm relative overflow-hidden text-center">
                    <p class="text-xs font-bold text-on-primary-container/80 uppercase tracking-widest mb-2">Total Skor Sistem</p>
                    <div class="flex justify-center items-baseline gap-2">
                        <span class="text-6xl font-black font-headline text-on-primary-container">{{ $attempt->total_score ?? 0 }}</span>
                        <span class="text-xl font-bold text-on-primary-container/60">/ {{ $quiz->questions->sum('score_weight') }}</span>
                    </div>
                    <p class="text-[10px] text-on-primary-container/70 mt-4 max-w-[200px] mx-auto leading-relaxed font-bold">Skor saat ini dihitung berdasarkan jawaban pilihan ganda secara otomatis.</p>
                    <span class="material-symbols-outlined absolute -left-4 top-1/2 -translate-y-1/2 text-[100px] text-white/[0.05] pointer-events-none drop-shadow-md">social_leaderboard</span>
                </div>
            </div>

            <!-- Review Answers Card -->
            <div class="lg:col-span-8 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6 lg:p-8">
                <h3 class="text-lg font-extrabold font-headline text-on-surface tracking-tight mb-8 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[24px]">task_alt</span>
                    Transkrip Jawaban
                </h3>
                
                <div class="space-y-8">
                    @foreach($quiz->questions as $index => $question)
                        @php
                            $userAnswer = $attempt->answers[$question->id] ?? null;
                            $isCorrect = false;
                            $correctOption = null;
                            
                            if ($question->question_type === 'pilihan_ganda') {
                                $correctOption = $question->options->where('is_correct', true)->first();
                                $isCorrect = $userAnswer == $correctOption->id;
                            }
                        @endphp

                        <div class="relative">
                            <!-- Timeline connector (not on last item) -->
                            @if(!$loop->last)
                                <div class="absolute top-[40px] left-[15px] w-px h-[calc(100%+24px)] bg-outline-variant/30"></div>
                            @endif

                            <div class="flex items-start gap-5 relative z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0 mt-1 {{ $question->question_type === 'pilihan_ganda' ? ($isCorrect ? 'bg-emerald-100 text-emerald-700 ring-4 ring-emerald-50' : 'bg-error-container text-on-error-container ring-4 ring-error/10') : 'bg-surface-container-highest text-on-surface ring-4 ring-surface' }}">
                                    {{ $index + 1 }}
                                </div>
                                
                                <div class="flex-1 pb-2">
                                    <h4 class="text-base font-bold text-on-surface leading-relaxed mb-4">
                                        {{ $question->question_text }}
                                    </h4>

                                    @if($question->question_type === 'pilihan_ganda')
                                        <div class="space-y-3">
                                            @foreach($question->options as $option)
                                                @php
                                                    $isUserChoice = ($userAnswer == $option->id);
                                                    $isCorrectChoice = $option->is_correct;
                                                    
                                                    $cardClass = 'bg-surface border border-outline-variant/20';
                                                    if ($isUserChoice && $isCorrect) $cardClass = 'bg-emerald-50 border border-emerald-300 shadow-sm';
                                                    elseif ($isUserChoice && !$isCorrect) $cardClass = 'bg-error-container/30 border border-error/30';
                                                    elseif (!$isUserChoice && $isCorrectChoice) $cardClass = 'bg-emerald-50/50 border border-emerald-300/50 border-dashed';
                                                @endphp

                                                <div class="p-3 md:p-4 rounded-xl flex items-center justify-between gap-4 {{ $cardClass }} transition-colors">
                                                    <div class="flex items-center gap-3">
                                                        @if($isUserChoice)
                                                            @if($isCorrect)
                                                                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                                                            @else
                                                                <span class="material-symbols-outlined text-error">cancel</span>
                                                            @endif
                                                        @else
                                                            <div class="w-6 h-6 rounded-full border-2 border-outline-variant/40"></div>
                                                        @endif
                                                        <span class="text-sm {{ $isUserChoice ? 'font-bold text-on-surface' : 'font-medium text-on-surface-variant' }}">{{ $option->option_text }}</span>
                                                    </div>
                                                    
                                                    <!-- Badges -->
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        @if($isCorrectChoice)
                                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase rounded-md">Kunci Jawaban</span>
                                                        @endif
                                                        @if($isUserChoice && !$isCorrect)
                                                            <span class="px-2 py-1 bg-error/10 text-error text-[10px] font-bold uppercase rounded-md">Pilihan Mahasiswa</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === 'essay' || $question->question_type === 'code_snippet')
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[14px]">edit_document</span> Jawaban Mahasiswa:
                                                </p>
                                                <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/20 font-mono text-sm whitespace-pre-wrap shadow-inner text-on-surface">{{ $userAnswer ?? 'Tidak ada jawaban' }}</div>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[14px]">verified</span> Kriteria Rubrik / Kunci Jawaban:
                                                </p>
                                                <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200 text-sm text-emerald-900 font-medium">
                                                    {{ $question->correct_answer ?? 'Kunci jawaban tidak tersedia.' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
