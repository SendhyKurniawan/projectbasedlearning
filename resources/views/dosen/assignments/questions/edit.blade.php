<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                Edit Question - {{ $quiz->title }}
            </h2>
            <a href="{{ route('dosen.quizzes.edit', $quiz) }}" 
               class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                &larr; Cancel
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6" x-data="{ type: '{{ old('question_type', $question->question_type) }}' }">
            <form action="{{ route('dosen.quizzes.questions.update', [$quiz, $question]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <!-- Question Text -->
                    <div>
                        <label for="question_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Question Text
                        </label>
                        <textarea name="question_text" id="question_text" rows="4" required
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">{{ old('question_text', $question->question_text) }}</textarea>
                        @error('question_text')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Type -->
                        <div>
                            <label for="question_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Question Type
                            </label>
                            <select name="question_type" id="question_type" x-model="type" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="pilihan_ganda">Pilihan Ganda (Multiple Choice)</option>
                                <option value="essay">Essay</option>
                                <option value="code_snippet">Code Snippet</option>
                            </select>
                        </div>

                        <!-- Score Weight -->
                        <div>
                            <label for="score_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Score Weight
                            </label>
                            <input type="number" name="score_weight" id="score_weight" value="{{ old('score_weight', $question->score_weight) }}" required min="1"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div x-show="type === 'pilihan_ganda'" class="space-y-4 border-t pt-4 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Answer Options
                        </label>
                        
                        @php
                            $options = $question->options;
                            // Ensure at least 4 slots
                            $loopCount = max(4, $options->count());
                        @endphp

                        @for($i = 0; $i < $loopCount; $i++)
                            @php
                                $opt = $options[$i] ?? null;
                                $isCorrect = old("options.{$i}.is_correct", $opt ? $opt->is_correct : false);
                                $text = old("options.{$i}.text", $opt ? $opt->option_text : '');
                            @endphp
                            <div class="flex items-center gap-3">
                                <input type="radio" name="options[{{ $i }}][is_correct]" value="1" {{ $isCorrect ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                <input type="text" name="options[{{ $i }}][text]" value="{{ $text }}" placeholder="Option {{ $i + 1 }}"
                                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        @endfor
                        <p class="text-xs text-gray-500">Select the radio button for the correct answer.</p>
                    </div>

                    <!-- Correct Answer (Essay/Code) -->
                    <div x-show="type !== 'pilihan_ganda'" class="space-y-4 border-t pt-4 dark:border-gray-700">
                         <label for="correct_answer" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Reference Answer / Key (Optional)
                        </label>
                        <textarea name="correct_answer" id="correct_answer" rows="4"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter the expected answer or code here for reference...">{{ old('correct_answer', $question->correct_answer) }}</textarea>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                            Update Question
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
