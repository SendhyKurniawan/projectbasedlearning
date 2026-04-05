<x-app-layout>
 <div class="py-6">
 <div class="flex justify-between items-center mb-6">
 <div>
 <h2 class="text-2xl font-bold text-on-surface">Questions - {{ $assignment->title }}</h2>
 <p class="text-on-surface-variant">Total Questions: {{ $questions->count() }}</p>
 </div>
 <div class="space-x-2">
 <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="px-4 py-2 border border-outline-variant/30 text-on-surface-variant rounded-lg hover:bg-surface-bright transition">
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
 <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-container-low p-6">
 <div class="flex justify-between items-start">
 <div class="flex-1">
 <div class="flex items-center gap-2 mb-2">
 <span class="bg-surface-container-low text-on-surface text-xs font-bold px-2 py-1 rounded">
 Q{{ $index + 1 }}
 </span>
 <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full uppercase">
 {{ str_replace('_', ' ', $question->question_type) }}
 </span>
 <span class="text-xs text-on-surface-variant">
 Weight: {{ $question->score_weight }}
 </span>
 </div>
 <p class="text-on-surface text-lg font-medium mb-4">
 {{ $question->question_text }}
 </p>

 @if($question->question_type === 'pilihan_ganda')
 <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-4">
 @foreach($question->options as $option)
 <div class="flex items-center p-3 rounded-lg border {{ $option->is_correct ? 'border-green-500 bg-green-50' : 'border-surface-container-low' }}">
 @if($option->is_correct)
 <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
 @else
 <div class="w-5 h-5 mr-2"></div>
 @endif
 <span class="text-sm {{ $option->is_correct ? 'text-green-900 font-medium' : 'text-on-surface-variant' }}">
 {{ $option->option_text }}
 </span>
 </div>
 @endforeach
 </div>
 @elseif($question->question_type === 'code_snippet' || $question->question_type === 'essay')
 <div class="mt-4 p-4 bg-surface-container-low rounded-lg border border-surface-container-low">
 <p class="text-xs text-on-surface-variant uppercase font-bold mb-1">Correct Answer / Key:</p>
 <pre class="text-sm text-on-surface whitespace-pre-wrap">{{ $question->correct_answer ?? '(No correct answer provided)' }}</pre>
 </div>
 @endif
 </div>
 
 <div class="ml-4 flex flex-col gap-2">
 <a href="{{ route('dosen.assignments.questions.edit', $question) }}" class="text-blue-600 hover:text-blue-800:text-blue-400 text-sm font-medium">Edit</a>
 <form action="{{ route('dosen.assignments.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Delete this question?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-red-800:text-red-400 text-sm font-medium">Delete</button>
 </form>
 </div>
 </div>
 </div>
 @empty
 <div class="text-center space-y-6 bg-surface-container-lowest rounded-xl border border-dashed border-outline-variant/30">
 <p class="text-on-surface-variant mb-4">No questions added yet.</p>
 <a href="{{ route('dosen.assignments.questions.create', $assignment) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
 Start Adding Questions
 </a>
 </div>
 @endforelse
 </div>
 </div>
</x-app-layout>
