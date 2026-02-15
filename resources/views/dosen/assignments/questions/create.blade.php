<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                Add Question - {{ $assignment->title }}
            </h2>
            <a href="{{ route('dosen.assignments.questions.index', $assignment) }}" 
               class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                &larr; Cancel
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6" x-data="{ type: '{{ old('question_type', 'pilihan_ganda') }}' }">
            <form action="{{ route('dosen.assignments.questions.store', $assignment) }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Question Text -->
                    <div>
                        <label for="question_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Question Text
                        </label>
                        <textarea name="question_text" id="question_text" rows="4" required
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">{{ old('question_text') }}</textarea>
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
                            <input type="number" name="score_weight" id="score_weight" value="{{ old('score_weight', 1) }}" required min="1"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Bobot nilai pertanyaan ini.</p>
                        </div>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div x-show="type === 'pilihan_ganda'" class="space-y-4 border-t pt-4 dark:border-gray-700" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Answer Options
                        </label>
                        
                        @for($i = 0; $i < 4; $i++)
                            <div class="flex items-center gap-3">
                                <input type="radio" name="correct_idx" @click="$refs.correctIdxInput_{{ $i }}.checked = true; $refs.correctInput_{{ $i }}.value = 1" 
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600"
                                       {{ $i === 0 ? 'checked' : '' }}>
                                <input type="hidden" name="options[{{ $i }}][is_correct]" x-ref="correctInput_{{ $i }}" value="{{ $i === 0 ? '1' : '0' }}">
                                <input type="text" name="options[{{ $i }}][text]" value="{{ old("options.{$i}.text") }}" placeholder="Option {{ $i + 1 }}"
                                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        @endfor
                        <p class="text-xs text-gray-500">Select the radio button for the correct answer.</p>
                        @error('options')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Correct Answer (Essay/Code) -->
                    <div x-show="type !== 'pilihan_ganda'" class="space-y-4 border-t pt-4 dark:border-gray-700" style="display: none;">
                         <label for="correct_answer" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Reference Answer / Key (Optional)
                        </label>
                        <textarea name="correct_answer" id="correct_answer" rows="4"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter the expected answer or code here for reference...">{{ old('correct_answer') }}</textarea>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                            Save Question
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Simple script to handle radio button logic for `options[i][is_correct]`
        document.querySelectorAll('input[type=radio][name=correct_idx]').forEach((radio, index) => {
            radio.addEventListener('change', () => {
                // Reset all hidden inputs to 0
                document.querySelectorAll('input[type=hidden][name^="options"][name$="[is_correct]"]').forEach(hidden => {
                    hidden.value = '0';
                });
                // Set the corresponding hidden input to 1
                const hiddenInput = document.querySelector(`input[name="options[${index}][is_correct]"]`);
                if(hiddenInput) hiddenInput.value = '1';
            });
        });
    </script>
</x-app-layout>
