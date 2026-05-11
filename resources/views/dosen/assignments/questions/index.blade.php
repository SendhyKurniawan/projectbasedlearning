@push('styles')
    @vite('resources/css/pages/dosen/questions.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="hover:text-primary transition-colors">Assignments</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary truncate max-w-[160px]">{{ $assignment->title }}</span>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">Bank Soal</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Bank Soal</h1>
                <p class="mt-1 text-sm text-on-surface-variant">{{ $assignment->title }} · {{ $questions->count() }} pertanyaan</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </a>
                <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                    <span class="material-symbols-outlined text-base">add</span> Buat Pertanyaan
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-2 bg-secondary-container border border-secondary/20 text-secondary px-5 py-4 rounded-xl font-bold shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse($questions as $index => $question)
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-6 md:p-8 relative overflow-hidden group">
                    <!-- Decorator Strip -->
                    <div class="absolute left-0 top-0 bottom-0 w-2 {{ $question->question_type === 'pilihan_ganda' ? 'bg-secondary' : ($question->question_type === 'code_snippet' ? 'bg-tertiary' : 'bg-primary') }}"></div>
                    
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
                                        <div class="flex items-center p-3 md:p-4 rounded-xl border {{ $option->is_correct ? 'border-secondary/30 bg-secondary-container shadow-sm' : 'border-outline-variant/20 bg-surface' }} transition-colors">
                                            @if($option->is_correct)
                                                <span class="material-symbols-outlined text-secondary mr-3">check_circle</span>
                                            @else
                                                <div class="w-6 h-6 rounded-full border-2 border-outline-variant/40 mr-3 shrink-0"></div>
                                            @endif
                                            <span class="text-sm {{ $option->is_correct ? 'text-on-surface font-bold' : 'text-on-surface-variant font-medium' }}">
                                                {{ $option->option_text }}
                                            </span>
                                            @if($option->is_correct)
                                                <span class="ml-auto px-2 py-1 bg-secondary-container text-secondary text-[10px] font-bold uppercase rounded">Kunci Jawaban</span>
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
                    <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="flex items-center gap-2 px-8 py-3 bg-primary hover:bg-primary/90 text-on-primary rounded-xl shadow-md font-bold transition-all hover:scale-105 active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        Buat Pertanyaan Pertama
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
