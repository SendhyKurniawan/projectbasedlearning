<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Daftar Course') }}
            </h2>
            <a href="{{ route('mahasiswa.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
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

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Course Tersedia</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($available_courses as $course)
                            <div class="border dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $course->nama_matkul }}</h4>
                                    @if(in_array($course->id, $enrolled_ids))
                                        <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                                            Terdaftar
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $course->kode_matkul }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Dosen: {{ $course->dosen->name }}
                                </p>
                                
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    {{ $course->materials_count }} Materi &bull; 
                                    {{ $course->assignments_count }} Tugas &bull; 
                                    {{ $course->students_count }} Mahasiswa
                                </div>
                                
                                @if($course->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                        {{ $course->description }}
                                    </p>
                                @endif
                                
                                <div class="flex gap-2">
                                    @if(in_array($course->id, $enrolled_ids))
                                        <a href="{{ route('mahasiswa.courses.show', $course) }}" 
                                           class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-2 rounded">
                                            Lihat Course
                                        </a>
                                    @else
                                        <form action="{{ route('mahasiswa.courses.enroll', $course) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded">
                                                Daftar Course
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 col-span-3">Belum ada course tersedia.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
