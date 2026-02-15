<x-app-layout>
    <div class="max-w-4xl mx-auto py-12">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="p-8 md:p-12 text-center">
                <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">{{ $quiz->title }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mb-8">{{ $quiz->course->nama_matkul }}</p>

                <div class="inline-flex flex-wrap justify-center gap-4 mb-8">
                    <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-bold">Duration</span>
                        <span class="block text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->duration_minutes }} Mins</span>
                    </div>
                    <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-bold">Questions</span>
                        <span class="block text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->questions->count() }}</span>
                    </div>
                    <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-bold">Type</span>
                        <span class="block text-lg font-semibold text-gray-900 dark:text-white">{{ Str::title(str_replace('_', ' ', $quiz->type)) }}</span>
                    </div>
                </div>

                @if($existingAttempt && $existingAttempt->finished_at)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 mb-6">
                        <h3 class="text-lg font-bold text-green-800 dark:text-green-300 mb-2">Quiz Completed</h3>
                        <p class="text-green-700 dark:text-green-400">
                             You finished this quiz on {{ $existingAttempt->finished_at->format('M d, Y H:i') }}.
                        </p>
                        @if($existingAttempt->total_score !== null)
                            <div class="mt-4 text-4xl font-extrabold text-green-600 dark:text-green-400">
                                {{ $existingAttempt->total_score }} <span class="text-base font-normal text-gray-500 dark:text-gray-400">/ {{ $quiz->questions->sum('score_weight') }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Score (Auto-graded only)</p>
                        @endif
                    </div>
                    <a href="{{ route('mahasiswa.dashboard') }}" class="inline-block px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Back to Dashboard
                    </a>
                @elseif($existingAttempt && !$existingAttempt->finished_at)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 mb-6">
                        <h3 class="text-lg font-bold text-yellow-800 dark:text-yellow-300 mb-2">Quiz in Progress</h3>
                        <p class="text-yellow-700 dark:text-yellow-400 mb-4">You have an ongoing attempt. Resume it now.</p>
                        <a href="{{ route('mahasiswa.quizzes.take', $quiz) }}" class="inline-block px-8 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-xl shadow-lg transition">
                            Resume Quiz
                        </a>
                    </div>
                @else
                    <form action="{{ route('mahasiswa.quizzes.start', $quiz) }}" method="POST">
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
