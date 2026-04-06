<x-app-layout>
    @vite(['resources/js/markdown-editor.js'])
    
    @php
        $totalItems = count($learningPath);
        $completedItems = collect($learningPath)->where('completed', true)->count();
        $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
    @endphp

    <div class="flex flex-col lg:flex-row gap-8 min-h-[calc(100vh-120px)]">
        <!-- Sidebar: Learning Path (Fixed on Desktop) -->
        <aside class="lg:w-80 flex-shrink-0">
            <div class="sticky top-24 space-y-6">
                <!-- Course Progress Card -->
                <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/10 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-headline font-black italic text-on-surface uppercase tracking-tighter">Progres Belajar</h3>
                        <span class="text-secondary font-black italic">{{ $progress }}%</span>
                    </div>
                    <div class="h-1.5 bg-surface-container rounded-full overflow-hidden mb-6">
                        <div class="h-full bg-secondary rounded-full shadow-[0_0_10px_rgba(33,197,94,0.3)] transition-all duration-1000" style="width: {{ $progress }}%"></div>
                    </div>
                    
                    <!-- Scrollable Path -->
                    <div class="space-y-2 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($learningPath as $index => $item)
                            @php
                                $isCurrent = $index === $currentIndex;
                                $isCompleted = $item['completed'];
                                $isLocked = $item['locked'];
                                
                                if ($item['type'] === 'material') {
                                    $itemUrl = route('mahasiswa.materials.show', [$course, $item['item']->id]);
                                } else {
                                    if ($isLocked) { $itemUrl = null; }
                                    else {
                                        $itemUrl = $item['item']->type === 'exercise'
                                            ? route('mahasiswa.exercises.solve', $item['item'])
                                            : route('mahasiswa.submissions.create', ['assignment_id' => $item['item']->id]);
                                    }
                                }
                            @endphp

                            @if($itemUrl)
                                <a href="{{ $itemUrl }}" 
                                   class="group flex items-center gap-3 p-3 rounded-2xl transition-all border-l-4 
                                   {{ $isCurrent 
                                        ? 'bg-primary/5 border-primary shadow-sm' 
                                        : ($isCompleted 
                                            ? 'bg-secondary/5 border-secondary/40 hover:bg-secondary/10' 
                                            : 'bg-surface-container-low border-transparent hover:border-outline-variant/30 hover:bg-white') 
                                   }}">
                                    <div class="flex-shrink-0">
                                        @if($isCurrent)
                                            <div class="w-8 h-8 rounded-lg bg-primary text-on-primary flex items-center justify-center shadow-lg shadow-primary/20">
                                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                                            </div>
                                        @elseif($isCompleted)
                                            <div class="w-8 h-8 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center border border-secondary/20">
                                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-on-surface-variant border border-outline-variant/10">
                                                <span class="material-symbols-outlined text-[18px]">@if($item['type'] === 'material') article @else assignment @endif</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[9px] font-black uppercase tracking-widest italic leading-none mb-1 {{ $isCurrent ? 'text-primary' : 'text-on-surface-variant opacity-60' }}">
                                            {{ $item['type'] === 'material' ? 'Materi' : ($item['item']->type === 'exercise' ? 'Koding' : 'Tugas') }}
                                        </p>
                                        <p class="text-xs font-bold truncate leading-tight italic {{ $isCurrent ? 'text-on-surface' : ($isCompleted ? 'text-on-surface/70' : 'text-on-surface/50') }}">
                                            {{ $item['item']->title }}
                                        </p>
                                    </div>
                                </a>
                            @else
                                <div class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-low border-l-4 border-transparent opacity-40 grayscale cursor-not-allowed">
                                    <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[18px]">lock</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[9px] font-black uppercase tracking-widest italic leading-none mb-1">Locked</p>
                                        <p class="text-xs font-bold truncate leading-tight italic">{{ $item['item']->title }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Help/TA Card -->
                <div class="bg-primary rounded-[2rem] p-6 text-on-primary shadow-xl shadow-primary/10 relative overflow-hidden group">
                    <div class="relative z-10">
                        <h4 class="font-headline font-black italic mb-2 uppercase tracking-tighter">Butuh Bantuan?</h4>
                        <p class="text-xs text-primary-fixed leading-relaxed mb-4 italic opacity-80">Hubungi dosen atau asisten praktikum jika anda menemui kendala dalam memahami materi ini.</p>
                        <a href="{{ route('mahasiswa.dashboard') }}" class="inline-block bg-white text-primary font-black text-[10px] uppercase tracking-widest px-4 py-2 rounded-xl hover:bg-primary-fixed transition-all italic">
                            DISCUSSION FORUM
                        </a>
                    </div>
                    <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-7xl opacity-10 rotate-12" style="font-variation-settings: 'FILL' 1;">help_center</span>
                </div>
            </div>
        </aside>

        <!-- Main Content area -->
        <main class="flex-1 min-w-0 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-widest mb-2 italic">
                        <span><a href="{{ route('mahasiswa.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->kode_matkul }}</a></span>
                        <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                        <span class="text-primary">Materi #{{ $material->order }}</span>
                    </nav>
                    <h1 class="text-4xl font-headline font-black text-on-surface italic uppercase tracking-tighter leading-tight">{{ $material->title }}</h1>
                </div>
                <div class="flex gap-2">
                    <button class="bg-secondary-container text-on-secondary-container px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-2 shadow-sm italic hover:translate-y-[-1px] transition-all">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">bookmark</span>
                        SIMPAN
                    </button>
                    @if($material->file_path)
                        <a href="{{ Storage::url($material->file_path) }}" download class="bg-surface-container-high text-on-surface-variant px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-2 shadow-sm italic hover:translate-y-[-1px] transition-all">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            FILE
                        </a>
                    @endif
                </div>
            </div>

            <!-- Content Container -->
            <div class="bg-surface-container-lowest rounded-[2.5rem] border border-outline-variant/10 shadow-sm overflow-hidden flex flex-col">
                <!-- Main Body -->
                <div class="p-8 md:p-12 space-y-10">
                    <!-- Markdown Content -->
                    @if($material->content)
                        <div class="prose prose-slate max-w-none prose-headings:font-headline prose-headings:italic prose-headings:font-black prose-headings:uppercase prose-headings:tracking-tighter prose-strong:text-on-surface markdown-content" 
                             id="material-content" 
                             data-markdown="{{ base64_encode($material->content) }}">
                        </div>
                    @endif

                    <!-- File Section -->
                    @if($material->file_path)
                        <div x-data="{ fullscreen: false }" class="space-y-4">
                            <div class="flex items-center justify-between px-2">
                                <h3 class="text-lg font-headline font-black italic uppercase tracking-tighter text-on-surface/80 flex items-center gap-3">
                                    <span class="w-8 h-1 bg-primary rounded-full"></span>
                                    DOKUMEN LAMPIRAN
                                </h3>
                                <button @click="fullscreen = !fullscreen" class="text-[10px] font-black uppercase text-primary tracking-widest italic flex items-center gap-2 hover:underline">
                                    <span class="material-symbols-outlined text-[18px]">@if(true) open_in_full @endif</span>
                                    <span x-text="fullscreen ? 'EXIT FULLSCREEN' : 'FULLSCREEN VIEW'"></span>
                                </button>
                            </div>

                            <div :class="{'fixed inset-0 z-[100] bg-black/95 flex flex-col p-4 md:p-10': fullscreen, 'relative overflow-hidden rounded-[2rem] border border-outline-variant/10 bg-surface-container-low': !fullscreen}">
                                @if(Str::endsWith(strtolower($material->file_path), ['.pdf']))
                                    <iframe src="{{ Storage::url($material->file_path) }}" 
                                            class="w-full transition-all duration-500" 
                                            :class="{'h-full rounded-2xl': fullscreen, 'h-[800px]': !fullscreen}" 
                                            frameborder="0"></iframe>
                                @elseif(Str::endsWith(strtolower($material->file_path), ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp']))
                                    <div class="flex justify-center items-center p-4" :class="{'h-full': fullscreen}">
                                        <img src="{{ Storage::url($material->file_path) }}" 
                                             class="max-w-full h-auto rounded-xl shadow-lg border border-white/10" 
                                             alt="{{ $material->title }}">
                                    </div>
                                @endif
                                
                                <button x-show="fullscreen" @click="fullscreen = false" class="absolute top-6 right-6 w-12 h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-all">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Navigation -->
                <div class="p-6 md:p-8 bg-surface-container-low border-t border-outline-variant/10 mt-auto">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        @if($prevItem)
                            @php
                                if ($prevItem['type'] === 'material') {
                                    $prevUrl = route('mahasiswa.materials.show', [$course, $prevItem['item']->id]);
                                } else {
                                    $prevUrl = $prevItem['item']->type === 'exercise'
                                        ? route('mahasiswa.exercises.solve', $prevItem['item'])
                                        : route('mahasiswa.submissions.create', ['assignment_id' => $prevItem['item']->id]);
                                }
                            @endphp
                            <a href="{{ $prevUrl }}" class="group w-full md:w-auto flex items-center gap-4 p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/10 hover:border-primary/30 hover:shadow-lg transition-all shadow-sm">
                                <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center group-hover:bg-primary group-hover:text-on-primary transition-all">
                                    <span class="material-symbols-outlined">arrow_back</span>
                                </div>
                                <div class="text-left">
                                    <p class="text-[9px] font-black uppercase text-on-surface-variant italic opacity-60">PREVIOUS MODULE</p>
                                    <p class="text-xs font-bold text-on-surface italic">{{ Str::limit($prevItem['item']->title, 35) }}</p>
                                </div>
                            </a>
                        @else
                            <div class="hidden md:block"></div>
                        @endif

                        <div class="hidden md:flex flex-col items-center">
                            <span class="text-[10px] font-black text-on-surface-variant italic uppercase tracking-[0.3em]">FINISHED?</span>
                            <div class="w-8 h-1 bg-outline-variant/20 rounded-full mt-1"></div>
                        </div>

                        @if($nextItem)
                            @if($nextItem['locked'])
                                <div class="w-full md:w-auto flex items-center gap-4 p-4 rounded-2xl bg-surface-container-low border border-outline-variant/10 opacity-50 grayscale cursor-not-allowed">
                                    <div class="text-right">
                                        <p class="text-[9px] font-black uppercase text-on-surface-variant italic leading-none mb-1">NEXT (LOCKED)</p>
                                        <p class="text-xs font-bold text-on-surface italic">{{ Str::limit($nextItem['item']->title, 35) }}</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center">
                                        <span class="material-symbols-outlined">lock</span>
                                    </div>
                                </div>
                            @else
                                @php
                                    if ($nextItem['type'] === 'material') {
                                        $nextUrl = route('mahasiswa.materials.show', [$course, $nextItem['item']->id]);
                                    } else {
                                        $nextUrl = $nextItem['item']->type === 'exercise'
                                            ? route('mahasiswa.exercises.solve', $nextItem['item'])
                                            : route('mahasiswa.submissions.create', ['assignment_id' => $nextItem['item']->id]);
                                    }
                                @endphp
                                <a href="{{ $nextUrl }}" class="group w-full md:w-auto flex items-center gap-4 p-4 rounded-2xl bg-primary text-on-primary hover:shadow-xl hover:shadow-primary/20 transition-all shadow-lg active:scale-[0.98]">
                                    <div class="text-right">
                                        <p class="text-[9px] font-black uppercase text-primary-fixed italic leading-none mb-1">NEXT MODULE</p>
                                        <p class="text-xs font-bold italic">{{ Str::limit($nextItem['item']->title, 35) }}</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center group-hover:bg-white group-hover:text-primary transition-all">
                                        <span class="material-symbols-outlined">arrow_forward</span>
                                    </div>
                                </a>
                            @endif
                        @else
                            <a href="{{ route('mahasiswa.courses.show', $course) }}" class="group w-full md:w-auto flex items-center gap-4 p-4 rounded-2xl bg-secondary text-on-secondary hover:shadow-lg transition-all shadow-md">
                                <div class="text-right">
                                    <p class="text-[9px] font-black uppercase text-on-secondary italic leading-none mb-1">COURSE OVERVIEW</p>
                                    <p class="text-xs font-bold italic">Selesai Membaca</p>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center group-hover:bg-white group-hover:text-secondary transition-all">
                                    <span class="material-symbols-outlined">done_all</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 74, 198, 0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0, 74, 198, 0.2); }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render markdown content
            const contentElement = document.getElementById('material-content');
            if (contentElement) {
                const base64Markdown = contentElement.getAttribute('data-markdown');
                if (base64Markdown) {
                    const markdown = atob(base64Markdown);
                    // Assuming renderMarkdown is available globally from markdown-editor.js
                    if (typeof renderMarkdown === 'function') {
                        contentElement.innerHTML = renderMarkdown(markdown);
                    } else {
                        // Fallback if not loaded
                        contentElement.innerText = markdown;
                    }
                }
            }
        });
    </script>
</x-app-layout>
