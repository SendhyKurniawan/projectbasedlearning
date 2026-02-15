<x-app-layout>
    <div class="max-w-6xl mx-auto py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Questions - {{ $assignment->title }}</h2>
                <p class="text-gray-600 dark:text-gray-400">Total Questions: {{ $questions->count() }}</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    &larr; Back
                </a>
                <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                    + Add New Question
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($questions as $index => $question)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold px-2 py-1 rounded">
                                    Q{{ $index + 1 }}
                                </span>
                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold px-2 py-1 rounded-full uppercase">
                                    {{ str_replace('_', ' ', $question->question_type) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Weight: {{ $question->score_weight }}
                                </span>
                            </div>
                            <p class="text-gray-900 dark:text-white text-lg font-medium mb-4">
                                {{ $question->question_text }}
                            </p>

                            @if($question->question_type === 'pilihan_ganda')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-4">
                                    @foreach($question->options as $option)
                                        <div class="flex items-center p-3 rounded-lg border {{ $option->is_correct ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                            @if($option->is_correct)
                                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            @else
                                                <div class="w-5 h-5 mr-2"></div>
                                            @endif
                                            <span class="text-sm {{ $option->is_correct ? 'text-green-900 dark:text-green-100 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $option->option_text }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'code_snippet' || $question->question_type === 'essay')
                                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-xs text-gray-500 uppercase font-bold mb-1">Correct Answer / Key:</p>
                                    <pre class="text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap">{{ $question->correct_answer ?? '(No correct answer provided)' }}</pre>
                                </div>
                            @endif
                        </div>
                        
                        <div class="ml-4 flex flex-col gap-2">
                            <a href="{{ route('dosen.assignments.questions.edit', $question) }}" class="text-blue-600 hover:text-blue-800 dark:hover:text-blue-400 text-sm font-medium">Edit</a>
                             <form action="{{ route('dosen.assignments.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Delete this question?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 dark:hover:text-red-400 text-sm font-medium">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No questions added yet.</p>
                    <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                        Start Adding Questions
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
