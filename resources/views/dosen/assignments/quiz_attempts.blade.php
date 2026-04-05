<x-app-layout>
 <div class="space-y-6">
 <div class="flex justify-between items-center">
 <div>
 <h2 class="text-2xl font-bold text-on-surface">Attempts: {{ $quiz->title }}</h2>
 <p class="text-on-surface-variant">List of student submissions.</p>
 </div>
 <a href="{{ route('dosen.quizzes.index', $quiz->course) }}" class="px-4 py-2 bg-gray-600 hover:bg-surface-container-high text-white rounded-lg transition shadow-md">
 &larr; Back to Quizzes
 </a>
 </div>

 <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-surface-container-low">
 @if($attempts->isEmpty())
 <div class="p-8 text-center text-on-surface-variant">
 No attempts found yet.
 </div>
 @else
 <table class="min-w-full divide-y divide-surface-container-low">
 <thead class="bg-surface-container-low/50">
 <tr>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Student</th>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Date</th>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Score</th>
 <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-on-surface-variant uppercase tracking-wider">Actions</th>
 </tr>
 </thead>
 <tbody class="bg-surface-container-lowest divide-y divide-surface-container-low">
 @foreach($attempts as $attempt)
 <tr class="hover:bg-surface-bright transition">
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="flex items-center">
 
 <div class="ml-4">
 <div class="text-sm font-medium text-on-surface">{{ $attempt->mahasiswa->name }}</div>
 <div class="text-sm text-on-surface-variant">{{ $attempt->mahasiswa->email }}</div>
 </div>
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant">
 {{ $attempt->finished_at ? $attempt->finished_at->format('d M Y, H:i') : 'In Progress' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-on-surface">
 {{ $attempt->total_score ?? '-' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 @if($attempt->finished_at)
 <a href="{{ route('dosen.quizzes.attempts.show', [$quiz, $attempt]) }}" class="text-blue-600 hover:text-blue-900:text-blue-400">View Details</a>
 @else
 <span class="text-outline cursor-not-allowed">View Details</span>
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
