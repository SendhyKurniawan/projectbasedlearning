<x-app-layout>
    @vite(['resources/js/markdown-editor.js'])
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $material->title }}
            </h2>
            <a href="{{ route('mahasiswa.courses.show', $course) }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali ke Course
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                
                <!-- Sidebar (Course Tree) -->
                <aside class="lg:w-1/4 flex-shrink-0">
                    <div class="card sticky top-6">
                        <!-- Course Info -->
                        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-1">
                                {{ $course->nama_matkul }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ $course->kode_matkul }}</p>
                        </div>
                        
                        <!-- Progress -->
                        @php
                            $totalItems = count($learningPath);
                            $completedItems = collect($learningPath)->where('completed', true)->count();
                            $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                        @endphp
                        
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Progress</span>
                                <span class="text-xs font-bold text-primary">{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all" 
                                     style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <!-- Learning Path -->
                        <div class="space-y-1 max-h-[calc(100vh-300px)] overflow-y-auto">
                            @foreach($learningPath as $index => $item)
                                @php
                                    $isCurrent = $index === $currentIndex;
                                    $isCompleted = $item['completed'];
                                    $isLocked = $item['locked'];
                                    
                                    // Determine URL
                                    if ($item['type'] === 'material') {
                                        $itemUrl = route('mahasiswa.materials.show', [$course, $item['item']->id]);
                                    } else {
                                        if ($isLocked) {
                                            $itemUrl = null;
                                        } else {
                                            $itemUrl = $item['item']->type === 'exercise'
                                                ? route('mahasiswa.exercises.solve', $item['item'])
                                                : route('mahasiswa.submissions.create', ['assignment_id' => $item['item']->id]);
                                        }
                                    }
                                @endphp
                                
                                @if($itemUrl)
                                    <a href="{{ $itemUrl }}"
                                       class="block p-3 rounded-lg transition border-l-4 
                                              {{ $isCurrent ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-500' : ($isCompleted ? 'bg-green-50 dark:bg-green-900/10 border-green-500 hover:bg-green-100 dark:hover:bg-green-900/20' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700') }}">
                                        <div class="flex items-start gap-2">
                                            <div class="flex-shrink-0 mt-0.5">
                                                @if($isCurrent)
                                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($isCompleted)
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 dark:border-gray-600"></div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                                    {{ $item['type'] === 'material' ? 'Materi' : ($item['item']->type === 'exercise' ? 'Exercise' : 'Tugas') }}
                                                </p>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ Str::limit($item['item']->title, 50) }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    <div class="block p-3 rounded-lg bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-300 dark:border-gray-700 opacity-60">
                                        <div class="flex items-start gap-2">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                                    {{ $item['type'] === 'material' ? 'Materi' : ($item['item']->type === 'exercise' ? 'Exercise' : 'Tugas') }}
                                                </p>
                                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">
                                                    {{ Str::limit($item['item']->title, 50) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </aside>
                
                <!-- Main Content -->
                <main class="flex-1 min-w-0">
                    <!-- Material Header -->
                    <div class="card mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                    {{ $material->title }}
                                </h1>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Course:</strong> {{ $course->nama_matkul }} | 
                                    <strong>Dosen:</strong> {{ $course->dosen->name }}
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-sm">
                                Materi #{{ $material->order }}
                            </span>
                        </div>
                    </div>

                    <!-- Material Content -->
                    @if($material->content)
                        <div class="card mb-6">
                            <div class="prose dark:prose-invert max-w-none markdown-content" 
                                 id="material-content" 
                                 data-markdown="{{ base64_encode($material->content) }}"></div>
                        </div>
                    @endif

                    <!-- Material File Download / Viewer -->
                    @if($material->file_path)
                        <div class="card mb-6">
                            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                                </svg>
                                File Lampiran
                            </h3>
                            
                            @if(Str::endsWith(strtolower($material->file_path), ['.pdf']))
                                <div x-data="{ fullscreen: false }" class="mb-4">
                                    <div class="flex justify-end mb-2">
                                        <button @click="fullscreen = !fullscreen" class="text-sm flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            <svg x-show="!fullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                            <svg x-show="fullscreen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            <span x-text="fullscreen ? 'Tutup Fullscreen' : 'Fullscreen'"></span>
                                        </button>
                                    </div>
                                    <div :class="{'fixed inset-0 z-[100] bg-gray-900/95 flex flex-col p-4': fullscreen, 'border dark:border-gray-700 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900': !fullscreen}">
                                        <div x-show="fullscreen" x-cloak class="flex justify-end mb-4">
                                            <button @click="fullscreen = false" class="text-white bg-red-600 hover:bg-red-700 rounded px-4 py-2 font-semibold shadow">Tutup</button>
                                        </div>
                                        <iframe src="{{ Storage::url($material->file_path) }}" :class="{'w-full h-full rounded': fullscreen, 'w-full min-h-[800px]': !fullscreen}" frameborder="0"></iframe>
                                    </div>
                                </div>
                            @elseif(Str::endsWith(strtolower($material->file_path), ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp']))
                                <div class="mb-4 border dark:border-gray-700 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 flex justify-center p-4">
                                    <img src="{{ Storage::url($material->file_path) }}" alt="{{ basename($material->file_path) }}" class="max-w-full h-auto rounded">
                                </div>
                            @endif

                            <a href="{{ Storage::url($material->file_path) }}" 
                               target="_blank"
                               class="btn btn-primary inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download {{ basename($material->file_path) }}
                            </a>
                        </div>
                    @endif
                    
                    <!-- Navigation Buttons -->
                    <div class="card">
                        <div class="flex justify-between items-center gap-4">
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
                                <a href="{{ $prevUrl }}" 
                                   class="btn btn-secondary inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-left">
                                        <div class="text-xs opacity-80">Previous</div>
                                        <div>{{ Str::limit($prevItem['item']->title, 30) }}</div>
                                    </div>
                                </a>
                            @else
                                <div></div>
                            @endif
                            
                            @if($nextItem)
                                @if($nextItem['locked'])
                                    <div class="btn btn-gray inline-flex items-center gap-2 cursor-not-allowed opacity-60" 
                                         title="Selesaikan prerequisite terlebih dahulu">
                                        <div class="text-right">
                                            <div class="text-xs opacity-60">Next (Locked)</div>
                                            <div>{{ Str::limit($nextItem['item']->title, 30) }}</div>
                                        </div>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
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
                                    <a href="{{ $nextUrl }}" 
                                       class="btn btn-primary inline-flex items-center gap-2">
                                        <div class="text-right">
                                            <div class="text-xs opacity-80">Next</div>
                                            <div>{{ Str::limit($nextItem['item']->title, 30) }}</div>
                                        </div>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    

    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render markdown content
            const contentElement = document.getElementById('material-content');
            if (contentElement) {
                const base64Markdown = contentElement.getAttribute('data-markdown');
                if (base64Markdown) {
                    const markdown = atob(base64Markdown);
                    contentElement.innerHTML = renderMarkdown(markdown);
                }
            }
        });
    </script>
</x-app-layout>
