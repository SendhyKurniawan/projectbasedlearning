<x-app-layout>
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $quiz->title }} - Attempt Details</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Student: {{ $attempt->mahasiswa->name }} <br>
                    Finished at: {{ $attempt->finished_at->format('d M Y, H:i') }}
                </p>
            </div>
            <a href="{{ route('dosen.quizzes.attempts.index', $quiz) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition shadow-md">
                &larr; Back to Attempts
            </a>
        </div>

        <!-- Score Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8 text-center">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">Total Score</h2>
            <div class="text-5xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                {{ $attempt->total_score ?? 0 }} <span class="text-lg text-gray-500">/ {{ $quiz->questions->sum('score_weight') }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                (Note: Score currently reflects auto-graded multiple choice questions.)
            </p>
        </div>

        <!-- Questions Review -->
        <div class="space-y-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Review Answers</h3>
            
            @foreach($quiz->questions as $index => $question)
                @php
                    $userAnswer = $attempt->answers[$question->id] ?? null;
                    $isCorrect = false;
                    $correctOption = null;
                    
                    if ($question->question_type === 'pilihan_ganda') {
                        $correctOption = $question->options->where('is_correct', true)->first();
                        $isCorrect = $userAnswer == $correctOption->id;
                    }
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border {{ $question->question_type === 'pilihan_ganda' ? ($isCorrect ? 'border-green-200 dark:border-green-800' : 'border-red-200 dark:border-red-800') : 'border-gray-200 dark:border-gray-700' }} p-6">
                    <div class="flex items-start gap-4">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center {{ $question->question_type === 'pilihan_ganda' ? ($isCorrect ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') : 'bg-gray-100 text-gray-700' }} font-bold rounded-lg">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $question->question_text }}
                            </h3>

                            @if($question->question_type === 'pilihan_ganda')
                                <div class="space-y-2">
                                    @foreach($question->options as $option)
                                        <div class="flex items-center justify-between p-3 rounded-lg border 
                                            {{ $userAnswer == $option->id ? ($isCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : 'border-gray-200' }}
                                            {{ $option->is_correct && $userAnswer != $option->id ? 'bg-green-50 border-green-200' : '' }}
                                        ">
                                            <span class="flex items-center">
                                                @if($userAnswer == $option->id)
                                                    @if($isCorrect)
                                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    @endif
                                                @endif
                                                <span class="{{ $userAnswer == $option->id ? 'font-semibold' : '' }}">{{ $option->option_text }}</span>
                                            </span>
                                            @if($option->is_correct)
                                                <span class="text-xs font-semibold text-green-600 uppercase">Correct Answer</span>
                                            @endif
                                            @if($userAnswer == $option->id && !$isCorrect)
                                                <span class="text-xs font-semibold text-red-600 uppercase">Student Answer</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'essay' || $question->question_type === 'code_snippet')
                                <div class="mt-2">
                                    <p class="text-sm font-semibold text-gray-500 mb-1">Student Answer:</p>
                                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-sm whitespace-pre-wrap">{{ $userAnswer ?? 'No answer provided' }}</div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-sm font-semibold text-gray-500 mb-1">Correct Answer / Rubric:</p>
                                    <div class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-100 dark:border-blue-800 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $question->correct_answer ?? 'No correct answer key provided.' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
