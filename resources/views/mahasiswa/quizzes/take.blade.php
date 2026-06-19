{{-- Halaman pengerjaan quiz (mahasiswa). --}}
<x-app-layout>
    @php
        $duration = $assignment->duration_minutes ?? 0;
        $endTime = now()->addMinutes($duration)->getTimestamp();
    @endphp

    <div class="flex flex-col min-h-[calc(100vh-80px)] -mt-6">
        <!-- Sticky Header with Timer & Progress -->
        <header class="sticky top-0 z-40 bg-surface/90 backdrop-blur-xl border-b border-outline-variant/10 px-8 py-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">quiz</span>
                </div>
                <div>
                    <h2 class="text-sm font-black text-on-surface uppercase tracking-tighter leading-none">{{ $assignment->title }}</h2>
                    <p class="text-[10px] font-bold text-on-surface-variant opacity-60 uppercase tracking-widest mt-1">Sesi Evaluasi Aktif</p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <!-- Progress Indicator -->
                <div class="hidden md:flex flex-col items-end gap-1.5 min-w-[150px]">
                    <div class="flex justify-between w-full text-[10px] font-black text-on-surface-variant uppercase tracking-widest">
                        <span>Pengerjaan</span>
                        <span id="progress-text">0%</span>
                    </div>
                    <div class="w-full h-1.5 bg-surface-container rounded-full overflow-hidden">
                        <div id="progress-bar" class="h-full bg-primary transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Timer -->
                @if($duration > 0)
                    <div class="flex items-center gap-3 px-5 py-2 bg-secondary-container text-on-secondary-container rounded-2xl border border-secondary/10 shadow-sm" x-data="{ 
                        timeLeft: {{ $duration * 60 }},
                        timer: null,
                        formatTime() {
                            const m = Math.floor(this.timeLeft / 60);
                            const s = this.timeLeft % 60;
                            return `${m}:${s < 10 ? '0' : ''}${s}`;
                        }
                    }" x-init="timer = setInterval(() => { 
                        if(timeLeft > 0) timeLeft--;
                        else {
                            clearInterval(timer);
                            document.getElementById('quizForm').submit();
                        }
                    }, 1000)">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">timer</span>
                        <span class="font-mono text-lg font-black tracking-tighter" x-text="formatTime()"></span>
                    </div>
                @endif
            </div>
        </header>

        <div class="flex-1 max-w-7xl mx-auto w-full p-8 md:p-12 grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Left: Question List -->
            <main class="lg:col-span-8 space-y-12">
                <form action="{{ route('mahasiswa.quizzes.submit', $assignment) }}" method="POST" id="quizForm">
                    @csrf
                    
                    <div class="space-y-16">
                        @foreach($questions as $index => $question)
                            <section id="question-{{ $index + 1 }}" class="relative group scroll-mt-24">
                                <div class="absolute -left-4 top-0 w-1 h-full bg-primary/20 scale-y-0 group-hover:scale-y-100 transition-transform origin-top duration-500 rounded-full"></div>
                                
                                <div class="space-y-6">
                                    <div class="flex items-center gap-4">
                                        <span class="w-10 h-10 flex items-center justify-center bg-surface-container-low text-primary dark:text-primary-fixed-dim font-black rounded-xl border border-outline-variant/10 text-lg">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-[10px] font-black uppercase text-on-surface-variant tracking-[0.2em] opacity-60">PERTANYAAN #{{ $index + 1 }}</span>
                                    </div>

                                    <h3 class="text-xl md:text-2xl font-bold text-on-surface leading-snug tracking-tight">
                                        {{ $question->question_text }}
                                    </h3>

                                    @if($question->question_type === 'pilihan_ganda')
                                        <div class="grid grid-cols-1 gap-3 pt-4">
                                            @foreach($question->options as $optIndex => $option)
                                                @php $label = chr(65 + $optIndex); @endphp
                                                <label class="group relative flex items-center gap-4 p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/10 hover:border-primary/40 hover:bg-white hover:shadow-xl hover:shadow-primary/5 transition-all cursor-pointer">
                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" 
                                                        onchange="updateProgress()"
                                                        class="peer hidden">
                                                    
                                                    <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-surface-container-low group-hover:bg-primary-fixed peer-checked:bg-primary peer-checked:text-white transition-colors font-black text-on-surface-variant peer-checked:shadow-lg peer-checked:shadow-primary/20">
                                                        {{ $label }}
                                                    </div>
                                                    
                                                    <span class="text-on-surface font-medium peer-checked:text-primary peer-checked:font-bold transition-all">{{ $option->option_text }}</span>
                                                    
                                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-primary opacity-0 peer-checked:opacity-100 transition-opacity">
                                                        <span class="material-symbols-outlined scale-125" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === 'essay')
                                        <div class="pt-4">
                                            <textarea name="answers[{{ $question->id }}]" rows="5" 
                                                oninput="updateProgress()"
                                                class="w-full bg-surface-container-lowest rounded-2xl border-outline-variant/10 focus:border-primary focus:ring-4 focus:ring-primary/5 text-on-surface placeholder:opacity-30 p-6 shadow-xs transition-all"
                                                placeholder="Berikan jawaban lengkap anda di sini..."></textarea>
                                        </div>
                                    @elseif($question->question_type === 'code_snippet')
                                        <div class="pt-4">
                                            <div class="relative rounded-2xl overflow-hidden bg-[#1e2029] border border-white/5">
                                                <div class="absolute right-4 top-4 text-white/20">
                                                    <span class="material-symbols-outlined text-[20px]">code_blocks</span>
                                                </div>
                                                <textarea name="answers[{{ $question->id }}]" rows="8" 
                                                    oninput="updateProgress()"
                                                    class="w-full bg-transparent font-mono text-sm text-[#f8f8f2] border-none focus:ring-0 p-8 placeholder:text-white/10"
                                                    placeholder="// Tuliskan snippet kode anda di sini..."></textarea>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>

                            @if(!$loop->last)
                                <div class="py-4 flex justify-center opacity-10">
                                    <div class="w-24 h-px bg-on-surface "></div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Submit Footer -->
                    <div class="mt-20 pt-10 border-t border-outline-variant/10 flex flex-col md:flex-row items-center justify-between gap-8 bg-surface-container-lowest/50 p-8 rounded-[3rem] border border-outline-variant/10 ">
                        <div class="text-center md:text-left">
                            <h4 class="text-lg font-black text-on-surface uppercase tracking-tighter">Konfirmasi Akhir</h4>
                            <p class="text-xs text-on-surface-variant mt-1">Pastikan seluruh pertanyaan telah terjawab sebelum mengakhiri sesi.</p>
                        </div>
                        <button type="button" id="submitQuizBtn" onclick="showQuizModal()"
                            class="px-12 py-4 bg-primary text-on-primary font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:shadow-2xl hover:shadow-primary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-3 ">
                            AKHIRI & KIRIM KUIS
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">cloud_upload</span>
                        </button>
                    </div>
                </form>

                <!-- Confirmation Modal -->
                <div id="quizConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                    <div class="bg-surface-container-lowest rounded-[2.5rem] p-10 max-w-md w-full mx-6 border border-outline-variant/10 shadow-2xl">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">quiz</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-on-surface uppercase tracking-tighter">Kirim Kuis?</h3>
                                <p class="text-xs text-on-surface-variant mt-1">Jawaban tidak dapat diubah setelah pengiriman.</p>
                            </div>
                        </div>
                        <p class="text-sm text-on-surface-variant leading-relaxed mb-8">Selesaikan dan kirim kuis sekarang? Pastikan seluruh jawaban telah Anda tinjau kembali sebelum melanjutkan.</p>
                        <div class="flex gap-4">
                            <button onclick="document.getElementById('quizConfirmModal').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-2xl border border-outline-variant/20 text-on-surface-variant font-bold text-xs uppercase tracking-widest hover:bg-surface-container transition-all">
                                Batal
                            </button>
                            <button onclick="submitQuizNow()" id="confirmSubmitBtn" class="flex-1 px-6 py-3 rounded-2xl bg-primary text-on-primary font-black text-xs uppercase tracking-widest hover:shadow-xl hover:shadow-primary/30 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">cloud_upload</span>
                                Kirim Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Right: Question Palette -->
            <aside class="lg:col-span-4 sticky top-28 space-y-8">
                <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 border border-outline-variant/10 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-6 opacity-5">
                        <span class="material-symbols-outlined text-6xl">apps</span>
                    </div>

                    <h4 class="text-[10px] font-black text-on-surface-variant uppercase tracking-[0.2em] mb-6 opacity-60">NAVIGASI SOAL</h4>
                    
                    <div class="grid grid-cols-5 gap-3">
                        @foreach($questions as $index => $question)
                            <a href="#question-{{ $index + 1 }}" 
                                id="nav-item-{{ $question->id }}"
                                class="nav-palette-item flex items-center justify-center h-10 w-10 text-[10px] font-black rounded-lg border border-outline-variant/10 bg-surface-container-low text-on-surface-variant hover:bg-primary-fixed hover:text-primary transition-all">
                                {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8 pt-6 border-t border-outline-variant/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded bg-primary shadow-sm shadow-primary/20"></div>
                            <span class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest ">Terjawab</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded bg-surface-container-low border border-outline-variant/20 "></div>
                            <span class="text-[9px] font-bold text-on-surface-variant uppercase tracking-widest ">Belum</span>
                        </div>
                    </div>
                </div>

                <!-- Tips / Motivational -->
                <div class="p-8 bg-secondary/5 rounded-[2rem] border border-secondary/10 relative overflow-hidden text-xs leading-relaxed text-secondary font-medium">
                    <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-7xl opacity-5">lightbulb</span>
                    "Fokuslah pada setiap pertanyaan. Kualitas jawaban anda mencerminkan dedikasi anda pada pembelajaran ini."
                </div>
            </aside>
        </div>
    </div>

    <style>
        .nav-palette-item.answered {
            @apply bg-primary text-white border-primary shadow-lg shadow-primary/20;
        }
    </style>

    <script>
        function updateProgress() {
            const total = {{ $questions->count() }};
            const answeredInputs = document.querySelectorAll('input:checked, textarea');
            let answeredCount = 0;

            // Simple check for answered questions
            const questions = @json($questions->pluck('id'));
            questions.forEach(qid => {
                const radios = document.querySelectorAll(`input[name="answers[${qid}]"]:checked`);
                const textArea = document.querySelector(`textarea[name="answers[${qid}]"]`);
                
                const navItem = document.getElementById(`nav-item-${qid}`);
                
                if (radios.length > 0 || (textArea && textArea.value.trim() !== '')) {
                    answeredCount++;
                    if(navItem) navItem.classList.add('answered');
                } else {
                    if(navItem) navItem.classList.remove('answered');
                }
            });

            const percent = Math.round((answeredCount / total) * 100);
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            if(progressBar) progressBar.style.width = percent + '%';
            if(progressText) progressText.innerText = percent + '%';
        }

        function showQuizModal() {
            document.getElementById('quizConfirmModal').classList.remove('hidden');
        }

        function submitQuizNow() {
            const btn = document.getElementById('confirmSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">sync</span> Mengirim...';
            document.getElementById('quizForm').submit();
        }

        // Initialize progress
        document.addEventListener('DOMContentLoaded', updateProgress);
    </script>
</x-app-layout>
