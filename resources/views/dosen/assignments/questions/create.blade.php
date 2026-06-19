{{-- Form tambah tugas (dosen). --}}
@push('styles')
    @vite('resources/css/pages/dosen/questions.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="hover:text-primary transition-colors">Assignments</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('dosen.assignments.questions.index', $assignment) }}" class="hover:text-primary transition-colors truncate max-w-[140px]">{{ $assignment->title }}</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">Pertanyaan Baru</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Buat Pertanyaan</h1>
                <p class="mt-1 text-sm text-on-surface-variant">{{ $assignment->title }}</p>
            </div>
            <a href="{{ route('dosen.assignments.questions.index', $assignment) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6 lg:p-10 relative overflow-hidden" x-data="{ type: '{{ old('question_type', 'pilihan_ganda') }}' }">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-tertiary"></div>
            
            <form action="{{ route('dosen.assignments.questions.store', $assignment) }}" method="POST">
                @csrf
                
                <div class="space-y-8">
                    <!-- Question Text -->
                    <div>
                        <label for="question_text" class="block text-xs font-bold font-headline uppercase tracking-widest text-primary mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">format_align_left</span> Teks Pertanyaan
                        </label>
                        <textarea name="question_text" id="question_text" rows="4" required
                            class="w-full bg-surface border border-outline-variant/30 rounded-xl px-4 py-3 text-base text-on-surface focus:ring-2 focus:ring-primary shadow-inner transition-shadow placeholder:text-on-surface-variant/50" placeholder="Tuliskan pertanyaan lengkap Anda di sini...">{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <p class="text-error text-xs font-bold mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-surface-container-low/30 p-6 rounded-2xl border border-outline-variant/20">
                        <!-- Type -->
                        <div>
                            <label for="question_type" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Tipe Pertanyaan
                            </label>
                            <div class="relative">
                                <select name="question_type" id="question_type" x-model="type" required
                                    class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl pl-4 pr-10 py-3 text-sm font-bold text-on-surface focus:ring-2 focus:ring-primary appearance-none cursor-pointer shadow-sm">
                                    <option value="pilihan_ganda">Pilihan Ganda (Multiple Choice)</option>
                                    <option value="essay">Uraian (Essay)</option>
                                    <option value="code_snippet">Snippet Kode / Praktik IT</option>
                                </select>
                            </div>
                        </div>

                        <!-- Score Weight -->
                        <div>
                            <label for="score_weight" class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">
                                Bobot Nilai
                            </label>
                            <input type="number" name="score_weight" id="score_weight" value="{{ old('score_weight', 1) }}" required min="1"
                                class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl px-4 py-3 text-sm font-bold text-on-surface focus:ring-2 focus:ring-primary shadow-inner">
                            <p class="text-[10px] uppercase font-bold text-on-surface-variant mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">info</span> Poin nilai jika jawaban benar.</p>
                        </div>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div x-show="type === 'pilihan_ganda'" style="display: none;" class="space-y-4">
                        <label class="block text-xs font-bold font-headline uppercase tracking-widest text-primary mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">rule</span> Opsi Jawaban
                        </label>
                        <p class="text-xs text-on-surface-variant mb-4 font-medium">Isi teks pada masing-masing opsi, dan pilih <strong>radio button</strong> di sebelah kiri untuk opsi yang merupakan kunci jawaban benar.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @for($i = 0; $i < 4; $i++)
                                <div class="relative group">
                                    <div class="flex items-center gap-3 p-3 bg-surface-container-lowest border border-outline-variant/30 rounded-xl shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                                        <div class="flex items-center justify-center shrink-0">
                                            <input type="radio" name="correct_idx" id="opt_{{ $i }}" @click="$refs.correctIdxInput_{{ $i }}.checked = true; $refs.correctInput_{{ $i }}.value = 1" 
                                                class="w-5 h-5 text-secondary dark:text-secondary-fixed-dim border-outline-variant/40 bg-surface-container-high focus:ring-secondary/30 cursor-pointer transition-colors"
                                                {{ $i === 0 ? 'checked' : '' }}>
                                        </div>
                                        <label for="opt_{{ $i }}" class="w-6 h-6 rounded bg-surface-container-highest text-on-surface text-[10px] font-bold flex items-center justify-center shrink-0 border border-outline-variant/20 uppercase tracking-widest cursor-pointer">
                                            {{ chr(65 + $i) }}
                                        </label>
                                        <input type="hidden" name="options[{{ $i }}][is_correct]" x-ref="correctInput_{{ $i }}" value="{{ $i === 0 ? '1' : '0' }}">
                                        <input type="text" name="options[{{ $i }}][text]" value="{{ old("options.{$i}.text") }}" placeholder="Ketikan opsi jawaban..."
                                            class="flex-1 bg-transparent border-none text-sm font-semibold text-on-surface placeholder:text-on-surface-variant/50 focus:ring-0 px-0 h-8">
                                    </div>
                                </div>
                            @endfor
                        </div>
                        @error('options')
                            <p class="text-error text-xs font-bold mt-2"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Correct Answer (Essay/Code) -->
                    <div x-show="type !== 'pilihan_ganda'" style="display: none;" class="space-y-4">
                        <label for="correct_answer" class="block text-xs font-bold font-headline uppercase tracking-widest text-primary mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">verified</span> Kunci / Referensi Jawaban (Opsional)
                        </label>
                        <textarea name="correct_answer" id="correct_answer" rows="4"
                            class="w-full bg-surface border border-outline-variant/30 rounded-xl px-4 py-3 text-sm font-mono text-on-surface shadow-inner focus:ring-2 focus:ring-primary placeholder:font-body placeholder:text-on-surface-variant/50"
                            placeholder="Ketikkan rubrik penilaian, kunci jawaban essay, atau referensi kode..."></textarea>
                    </div>

                    <div class="pt-6 border-t border-outline-variant/20 flex flex-col sm:flex-row justify-end gap-3">
                        <a href="{{ route('dosen.assignments.questions.index', $assignment) }}" class="px-6 py-3 border border-outline-variant/30 text-on-surface-variant font-bold rounded-xl hover:bg-surface-container transition-colors text-center">Batalkan</a>
                        <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary/90 text-on-primary font-bold rounded-xl shadow-md flex items-center justify-center gap-2 transition-transform hover:scale-105 active:scale-95">
                            <span class="material-symbols-outlined text-[20px]">save</span> Simpan Pertanyaan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[type=radio][name=correct_idx]').forEach((radio, index) => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('input[type=hidden][name^="options"][name$="[is_correct]"]').forEach(hidden => {
                    hidden.value = '0';
                });
                const hiddenInput = document.querySelector(`input[name="options[${index}][is_correct]"]`);
                if(hiddenInput) hiddenInput.value = '1';
                
                // UX Enhancement: remove border from unselected, add to selected parent div could be added here
            });
        });
    </script>
</x-app-layout>
