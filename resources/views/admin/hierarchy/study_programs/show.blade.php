<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm sm:text-base">
            <a href="{{ route('admin.hierarchy.departments.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                Data Akademik
            </a>
            <span class="text-gray-500">/</span>
            <a href="{{ route('admin.hierarchy.departments.show', $studyProgram->department_id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                {{ $studyProgram->department->name }}
            </a>
            <span class="text-gray-500">/</span>
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                {{ $studyProgram->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Pilih Semester Aktif/Tersedia</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @forelse($semesters as $semester)
                            <a href="{{ route('admin.hierarchy.study-programs.semesters.show', [$studyProgram, $semester]) }}" 
                               class="block p-4 border rounded-lg hover:shadow-md transition duration-150 {{ $semester->is_active ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/50' : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600/80' }}">
                                <h4 class="text-lg font-bold mb-1 {{ $semester->is_active ? 'text-green-800 dark:text-green-300' : 'text-gray-800 dark:text-gray-300' }}">
                                    {{ $semester->name }}
                                </h4>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                    TA: {{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }}
                                </p>
                                @if($semester->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @endif
                            </a>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 col-span-full">Belum ada data semester.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
