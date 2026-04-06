<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="p-2.5 w-10 h-10 flex items-center justify-center bg-white border border-outline-variant/30 rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-on-surface">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-extrabold font-headline tracking-tight text-on-surface">Questions Bank</h2>
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mt-1">{{ $assignment->title }}</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="bg-surface-container-lowest px-4 py-2 border border-outline-variant/30 rounded-xl shadow-sm text-center">
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest block mb-0.5">Total Pertanyaan</span>
                    <span class="text-lg font-extrabold text-primary leading-none">{{ $questions->count() }}</span>
                </div>
                <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="flex items-center gap-2 h-full px-6 bg-primary hover:bg-primary/90 text-white rounded-xl shadow-md font-bold transition-all hover:scale-105 active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    Buat Pertanyaan
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-2 bg-emerald-50 border border-emerald-200 text-secondary-fixed-variant px-5 py-4 rounded-xl font-bold shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse($questions as $index => $question)
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-6 md:p-8 relative overflow-hidden group">
                    <!-- Decorator Strip -->
                    <div class="absolute left-0 top-0 bottom-0 w-2 {{ $question->question_type === 'pilihan_ganda' ? 'bg-secondary' : ($question->question_type === 'code_snippet' ? 'bg-violet-500' : 'bg-primary') }}"></div>
                    
                    <div class="flex justify-between items-start pl-4">
                        <div class="flex-1 pr-6">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center font-bold text-sm text-on-surface">
                                    {{ $index + 1 }}
                                </div>
                                <span class="bg-primary/10 border border-primary/20 text-primary text-[10px] font-bold px-3 py-1 rounded-md uppercase tracking-widest">
                                    {{ str_replace('_', ' ', $question->question_type) }}
                                </span>
                                <span class="text-[10px] font-bold text-on-surface-variant bg-surface-container px-3 py-1 rounded-md uppercase tracking-widest flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">balance</span> Bobot: {{ $question->score_weight }}
                                </span>
                            </div>
                            
                            <h3 class="text-on-surface text-lg md:text-xl font-extrabold font-headline leading-relaxed mb-6">
                                {{ $question->question_text }}
                            </h3>

                            @if($question->question_type === 'pilihan_ganda')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                                    @foreach($question->options as $option)
                                        <div class="flex items-center p-3 md:p-4 rounded-xl border {{ $option->is_correct ? 'border-emerald-300 bg-emerald-50 shadow-sm' : 'border-outline-variant/20 bg-surface' }} transition-colors">
                                            @if($option->is_correct)
                                                <span class="material-symbols-outlined text-emerald-600 mr-3">check_circle</span>
                                            @else
                                                <div class="w-6 h-6 rounded-full border-2 border-outline-variant/40 mr-3 shrink-0"></div>
                                            @endif
                                            <span class="text-sm {{ $option->is_correct ? 'text-emerald-900 font-bold' : 'text-on-surface-variant font-medium' }}">
                                                {{ $option->option_text }}
                                            </span>
                                            @if($option->is_correct)
                                                <span class="ml-auto px-2 py-1 bg-emerald-100/50 text-emerald-800 text-[10px] font-bold uppercase rounded">Kunci Jawaban</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'code_snippet' || $question->question_type === 'essay')
                                <div class="mt-4 p-5 bg-surface-container-low rounded-xl border border-outline-variant/30 relative">
                                    <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-3 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">verified</span> Kunci Jawaban Benar / Referensi:
                                    </p>
                                    <pre class="text-sm text-on-surface whitespace-pre-wrap font-mono">{{ $question->correct_answer ?? '(Tidak ada kunci jawaban tertulis)' }}</pre>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex flex-col gap-2 shrink-0">
                            <a href="{{ route('dosen.assignments.questions.edit', $question) }}" class="p-2 border border-outline-variant/30 rounded-xl text-primary hover:bg-primary/10 transition-colors" title="Edit Pertanyaan">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <form action="{{ route('dosen.assignments.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini secara permanen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 border border-outline-variant/30 rounded-xl text-error hover:bg-error-container hover:text-on-error-container transition-colors" title="Hapus Pertanyaan">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 px-4 text-center bg-surface-container-lowest rounded-2xl border border-outline-variant/30 shadow-sm">
                    <span class="material-symbols-outlined text-[80px] text-primary/20 mb-4">quiz</span>
                    <h3 class="text-xl font-bold font-headline text-on-surface">Bank Soal Masih Kosong</h3>
                    <p class="text-on-surface-variant text-sm mt-2 max-w-md mb-8">Belum ada pertanyaan yang ditambahkan ke evaluasi ini. Mulai tambahkan pertanyaan untuk menguji pemahaman mahasiswa.</p>
                    <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="flex items-center gap-2 px-8 py-3 bg-primary hover:bg-primary/90 text-white rounded-xl shadow-md font-bold transition-all hover:scale-105 active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        Buat Pertanyaan Pertama
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
