<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.hierarchy.departments.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                Data Akademik
            </a>
            <span class="text-gray-500">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $department->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Pilih Program Studi</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($studyPrograms as $prodi)
                            <a href="{{ route('admin.hierarchy.study-programs.show', $prodi) }}" class="block p-6 bg-purple-50 dark:bg-gray-700 border border-purple-100 dark:border-gray-600 rounded-lg hover:shadow-md hover:bg-purple-100 dark:hover:bg-gray-600 transition duration-150">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-xl font-bold text-purple-700 dark:text-purple-400">{{ $prodi->name }}</h4>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-200 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        {{ $prodi->level }}
                                    </span>
                                </div>
                                <p class="text-sm font-mono text-gray-500 dark:text-gray-400 mb-4">{{ $prodi->code }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $prodi->student_classes_count }} Kelas terdaftar
                                </p>
                            </a>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 col-span-full">Belum ada program studi di jurusan ini. <a href="{{ route('admin.study-programs.create') }}" class="text-purple-600 underline">Tambah Prodi</a>.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
