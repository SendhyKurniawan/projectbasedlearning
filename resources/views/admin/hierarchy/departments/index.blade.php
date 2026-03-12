<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Data Akademik - Daftar Jurusan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Pilih Jurusan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($departments as $department)
                            <a href="{{ route('admin.hierarchy.departments.show', $department) }}" class="block p-6 bg-indigo-50 dark:bg-gray-700 border border-indigo-100 dark:border-gray-600 rounded-lg hover:shadow-md hover:bg-indigo-100 dark:hover:bg-gray-600 transition duration-150">
                                <h4 class="text-xl font-bold text-indigo-700 dark:text-indigo-400 mb-2">{{ $department->name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $department->study_programs_count }} Program Studi
                                </p>
                            </a>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 col-span-full">Belum ada jurusan yang terdaftar. <a href="{{ route('admin.departments.create') }}" class="text-indigo-600 underline">Tambah Jurusan</a>.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
