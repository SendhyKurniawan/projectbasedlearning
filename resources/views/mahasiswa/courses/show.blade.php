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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

            <!-- Linear Learning Path -->
            <div class="space-y-4">
                @foreach($learningPath as $index => $item)
                    @if($item['type'] === 'material')
                        <!-- Material Card -->
                        <div class="card {{ $item['completed'] ? 'border-l-4 border-green-500' : 'border-l-4 border-blue-500' }}" 
                             style="transition: all 0.2s;">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if($item['completed'])
                                        <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-semibold text-gray-500 uppercase">Materi {{ $item['item']->order }}</span>
                                        @if($item['completed'])
                                            <span class="badge badge-success text-xs">
                                                <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Selesai
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $item['item']->title }}</h3>
                                    
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                        {{ Str::limit(strip_tags($item['item']->content), 150) }}
                                    </p>
                                    
                                    <a href="{{ route('mahasiswa.materials.show', [$course, $item['item']]) }}" 
                                       class="btn btn-primary btn-sm inline-flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                        </svg>
                                        {{ $item['completed'] ? 'Review Materi' : 'Baca Materi' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                    @else
                        <!-- Assignment Card -->
                        <div class="card {{ $item['locked'] ? 'opacity-60 border-l-4 border-gray-300' : ($item['completed'] ? 'border-l-4 border-green-500' : 'border-l-4 border-purple-500') }}" 
                             style="transition: all 0.2s;">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if($item['locked'])
                                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @elseif($item['completed'])
                                        <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-semibold text-gray-500 uppercase">
                                            {{ $item['item']->type === 'exercise' ? 'Exercise' : 'Tugas' }}
                                        </span>
                                        @if($item['locked'])
                                            <span class="badge badge-gray text-xs">
                                                <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Locked
                                            </span>
                                        @elseif($item['completed'])
                                            <span class="badge badge-success text-xs">
                                                <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Submitted
                                            </span>
                                        @else
                                            <span class="badge badge-warning text-xs">
                                                <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                Available
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="font-bold text-lg text-gray-900 mb-2">{{ $item['item']->title }}</h3>
                                    
                                    @if($item['item']->description)
                                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($item['item']->description, 120) }}</p>
                                    @endif
                                    
                                    <p class="text-xs text-gray-500 mb-3 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                        Deadline: {{ $item['item']->deadline->format('d M Y, H:i') }}
                                    </p>
                                    
                                    @if($item['locked'])
                                        <div class="p-3 bg-gray-50 rounded text-sm text-gray-700 border border-gray-200 flex items-start gap-2">
                                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <div>
                                                <strong>Terkunci</strong><br>
                                                Baca <strong>{{ $item['item']->requiredMaterial->title }}</strong> terlebih dahulu untuk membuka tugas ini.
                                            </div>
                                        </div>
                                    @else
                                        @if($item['item']->type === 'exercise')
                                            <a href="{{ route('mahasiswa.exercises.solve', $item['item']) }}" 
                                               class="btn btn-purple btn-sm inline-flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $item['completed'] ? 'Review Exercise' : 'Start Exercise' }}
                                            </a>
                                        @else
                                            <a href="{{ route('mahasiswa.submissions.create', ['assignment_id' => $item['item']->id]) }}" 
                                               class="btn btn-purple btn-sm inline-flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $item['completed'] ? 'View Submission' : 'Submit Assignment' }}
                                            </a>
                                        @endif
                                        
                                        @if($item['completed'] && isset($submissions[$item['item']->id]))
                                            @php $sub = $submissions[$item['item']->id]; @endphp
                                            <div class="mt-3 p-3 bg-green-50 rounded text-sm border border-green-200 flex items-start gap-2">
                                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <div>
                                                    <strong class="text-green-700">Submitted</strong>
                                                    @if($sub->score !== null)
                                                        <br>Score: <strong>{{ $sub->score }}/{{ $item['item']->max_score }}</strong>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                @if(count($learningPath) == 0)
                    <div class="empty-state">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                        </svg>
                        <p class="empty-state-text">Belum ada materi atau tugas tersedia.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
