<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('mahasiswa.courses.index') }}" 
               class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke Daftar Course
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mb-4">
                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Course Info -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $course->nama_matkul }}</h1>
                        <p class="text-gray-600 text-sm">{{ $course->kode_matkul }} &bull; Dosen: {{ $course->dosen->name }}</p>
                        @if($course->description)
                            <p class="text-gray-700 mt-3">{{ $course->description }}</p>
                        @endif
                    </div>
                    <span class="badge badge-primary">Terdaftar</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="card" style="margin-bottom: 1.5rem;">
                @php
                    $totalItems = count($learningPath);
                    $completedItems = collect($learningPath)->where('completed', true)->count();
                    $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                @endphp
                
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">Course Progress</h3>
                        <p class="text-sm text-gray-600">{{ $completedItems }} of {{ $totalItems}} completed</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-primary">{{ $progress }}%</div>
                    </div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" 
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <!-- Three Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Column 1: Materi -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 px-1 border-b pb-2">Materi</h3>
                    <div class="space-y-4">
                        @forelse(collect($learningPath)->where('type', 'material') as $index => $item)
                            <div class="card {{ $item['completed'] ? 'border-l-4 border-green-500' : 'border-l-4 border-blue-500' }}" style="transition: all 0.2s;">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        @if($item['completed'])
                                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-semibold text-gray-500 uppercase">Bagian {{ $item['item']->order }}</span>
                                            @if($item['completed'])
                                                <span class="badge badge-success text-[10px]">Selesai</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-base text-gray-900 mb-1 leading-tight">{{ $item['item']->title }}</h3>
                                        <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ Str::limit(strip_tags($item['item']->content), 80) }}</p>
                                        <a href="{{ route('mahasiswa.materials.show', [$course, $item['item']]) }}" class="btn btn-primary text-xs px-2 py-1 inline-flex items-center gap-1">
                                            {{ $item['completed'] ? 'Review' : 'Baca Materi' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-sm text-gray-500 italic">Belum ada materi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Column 2: Tugas -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 px-1 border-b pb-2">Tugas</h3>
                    <div class="space-y-4">
                        @forelse(collect($learningPath)->where('type', 'assignment')->filter(fn($i) => !in_array($i['item']->type, ['quiz', 'exercise'])) as $index => $item)
                            <div class="card {{ $item['locked'] ? 'opacity-60 border-l-4 border-gray-300' : ($item['completed'] ? 'border-l-4 border-green-500' : 'border-l-4 border-purple-500') }}" style="transition: all 0.2s;">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        @if($item['locked'])
                                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @elseif($item['completed'])
                                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-semibold text-gray-500 uppercase">Tugas {{ $loop->iteration }}</span>
                                            @if($item['locked'])<span class="badge badge-gray text-[10px]">Locked</span>
                                            @elseif($item['completed'])<span class="badge badge-success text-[10px]">Submitted</span>
                                            @else<span class="badge badge-warning text-[10px]">Available</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-base text-gray-900 mb-1 leading-tight">{{ $item['item']->title }}</h3>
                                        <p class="text-[11px] text-gray-500 mb-2">Deadline: {{ $item['item']->deadline->format('d M, H:i') }}</p>
                                        
                                        @if($item['locked'])
                                            <div class="text-[11px] text-gray-500 bg-gray-50 p-2 rounded">
                                                Baca materi "{{ $item['item']->requiredMaterial->title }}" dahulu.
                                            </div>
                                        @else
                                            <a href="{{ route('mahasiswa.submissions.create', ['assignment_id' => $item['item']->id]) }}" class="btn btn-purple text-xs px-2 py-1 inline-flex items-center gap-1">
                                                {{ $item['completed'] ? 'Lihat' : 'Kerjakan Tugas' }}
                                            </a>
                                            @if($item['completed'] && isset($submissions[$item['item']->id]))
                                                @php $sub = $submissions[$item['item']->id]; @endphp
                                                @if($sub->score !== null)
                                                    <div class="mt-2 text-xs font-semibold text-green-700">Skor: {{ $sub->score }}/{{ $item['item']->max_score }}</div>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-sm text-gray-500 italic">Belum ada tugas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Column 3: Latihan -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 px-1 border-b pb-2">Latihan & Quiz</h3>
                    <div class="space-y-4">
                        <!-- Exercises from learningPath -->
                        @foreach(collect($learningPath)->where('type', 'assignment')->filter(fn($i) => $i['item']->type === 'exercise') as $index => $item)
                            <div class="card {{ $item['locked'] ? 'opacity-60 border-l-4 border-gray-300' : ($item['completed'] ? 'border-l-4 border-green-500' : 'border-l-4 border-purple-500') }}" style="transition: all 0.2s;">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        @if($item['locked'])
                                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @elseif($item['completed'])
                                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-semibold text-gray-500 uppercase">Latihan Kode {{ $loop->iteration }}</span>
                                            @if($item['locked'])<span class="badge badge-gray text-[10px]">Locked</span>
                                            @elseif($item['completed'])<span class="badge badge-success text-[10px]">Selesai</span>
                                            @else<span class="badge badge-warning text-[10px]">Available</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-base text-gray-900 mb-1 leading-tight">{{ $item['item']->title }}</h3>
                                        <p class="text-[11px] text-gray-500 mb-2 border-b pb-1">Deadline: {{ $item['item']->deadline->format('d M, H:i') }}</p>
                                        
                                        @if($item['locked'])
                                            <div class="text-[11px] text-gray-500 bg-gray-50 p-2 rounded">
                                                Baca materi "{{ $item['item']->requiredMaterial->title }}" dahulu.
                                            </div>
                                        @else
                                            <a href="{{ route('mahasiswa.exercises.solve', $item['item']) }}" class="btn btn-purple text-xs px-2 py-1 inline-flex items-center gap-1">
                                                {{ $item['completed'] ? 'Review' : 'Mulai Latihan' }}
                                            </a>
                                            @if($item['completed'] && isset($submissions[$item['item']->id]))
                                                @php $sub = $submissions[$item['item']->id]; @endphp
                                                @if($sub->score !== null)
                                                    <div class="mt-2 text-xs font-semibold text-green-700">Skor: {{ $sub->score }}/{{ $item['item']->max_score }}</div>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Quizzes array -->
                        @foreach($quizzes as $quiz)
                            @php
                                $submission = $submissions[$quiz->id] ?? null;
                                $isFinished = $submission && $submission->finished_at;
                            @endphp
                            <div class="card {{ $isFinished ? 'border-l-4 border-green-500' : 'border-l-4 border-yellow-500' }} transition-all hover:shadow-md">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full {{ $isFinished ? 'bg-green-100' : 'bg-yellow-100' }} flex items-center justify-center">
                                            <svg class="w-6 h-6 {{ $isFinished ? 'text-green-600' : 'text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-semibold text-gray-500 uppercase">Kuis {{ $loop->iteration }}</span>
                                            @if($isFinished)<span class="badge badge-success text-[10px]">Completed</span>
                                            @else<span class="badge badge-warning text-[10px]">Available</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-base text-gray-900 mb-1 leading-tight">{{ $quiz->title }}</h3>
                                        <p class="text-[11px] text-gray-600 mb-2">{{ $quiz->duration_minutes }} Min &bull; {{ $quiz->questions->count() }} Qs</p>
                                        
                                        <a href="{{ route('mahasiswa.quizzes.show', $quiz) }}" class="btn btn-sm {{ $isFinished ? 'btn-success' : 'btn-primary' }} text-xs px-2 py-1">
                                            {{ $isFinished ? 'View Result' : 'Start Quiz' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if(collect($learningPath)->where('type', 'assignment')->filter(fn($i) => $i['item']->type === 'exercise')->isEmpty() && $quizzes->isEmpty())
                            <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-sm text-gray-500 italic">Belum ada latihan atau kuis.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
