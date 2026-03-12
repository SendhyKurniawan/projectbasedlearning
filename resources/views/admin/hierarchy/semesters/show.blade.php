<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm sm:text-base flex-wrap">
            <a href="{{ route('admin.hierarchy.departments.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                Data Akademik
            </a>
            <span class="text-gray-500">/</span>
            <a href="{{ route('admin.hierarchy.departments.show', $studyProgram->department_id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                {{ $studyProgram->department->name }}
            </a>
            <span class="text-gray-500">/</span>
            <a href="{{ route('admin.hierarchy.study-programs.show', $studyProgram) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                {{ $studyProgram->name }}
            </a>
            <span class="text-gray-500">/</span>
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                {{ $semester->name }} ({{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }})
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Kelas Tersedia --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Kelas</h3>
                            <a href="{{ route('admin.student-classes.create') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                + Kelas Baru
                            </a>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($classes as $kelas)
                                <a href="{{ route('admin.hierarchy.student-classes.show', $kelas) }}" 
                                   class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <div>
                                        <h4 class="font-bold text-gray-800 dark:text-gray-200">{{ $kelas->name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $kelas->students_count }} Mahasiswa Terdaftar</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @empty
                                <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                                    Belum ada kelas yang ditentukan untuk prodi dan semester ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Mata Kuliah Semester (Diwariskan) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Mata Kuliah Semester</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Atur mata kuliah yang akan diikuti oleh semua kelas di semester ini</p>
                            </div>
                        </div>

                        {{-- Form Tambah Mata Kuliah (X-Data Toggle) --}}
                        <div x-data="{ open: false }" class="mb-6">
                            <button @click="open = !open" class="mb-4 text-sm px-3 py-1.5 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-semibold rounded-md border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition">
                                + Tambah Mata Kuliah
                            </button>

                            <div x-show="open" x-transition class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 mb-4">
                                <form action="{{ route('admin.hierarchy.study-programs.semesters.add-course', [$studyProgram, $semester]) }}" method="POST">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-input-label for="kode_matkul" value="Kode MK" />
                                            <x-text-input id="kode_matkul" name="kode_matkul" type="text" class="mt-1 block w-full text-sm" required />
                                        </div>
                                        <div>
                                            <x-input-label for="nama_matkul" value="Nama Mata Kuliah" />
                                            <x-text-input id="nama_matkul" name="nama_matkul" type="text" class="mt-1 block w-full text-sm" required />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <x-input-label for="sks" value="SKS" />
                                            <x-text-input id="sks" name="sks" type="number" class="mt-1 block w-full text-sm" min="1" required />
                                        </div>
                                        <div>
                                            <x-input-label for="dosen_id" value="Dosen Pengampu" />
                                            <select id="dosen_id" name="dosen_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm text-sm" required>
                                                <option value="" disabled selected>-- Pilih Dosen --</option>
                                                @foreach($dosens as $dosen)
                                                    <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="description" value="Deskripsi (Opsional)" />
                                        <textarea id="description" name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="open = false" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">Batal</button>
                                        <x-primary-button class="text-sm">Simpan Course</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Daftar Mata Kuliah --}}
                        <div class="space-y-3">
                            @forelse($semesterCourses as $course)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-gray-800 border-l-4 border-l-indigo-500 border-y border-r border-gray-200 dark:border-gray-700 rounded-r-lg shadow-sm">
                                    <div class="mb-2 sm:mb-0">
                                        <h4 class="font-bold text-gray-800 dark:text-gray-200">{{ $course->nama_matkul }}</h4>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap gap-2">
                                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 font-mono">{{ $course->kode_matkul }}</span>
                                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700">{{ $course->sks }} SKS</span>
                                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Dosen: {{ $course->dosen->name }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('admin.hierarchy.courses.destroy', $course) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus mata kuliah {{ addslashes($course->nama_matkul) }}?')" class="text-xs px-2 py-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 border border-red-200 dark:border-red-800 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                                    Belum ada mata kuliah yang di-assign untuk semester ini.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
