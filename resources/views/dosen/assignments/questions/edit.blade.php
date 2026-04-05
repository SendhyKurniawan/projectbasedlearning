<x-app-layout>
 <div class="p-6 ">
 <div class="flex justify-between items-center mb-6">
 <h2 class="text-2xl font-semibold text-on-surface">
 Edit Question - {{ $question->assignment->title }}
 </h2>
 <a href="{{ route('dosen.assignments.questions.index', $question->assignment) }}" 
 class="px-4 py-2 bg-surface-container-low/500 hover:bg-gray-600 text-white rounded-lg transition">
 &larr; Cancel
 </a>
 </div>

 <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6" x-data="{ type: '{{ old('question_type', $question->question_type) }}' }">
 <form action="{{ route('dosen.assignments.questions.update', $question) }}" method="POST">
 @csrf
 @method('PUT')
 
 <div class="space-y-6">
 <!-- Question Text -->
 <div>
 <label for="question_text" class="block text-sm font-medium text-on-surface-variant mb-1">
 Question Text
 </label>
 <textarea name="question_text" id="question_text" rows="4" required
 class="w-full rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500">{{ old('question_text', $question->question_text) }}</textarea>
 @error('question_text')
 <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
 @enderror
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Type -->
 <div>
 <label for="question_type" class="block text-sm font-medium text-on-surface-variant mb-1">
 Question Type
 </label>
 <select name="question_type" id="question_type" x-model="type" required
 class="w-full rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500">
 <option value="pilihan_ganda">Pilihan Ganda (Multiple Choice)</option>
 <option value="essay">Essay</option>
 <option value="code_snippet">Code Snippet</option>
 </select>
 </div>

 <!-- Score Weight -->
 <div>
 <label for="score_weight" class="block text-sm font-medium text-on-surface-variant mb-1">
 Score Weight
 </label>
 <input type="number" name="score_weight" id="score_weight" value="{{ old('score_weight', $question->score_weight) }}" required min="1"
 class="w-full rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500">
 </div>
 </div>

 <!-- Multiple Choice Options -->
 <div x-show="type === 'pilihan_ganda'" class="space-y-4 border-t pt-4 " style="display: none;">
 <label class="block text-sm font-medium text-on-surface-variant mb-2">
 Answer Options
 </label>
 
 @for($i = 0; $i < 4; $i++)
 @php
 $option = $question->options[$i] ?? null;
 @endphp
 <div class="flex items-center gap-3">
 <input type="radio" name="correct_idx" @click="updateCorrect({{ $i }})" 
 class="w-4 h-4 text-blue-600 border-outline-variant/30 focus:ring-blue-500:ring-blue-600 "
 {{ $option && $option->is_correct ? 'checked' : ($i===0 && !$option ? 'checked' :'') }}>
 <input type="hidden" name="options[{{ $i }}][is_correct]" id="hidden_correct_{{ $i }}" value="{{ $option && $option->is_correct ? '1' : '0' }}">
 <input type="text" name="options[{{ $i }}][text]" value="{{ old("options.{$i}.text", $option ? $option->option_text : '') }}" placeholder="Option {{ $i + 1 }}"
 class="flex-1 rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500">
 </div>
 @endfor
 <script>
 function updateCorrect(index) {
 for(let j=0; j<4; j++) {
 document.getElementById('hidden_correct_' + j).value = (j === index) ? '1' : '0';
 }
 }
 </script>
 </div>

 <!-- Correct Answer (Essay/Code) -->
 <div x-show="type !== 'pilihan_ganda'" class="space-y-4 border-t pt-4 " style="display: none;">
 <label for="correct_answer" class="block text-sm font-medium text-on-surface-variant mb-1">
 Reference Answer / Key (Optional)
 </label>
 <textarea name="correct_answer" id="correct_answer" rows="4"
 class="w-full rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500"
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
