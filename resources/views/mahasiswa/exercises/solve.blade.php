<x-app-layout>
    @vite(['resources/js/code-editor.js'])
    
    <div class="flex flex-col h-[calc(100vh-80px)] -mt-6">
        <!-- Main Interface: Split Screen -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- Left Side: Instructions & Requirements -->
            <section class="w-1/3 min-w-[380px] bg-surface-container-low border-r border-outline-variant/10 overflow-y-auto p-8 custom-scrollbar">
                <nav class="flex items-center gap-2 text-[10px] font-black font-headline text-on-surface-variant/60 uppercase tracking-widest mb-6 ">
                    <span><a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" class="hover:text-primary transition-colors">{{ $assignment->course->kode_matkul }}</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary ">Code Exercise</span>
                </nav>

                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2 py-0.5 bg-secondary-container text-on-secondary-container text-[8px] font-black rounded uppercase tracking-widest ">Assignment #{{ $assignment->assignment_number }}</span>
                    <span class="text-on-surface-variant text-[10px] font-bold ">• {{ $assignment->max_score }} Points Max</span>
                </div>
                
                <h2 class="text-3xl font-headline font-black text-on-surface uppercase tracking-tighter leading-tight mb-8">{{ $assignment->title }}</h2>

                @if($existing)
                    <div class="mb-8 p-5 bg-secondary/5 border border-secondary/20 rounded-2xl space-y-3 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <span class="material-symbols-outlined text-4xl">verified</span>
                        </div>
                        <h4 class="text-xs font-black text-secondary uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">task_alt</span> SOLVED & SUBMITTED
                        </h4>
                        <div class="flex justify-between items-center text-on-surface ">
                            <span class="text-[10px] font-bold opacity-60">Your Score:</span>
                            <span class="text-xl font-black">{{ $existing->score }}/{{ $assignment->max_score }}</span>
                        </div>
                        @if($existing->feedback)
                            <div class="pt-3 border-t border-secondary/10">
                                <p class="text-[10px] font-bold text-secondary uppercase mb-1">Feedback:</p>
                                <p class="text-xs text-on-surface-variant leading-relaxed opacity-90">{{ $existing->feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="prose prose-slate prose-sm max-w-none space-y-8">
                    <div>
                        <h4 class="text-[10px] font-black text-on-surface-variant uppercase tracking-[0.2em] mb-4 opacity-60">PROBLEM STATEMENT</h4>
                        <div class="text-on-surface leading-loose ">
                            {{ $assignment->description }}
                        </div>
                    </div>

                    <!-- Requirements Box -->
                    @if(!empty(data_get($assignment->exercise_config, 'hints', [])))
                        <div class="bg-primary/5 rounded-[2rem] p-6 border border-primary/10 relative overflow-hidden">
                            <div class="absolute -right-4 -top-4 opacity-5">
                                <span class="material-symbols-outlined text-6xl">lightbulb</span>
                            </div>
                            <h4 class="text-[10px] font-black text-primary uppercase tracking-widest mb-4 ">KEY REQUIREMENTS & HINTS</h4>
                            <ul class="space-y-4 m-0 p-0 list-none">
                                @foreach($assignment->exercise_config['hints'] as $hint)
                                    <li class="flex items-start gap-3 m-0 text-xs text-on-surface-variant">
                                        <span class="material-symbols-outlined text-primary text-sm mt-0.5">check_circle</span>
                                        <span>{{ $hint }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="pt-6">
                        <div class="flex items-center gap-3 p-4 bg-surface-container rounded-2xl border border-outline-variant/10 ">
                            <span class="material-symbols-outlined text-on-surface-variant" style="font-variation-settings: 'FILL' 1;">timer</span>
                            <div class="flex-1">
                                <p class="text-[9px] font-black uppercase text-on-surface-variant opacity-60 leading-none">Batas Waktu</p>
                                <p class="text-xs font-bold text-on-surface">{{ $assignment->deadline->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right Side: Code Editor -->
            <section class="flex-1 flex flex-col bg-[#1e2029] relative">
                <!-- Editor Toolbar -->
                <div class="h-12 bg-[#191b23] border-b border-white/5 flex items-center justify-between px-6 shrink-0">
                    <div class="flex items-center gap-1 h-full">
                        <div class="h-full border-b-2 border-primary bg-[#282a36] px-4 flex items-center gap-2 group cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-[#f1fa8c]" style="font-variation-settings: 'FILL' 1;">code</span>
                            <span class="text-[10px] font-black text-on-primary-container uppercase tracking-widest">Main.{{ str_replace('mixed', '', data_get($assignment->exercise_config, 'language', 'html')) }}</span>
                        </div>
                        <div class="h-full px-4 flex items-center gap-2 opacity-40 hover:opacity-100 transition-opacity cursor-pointer text-white">
                            <span class="material-symbols-outlined text-sm">description</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest">ReadMe.md</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-[10px] font-bold text-outline-variant uppercase tracking-widest animate-pulse">Autosaving...</span>
                        <div class="w-px h-4 bg-white/10"></div>
                        <span class="material-symbols-outlined text-on-surface-variant text-sm cursor-pointer hover:text-white transition-colors" data-icon="settings">settings</span>
                    </div>
                </div>

                <!-- Editor Canvas -->
                <div class="flex-1 overflow-hidden relative" id="editor-container">
                    <textarea id="code-editor" class="hidden">{{ data_get($assignment->exercise_config, 'starter_code', '') }}</textarea>
                </div>

                <!-- Resizable Dashboard (Preview & Logs) -->
                <div x-data="{ tab: 'preview' }" class="h-1/3 min-h-[200px] border-t border-white/10 bg-[#191b23] flex flex-col shrink-0">
                    <!-- Dashboard Tabs -->
                    <div class="h-10 bg-[#1e2029] border-b border-white/5 flex items-center px-6 gap-6 shrink-0">
                        <button @click="tab = 'preview'" :class="tab === 'preview' ? 'text-primary border-b-2 border-primary ' : 'text-outline-variant hover:text-white '" class="h-full text-[10px] font-black uppercase tracking-widest transition-all">OUTPUT PREVIEW</button>
                        <button @click="tab = 'logs'" :class="tab === 'logs' ? 'text-primary border-b-2 border-primary ' : 'text-outline-variant hover:text-white '" class="h-full text-[10px] font-black uppercase tracking-widest transition-all">TERMINAL LOGS</button>
                    </div>

                    <!-- Dashboard Content -->
                    <div class="flex-1 overflow-hidden">
                        <div x-show="tab === 'preview'" class="h-full bg-white relative">
                            <iframe id="preview-iframe" class="w-full h-full border-none"></iframe>
                        </div>
                        <div x-show="tab === 'logs'" class="h-full p-4 font-mono text-[11px] text-surface-dim space-y-1 overflow-y-auto custom-scrollbar">
                            <p class="text-secondary/60">&gt; Workspace initialized successfully.</p>
                            <p class="text-on-surface-variant/40 ">&gt; Listening for code changes...</p>
                            <div id="terminal-output" class="pt-2"></div>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="h-16 bg-[#1e2029] border-t border-white/10 px-6 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-4">
                            <button type="button" onclick="runCodePreview()" class="px-6 py-2 bg-white/5 text-white hover:bg-white/10 border border-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 ">
                                <span class="material-symbols-outlined text-sm">play_arrow</span> RUN CODE
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            @if(!$existing)
                                <form action="{{ route('mahasiswa.exercises.submit') }}" method="POST" id="submit-form">
                                    @csrf
                                    <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                                    <input type="hidden" name="code_answer" id="code-answer-input">
                                    <button type="button" onclick="confirmSubmit()" class="px-10 py-2.5 bg-primary text-on-primary rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:shadow-2xl hover:shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2 shadow-lg shadow-black/20">
                                        SUBMIT SOLUTION
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">send</span>
                                    </button>
                                </form>
                            @else
                                <div class="px-6 py-2.5 bg-secondary/10 text-secondary border border-secondary/20 rounded-xl text-[10px] font-black uppercase tracking-widest ">
                                    SUBMITTED
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.1); }
        .CodeMirror { height: 100% !important; font-family: 'JetBrains Mono', monospace; font-size: 14px; background: transparent !important; }
        .CodeMirror-gutters { background: transparent !important; border-right: 1px solid rgba(255, 255, 255, 0.05) !important; }
        .CodeMirror-linenumber { color: rgba(255, 255, 255, 0.15) !important; padding: 0 10px !important; }
    </style>

    <script>
        let codeEditor;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize CodeMirror via our helper
            if (typeof initCodeEditor === 'function') {
                codeEditor = initCodeEditor('code-editor', {
                    mode: '{{ data_get($assignment->exercise_config, 'language', 'htmlmixed') }}',
                    theme: 'dracula',
                    lineNumbers: true,
                    lineWrapping: true,
                    viewportMargin: Infinity
                });
                
                // Initial Preview
                setTimeout(() => runCodePreview(), 500);
            }
        });

        function runCodePreview() {
            if (typeof runCode === 'function' && codeEditor) {
                runCode(codeEditor, 'preview-iframe');
                
                // Add log to terminal
                const log = document.getElementById('terminal-output');
                const time = new Date().toLocaleTimeString();
                log.innerHTML += `<p class="text-secondary/80"> &gt; [${time}] Build successful. Updated preview.</p>`;
                log.scrollTop = log.scrollHeight;
            }
        }

        function confirmSubmit() {
            if (!confirm('Apakah anda yakin ingin mengirimkan solusi ini? Pastikan kode telah berjalan dengan benar pada preview.')) {
                return;
            }

            if (codeEditor) {
                const code = codeEditor.getValue();
                document.getElementById('code-answer-input').value = code;
                document.getElementById('submit-form').submit();
            } else {
                alert('Sistem sedang memuat, silakan tunggu sebentar...');
            }
        }
    </script>
</x-app-layout>
