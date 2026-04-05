<x-app-layout>
 <div class="py-8">
 <div class="flex justify-between items-start mb-6">
 <div>
 <h1 class="text-3xl font-bold text-on-surface">{{ $assignment->title }}</h1>
 <p class="text-on-surface-variant mt-1">Duration: {{ $assignment->duration_minutes ? $assignment->duration_minutes . ' Minutes' : 'Unlimited' }}</p>
 </div>
 <div class="text-right">
 <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full uppercase font-bold tracking-wide">
 {{ str_replace('_', ' ', $assignment->type) }}
 </span>
 </div>
 </div>

 <form action="{{ route('mahasiswa.quizzes.submit', $assignment) }}" method="POST" id="quizForm" onsubmit="return handleQuizSubmit(this);">
 @csrf
 
 <div class="space-y-8">
 @foreach($questions as $index => $question)
 <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-container-low p-6">
 <div class="flex items-start gap-4">
 <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-surface-container-low text-on-surface-variant font-bold rounded-lg border border-outline-variant/30">
 {{ $index + 1 }}
 </span>
 <div class="flex-1">
 <h3 class="text-lg font-medium text-on-surface mb-4">
 {{ $question->question_text }}
 </h3>

 @if($question->question_type === 'pilihan_ganda')
 <div class="space-y-3">
 @foreach($question->options as $option)
 <label class="flex items-center p-3 rounded-lg border border-surface-container-low hover:bg-surface-bright cursor-pointer transition">
 <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" 
 class="w-4 h-4 text-blue-600 border-outline-variant/30 focus:ring-blue-500:ring-blue-600 ">
 <span class="ml-3 text-on-surface-variant">{{ $option->option_text }}</span>
 </label>
 @endforeach
 </div>
 @elseif($question->question_type === 'essay')
 <textarea name="answers[{{ $question->id }}]" rows="5" 
 class="w-full rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500"
 placeholder="Type your answer here..."></textarea>
 @elseif($question->question_type === 'code_snippet')
 <textarea name="answers[{{ $question->id }}]" rows="8" 
 class="w-full font-mono text-sm rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500"
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
