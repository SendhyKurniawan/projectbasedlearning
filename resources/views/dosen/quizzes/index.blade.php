<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Quizzes - {{ $course->nama_matkul }}</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage quizzes for this course.</p>
            </div>
            <a href="{{ route('dosen.quizzes.create', $course) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                + Create Quiz
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            @if($quizzes->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No quizzes found. Click "Create Quiz" to add one.
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($quizzes as $quiz)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $quiz->quiz_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $quiz->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ ucfirst(str_replace('_', ' ', $quiz->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $quiz->duration_minutes }} Min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('dosen.quizzes.attempts.index', $quiz) }}" class="text-purple-600 hover:text-purple-900 dark:hover:text-purple-400">Attempts</a>
                                    <a href="{{ route('dosen.quizzes.questions.index', $quiz) }}" class="text-green-600 hover:text-green-900 dark:hover:text-green-400">Questions ({{ $quiz->questions->count() }})</a>
                                    <a href="{{ route('dosen.quizzes.edit', $quiz) }}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">Edit</a>
                                    <form action="{{ route('dosen.quizzes.destroy', $quiz) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this quiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
        <div class="mt-4">
            <a href="{{ route('dosen.dashboard') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
