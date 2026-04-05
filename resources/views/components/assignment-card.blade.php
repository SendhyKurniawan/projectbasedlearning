@props(['assignment'])

<div class="border rounded-lg p-4 bg-surface-container-lowest sortable-item group" data-id="{{ $assignment->id }}">
 <div class="flex justify-between items-start">
 <div class="flex-1 flex items-start">
 <!-- Drag Handle -->
 <div class="cursor-grab text-outline hover:text-on-surface-variant:text-outline mr-4 mt-1 drag-handle opacity-50 group-hover:opacity-100 transition-opacity" title="Geser untuk mengubah urutan">
 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8" />
 </svg>
 </div>
 <div class="flex-1">
 <div class="flex items-center gap-3 mb-2">
 <h4 class="font-semibold text-on-surface">{{ $assignment->title }}</h4>
 
 @if($assignment->type === 'exercise')
 <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">
 Exercise
 </span>
 @elseif($assignment->type === 'quiz')
 <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">
 Quiz
 </span>
 @elseif($assignment->type === 'tugas')
 <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
 Tugas
 </span>
 @endif
 
 @if($assignment->deadline < now())
 <span class="px-2 py-1 text-xs bg-surface-container-high text-on-surface-variant rounded">
 Closed
 </span>
 @else
 <span class="px-2 py-1 text-xs bg-emerald-50 text-secondary rounded">
 Active
 </span>
 @endif
 </div>
 
 @if($assignment->description)
 <p class="text-sm text-on-surface-variant mb-2">{{ $assignment->description }}</p>
 @endif
 
 <div class="text-sm text-on-surface-variant space-y-1">
 <p><strong>Deadline:</strong> {{ $assignment->deadline->format('d M Y, H:i') }}</p>
 <p><strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}</p>
 <p><strong>Submissions:</strong> {{ $assignment->submissions_count }} mahasiswa</p>
 </div>
 </div> <!-- ends flex-1 content block -->
 </div> <!-- ends flex-1 flex drag container -->
 
 <div class="flex flex-col gap-2 ml-4">
 @if($assignment->type === 'quiz')
 <a href="{{ route('dosen.assignments.questions.index', $assignment) }}" 
 class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm text-center">
 Kelola Pertanyaan
 </a>
 @endif
 <a href="{{ route('dosen.assignments.submissions', $assignment) }}" 
 class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm text-center">
 Lihat Submissions
 </a>
 <a href="{{ route('dosen.assignments.edit', $assignment) }}" 
 class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm text-center">
 Edit
 </a>
 <form action="{{ route('dosen.assignments.destroy', $assignment) }}" 
 method="POST" 
 onsubmit="return confirm('Yakin ingin menghapus ini?')">
 @csrf
 @method('DELETE')
 <button type="submit" 
 class="w-full bg-error hover:bg-red-800 text-white px-3 py-1 rounded text-sm">
 Hapus
 </button>
 </form>
 </div> <!-- ends flex flex-col gap-2 ml-4 -->
 </div> <!-- ends flex justify-between -->
</div> <!-- ends border card -->
