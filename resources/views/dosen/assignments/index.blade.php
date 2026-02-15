<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tugas - {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('dosen.dashboard') }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daftar Tugas & Quiz</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('dosen.assignments.create', $course) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                + Tambah Tugas / Quiz
                            </a>
                        </div>
                    </div>

                    @forelse($assignments as $assignment)
                        <div class="border dark:border-gray-700 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $assignment->title }}</h4>
                                        
                                        @if($assignment->type === 'exercise')
                                            <span class="px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded">
                                                Exercise
                                            </span>
                                        @elseif($assignment->type === 'quiz')
                                            <span class="px-2 py-1 text-xs bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded">
                                                Quiz
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                                Project
                                            </span>
                                        @endif
                                        
                                        @if($assignment->deadline < now())
                                            <span class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                                                Closed
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                                                Active
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($assignment->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $assignment->description }}</p>
                                    @endif
                                    
                                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                        <p><strong>Deadline:</strong> {{ $assignment->deadline->format('d M Y, H:i') }}</p>
                                        <p><strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}</p>
                                        <p><strong>Submissions:</strong> {{ $assignment->submissions_count }} mahasiswa</p>
                                    </div>
                                </div>
                                
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
                                          onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            Belum ada tugas. Klik "Tambah Tugas" untuk menambahkan tugas baru.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
