<x-app-layout>
    @vite(['resources/js/code-editor.js'])
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $assignment->title }}
            </h2>
            <a href="{{ route('mahasiswa.courses.show', $assignment->course_id) }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali ke Course
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($existing)
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-green-800 dark:text-green-200 mb-2">Exercise Sudah Dikumpulkan!</h3>
                    <p class="text-sm text-green-700 dark:text-green-300 mb-2">
                        Dikumpulkan pada: {{ $existing->submitted_at->format('d M Y, H:i') }}
                    </p>
                    <p class="text-sm text-green-700 dark:text-green-300 mb-2">
                        <strong>Skor Anda: {{ $existing->score }}/{{ $assignment->max_score }}</strong>
                    </p>
                    @if($existing->feedback)
                        <div class="bg-white dark:bg-gray-800 p-3 rounded mt-3">
                            <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $existing->feedback }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Panel: Instructions -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Instruksi</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">{{ $assignment->description }}</p>
                            
                            <div class="text-sm space-y-2">
                                <p><strong>Deadline:</strong></p>
                                <p class="text-gray-600 dark:text-gray-400">{{ $assignment->deadline->format('d M Y, H:i') }}</p>
                                
                                <p><strong>Nilai Maksimal:</strong></p>
                                <p class="text-gray-600 dark:text-gray-400">{{ $assignment->max_score }} poin</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hints -->
                    @if(!empty($assignment->exercise_config['hints']))
                        <div class="bg-blue-50 dark:bg-blue-900 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-3 text-blue-900 dark:text-blue-100">Hints</h3>
                                <ul class="list-disc list-inside text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                    @foreach($assignment->exercise_config['hints'] as $hint)
                                        <li>{{ $hint }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Panel: Code Editor & Preview -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Code Editor</h3>
                                <button type="button" 
                                        onclick="runCodePreview()"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                    Run Code
                                </button>
                            </div>
                            
                            <div class="border dark:border-gray-700 rounded-md overflow-hidden">
                                <textarea id="code-editor">{{ $assignment->exercise_config['starter_code'] }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Area -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Preview</h3>
                            <iframe id="preview-iframe" 
                                    class="w-full border dark:border-gray-700 rounded-md bg-white h-[400px]"></iframe>
                        </div>
                    </div>

                    <!-- Submit Form -->
                    @if(!$existing)
                        <form action="{{ route('mahasiswa.exercises.submit') }}" method="POST" id="submit-form">
                            @csrf
                            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                            <input type="hidden" name="code_answer" id="code-answer-input">
                            
                            <button type="submit" 
                                    onclick="return confirmSubmit()"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded font-semibold">
                                Submit Solution
                            </button>
                        </form>
                    @else
                        <div class="text-center text-gray-500 dark:text-gray-400 py-3">
                            Anda sudah mengumpulkan exercise ini. Lihat hasil di atas.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        let codeEditor;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize CodeMirror
            codeEditor = initCodeEditor('code-editor', {
                mode: '{{ $assignment->exercise_config['language'] ?? 'htmlmixed' }}',
                lineNumbers: true,
            });
        });

        function runCodePreview() {
            runCode(codeEditor, 'preview-iframe');
        }

        function confirmSubmit() {
            if (!confirm('Yakin ingin submit solution? Pastikan kode Anda sudah benar!')) {
                return false;
            }

            // Get code from editor and put to hidden input
            const code = codeEditor.getValue();
            document.getElementById('code-answer-input').value = code;
            
            return true;
        }
    </script>
</x-app-layout>
