<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Materi - {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('dosen.dashboard') }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daftar Materi</h3>
                        <a href="{{ route('dosen.materials.create', $course) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            + Tambah Materi
                        </a>
                    </div>

                    @forelse($materials as $material)
                        <div class="border dark:border-gray-700 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded text-xs font-mono">
                                            #{{ $material->order }}
                                        </span>
                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $material->title }}</h4>
                                    </div>
                                    
                                    @if($material->content)
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
                                            {!! Str::limit(strip_tags($material->content), 200) !!}
                                        </div>
                                    @endif
                                    
                                    @if($material->file_path)
                                        <a href="{{ Storage::url($material->file_path) }}" 
                                           target="_blank"
                                           class="inline-block mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ basename($material->file_path) }}
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="flex gap-2 ml-4">
                                    <a href="{{ route('dosen.materials.edit', $material) }}" 
                                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('dosen.materials.destroy', $material) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus materi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            Belum ada materi. Klik "Tambah Materi" untuk menambahkan materi baru.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
