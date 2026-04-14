<x-app-layout>
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest">
                    <span><a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary truncate max-w-[200px]">Semua Kursus</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline ">Eksplorasi Pembelajaran</h1>
                <p class="text-on-surface-variant max-w-lg font-medium ">Temukan dan daftar mata kuliah yang relevan dengan jalur akademik Anda.</p>
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

        <div class="bg-surface-container-lowest rounded-[2.5rem] p-8 border border-outline-variant/10 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-on-surface font-headline uppercase tracking-tighter ">Katalog Mata Kuliah</h3>
                <div class="flex items-center gap-2 bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant/20">
                    <span class="material-symbols-outlined text-sm text-outline">filter_list</span>
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Terbaru</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($available_courses as $course)
                    @php $isEnrolled = in_array($course->id, $enrolled_ids); @endphp
                    <div class="group relative bg-surface rounded-[2rem] p-5 border border-outline-variant/10 transition-all hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5 flex flex-col">
                        <div class="relative h-44 mb-6 rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                            
                            @if($isEnrolled)
                                <div class="absolute top-4 right-4 z-10">
                                    <span class="px-3 py-1.5 bg-secondary text-on-secondary text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg shadow-secondary/20 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-xs">check_circle</span> Terdaftar
                                    </span>
                                </div>
                            @endif

                            <div class="absolute bottom-4 left-4 right-4 z-10 text-white">
                                <span class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1 block">{{ $course->kode_matkul }}</span>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col">
                            <div class="flex items-start border-l-4 border-primary/30 pl-4 mb-4">
                                <h4 class="font-bold font-headline text-xl text-on-surface leading-tight group-hover:text-primary transition-colors line-clamp-2">
                                    {{ $course->nama_matkul }}
                                </h4>
                            </div>

                            <div class="flex items-center gap-3 mb-4 pl-4">
                                <div class="w-8 h-8 rounded-full bg-surface-container-high border border-outline-variant/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-xs text-on-surface-variant">person</span>
                                </div>
                                <span class="text-xs font-bold text-on-surface-variant truncate ">{{ $course->dosen->name }}</span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 px-4 mb-6 text-center">
                                <div class="py-2 bg-surface-container-low rounded-xl">
                                    <span class="block text-xs font-black text-on-surface-variant tracking-tighter">{{ $course->materials_count }}</span>
                                    <span class="text-[8px] font-bold text-outline-variant uppercase">Materi</span>
                                </div>
                                <div class="py-2 bg-surface-container-low rounded-xl">
                                    <span class="block text-xs font-black text-on-surface-variant tracking-tighter">{{ $course->assignments_count }}</span>
                                    <span class="text-[8px] font-bold text-outline-variant uppercase">Tugas</span>
                                </div>
                                <div class="py-2 bg-surface-container-low rounded-xl">
                                    <span class="block text-xs font-black text-on-surface-variant tracking-tighter">{{ $course->students_count }}</span>
                                    <span class="text-[8px] font-bold text-outline-variant uppercase">Siswa</span>
                                </div>
                            </div>

                            @if($course->description)
                                <p class="text-xs text-on-surface-variant mb-6 line-clamp-2 px-4 leading-relaxed bg-surface-container-lowest/50 py-2 rounded-lg">
                                    "{{ $course->description }}"
                                </p>
                            @endif

                            <div class="mt-auto px-4 pb-2">
                                @if($isEnrolled)
                                    <a href="{{ route('mahasiswa.courses.show', $course) }}" 
                                       class="w-full h-12 bg-primary/5 text-primary font-black text-xs uppercase tracking-widest rounded-xl hover:bg-primary hover:text-on-primary transition-all flex items-center justify-center gap-2">
                                        MASUK KELAS <span class="material-symbols-outlined text-[18px]">keyboard_tab</span>
                                    </a>
                                @else
                                    <form action="{{ route('mahasiswa.courses.enroll', $course) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full h-12 bg-primary text-on-primary font-black text-xs uppercase tracking-widest rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all flex items-center justify-center gap-2">
                                            DAFTAR SEKARANG <span class="material-symbols-outlined text-[18px]">add_task</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 bg-surface-container-low border-2 border-dashed border-outline-variant/30 rounded-[3rem] flex flex-col items-center justify-center text-center">
                        <div class="w-20 h-20 bg-surface-container mx-auto rounded-3xl flex items-center justify-center mb-6 transform -rotate-12">
                            <span class="material-symbols-outlined text-outline-variant text-4xl">folder_off</span>
                        </div>
                        <h3 class="text-2xl font-bold text-on-surface font-headline mb-3 ">Belum Ada Kursus Tersedia</h3>
                        <p class="text-on-surface-variant font-medium text-sm max-w-sm ">Sistem saat ini belum memiliki mata kuliah yang aktif. Harap hubungi administrator atau tunggu hingga periode akademik baru dimulai.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
