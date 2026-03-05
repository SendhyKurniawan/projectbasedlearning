<x-app-layout>
    @vite(['resources/js/code-editor.js'])
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Submissions - {{ $assignment->title }}
            </h2>
            <a href="{{ route('dosen.assignments.index', $course) }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali ke Daftar Tugas
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Assignment Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ $assignment->title }}</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><strong>Course:</strong> {{ $course->nama_matkul }}</p>
                        <p><strong>Deadline:</strong> {{ $assignment->deadline->format('d M Y, H:i') }}</p>
                        <p><strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}</p>
                        <p><strong>Total Submissions:</strong> {{ $submissions->count() }}</p>
                        <p><strong>Sudah Dinilai:</strong> {{ $submissions->whereNotNull('score')->count() }} / {{ $submissions->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Submissions List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Daftar Submission</h3>

                    @forelse($submissions as $submission)
                        <div class="border dark:border-gray-700 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $submission->mahasiswa->name ?? 'Unknown Student' }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $submission->mahasiswa->email ?? 'No Email' }}
                                    </p>
                                </div>
                                @if($submission->score !== null)
                                    <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded font-semibold">
                                        {{ $submission->score }}/{{ $assignment->max_score }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded text-sm">
                                        Belum Dinilai
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-3 space-y-1">
                                <p><strong>Dikumpulkan:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</p>
                                
                                @if($submission->notes)
                                    <p><strong>Catatan Mahasiswa:</strong> {{ $submission->notes }}</p>
                                @endif
                                
                                @if($submission->file_path)
                                    <div class="mb-4">
                                        <p class="mb-2">
                                            <strong>File:</strong> 
                                            <a href="{{ Storage::url($submission->file_path) }}" 
                                               target="_blank"
                                               class="text-blue-600 dark:text-blue-400 hover:underline break-all">
                                                {{ basename($submission->file_path) }}
                                            </a>
                                        </p>
                                        @if(Str::endsWith(strtolower($submission->file_path), ['.pdf']))
                                            <div x-data="{ fullscreen: false }" class="mt-2 text-right">
                                                <button @click="fullscreen = !fullscreen" class="mb-2 text-sm inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                                    <svg x-show="!fullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                                    <svg x-show="fullscreen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    <span x-text="fullscreen ? 'Tutup Fullscreen' : 'Fullscreen'"></span>
                                                </button>
                                                <div :class="{'fixed inset-0 z-[100] bg-gray-900/95 flex flex-col p-4 text-left': fullscreen, 'border dark:border-gray-700 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900': !fullscreen}">
                                                    <div x-show="fullscreen" x-cloak class="flex justify-end mb-4">
                                                        <button @click="fullscreen = false" class="text-white bg-red-600 hover:bg-red-700 rounded px-4 py-2 font-semibold shadow">Tutup</button>
                                                    </div>
                                                    <iframe src="{{ Storage::url($submission->file_path) }}" :class="{'w-full h-full rounded': fullscreen, 'w-full min-h-[800px]': !fullscreen}" frameborder="0"></iframe>
                                                </div>
                                            </div>
                                        @elseif(Str::endsWith(strtolower($submission->file_path), ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                                            <div class="border dark:border-gray-700 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 mt-2 flex justify-center p-2">
                                                <img src="{{ Storage::url($submission->file_path) }}" alt="Submission Image" class="max-w-full h-auto rounded">
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($submission->url_link)
                                    <div class="mb-4">
                                        <p class="mb-2">
                                            <strong>Link URL:</strong> 
                                            <a href="{{ $submission->url_link }}" 
                                               target="_blank"
                                               class="text-blue-600 dark:text-blue-400 hover:underline break-all">
                                                {{ $submission->url_link }}
                                            </a>
                                        </p>
                                        <div x-data="{ fullscreen: false }" class="mt-2 text-right">
                                            <button @click="fullscreen = !fullscreen" class="mb-2 text-sm inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                                <svg x-show="!fullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                                <svg x-show="fullscreen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                <span x-text="fullscreen ? 'Tutup Fullscreen' : 'Fullscreen'"></span>
                                            </button>
                                            <div :class="{'fixed inset-0 z-[100] bg-gray-900/95 flex flex-col p-4 text-left': fullscreen, 'border dark:border-gray-700 rounded-lg overflow-hidden bg-white': !fullscreen}">
                                                <div x-show="fullscreen" x-cloak class="flex justify-end mb-4">
                                                    <button @click="fullscreen = false" class="text-white bg-red-600 hover:bg-red-700 rounded px-4 py-2 font-semibold shadow">Tutup</button>
                                                </div>
                                                <iframe src="{{ preg_match('/^https?:\/\//', $submission->url_link) ? $submission->url_link : 'https://' . $submission->url_link }}" :class="{'w-full h-full rounded': fullscreen, 'w-full min-h-[800px]': !fullscreen}" frameborder="0" sandbox="allow-scripts allow-same-origin allow-popups"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($submission->code_answer)
                                    <div class="mt-3 pt-3 border-t dark:border-gray-600">
                                        <div class="flex justify-between items-center mb-2">
                                            <strong class="text-gray-900 dark:text-gray-100">Code Submission:</strong>
                                            @if($submission->auto_graded)
                                                <span class="px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded">
                                                    Auto-Graded
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($submission->validation_result)
                                            <div class="bg-blue-50 dark:bg-blue-900 p-2 rounded mb-2 text-sm">
                                                <p class="text-blue-800 dark:text-blue-200">
                                                    <strong>Validation Result:</strong>
                                                    {{ $submission->validation_result['passed'] ? 'Passed' : 'Needs Review' }}
                                                </p>
                                                @if(isset($submission->validation_result['feedback']))
                                                    <p class="text-blue-700 dark:text-blue-300 mt-1 whitespace-pre-line">{{ $submission->validation_result['feedback'] }}</p>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            <!-- Code Editor -->
                                            <div class="border dark:border-gray-600 rounded overflow-hidden flex flex-col">
                                                <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-b dark:border-gray-600 flex justify-between items-center">
                                                    <span>Source Code</span>
                                                    <span class="px-2 hidden md:inline-block rounded bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300">{{ $assignment->exercise_config['language'] ?? 'htmlmixed' }}</span>
                                                </div>
                                                <div class="flex-1">
                                                    <textarea class="code-viewer" id="code-viewer-{{ $submission->id }}" data-language="{{ $assignment->exercise_config['language'] ?? 'htmlmixed' }}" readonly>{{ $submission->code_answer }}</textarea>
                                                </div>
                                            </div>
                                            
                                            <!-- Code Preview (Webview) -->
                                            <div x-data="{ fullscreen: false }">
                                                <div :class="{'fixed inset-0 z-[100] bg-gray-900 flex flex-col p-4 text-left': fullscreen, 'border dark:border-gray-600 rounded overflow-hidden flex flex-col bg-white h-full relative': !fullscreen}">
                                                    
                                                    <!-- Header Bar -->
                                                    <div class="bg-gray-100 dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border-b dark:border-gray-600 flex justify-between items-center" :class="{'rounded-t': !fullscreen, 'mb-2 rounded': fullscreen}">
                                                        <span>Live Result / Preview</span>
                                                        <div class="flex items-center gap-3">
                                                            <button type="button" @click="fullscreen = !fullscreen" class="hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center gap-1" title="Toggle Fullscreen">
                                                                <svg x-show="!fullscreen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                                                <svg x-show="fullscreen" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                <span x-text="fullscreen ? 'Tutup' : 'Fullscreen'"></span>
                                                            </button>
                                                            <button type="button" onclick="refreshIframe('preview-iframe-{{ $submission->id }}')" class="hover:text-blue-600 dark:hover:text-blue-400 transition" title="Refresh Preview">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="flex-1 relative min-h-[300px] bg-white rounded-b">
                                                        <iframe id="preview-iframe-{{ $submission->id }}" 
                                                                srcdoc="{{ ($assignment->exercise_config['language'] ?? 'htmlmixed') == 'javascript' ? '<script>' . $submission->code_answer . '<\/script><div style=\'font-family:sans-serif;padding:10px;\'>Check console for JS output, or if it modifies DOM it will appear here.</div>' : $submission->code_answer }}" 
                                                                class="absolute inset-0 w-full h-full border-0 preview-iframe" 
                                                                sandbox="allow-scripts allow-same-origin"></iframe>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($submission->feedback)
                                    <p><strong>Feedback Anda:</strong> {{ $submission->feedback }}</p>
                                @endif
                            </div>

                            <!-- Grading Form -->
                            <form action="{{ route('dosen.submissions.grade', $submission) }}" 
                                  method="POST" 
                                  class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
                                @csrf
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Nilai (0-{{ $assignment->max_score }})
                                        </label>
                                        <input type="number" 
                                               name="score" 
                                               value="{{ old('score', $submission->score) }}"
                                               min="0" 
                                               max="{{ $assignment->max_score }}"
                                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Feedback (Opsional)
                                        </label>
                                        <input type="text" 
                                               name="feedback" 
                                               value="{{ old('feedback', $submission->feedback) }}"
                                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="Catatan untuk mahasiswa">
                                    </div>
                                </div>

                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                    {{ $submission->score !== null ? 'Update Nilai' : 'Berikan Nilai' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            Belum ada submission untuk tugas ini.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize CodeMirror for all code viewers
            document.querySelectorAll('.code-viewer').forEach(function(textarea) {
                const language = textarea.getAttribute('data-language') || 'htmlmixed';
                // Replace textarea with CodeMirror
                const editor = CodeMirror.fromTextArea(textarea, {
                    mode: language,
                    theme: 'dracula',
                    readOnly: true,
                    lineNumbers: true,
                    lineWrapping: true,
                });
                
                // Adjust height to match the sibling iframe container precisely if possible, or give it a fixed standard height to look balanced.
                editor.setSize(null, "100%");
                editor.getWrapperElement().style.minHeight = "300px";
            });
        });

        // Function to refresh the execution iframe
        function refreshIframe(iframeId) {
            const iframe = document.getElementById(iframeId);
            if(iframe) {
                // To force re-render, we clone the iframe and replace it
                const clone = iframe.cloneNode(true);
                iframe.parentNode.replaceChild(clone, iframe);
            }
        }
    </script>
</x-app-layout>
