<x-app-layout>
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Quiz - {{ $quiz->title }}</h2>
            <a href="{{ route('dosen.quizzes.index', $quiz->course) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                Cancel
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6 border border-gray-200 dark:border-gray-700">
            <form action="{{ route('dosen.quizzes.update', $quiz) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quiz Title</label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $quiz->title) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quiz_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quiz Number</label>
                            <input type="number" name="quiz_number" id="quiz_number" required min="1" value="{{ old('quiz_number', $quiz->quiz_number) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('quiz_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" required min="1" value="{{ old('duration_minutes', $quiz->duration_minutes) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('duration_minutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Question Type</label>
                        <select name="type" id="type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="pilihan_ganda" {{ old('type', $quiz->type) == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda (Multiple Choice)</option>
                            <option value="essay" {{ old('type', $quiz->type) == 'essay' ? 'selected' : '' }}>Essay</option>
                            <option value="code_snippet" {{ old('type', $quiz->type) == 'code_snippet' ? 'selected' : '' }}>Code Snippet</option>
                        </select>
                        @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md transition">
                            Update Quiz
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
