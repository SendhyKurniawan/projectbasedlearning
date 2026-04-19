@push('styles')
    @vite('resources/css/pages/dosen/assignments.css')
@endpush
<x-app-layout>
    @vite(['resources/js/code-editor.js'])
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('dosen.assignments.index', $course) }}" class="p-2.5 bg-surface-container-lowest border border-outline-variant/30 rounded-xl hover:bg-surface-container-low transition-colors shadow-sm text-on-surface">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight tracking-tight">
                        Review Submissions
                    </h2>
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mt-1">
                        {{ $assignment->title }} <span class="mx-2">•</span> {{ $course->nama_matkul }}
                    </p>
                </div>
            </div>
            
            <div class="hidden md:flex gap-4">
                <div class="bg-surface-container-lowest px-5 py-2.5 border border-outline-variant/30 rounded-xl shadow-sm text-center">
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest block mb-0.5">Sudah Dinilai</span>
                    <span class="text-xl font-black font-headline text-primary">{{ $submissions->whereNotNull('score')->count() }}</span>
                    <span class="text-sm font-bold text-on-surface-variant">/ {{ $submissions->count() }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        @if(session('success'))
            <div class="mb-6 bg-secondary-container border border-secondary/20 text-secondary px-5 py-4 rounded-xl font-bold shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if($submissions->isEmpty())
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

                                <!-- File Attachment -->
                                @if($submission->file_path)
                                    <div>
                                        <h4 class="text-sm font-bold font-headline text-on-surface mb-3 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[20px]">attach_file</span>
                                            Lampiran File
                                        </h4>
                                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="inline-flex items-center gap-3 px-5 py-3 bg-white border border-outline-variant/30 rounded-xl hover:bg-surface-container-low hover:border-primary transition-all shadow-sm group">
                                            <div class="w-10 h-10 rounded-lg bg-error-container text-error flex items-center justify-center">
                                                <span class="material-symbols-outlined">picture_as_pdf</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Unduh / Lihat Dokumen</p>
                                                <p class="text-xs text-on-surface-variant break-all truncate max-w-[200px] md:max-w-md">{{ basename($submission->file_path) }}</p>
                                            </div>
                                        </a>

                                        @if(Str::endsWith(strtolower($submission->file_path), ['.jpg', '.jpeg', '.png', '.webp']))
                                            <div class="mt-4 border border-outline-variant/20 rounded-2xl overflow-hidden shadow-sm">
                                                 <img src="{{ Storage::url($submission->file_path) }}" class="w-full object-contain max-h-[500px] bg-surface-container-low">
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- URL Submission -->
                                @if($submission->url_link)
                                    <div>
                                        <h4 class="text-sm font-bold font-headline text-on-surface mb-3 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-[20px]">link</span>
                                            Tautan / Repositori
                                        </h4>
                                        <a href="{{ $submission->url_link }}" target="_blank" class="inline-flex items-center gap-3 px-5 py-3 bg-white border border-outline-variant/30 rounded-xl hover:bg-surface-container-low hover:border-primary transition-all shadow-sm group w-full md:w-auto">
                                            <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined">public</span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Buka Tautan Baru</p>
                                                <p class="text-xs text-on-surface-variant truncate w-full group-hover:underline">{{ $submission->url_link }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif

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

                                        <div class="border border-outline-variant/30 rounded-2xl overflow-hidden shadow-sm flex flex-col md:flex-row h-auto min-h-[400px]">
                                            <!-- Code Viewer -->
                                            <div class="flex-1 flex flex-col border-r border-outline-variant/30">
                                                <div class="bg-surface-container-low px-4 py-2 text-xs font-bold text-on-surface-variant flex justify-between items-center border-b border-outline-variant/30">
                                                    <span class="uppercase tracking-widest">Koding</span>
                                                    <span class="px-2 py-0.5 rounded bg-surface-container text-[10px]">{{ $assignment->exercise_config['language'] ?? 'htmlmixed' }}</span>
                                                </div>
                                                <div class="flex-1 w-full relative">
                                                    <textarea class="code-viewer w-full h-full absolute inset-0" id="code-viewer-{{ $submission->id }}" data-language="{{ $assignment->exercise_config['language'] ?? 'htmlmixed' }}" readonly>{{ $submission->code_answer }}</textarea>
                                                </div>
                                            </div>
                                            <!-- Output Preview -->
                                            <div class="flex-1 flex flex-col bg-white h-[400px] md:h-auto">
                                                <div class="bg-surface-container-low px-4 py-2 text-xs font-bold text-on-surface-variant flex justify-between items-center border-b border-outline-variant/30">
                                                    <span class="uppercase tracking-widest">Output Visual</span>
                                                    <button type="button" onclick="refreshIframe('preview-iframe-{{ $submission->id }}')" class="hover:text-primary transition flex items-center gap-1" title="Refresh">
                                                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                                                    </button>
                                                </div>
                                                <div class="flex-1 relative w-full h-full">
                                                    <iframe id="preview-iframe-{{ $submission->id }}" 
                                                    srcdoc="{{ ($assignment->exercise_config['language'] ?? 'htmlmixed') == 'javascript' ? '<script>' . $submission->code_answer . '<\/script><div style=\'font-family:sans-serif;padding:20px;font-size:14px;color:#333;\'>Silakan periksa console browser untuk output JS.<br><br>Atau gunakan DOM API untuk mencetak sesuatu disini.</div>' : $submission->code_answer }}" 
                                                    class="absolute inset-0 w-full h-full border-0 preview-iframe" 
                                                    sandbox="allow-scripts allow-same-origin"></iframe>
                                                </div>
                                            </div>
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
