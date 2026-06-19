{{-- Halaman pengerjaan exercise/latihan koding (mahasiswa). --}}
@php
    $language = data_get($assignment->exercise_config, 'language', 'htmlmixed');
    $isServerSide = in_array($language, config('code_execution.server_side_languages'));
    $defaultTab = $isServerSide ? 'logs' : 'preview';
    $starterCode = data_get($assignment->exercise_config, 'starter_code', '');
    $languageLabel = strtoupper(str_replace('mixed', '', $language));
    $fileExt = str_replace('mixed', '', $language);
    if ($fileExt === 'javascript') $fileExt = 'js';
    if ($fileExt === 'csharp') $fileExt = 'cs';
@endphp

<x-app-layout>
    @vite(['resources/js/code-editor.js', 'resources/css/pages/mahasiswa/code-exercise.css'])

    <div x-data="solvePage({
            defaultTab: '{{ $defaultTab }}',
            isServerSide: {{ $isServerSide ? 'true' : 'false' }},
            hasSubmission: {{ $existing ? 'true' : 'false' }},
            language: '{{ $language }}'
         })"
         x-cloak
         class="solve-root solve-scroll">

        {{-- ============================================================
             MOBILE — segmented tab switcher (< md)
             ============================================================ --}}
        <div class="md:hidden px-3 pt-3 pb-2 bg-surface-container-lowest border-b border-outline-variant/15 shrink-0">
            <div class="solve-segbar" role="tablist" aria-label="Exercise sections">
                <button role="tab" type="button"
                        :aria-selected="view === 'problem'"
                        @click="view = 'problem'"
                        class="solve-seg">
                    <span class="material-symbols-outlined" style="font-size:18px;">menu_book</span>
                    Problem
                </button>
                <button role="tab" type="button"
                        :aria-selected="view === 'code'"
                        @click="view = 'code'"
                        class="solve-seg">
                    <span class="material-symbols-outlined" style="font-size:18px;">code</span>
                    Code
                </button>
                <button role="tab" type="button"
                        :aria-selected="view === 'output'"
                        @click="view = 'output'"
                        class="solve-seg">
                    <span class="material-symbols-outlined" style="font-size:18px;">terminal</span>
                    Output
                </button>
            </div>
        </div>

        {{-- ============================================================
             TABLET — "Show Problem" trigger (md, hidden on lg)
             ============================================================ --}}
        <div class="hidden md:flex lg:hidden items-center justify-between px-6 py-3 bg-surface-container-lowest border-b border-outline-variant/15 shrink-0">
            <button type="button" @click="problemOpen = true"
                    class="flex items-center gap-2 text-sm font-semibold text-on-surface hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-base">menu_book</span>
                Show Problem Description
            </button>
            <div class="flex items-center gap-2 text-xs font-semibold text-on-surface-variant">
                <span class="material-symbols-outlined text-base">code</span>
                {{ $languageLabel }}
            </div>
        </div>

        {{-- ============================================================
             MAIN — horizontal split (lg inline column · everything else overlay)
             ============================================================ --}}
        <div class="flex-1 min-h-0 flex overflow-hidden">

            {{-- Desktop-inline problem panel (lg+, collapsible) --}}
            <aside x-show="isLg && problemOpen" x-cloak
                   class="hidden lg:block w-[clamp(320px,28vw,440px)] shrink-0 overflow-y-auto solve-scroll bg-surface-container-lowest border-r border-outline-variant/15">
                <div class="flex items-center justify-end p-3">
                    <button type="button"
                            @click="problemOpen = false"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors"
                            aria-label="Hide problem panel">
                        <span class="material-symbols-outlined text-base">chevron_left</span>
                        Hide
                    </button>
                </div>
                @include('mahasiswa.exercises._solve-problem')
            </aside>

            {{-- Desktop collapsed sidebar toggle (lg+, visible when panel hidden) --}}
            <div x-show="isLg && !problemOpen" x-cloak
                 class="hidden lg:flex flex-col items-center gap-2 px-2 py-4 bg-surface-container-lowest border-r border-outline-variant/15 shrink-0">
                <button type="button"
                        @click="problemOpen = true"
                        title="Show problem panel"
                        aria-label="Show problem panel"
                        class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors">
                    <span class="material-symbols-outlined">menu_open</span>
                </button>
            </div>

            {{-- Mobile-inline problem content (<md, when Problem tab is active) --}}
            <div x-show="!isMd && view === 'problem'" x-cloak
                 class="md:hidden flex-1 min-w-0 overflow-y-auto solve-scroll bg-surface-container-lowest">
                @include('mahasiswa.exercises._solve-problem')
            </div>

            {{-- =====  EDITOR + OUTPUT zone  ===== --}}
            <section x-show="isMd || view !== 'problem'"
                     class="solve-editor flex-1 min-w-0 relative"
                     :data-editor-theme="editorTheme">

                {{-- Editor toolbar --}}
                <div x-show="showEditorArea" x-cloak
                     class="h-12 flex items-center justify-between px-4 shrink-0 border-b"
                     style="background: var(--editor-surface-raised); border-color: var(--editor-divider);">
                    <div class="flex items-center gap-1 h-full min-w-0">
                        <div class="h-full px-3 flex items-center gap-2 border-b-2 min-w-0"
                             style="background: var(--editor-surface-tab); border-color: var(--editor-accent);">
                            <span class="material-symbols-outlined text-base shrink-0"
                                  style="color: var(--editor-accent); font-variation-settings:'FILL' 1;">code</span>
                            <span class="text-xs font-semibold truncate" style="color: var(--editor-text);">
                                Main.{{ $fileExt ?: 'txt' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        {{-- Font size controls --}}
                        <div class="hidden sm:flex items-center rounded-lg"
                             style="background: rgba(128,128,128,0.08); border: 1px solid var(--editor-divider);">
                            <button type="button" class="solve-iconbtn" style="border-radius: 0.5rem 0 0 0.5rem;"
                                    @click="setFontSize(fontSize - 2)"
                                    :disabled="fontSize <= 12"
                                    aria-label="Decrease font size" title="Decrease font size (Ctrl+-)">
                                <span class="material-symbols-outlined text-base">text_decrease</span>
                            </button>
                            <span class="px-2 text-xs font-mono tabular-nums select-none"
                                  style="color: var(--editor-text-muted);"
                                  x-text="fontSize + 'px'"></span>
                            <button type="button" class="solve-iconbtn" style="border-radius: 0 0.5rem 0.5rem 0;"
                                    @click="setFontSize(fontSize + 2)"
                                    :disabled="fontSize >= 20"
                                    aria-label="Increase font size" title="Increase font size (Ctrl+=)">
                                <span class="material-symbols-outlined text-base">text_increase</span>
                            </button>
                        </div>

                        {{-- Theme toggle --}}
                        <button type="button" class="solve-iconbtn"
                                @click="toggleEditorTheme()"
                                :aria-pressed="editorTheme === 'one-light'"
                                :aria-label="editorTheme === 'dracula' ? 'Switch to light editor theme' : 'Switch to dark editor theme'"
                                :title="editorTheme === 'dracula' ? 'Light editor theme' : 'Dark editor theme'">
                            <span class="material-symbols-outlined text-base" x-show="editorTheme === 'dracula'">light_mode</span>
                            <span class="material-symbols-outlined text-base" x-show="editorTheme === 'one-light'" x-cloak>dark_mode</span>
                        </button>

                        {{-- Shortcuts help --}}
                        <button type="button" class="solve-iconbtn"
                                @click="showShortcuts = true"
                                aria-label="Keyboard shortcuts" title="Keyboard shortcuts (Ctrl+/)">
                            <span class="material-symbols-outlined text-base">keyboard</span>
                        </button>

                        {{-- Reset starter --}}
                        <button type="button" class="solve-iconbtn"
                                @click="resetCode()"
                                aria-label="Reset to starter code" title="Reset to starter code">
                            <span class="material-symbols-outlined text-base">restart_alt</span>
                        </button>
                    </div>
                </div>

                {{-- Editor canvas --}}
                <div x-show="showEditorArea" x-cloak
                     id="editor-container"
                     class="absolute left-0 right-0 overflow-hidden"
                     :style="editorPaneStyle">
                    <textarea id="code-editor" class="hidden">{{ $starterCode }}</textarea>
                </div>

                {{-- Drag-to-resize handle (lg only) --}}
                <div x-show="isLg && showEditorArea && showOutputArea" x-cloak
                     class="solve-resizer absolute left-0 right-0"
                     :style="resizerStyle"
                     :data-dragging="isResizing ? 'true' : 'false'"
                     @mousedown="startResize($event)"
                     @touchstart.passive="startResize($event)"
                     role="separator"
                     aria-orientation="horizontal"
                     aria-label="Resize editor and output"></div>

                {{-- Output panel --}}
                <div x-show="showOutputArea" x-cloak
                     class="absolute left-0 right-0 flex flex-col border-t"
                     :style="outputPaneStyle"
                     style="background: var(--editor-surface-raised); border-color: var(--editor-divider);">

                    {{-- Output toolbar --}}
                    <div class="h-10 flex items-center justify-between px-4 shrink-0 border-b"
                         style="border-color: var(--editor-divider);">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="solve-status-dot" :data-state="statusState"></span>
                                <span class="text-[11px] font-bold uppercase tracking-wider"
                                      style="color: var(--editor-text-muted);"
                                      x-text="statusLabel"></span>
                            </div>
                            <div class="flex items-center gap-1" role="tablist">
                                @if(!$isServerSide)
                                    <button role="tab" type="button"
                                            @click="outputTab = 'preview'"
                                            :aria-selected="outputTab === 'preview'"
                                            class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider transition-colors"
                                            :class="outputTab === 'preview'
                                                ? 'bg-[color:var(--editor-surface-tab)] text-[color:var(--editor-accent)]'
                                                : 'text-[color:var(--editor-text-muted)] hover:text-[color:var(--editor-text)]'">
                                        Preview
                                    </button>
                                @endif
                                <button role="tab" type="button"
                                        @click="outputTab = 'logs'"
                                        :aria-selected="outputTab === 'logs'"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider transition-colors"
                                        :class="outputTab === 'logs'
                                            ? 'bg-[color:var(--editor-surface-tab)] text-[color:var(--editor-accent)]'
                                            : 'text-[color:var(--editor-text-muted)] hover:text-[color:var(--editor-text)]'">
                                    Terminal
                                </button>
                            </div>
                        </div>

                        <button type="button" class="solve-iconbtn"
                                @click="clearLogs()"
                                aria-label="Clear terminal" title="Clear terminal">
                            <span class="material-symbols-outlined text-base">delete_sweep</span>
                        </button>
                    </div>

                    {{-- Output content --}}
                    <div class="flex-1 min-h-0 relative">
                        @if(!$isServerSide)
                            <div x-show="outputTab === 'preview'" class="absolute inset-0 bg-white">
                                <iframe id="preview-iframe" class="w-full h-full border-0" title="Code preview"></iframe>
                            </div>
                        @endif

                        <div x-show="outputTab === 'logs'"
                             class="absolute inset-0 overflow-y-auto solve-scroll p-3"
                             x-ref="logsPane">
                            <template x-if="logs.length === 0">
                                <div class="h-full flex flex-col items-center justify-center text-center px-4 py-8 gap-2"
                                     style="color: var(--editor-text-muted);">
                                    <span class="material-symbols-outlined text-3xl opacity-60">terminal</span>
                                    <p class="text-sm">Run your code to see output here.</p>
                                    <p class="text-xs opacity-70">Shortcut: <span class="solve-kbd">Ctrl</span> <span class="solve-kbd">Enter</span></p>
                                </div>
                            </template>
                            <template x-for="(entry, i) in logs" :key="i">
                                <div class="solve-log" :class="'solve-log--' + entry.type">
                                    <span class="solve-log-time" x-text="entry.time"></span>
                                    <span class="solve-log-icon" x-text="entry.icon"></span>
                                    <span class="solve-log-msg" x-text="entry.msg"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Action bar --}}
                <div x-show="showActionBar" x-cloak
                     class="absolute left-0 right-0 bottom-0 h-16 flex items-center justify-between px-4 md:px-6 border-t shrink-0 z-10"
                     style="background: var(--editor-surface-raised); border-color: var(--editor-divider);">

                    <div class="flex items-center gap-2 min-w-0">
                        <button type="button" id="run-code-btn"
                                @click="run()"
                                :disabled="!editorReady || running"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background: rgba(128,128,128,0.12); color: var(--editor-text); border: 1px solid var(--editor-divider);">
                            <span class="material-symbols-outlined text-base" x-show="!running" style="font-variation-settings:'FILL' 1;">play_arrow</span>
                            <span class="material-symbols-outlined text-base animate-spin" x-show="running" x-cloak>progress_activity</span>
                            <span x-text="running ? 'Running…' : 'Run'"></span>
                            <span class="solve-kbd hidden md:inline-flex ml-1">Ctrl+Enter</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!$existing)
                            <button type="button"
                                    @click="openSubmit()"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-primary text-on-primary hover:bg-primary-hover transition-all shadow-lg shadow-primary/20">
                                Submit Solution
                                <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">send</span>
                                <span class="solve-kbd hidden md:inline-flex ml-1" style="background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.85); border-color: rgba(255,255,255,0.2);">Ctrl+Shift+Enter</span>
                            </button>
                        @else
                            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-lg bg-secondary-container text-on-secondary-container">
                                <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">task_alt</span>
                                <span class="text-xs font-bold uppercase tracking-wider">Submitted</span>
                                <span class="text-sm font-black tabular-nums">{{ $existing->score }}/{{ $assignment->max_score }}</span>
                                @if($existing->feedback)
                                    <button type="button" @click="scrollToFeedback()"
                                            class="text-[11px] font-bold uppercase tracking-wider underline hover:no-underline">
                                        View feedback
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        {{-- ============================================================
             Tablet — problem drawer (md only, hidden on lg)
             ============================================================ --}}
        <div x-show="isMd && !isLg && problemOpen" x-cloak
             class="hidden md:flex lg:hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
             @keydown.escape.window="problemOpen = false"
             x-transition.opacity>
            <div class="w-[480px] max-w-[92vw] overflow-y-auto solve-scroll bg-surface-container-lowest shadow-2xl"
                 @click.stop>
                <div class="sticky top-0 z-10 flex items-center justify-between px-4 py-3 bg-surface-container-lowest border-b border-outline-variant/15">
                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Problem</span>
                    <button type="button" @click="problemOpen = false"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors"
                            aria-label="Close problem panel">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                @include('mahasiswa.exercises._solve-problem')
            </div>
            <div class="flex-1" @click="problemOpen = false"></div>
        </div>

        {{-- ============================================================
             Submit confirmation modal
             ============================================================ --}}
        @if(!$existing)
            <div x-show="showSubmitModal" x-cloak
                 class="solve-overlay"
                 role="dialog" aria-modal="true" aria-labelledby="submit-modal-title"
                 @click.self="showSubmitModal = false"
                 @keydown.escape.window="showSubmitModal = false"
                 x-transition.opacity>
                <div class="solve-modal p-6 md:p-7 space-y-5" @click.stop>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="submit-modal-title" class="text-xl font-extrabold tracking-tight">Submit final solution?</h3>
                            <p class="text-sm text-on-surface-variant mt-1">Once submitted, your code will be auto-graded and cannot be changed.</p>
                        </div>
                        <button type="button" @click="showSubmitModal = false"
                                class="shrink-0 w-9 h-9 inline-flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
                                aria-label="Close">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <dl class="grid grid-cols-3 gap-3 text-sm">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Language</dt>
                            <dd class="font-semibold mt-0.5">{{ $languageLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Deadline</dt>
                            <dd class="font-semibold mt-0.5">{{ $assignment->deadline?->format('d M Y, H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Lines</dt>
                            <dd class="font-semibold mt-0.5 tabular-nums" x-text="codeLineCount + ' lines'"></dd>
                        </div>
                    </dl>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">Code preview (first 10 lines)</p>
                        <pre class="solve-modal-code solve-scroll" x-text="codePreview"></pre>
                    </div>

                    <div class="flex items-start gap-2 p-3 rounded-lg bg-warning-light/50 border border-warning/30 text-[#713f12] text-xs">
                        <span class="material-symbols-outlined text-base shrink-0">warning</span>
                        <span>This submission is final. Make sure you have tested your code by clicking <strong>Run</strong> first.</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showSubmitModal = false"
                                class="px-4 py-2 rounded-lg text-sm font-bold text-on-surface hover:bg-surface-container transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="confirmSubmit()"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold bg-primary text-on-primary hover:bg-primary-hover transition-colors shadow-lg shadow-primary/20">
                            Submit Solution
                            <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">send</span>
                        </button>
                    </div>
                </div>
            </div>

            <form action="{{ route('mahasiswa.exercises.submit') }}" method="POST" id="submit-form" class="hidden">
                @csrf
                <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                <input type="hidden" name="code_answer" id="code-answer-input">
            </form>
        @endif

        {{-- ============================================================
             Keyboard shortcuts overlay
             ============================================================ --}}
        <div x-show="showShortcuts" x-cloak
             class="solve-overlay"
             role="dialog" aria-modal="true" aria-labelledby="shortcuts-title"
             @click.self="showShortcuts = false"
             @keydown.escape.window="showShortcuts = false"
             x-transition.opacity>
            <div class="solve-modal p-6 space-y-4" @click.stop>
                <div class="flex items-center justify-between">
                    <h3 id="shortcuts-title" class="text-lg font-extrabold tracking-tight">Keyboard shortcuts</h3>
                    <button type="button" @click="showShortcuts = false"
                            class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
                            aria-label="Close">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <dl class="divide-y divide-outline-variant/15 text-sm">
                    <div class="flex items-center justify-between py-2.5">
                        <dt>Run code</dt>
                        <dd><span class="solve-kbd">Ctrl</span> <span class="solve-kbd">Enter</span></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt>Open submit dialog</dt>
                        <dd><span class="solve-kbd">Ctrl</span> <span class="solve-kbd">Shift</span> <span class="solve-kbd">Enter</span></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt>Increase font size</dt>
                        <dd><span class="solve-kbd">Ctrl</span> <span class="solve-kbd">=</span></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt>Decrease font size</dt>
                        <dd><span class="solve-kbd">Ctrl</span> <span class="solve-kbd">-</span></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt>Toggle this dialog</dt>
                        <dd><span class="solve-kbd">Ctrl</span> <span class="solve-kbd">/</span></dd>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <dt>Close dialog / overlay</dt>
                        <dd><span class="solve-kbd">Esc</span></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <script>
        const PREFS_KEY = 'pbl:solve:prefs';

        function solvePage({ defaultTab, isServerSide, hasSubmission, language }) {
            return {
                // --- state ---
                view: 'code',             // mobile only: 'problem' | 'code' | 'output'
                problemOpen: true,        // desktop: inline column / tablet: drawer
                outputTab: defaultTab,
                outputPct: 35,
                fontSize: 14,
                editorTheme: 'dracula',
                running: false,
                editorReady: false,
                showSubmitModal: false,
                showShortcuts: false,
                isMd: false,
                isLg: false,
                isResizing: false,
                logs: [],
                statusState: 'idle',
                codePreview: '',
                codeLineCount: 0,

                // --- computed ---
                get showEditorArea() {
                    if (this.isMd) return true;
                    return this.view === 'code';
                },
                get showOutputArea() {
                    if (this.isMd) return true;
                    return this.view === 'output';
                },
                get showActionBar() {
                    if (this.isMd) return true;
                    return this.view !== 'problem';
                },
                get statusLabel() {
                    if (this.running) return 'Running';
                    if (this.statusState === 'error') return 'Error';
                    if (this.statusState === 'ok') return 'Ready';
                    return 'Idle';
                },
                get editorPaneStyle() {
                    const toolbarH = 48;
                    const actionH = this.showActionBar ? 64 : 0;
                    if (!this.isLg || !this.showOutputArea) {
                        return `top: ${toolbarH}px; bottom: ${actionH}px;`;
                    }
                    return `top: ${toolbarH}px; bottom: calc(${this.outputPct}% + ${actionH}px);`;
                },
                get resizerStyle() {
                    const actionH = this.showActionBar ? 64 : 0;
                    return `bottom: calc(${this.outputPct}% + ${actionH}px); transform: translateY(50%);`;
                },
                get outputPaneStyle() {
                    const actionH = this.showActionBar ? 64 : 0;
                    if (!this.isLg) {
                        const toolbarH = this.showEditorArea ? 48 : 0;
                        return `top: ${toolbarH}px; bottom: ${actionH}px;`;
                    }
                    return `top: calc(100% - ${this.outputPct}% - ${actionH}px); bottom: ${actionH}px;`;
                },

                // --- init ---
                init() {
                    this.restorePrefs();
                    this.setupMediaQueries();
                    this.initEditor();
                    this.bindShortcuts();
                    this.setupConsoleBridge();

                    if (hasSubmission) {
                        this.problemOpen = true;
                    }

                    const refresh = () => {
                        if (window.__codeEditor) {
                            this.$nextTick(() => window.__codeEditor.refresh());
                        }
                    };
                    this.$watch('showEditorArea', (v) => { if (v) refresh(); });
                    this.$watch('outputPct', refresh);
                    this.$watch('isLg', refresh);
                    window.addEventListener('resize', refresh);
                },

                restorePrefs() {
                    try {
                        const raw = localStorage.getItem(PREFS_KEY);
                        if (!raw) return;
                        const p = JSON.parse(raw);
                        if (typeof p.outputPct === 'number') this.outputPct = Math.min(75, Math.max(20, p.outputPct));
                        if ([12, 14, 16, 18, 20].includes(p.fontSize)) this.fontSize = p.fontSize;
                        if (['dracula', 'one-light'].includes(p.editorTheme)) this.editorTheme = p.editorTheme;
                    } catch (e) { /* noop */ }
                },
                savePrefs() {
                    try {
                        localStorage.setItem(PREFS_KEY, JSON.stringify({
                            outputPct: this.outputPct,
                            fontSize: this.fontSize,
                            editorTheme: this.editorTheme,
                        }));
                    } catch (e) { /* noop */ }
                },

                setupMediaQueries() {
                    const md = window.matchMedia('(min-width: 768px)');
                    const lg = window.matchMedia('(min-width: 1024px)');
                    const apply = () => {
                        this.isMd = md.matches;
                        this.isLg = lg.matches;
                        if (!this.isLg) this.problemOpen = false;
                    };
                    apply();
                    md.addEventListener('change', apply);
                    lg.addEventListener('change', apply);
                },

                async initEditor() {
                    let waited = 0;
                    while (typeof window.initCodeEditor !== 'function' && waited < 5000) {
                        await new Promise(r => setTimeout(r, 50));
                        waited += 50;
                    }
                    if (typeof window.initCodeEditor !== 'function') {
                        this.addLog('error', 'X', 'Failed to load editor. Please refresh the page.');
                        return;
                    }
                    const themeName = this.editorTheme === 'one-light' ? 'mdn-like' : 'dracula';
                    const editor = window.initCodeEditor('code-editor', {
                        mode: (window.cmModeMap && window.cmModeMap[language]) || language,
                        theme: themeName,
                        lineNumbers: true,
                        lineWrapping: true,
                        viewportMargin: Infinity,
                        fontSize: this.fontSize,
                    });
                    if (!editor) return;
                    this.editorReady = true;

                    if (!isServerSide) {
                        setTimeout(() => this.run(), 400);
                    }
                },

                setupConsoleBridge() {
                    window.addEventListener('message', (event) => {
                        const payload = event.data;
                        if (!payload || payload.__pblConsole !== true) return;
                        const type = payload.level === 'error' ? 'error' : 'ok';
                        this.addLog(type, type === 'error' ? 'X' : '>', payload.message);
                    });
                },

                // --- logs ---
                addLog(type, icon, msg) {
                    const d = new Date();
                    const time = String(d.getHours()).padStart(2, '0') + ':' +
                                 String(d.getMinutes()).padStart(2, '0') + ':' +
                                 String(d.getSeconds()).padStart(2, '0');
                    this.logs.push({ type, icon, msg, time });
                    if (this.logs.length > 500) this.logs.splice(0, this.logs.length - 500);
                    this.$nextTick(() => {
                        const el = this.$refs.logsPane;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },
                clearLogs() {
                    this.logs = [];
                    this.statusState = 'idle';
                },

                // --- actions ---
                run() {
                    if (!this.editorReady || this.running) return;
                    const editor = window.__codeEditor;
                    if (!editor) return;

                    this.running = true;
                    this.statusState = 'running';

                    if (!this.isMd) this.view = 'output';

                    if (isServerSide) {
                        this.outputTab = 'logs';
                        this.runServer();
                    } else {
                        this.outputTab = 'preview';
                        this.runClient();
                    }
                },

                runClient() {
                    const editor = window.__codeEditor;
                    try {
                        if (typeof window.runCode === 'function') {
                            window.runCode(editor, 'preview-iframe', language);
                            this.addLog('run', '>', 'Build successful · preview updated');
                            this.statusState = 'ok';
                        }
                    } catch (e) {
                        this.addLog('error', 'X', 'Client-side error: ' + (e && e.message ? e.message : e));
                        this.statusState = 'error';
                    } finally {
                        this.running = false;
                    }
                },

                async runServer() {
                    const editor = window.__codeEditor;
                    this.addLog('run', '>', `Running ${language}…`);

                    try {
                        const res = await fetch('{{ route('execute.code') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({ code: editor.getValue(), language }),
                        });

                        if (!res.ok) {
                            this.addLog('error', 'X', `Execution service returned HTTP ${res.status}`);
                            this.statusState = 'error';
                            return;
                        }

                        const data = await res.json();
                        if (data.stdout) this.addLog('ok', '>', data.stdout.trimEnd());
                        if (data.stderr) this.addLog('error', 'X', data.stderr.trimEnd());
                        if (!data.stdout && !data.stderr) this.addLog('info', 'i', '(no output)');
                        this.addLog('info', '<', `Exited with code ${data.exit_code}`);
                        this.statusState = data.stderr || data.exit_code !== 0 ? 'error' : 'ok';
                    } catch (e) {
                        this.addLog('error', '!', 'Network error. Check your connection.');
                        this.statusState = 'error';
                    } finally {
                        this.running = false;
                    }
                },

                openSubmit() {
                    if (hasSubmission) return;
                    const editor = window.__codeEditor;
                    if (!editor) {
                        alert('Editor is still loading, please wait a moment.');
                        return;
                    }
                    const code = editor.getValue();
                    const lines = code.split('\n');
                    this.codeLineCount = lines.length;
                    this.codePreview = lines.slice(0, 10).join('\n') + (lines.length > 10 ? '\n…' : '');
                    this.showSubmitModal = true;
                },

                confirmSubmit() {
                    const editor = window.__codeEditor;
                    if (!editor) return;
                    document.getElementById('code-answer-input').value = editor.getValue();
                    document.getElementById('submit-form').submit();
                },

                resetCode() {
                    if (!confirm('Reset your code to the starter template? This cannot be undone.')) return;
                    const editor = window.__codeEditor;
                    const starter = document.getElementById('code-editor').defaultValue;
                    if (editor) editor.setValue(starter);
                },

                setFontSize(n) {
                    n = Math.min(20, Math.max(12, n));
                    n = Math.round(n / 2) * 2;
                    this.fontSize = n;
                    const editor = window.__codeEditor;
                    if (editor) {
                        editor.getWrapperElement().style.fontSize = n + 'px';
                        editor.refresh();
                    }
                    this.savePrefs();
                },

                toggleEditorTheme() {
                    this.editorTheme = this.editorTheme === 'dracula' ? 'one-light' : 'dracula';
                    const editor = window.__codeEditor;
                    if (editor) editor.setOption('theme', this.editorTheme === 'one-light' ? 'mdn-like' : 'dracula');
                    this.savePrefs();
                },

                scrollToFeedback() {
                    if (this.isLg) {
                        this.problemOpen = true;
                    } else if (this.isMd) {
                        this.problemOpen = true;
                    } else {
                        this.view = 'problem';
                    }
                    this.$nextTick(() => {
                        const el = document.getElementById('solve-feedback-card');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                // --- resize ---
                startResize(e) {
                    if (!this.isLg) return;
                    this.isResizing = true;
                    const startY = e.touches ? e.touches[0].clientY : e.clientY;
                    const containerH = this.$el.closest('.solve-editor').clientHeight;
                    const startPct = this.outputPct;

                    const onMove = (ev) => {
                        const y = ev.touches ? ev.touches[0].clientY : ev.clientY;
                        const dy = startY - y;
                        const newPct = Math.min(75, Math.max(20, startPct + (dy / containerH) * 100));
                        this.outputPct = newPct;
                    };
                    const onEnd = () => {
                        this.isResizing = false;
                        this.savePrefs();
                        document.removeEventListener('mousemove', onMove);
                        document.removeEventListener('mouseup', onEnd);
                        document.removeEventListener('touchmove', onMove);
                        document.removeEventListener('touchend', onEnd);
                        document.body.style.userSelect = '';
                    };
                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onEnd);
                    document.addEventListener('touchmove', onMove, { passive: true });
                    document.addEventListener('touchend', onEnd);
                    document.body.style.userSelect = 'none';
                    if (e.preventDefault) e.preventDefault();
                },

                // --- shortcuts ---
                bindShortcuts() {
                    document.addEventListener('keydown', (e) => {
                        const mod = e.ctrlKey || e.metaKey;
                        if (!mod) {
                            if (e.key === 'Escape') {
                                if (this.showSubmitModal) this.showSubmitModal = false;
                                else if (this.showShortcuts) this.showShortcuts = false;
                            }
                            return;
                        }
                        if (e.key === 'Enter' && e.shiftKey) {
                            e.preventDefault();
                            this.openSubmit();
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            this.run();
                        } else if (e.key === '/') {
                            e.preventDefault();
                            this.showShortcuts = !this.showShortcuts;
                        } else if (e.key === '=' || e.key === '+') {
                            e.preventDefault();
                            this.setFontSize(this.fontSize + 2);
                        } else if (e.key === '-') {
                            e.preventDefault();
                            this.setFontSize(this.fontSize - 2);
                        }
                    });
                },
            };
        }

        window.solvePage = solvePage;
    </script>
</x-app-layout>
