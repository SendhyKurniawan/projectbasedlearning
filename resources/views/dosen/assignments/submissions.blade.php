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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        {{ $submission->student->name }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $submission->student->email }}
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
                                    <p>
                                        <strong>File:</strong> 
                                        <a href="{{ Storage::url($submission->file_path) }}" 
                                           target="_blank"
                                           class="text-blue-600 dark:text-blue-400 hover:underline break-all">
                                            {{ basename($submission->file_path) }}
                                        </a>
                                    </p>
                                @endif

                                @if($submission->url_link)
                                    <p>
                                        <strong>Link URL:</strong> 
                                        <a href="{{ $submission->url_link }}" 
                                           target="_blank"
                                           class="text-blue-600 dark:text-blue-400 hover:underline break-all">
                                            {{ $submission->url_link }}
                                        </a>
                                    </p>
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
                                        
                                        <details class="mt-2" {{ $loop->first ? 'open' : '' }}>
                                            <summary class="cursor-pointer text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                                {{ $loop->first ? 'Hide' : 'Show' }} Code
                                            </summary>
                                            <div class="mt-2 border dark:border-gray-600 rounded overflow-hidden">
                                                <textarea class="code-viewer" data-language="{{ $assignment->exercise_config['language'] ?? 'htmlmixed' }}" readonly>{{ $submission->code_answer }}</textarea>
                                            </div>
                                        </details>
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
                initCodeEditor(textarea.id || 'code-viewer-' + Math.random(), {
                    mode: language,
                    readOnly: true,
                    lineNumbers: true,
                });
                
                // Replace textarea with CodeMirror
                const editor = CodeMirror.fromTextArea(textarea, {
                    mode: language,
                    theme: 'dracula',
                    readOnly: true,
                    lineNumbers: true,
                    lineWrapping: true,
                });
            });
        });
    </script>
</x-app-layout>
