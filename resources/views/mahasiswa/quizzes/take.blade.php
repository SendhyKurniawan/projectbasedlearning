<x-app-layout>
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $quiz->title }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Duration: {{ $quiz->duration_minutes }} Minutes</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full dark:bg-blue-900 dark:text-blue-200 uppercase font-bold tracking-wide">
                    {{ str_replace('_', ' ', $quiz->type) }}
                </span>
            </div>
        </div>

        <form action="{{ route('mahasiswa.quizzes.submit', $quiz) }}" method="POST" id="quizForm" onsubmit="return handleQuizSubmit(this);">
            @csrf
            
            <div class="space-y-8">
                @foreach($questions as $index => $question)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg border border-gray-300 dark:border-gray-600">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    {{ $question->question_text }}
                                </h3>

                                @if($question->question_type === 'pilihan_ganda')
                                    <div class="space-y-3">
                                        @foreach($question->options as $option)
                                            <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" 
                                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                                <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $option->option_text }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($question->question_type === 'essay')
                                    <textarea name="answers[{{ $question->id }}]" rows="5" 
                                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Type your answer here..."></textarea>
                                @elseif($question->question_type === 'code_snippet')
                                    <textarea name="answers[{{ $question->id }}]" rows="8" 
                                              class="w-full font-mono text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-green-400 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="// Write your code here..."></textarea>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" id="submitQuizBtn"
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transform hover:scale-105 transition">
                    Finish & Submit Quiz
                </button>
            </div>
        </form>
    </div>

    <script>
        function handleQuizSubmit(form) {
            if (confirm('Are you sure you want to finish and submit? This action cannot be undone.')) {
                const btn = document.getElementById('submitQuizBtn');
                btn.disabled = true;
                btn.innerHTML = 'Submitting...';
                return true;
            }
            return false;
        }
    </script>
</x-app-layout>
