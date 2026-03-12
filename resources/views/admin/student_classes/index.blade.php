<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Dashboard
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Manajemen Kelas') }}
                </h2>
            </div>
            <a href="{{ route('admin.student-classes.create') }}" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Tambah Kelas
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama Kelas</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Program Studi</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Semester Aktif</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($studentClasses as $kelas)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $kelas->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $kelas->studyProgram->name }} ({{ $kelas->studyProgram->level }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $kelas->semester->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.student-classes.edit', $kelas) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('admin.student-classes.destroy', $kelas) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada data kelas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
