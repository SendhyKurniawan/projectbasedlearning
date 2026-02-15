<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Attempts: {{ $quiz->title }}</h2>
                <p class="text-gray-600 dark:text-gray-400">List of student submissions.</p>
            </div>
            <a href="{{ route('dosen.quizzes.index', $quiz->course) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition shadow-md">
                &larr; Back to Quizzes
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            @if($attempts->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No attempts found yet.
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($attempts as $attempt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $attempt->mahasiswa->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $attempt->mahasiswa->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attempt->finished_at ? $attempt->finished_at->format('d M Y, H:i') : 'In Progress' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $attempt->total_score ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($attempt->finished_at)
                                        <a href="{{ route('dosen.quizzes.attempts.show', [$quiz, $attempt]) }}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">View Details</a>
                                    @else
                                        <span class="text-gray-400 cursor-not-allowed">View Details</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
