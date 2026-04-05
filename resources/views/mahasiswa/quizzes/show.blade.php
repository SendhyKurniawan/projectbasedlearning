<x-app-layout>
 <div class="space-y-6">
 <div class="bg-surface-container-lowest rounded-2xl shadow-xl overflow-hidden border border-surface-container-low">
 <div class="p-8 md:p-12 text-center">
 <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
 <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
 </svg>
 </div>
 
 <h1 class="text-3xl font-extrabold text-on-surface mb-2">{{ $assignment->title }}</h1>
 <p class="text-on-surface-variant mb-8">{{ $assignment->course->nama_matkul }}</p>

 <div class="inline-flex flex-wrap justify-center gap-4 mb-8">
 <div class="px-4 py-2 bg-surface-container-low rounded-lg">
 <span class="block text-xs text-on-surface-variant uppercase tracking-wide font-bold">Duration</span>
 <span class="block text-lg font-semibold text-on-surface">{{ $assignment->duration_minutes ? $assignment->duration_minutes . ' Mins' : 'Unlimited' }}</span>
 </div>
 <div class="px-4 py-2 bg-surface-container-low rounded-lg">
 <span class="block text-xs text-on-surface-variant uppercase tracking-wide font-bold">Questions</span>
 <span class="block text-lg font-semibold text-on-surface">{{ $assignment->questions->count() }}</span>
 </div>
 <div class="px-4 py-2 bg-surface-container-low rounded-lg">
 <span class="block text-xs text-on-surface-variant uppercase tracking-wide font-bold">Type</span>
 <span class="block text-lg font-semibold text-on-surface">{{ Str::title($assignment->type) }}</span>
 </div>
 </div>

 @if($existingSubmission && $existingSubmission->finished_at)
 <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
 <h3 class="text-lg font-bold text-green-800 mb-2">Quiz Completed</h3>
 <p class="text-green-700">
 You finished this quiz on {{ $existingSubmission->finished_at->format('M d, Y H:i') }}.
 </p>
 @if($existingSubmission->score !== null)
 <div class="mt-4 text-4xl font-extrabold text-green-600">
 {{ $existingSubmission->score }} <span class="text-base font-normal text-on-surface-variant">/ {{ $assignment->questions->sum('score_weight') }}</span>
 </div>
 <p class="text-xs text-on-surface-variant mt-1">Score (Auto-graded only)</p>
 @endif
 </div>
 <a href="{{ route('mahasiswa.dashboard') }}" class="inline-block px-6 py-3 border border-outline-variant/30 text-on-surface-variant font-medium rounded-lg hover:bg-surface-bright transition">
 Back to Dashboard
 </a>
 @elseif($existingSubmission && !$existingSubmission->finished_at)
 <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
 <h3 class="text-lg font-bold text-yellow-800 mb-2">Quiz in Progress</h3>
 <p class="text-yellow-700 mb-4">You have an ongoing attempt. Resume it now.</p>
 <a href="{{ route('mahasiswa.quizzes.take', $assignment) }}" class="inline-block px-8 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-xl shadow-lg transition">
 Resume Quiz
 </a>
 </div>
 @else
 <form action="{{ route('mahasiswa.quizzes.start', $assignment) }}" method="POST">
 @csrf
 <button type="submit" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg rounded-xl shadow-xl transform hover:scale-105 transition">
 Start Quiz Now
 </button>
 </form>
 @endif
 </div>
 </div>
 </div>
</x-app-layout>
