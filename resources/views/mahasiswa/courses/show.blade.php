{{-- Halaman detail matkul + jalur belajar (learning path) untuk mahasiswa. --}}
@push('styles')
    @vite('resources/css/pages/mahasiswa/courses.css')
@endpush
<x-app-layout>
    @php
        $totalItems = count($learningPath);
        $completedItems = collect($learningPath)->where('completed', true)->count();
        $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
        
        $nextTask = collect($learningPath)->where('completed', false)->where('locked', false)->first();
        if (!$nextTask) {
            $nextTask = collect($learningPath)->where('completed', false)->first();
        }
    @endphp

    <div class="space-y-10">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-end gap-6">
            <div>
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest mb-2">
                    <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span><a href="{{ route('mahasiswa.courses.index') }}" class="hover:text-primary transition-colors">Courses</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary truncate max-w-[200px]">{{ $course->kode_matkul }}</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline ">{{ $course->nama_matkul }}</h1>
                <p class="text-on-surface-variant body-md mt-1 ">Dosen Pengampu: <span class="font-bold text-on-surface ">{{ $course->dosen->name }}</span></p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('mahasiswa.conferences.index', $course) }}" class="bg-secondary-container text-on-secondary-container px-6 py-3 rounded-xl font-bold flex items-center gap-2 shadow-sm hover:translate-y-[-2px] transition-all ">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">videocam</span>
                    Sesi Link Live <span class="hidden md:inline">Conference</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="px-5 py-4 bg-tertiary-fixed border-l-4 border-tertiary text-on-tertiary-fixed-variant rounded-xl text-sm font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-5 py-4 bg-error-container border-l-4 border-error text-on-error-container rounded-xl text-sm font-bold shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Bento Grid Layout -->
        <div class="grid grid-cols-12 gap-8 items-start">
            <!-- Course Progress Tracker (Primary Visual) -->
            <div class="col-span-12 lg:col-span-8 bg-surface-container-lowest rounded-3xl p-8 border border-outline-variant/10 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-secondary"></div>
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h3 class="text-xl font-headline font-black mb-1 uppercase tracking-tighter">Progres Belajar</h3>
                        <p class="text-xs font-bold text-on-surface-variant uppercase tracking-widest ">Anda telah menyelesaikan {{ $completedItems }} dari {{ $totalItems }} komponen modul</p>
                    </div>
                    <span class="text-3xl font-black text-secondary ">{{ $progress }}%</span>
                </div>
                <div class="space-y-8">
                    <div class="w-full bg-surface-container h-3 rounded-full overflow-hidden">
                        <div class="bg-secondary h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(33,197,94,0.3)]" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 pt-4">
                        <div class="p-4 bg-surface-container-low rounded-2xl text-center border border-outline-variant/5">
                            <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 ">Materi</p>
                            <p class="text-lg font-black ">{{ collect($learningPath)->where('type', 'material')->where('completed', true)->count() }} / {{ collect($learningPath)->where('type', 'material')->count() }}</p>
                        </div>
                        <div class="p-4 bg-surface-container-low rounded-2xl text-center border border-outline-variant/5">
                            <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 ">Tugas</p>
                            <p class="text-lg font-black ">{{ collect($learningPath)->where('type', 'assignment')->where('completed', true)->count() }} / {{ collect($learningPath)->where('type', 'assignment')->count() }}</p>
                        </div>
                        <div class="p-4 bg-surface-container-low rounded-2xl text-center border border-outline-variant/5">
                            <p class="text-[10px] font-black text-on-surface-variant uppercase tracking-widest mb-1 ">Kuis</p>
                            <p class="text-lg font-black ">{{ $quizzes->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Task Highlight (Asymmetric/Actionable) -->
            <div class="col-span-12 lg:col-span-4 bg-primary text-on-primary rounded-[2.5rem] p-8 shadow-xl shadow-primary/10 flex flex-col justify-between min-h-[320px]">
                <div>
                    <div class="bg-white/20 inline-block px-3 py-1 rounded-full text-[10px] font-black tracking-[0.2em] uppercase mb-5 ">KOMPONEN AKTIF</div>
                    @if($nextTask)
                        <h3 class="text-2xl font-headline font-black mb-4 leading-tight uppercase tracking-tighter">{{ $nextTask['item']->title }}</h3>
                        <p class="text-primary-fixed text-sm leading-relaxed mb-6 opacity-90 line-clamp-3">
                            {{ $nextTask['item']->description ?? (isset($nextTask['item']->content) ? Str::limit(strip_tags($nextTask['item']->content), 120) : 'Lanjutkan pengerjaan modul anda untuk mencapai progres maksimal.') }}
                        </p>
                    @else
                        <h3 class="text-2xl font-headline font-black mb-4 leading-tight uppercase tracking-tighter">Modul Selesai!</h3>
                        <p class="text-primary-fixed text-sm leading-relaxed mb-6 opacity-90">Selamat! Anda telah menyelesaikan seluruh komponen pembelajaran pada mata kuliah ini.</p>
                    @endif
                </div>
                <div class="space-y-4">
                    @if($nextTask)
                        <div class="flex items-center gap-2 text-xs text-primary-fixed font-bold opacity-80 mb-2">
                            <span class="material-symbols-outlined text-[18px]">timer</span>
                            @if(isset($nextTask['item']->deadline))
                                Deadline: {{ $nextTask['item']->deadline->format('d M, H:i') }}
                            @else
                                Segera Selesaikan
                            @endif
                        </div>
                        
                        @if($nextTask['type'] === 'material')
                            <a href="{{ route('mahasiswa.materials.show', [$course, $nextTask['item']]) }}" class="block w-full px-4 bg-white text-primary font-black text-center py-3.5 rounded-xl hover:bg-primary-fixed transition-all uppercase tracking-widest text-xs">
                                LANJUTKAN BACA
                            </a>
                        @elseif($nextTask['type'] === 'assignment')
                            @php $nextTaskHasSteps = $nextTask['item']->type === 'tugas' && $nextTask['item']->hasSteps(); @endphp
                            <a href="{{ $nextTaskHasSteps ? route('mahasiswa.assignments.steps.show', $nextTask['item']) : route('mahasiswa.submissions.create', ['assignment_id' => $nextTask['item']->id]) }}" class="block w-full px-4 bg-white text-primary font-black text-center py-3.5 rounded-xl hover:bg-primary-fixed transition-all uppercase tracking-widest text-xs">
                                KERJAKAN TUGAS
                            </a>
                        @endif
                    @else
                        <button class="block w-full px-4 bg-white/20 text-white font-black py-3.5 rounded-xl uppercase tracking-widest text-xs" disabled>
                            SEMUA SELESAI
                        </button>
                    @endif
                </div>
            </div>

            <!-- Left: Materials (Interactive List) -->
            <div class="col-span-12 lg:col-span-7 bg-surface-container-lowest rounded-3xl p-8 border border-outline-variant/10 shadow-sm flex flex-col">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-headline font-black uppercase tracking-tighter">Modul & Materi</h3>
                    <span class="px-3 py-1 bg-surface-container text-on-surface-variant text-[10px] font-black uppercase tracking-widest rounded-lg ">
                        {{ collect($learningPath)->where('type', 'material')->count() }} Item
                    </span>
                </div>
                <div class="space-y-4">
                    @forelse(collect($learningPath)->where('type', 'material') as $item)
                        <div class="group flex items-center justify-between p-4 bg-surface rounded-2xl border border-outline-variant/10 hover:border-primary/20 hover:bg-surface-bright transition-all cursor-pointer">
                            <div class="flex items-center gap-4">
                                @if($item['completed'])
                                    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary border border-secondary/20">
                                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">article</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-black text-on-surface leading-snug @if($item['completed']) line-through opacity-50 @endif">
                                        {{ $item['item']->title }}
                                    </p>
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest opacity-70">
                                        Bagian {{ $item['item']->order }} • PDF / Read
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('mahasiswa.materials.show', [$course, $item['item']]) }}" class="w-10 h-10 rounded-xl bg-surface-container-low flex items-center justify-center text-on-surface-variant group-hover:bg-primary group-hover:text-on-primary transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">@if($item['completed']) preview @else arrow_forward @endif</span>
                            </a>
                        </div>
                    @empty
                        <div class="py-10 text-center">
                            <p class="text-xs font-semibold text-on-surface-variant ">Belum tersedia materi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Quizzes & Stats -->
            <div class="col-span-12 lg:col-span-5 bg-surface-container-lowest rounded-3xl p-8 border border-outline-variant/10 shadow-sm flex flex-col">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-headline font-black uppercase tracking-tighter">Evaluasi & Kuis</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                        <span class="text-[10px] font-black uppercase text-on-surface-variant tracking-widest">Active</span>
                    </div>
                </div>
                <div class="space-y-4">
                    <!-- Exercise Items -->
                    @foreach(collect($learningPath)->where('type', 'assignment')->filter(fn($i) => $i['item']->type === 'exercise') as $item)
                        @php
                            $isCompleted = $item['completed'];
                            $submission = $submissions[$item['item']->id] ?? null;
                            $hasScore = $submission && $submission->score !== null;
                        @endphp
                        <div class="p-4 rounded-2xl bg-surface-container-low border border-outline-variant/10 hover:border-tertiary/30 transition-all group">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-tertiary/10 text-tertiary flex items-center justify-center border border-tertiary/20">
                                    <span class="material-symbols-outlined text-[18px]">code</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="text-sm font-black leading-tight text-on-surface line-clamp-1 group-hover:text-tertiary transition-colors">{{ $item['item']->title }}</h4>
                                        @if(!$isCompleted)
                                            <span class="px-2 py-0.5 bg-error/10 text-error text-[8px] font-black uppercase tracking-widest rounded-md mt-0.5">Belum</span>
                                        @elseif(!$hasScore)
                                            <span class="px-2 py-0.5 bg-secondary/10 text-secondary text-[8px] font-black uppercase tracking-widest rounded-md mt-0.5">Dikerjakan</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-secondary/10 text-secondary text-[8px] font-black uppercase tracking-widest rounded-md mt-0.5">Dinilai</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-on-surface-variant mb-3 opacity-80">Latihan Pemrograman • {{ $item['item']->deadline->format('d M') }}</p>
                                    
                                    <div class="flex items-center justify-between">
                                        @if($item['locked'])
                                            <span class="text-[10px] font-bold text-outline uppercase flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">lock</span> Terkunci
                                            </span>
                                        @elseif(!$hasScore)
                                            <a href="{{ route('mahasiswa.exercises.solve', $item['item']) }}" class="text-[10px] font-black text-tertiary uppercase tracking-widest hover:underline flex items-center gap-1">
                                                KERJAKAN <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                                            </a>
                                        @else
                                            <span class="text-[10px] font-black text-outline uppercase tracking-widest">
                                                Selesai
                                            </span>
                                        @endif
                                        
                                        @if($isCompleted && $submission)
                                            <span class="text-xs font-black ">{{ $submission->score ?? '...' }}/100</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Quizzes -->
                    @foreach($quizzes as $quiz)
                        @php
                            $submission = $submissions[$quiz->id] ?? null;
                            $isFinished = $submission && $submission->finished_at;
                        @endphp
                        <div class="p-4 rounded-2xl bg-surface-container-low border border-outline-variant/10 hover:border-primary/30 transition-all group">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                                    <span class="material-symbols-outlined text-[18px]">quiz</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="text-sm font-black leading-tight text-on-surface line-clamp-1 group-hover:text-primary transition-colors">{{ $quiz->title }}</h4>
                                        @if(!$isFinished)
                                            <span class="px-2 py-0.5 bg-error/10 text-error text-[8px] font-black uppercase tracking-widest rounded-md mt-0.5">Belum</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-secondary/10 text-secondary text-[8px] font-black uppercase tracking-widest rounded-md mt-0.5">Selesai</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-on-surface-variant mb-3 opacity-80 ">{{ $quiz->duration_minutes }} Menit • {{ $quiz->questions->count() }} Soal</p>
                                    
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('mahasiswa.quizzes.show', $quiz) }}" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline flex items-center gap-1 ">
                                            {{ $isFinished ? 'LIHAT HASIL' : 'MULAI KUIS' }} <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                                        </a>
                                        @if($isFinished && $submission->score !== null)
                                            <span class="text-xs font-black ">{{ $submission->score }}/100</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bottom: Assignments (Full grid style) -->
            <div class="col-span-12">
                <div class="flex justify-between items-center mb-8 px-2">
                    <h3 class="text-2xl font-headline font-black uppercase tracking-tighter">Penugasan & Proyek</h3>
                    <div class="w-24 h-1 bg-outline-variant/20 rounded-full"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse(collect($learningPath)->where('type', 'assignment')->filter(fn($i) => !in_array($i['item']->type, ['quiz', 'exercise'])) as $item)
                        @php
                            $isCompleted = $item['completed'];
                            $submission = $submissions[$item['item']->id] ?? null;
                            $hasScore = $submission && $submission->score !== null;
                        @endphp
                        <div class="bg-surface-container-lowest rounded-[2rem] p-6 border border-outline-variant/10 shadow-sm hover:shadow-xl transition-all relative @if($item['locked']) opacity-50 @endif">
                            @if(!$item['locked'])
                                @if(!$isCompleted)
                                    <span class="absolute top-6 right-6 px-3 py-1 bg-error-container text-on-error-container text-[10px] font-black uppercase tracking-widest rounded-lg">Belum Dikerjakan</span>
                                @elseif(!$hasScore)
                                    <span class="absolute top-6 right-6 px-3 py-1 bg-secondary-container text-on-secondary-container text-[10px] font-black uppercase tracking-widest rounded-lg">Sudah Dikerjakan</span>
                                @else
                                    <span class="absolute top-6 right-6 px-3 py-1 bg-secondary-container text-on-secondary-container text-[10px] font-black uppercase tracking-widest rounded-lg">Sudah Dinilai</span>
                                @endif
                            @endif

                            <div class="flex items-center gap-3 mb-5 pr-28">
                                <div class="w-12 h-12 bg-surface-container-low rounded-2xl flex items-center justify-center text-on-surface-variant shadow-inner shrink-0">
                                    <span class="material-symbols-outlined text-[24px]">assignment</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black uppercase text-outline-variant tracking-wider ">Penugasan Mandiri</span>
                                    <h4 class="font-black text-on-surface group-hover:text-primary transition-colors leading-none pr-2">{{ $item['item']->title }}</h4>
                                </div>
                            </div>
                            
                            @php
                                $cardHasSteps = $item['item']->hasSteps();
                                $stepTotal = $cardHasSteps ? $item['item']->steps()->count() : 0;
                                $stepDone = $cardHasSteps ? $item['item']->stepsCompletedCountFor(auth()->id()) : 0;
                            @endphp
                            <div class="flex items-center gap-3 mb-6 px-1">
                                <span class="material-symbols-outlined text-[16px] text-error">calendar_clock</span>
                                <span class="text-[11px] font-bold text-on-surface-variant ">Batas Akhir: {{ $item['item']->deadline->format('d M, H:i') }}</span>
                                @if($cardHasSteps)
                                    <span class="px-2 py-0.5 bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest rounded-lg">Step {{ $stepDone }}/{{ $stepTotal }}</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-outline-variant/10">
                                @if($item['locked'])
                                    <span class="text-[10px] font-black text-outline uppercase tracking-widest flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[18px]">lock</span> Terkunci
                                    </span>
                                @elseif(!$hasScore)
                                    <a href="{{ $cardHasSteps ? route('mahasiswa.assignments.steps.show', $item['item']) : route('mahasiswa.submissions.create', ['assignment_id' => $item['item']->id]) }}" class="px-6 py-2.5 bg-primary text-on-primary font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-primary/90 transition-all">
                                        {{ $cardHasSteps ? ($stepDone > 0 ? 'LANJUTKAN STEP' : 'MULAI STEP') : ($isCompleted ? 'RE-SUBMIT' : 'KERJAKAN') }}
                                    </a>
                                @else
                                    <div class="px-6 py-2.5 bg-surface-variant text-on-surface-variant font-black text-[10px] uppercase tracking-widest rounded-xl opacity-70">
                                        SELESAI
                                    </div>
                                @endif
                                
                                @if($isCompleted && $submission)
                                    <div class="text-right">
                                        <span class="text-[10px] font-bold text-on-surface-variant block uppercase opacity-60">Skor</span>
                                        <span class="text-lg font-black text-on-surface ">{{ $submission->score ?? '...' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 bg-surface-container-low/50 border border-dashed border-outline-variant/20 rounded-[2rem] text-center">
                            <p class="text-sm font-bold text-on-surface-variant ">Belum ada tugas proyek tambahan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
