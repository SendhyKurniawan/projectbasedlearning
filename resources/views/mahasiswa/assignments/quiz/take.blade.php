<x-app-layout>
 <div class="min-h-screen bg-surface-container-low space-y-6">
 <div class="px-6 lg:px-8">
 <!-- Header with Timer -->
 <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4" x-data="timer({{ $quiz->duration_minutes }}, '{{ $attempt->started_at }}')">
 <div>
 <h1 class="text-2xl font-bold text-on-surface">{{ $quiz->title }}</h1>
 <p class="text-sm text-on-surface-variant">Question {{ $quiz->questions->count() }} Items</p>
 </div>
 
 <div class="flex items-center gap-2 bg-surface-container-lowest px-4 py-2 rounded-lg shadow-sm border border-red-100">
 <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
 <span class="text-lg font-mono font-bold text-error" x-text="formattedTime">00:00:00</span>
 </div>
 </div>

 <form action="{{ route('mahasiswa.quizzes.submit', $quiz) }}" method="POST" id="quiz-form">
 @csrf
 
 <div class="space-y-6">
 @foreach($quiz->questions as $index => $question)
 <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-container-low overflow-hidden">
 <div class="p-6">
 <div class="flex items-start gap-4 mb-4">
 <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">
 {{ $index + 1 }}
 </div>
 <div class="flex-1">
 <h3 class="text-lg text-on-surface font-medium leading-relaxed">
 {!! nl2br(e($question->question_text)) !!}
 </h3>
 </div>
 </div>

 <div class="ml-12">
 @if($question->question_type === 'pilihan_ganda')
 <div class="space-y-3">
 @foreach($question->options as $option)
 <label class="flex items-center p-3 rounded-lg border border-surface-container-low hover:bg-surface-bright cursor-pointer transition">
 <input type="radio" 
 name="answers[{{ $question->id }}]" 
 value="{{ $option->id }}" 
 class="w-4 h-4 text-blue-600 border-outline-variant/30 focus:ring-blue-500:ring-blue-600 ">
 <span class="ml-3 text-on-surface-variant">{{ $option->option_text }}</span>
 </label>
 @endforeach
 </div>
 @elseif($question->question_type === 'essay' || $question->question_type === 'code_snippet')
 <textarea name="answers[{{ $question->id }}]" 
 rows="5" 
 class="w-full rounded-lg border-outline-variant/30 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm p-4"
 placeholder="Type your answer here..."></textarea>
 @endif
 </div>
 </div>
 </div>
 @endforeach
 </div>

 <div class="mt-8 flex justify-end">
 <button type="submit" 
 class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5"
 onclick="return confirm('Are you sure you want to submit your quiz? You cannot undo this action.')">
 Submit Quiz
 </button>
 </div>
 </form>
 </div>
 </div>

 @push('scripts')
 <script>
 document.addEventListener('alpine:init', () => {
 Alpine.data('timer', (durationMinutes, startedAt) => ({
 remainingStats: 0,
 formattedTime: '00:00:00',
 
 init() {
 const startTime = new Date(startedAt).getTime();
 const endTime = startTime + (durationMinutes * 60 * 1000);
 
 this.updateTimer(endTime);
 const interval = setInterval(() => {
 if (!this.updateTimer(endTime)) {
 clearInterval(interval);
 alert('Time is up! Submitting your quiz.');
 document.getElementById('quiz-form').submit();
 }
 }, 1000);
 },
 
 updateTimer(endTime) {
 const now = new Date().getTime();
 const distance = endTime - now;
 
 if (distance < 0) {
 this.formattedTime = "00:00:00";
 return false;
 }
 
 const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
 const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
 const seconds = Math.floor((distance % (1000 * 60)) / 1000);
 
 this.formattedTime = 
 (hours < 10 ? "0" + hours : hours) + ":" + 
 (minutes < 10 ? "0" + minutes : minutes) + ":" + 
 (seconds < 10 ? "0" + seconds : seconds);
 
 return true;
 }
 }))
 })
 </script>
 @endpush
</x-app-layout>
