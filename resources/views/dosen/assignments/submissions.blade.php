{{-- Halaman daftar pengumpulan tugas beserta form penilaian (dosen). --}}
@push('styles')
    @vite('resources/css/pages/dosen/assignments.css')
@endpush
<x-app-layout>
    @vite(['resources/js/code-editor.js'])
    
    <div class="w-full space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-primary transition-colors">Assignments</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary truncate max-w-[180px]">{{ $assignment->title }}</span>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">Review</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Review Submissions</h1>
                <p class="mt-1 text-sm text-on-surface-variant">{{ $assignment->title }} · {{ $course->nama_matkul }}</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="hidden md:flex bg-surface-container-lowest px-4 py-2 border border-outline-variant/30 rounded-xl shadow-sm items-center gap-3">
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Dinilai</span>
                    <span class="text-lg font-black font-headline text-primary">{{ $submissions->whereNotNull('score')->count() }}<span class="text-on-surface-variant">/{{ $submissions->count() }}</span></span>
                </div>
                <a href="{{ route('dosen.assignments.index', $course) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-secondary-container border border-secondary/20 text-secondary px-5 py-4 rounded-xl font-bold shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if(isset($steps) && $steps->isNotEmpty())
            @include('dosen.assignments._step_progress', ['assignment' => $assignment, 'steps' => $steps])
        @endif

        @if($assignment->is_group)
            @include('dosen.assignments._group_submissions', ['assignment' => $assignment, 'course' => $course, 'groups' => $groups])
        @elseif($submissions->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm">
                <span class="material-symbols-outlined text-[80px] text-primary/20 mb-4">inventory_2</span>
                <h3 class="text-xl font-bold font-headline text-on-surface">Belum Ada Pengumpulan</h3>
                <p class="text-on-surface-variant mt-2 text-sm max-w-sm text-center">Mahasiswa belum mengunggah tugas mereka. Silakan periksa kembali nanti.</p>
            </div>
        @else
            <!-- Split Pane Layout Using Alpine.js -->
            <div x-data="{ activeSubmission: {{ $submissions->first()->id }} }" class="flex flex-col lg:flex-row gap-6 lg:h-[calc(100vh-160px)] lg:min-h-[600px] mb-8">
                
                <!-- Left Sidebar: Students List -->
                <div class="w-full lg:w-[350px] shrink-0 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl flex flex-col shadow-sm overflow-hidden h-[400px] lg:h-full">
                    <div class="p-4 border-b border-outline-variant/20 bg-surface-container-low/50">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                            <input type="text" placeholder="Cari mahasiswa..." class="w-full bg-white border border-outline-variant/30 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary shadow-inner">
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-surface">
                        @foreach($submissions as $submission)
                            <button @click="activeSubmission = {{ $submission->id }}" 
                                    :class="{'bg-primary/10 border-primary shadow-sm': activeSubmission === {{ $submission->id }}, 'bg-surface-container-lowest border-outline-variant/20 hover:border-outline-variant/60 hover:bg-surface-container-low': activeSubmission !== {{ $submission->id }}}"
                                    class="w-full text-left p-4 rounded-xl border transition-all group flex gap-3 relative overflow-hidden">
                                
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-tertiary text-white flex items-center justify-center font-bold font-headline shrink-0 uppercase shadow-inner">
                                    {{ substr($submission->mahasiswa->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-sm text-on-surface truncate group-hover:text-primary transition-colors">
                                        {{ $submission->mahasiswa->name ?? 'Unknown Student' }}
                                    </h4>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-xs text-on-surface-variant truncate">
                                            {{ $submission->submitted_at->format('d M, H:i') }}
                                        </p>
                                        @if($submission->score !== null)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-secondary-container text-secondary">
                                                {{ $submission->score }}/{{ $assignment->max_score }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-warning-light text-on-warning">
                                                UNGRADED
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Right Pane: Submission Review Details -->
                <div class="flex-1 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl flex flex-col shadow-sm relative lg:h-full lg:overflow-hidden min-h-[600px]">
                    @foreach($submissions as $submission)
                        <div x-show="activeSubmission === {{ $submission->id }}" style="display: none;" class="h-full flex flex-col">
                            
                            <!-- Header Info -->
                            <div class="p-6 border-b border-outline-variant/20 shrink-0 bg-surface-container-low/30">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <h3 class="text-xl font-extrabold font-headline text-on-surface mb-1">
                                            Evaluasi Hasil: {{ $submission->mahasiswa->name ?? 'Unknown Student' }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-on-surface-variant font-medium">
                                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">mail</span> {{ $submission->mahasiswa->email ?? '-' }}</span>
                                            <span class="text-outline-variant">•</span>
                                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">schedule</span> Dikumpulkan pada {{ $submission->submitted_at->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                    @if($submission->score !== null)
                                        <div class="bg-primary/10 border border-primary/20 px-4 py-2 rounded-xl text-center shadow-sm">
                                            <p class="text-[10px] font-bold text-primary uppercase tracking-widest leading-tight">Nilai Akhir</p>
                                            <p class="text-xl font-black font-headline text-primary">{{ $submission->score }}</p>
                                        </div>
                                    @else
                                        <div class="bg-surface-container border border-outline-variant/30 px-4 py-2 rounded-xl text-center text-on-surface-variant shadow-inner">
                                            <p class="text-[10px] font-bold uppercase tracking-widest leading-tight">Status</p>
                                            <p class="text-sm font-bold font-headline mt-1">Pending Review</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Main Content Scroll Area -->
                            <div class="flex-1 overflow-y-auto p-6 bg-surface space-y-6">
                                
                                @if($submission->notes)
                                    <div class="bg-primary-container/30 border-l-4 border-primary p-5 rounded-r-xl shadow-sm">
                                        <h5 class="text-xs font-bold text-primary uppercase tracking-widest mb-2 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[16px]">format_quote</span> Catatan Mahasiswa
                                        </h5>
                                        <p class="text-sm text-on-surface leading-relaxed font-medium">{{ $submission->notes }}</p>
                                    </div>
                                @endif

                                <!-- File & URL Attachment Preview -->
                                @include('dosen.assignments._submission_attachment', ['item' => $submission])

                                <!-- Code Editor / Practical IT -->
                                @if($submission->code_answer)
                                    <div class="mt-6">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-bold font-headline text-on-surface flex items-center gap-2">
                                                <span class="material-symbols-outlined text-primary text-[20px]">code</span>
                                                Source Code Jawaban
                                            </h4>
                                            @if($submission->auto_graded || $submission->validation_result)
                                                <span class="px-3 py-1 bg-primary-container text-on-primary text-[10px] uppercase font-bold rounded-full border border-primary/20">
                                                    Sistem Validasi Otomatis
                                                </span>
                                            @endif
                                        </div>

                                        @if($submission->validation_result)
                                            <div class="mb-4 bg-surface-container-low border border-outline-variant/30 rounded-xl p-4 flex gap-4 items-start shadow-sm">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $submission->validation_result['passed'] ? 'bg-secondary-container text-secondary' : 'bg-warning-light text-on-warning' }}">
                                                    <span class="material-symbols-outlined">{{ $submission->validation_result['passed'] ? 'check_circle' : 'warning' }}</span>
                                                </div>
                                                <div>
                                                    <h5 class="text-sm font-bold text-on-surface">Validasi Mesin: {{ $submission->validation_result['passed'] ? 'Lulus / Sesuai' : 'Perlu Diperiksa Manual' }}</h5>
                                                    @if(isset($submission->validation_result['feedback']))
                                                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed whitespace-pre-line">{{ $submission->validation_result['feedback'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @php
                                            $lang = $assignment->exercise_config['language'] ?? 'htmlmixed';
                                            $isServerLang = in_array($lang, ['java', 'php', 'csharp']);
                                            $code = $submission->code_answer;
                                            if (!$isServerLang) {
                                                if ($lang === 'javascript') {
                                                    $srcdoc = '<!DOCTYPE html><html><body><script>' . $code . '<\/script></body></html>';
                                                } elseif ($lang === 'css') {
                                                    $srcdoc = '<!DOCTYPE html><html><head><style>' . $code . '</style></head><body><p style="font-family:sans-serif;padding:20px">CSS Preview</p></body></html>';
                                                } else {
                                                    $srcdoc = $code;
                                                }
                                            }
                                        @endphp
                                        <div class="border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm flex flex-col md:flex-row h-auto min-h-[400px]">
                                            <!-- Code Viewer -->
                                            <div class="{{ $isServerLang ? 'w-full' : 'flex-1' }} flex flex-col {{ $isServerLang ? '' : 'border-r border-outline-variant/30' }}">
                                                <div class="bg-surface-container-low px-4 py-2 text-xs font-bold text-on-surface-variant flex justify-between items-center border-b border-outline-variant/30">
                                                    <span class="uppercase tracking-widest">Koding</span>
                                                    <span class="px-2 py-0.5 rounded bg-surface-container text-[10px]">{{ $lang }}</span>
                                                </div>
                                                <div class="flex-1 w-full relative">
                                                    <textarea class="code-viewer w-full h-full absolute inset-0" id="code-viewer-{{ $submission->id }}" data-language="{{ $lang }}" readonly>{{ $submission->code_answer }}</textarea>
                                                </div>
                                            </div>
                                            <!-- Output Preview (browser-runnable languages only) -->
                                            @if(!$isServerLang)
                                            <div class="flex-1 flex flex-col bg-white h-[400px] md:h-auto">
                                                <div class="bg-surface-container-low px-4 py-2 text-xs font-bold text-on-surface-variant flex justify-between items-center border-b border-outline-variant/30">
                                                    <span class="uppercase tracking-widest">Output Visual</span>
                                                    <button type="button" onclick="refreshIframe('preview-iframe-{{ $submission->id }}')" class="hover:text-primary transition flex items-center gap-1" title="Refresh">
                                                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                                                    </button>
                                                </div>
                                                <div class="flex-1 relative w-full h-full">
                                                    <iframe id="preview-iframe-{{ $submission->id }}"
                                                    srcdoc="{{ $srcdoc }}"
                                                    class="absolute inset-0 w-full h-full border-0 preview-iframe"
                                                    sandbox="allow-scripts"></iframe>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                            </div>
                            
                            <!-- Form Penilaian (Bottom Anchored) -->
                            <div class="p-6 bg-surface-container-lowest border-t border-outline-variant/30 shrink-0">
                                <form action="{{ route('dosen.submissions.grade', $submission) }}" method="POST">
                                    @csrf
                                    <div class="flex flex-col md:flex-row gap-4 items-end">
                                        <div class="w-full md:w-32 shrink-0">
                                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Nilai (Maks: {{ $assignment->max_score }})</label>
                                            <input type="number" 
                                                   name="score" 
                                                   value="{{ old('score', $submission->score) }}"
                                                   min="0" 
                                                   max="{{ $assignment->max_score }}"
                                                   class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-4 py-3 font-extrabold text-on-surface text-lg focus:ring-2 focus:ring-primary shadow-inner"
                                                   placeholder="0"
                                                   required>
                                        </div>
                                        
                                        <div class="flex-1 w-full">
                                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Feedback untuk Mahasiswa</label>
                                            <input type="text" 
                                                   name="feedback" 
                                                   value="{{ old('feedback', $submission->feedback) }}"
                                                   class="w-full bg-white border border-outline-variant/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary shadow-sm"
                                                   placeholder="Berikan catatan, pujian, atau saran perbaikan (opsional)">
                                        </div>

                                        <button type="submit" class="w-full md:w-auto shrink-0 bg-primary hover:bg-primary-hover text-on-primary font-bold font-headline px-8 py-3 rounded-xl shadow-md flex items-center justify-center gap-2 transition-transform hover:scale-105 active:scale-95">
                                            <span class="material-symbols-outlined text-[20px]">save</span>
                                            {{ $submission->score !== null ? 'Perbarui' : 'Simpan Nilai' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelectorAll('.code-viewer').forEach(function(textarea) {
                    const language = textarea.getAttribute('data-language') || 'htmlmixed';
                    const editor = CodeMirror.fromTextArea(textarea, {
                        mode: language,
                        theme: 'material-darker',
                        readOnly: true,
                        lineNumbers: true,
                        lineWrapping: true,
                    });
                    
                    editor.setSize("100%", "100%");
                });
            }, 500); // Slight delay for x-show rendering, or trigger properly on Alpine init.
        });

        function refreshIframe(iframeId) {
            const iframe = document.getElementById(iframeId);
            if(iframe) {
                const clone = iframe.cloneNode(true);
                iframe.parentNode.replaceChild(clone, iframe);
            }
        }
    </script>
    @endpush
</x-app-layout>
