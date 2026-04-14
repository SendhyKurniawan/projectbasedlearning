<x-app-layout>
    <div class="space-y-10">
        <!-- Welcome Hero -->
        <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary to-primary-container p-10 text-white shadow-lg shadow-primary/10">
            <div class="relative z-10 max-w-2xl">
                <h2 class="text-4xl font-extrabold font-headline mb-4">Mulai Belajar, {{ Auth::user()->name }}!</h2>
                <p class="text-on-primary-container text-lg font-body leading-relaxed opacity-90">
                    Anda memiliki <span class="font-bold text-secondary-container">{{ $stats['enrolled_courses'] }} mata kuliah</span> terdaftar. Selesaikan <span class="font-bold text-tertiary-fixed">{{ $stats['total_assignments'] - $stats['submitted_assignments'] }} tugas</span> yang masih tersisa minggu ini.
                </p>
                <div class="mt-8 flex gap-4">
                    <a href="{{ route('mahasiswa.courses.index') }}" class="px-6 py-3 bg-white text-primary font-bold rounded-xl text-sm shadow-xl shadow-black/10 transition-transform active:scale-95">LIHAT MATAKULIAH</a>
                    <a href="#assignments" class="px-6 py-3 border border-white/30 hover:bg-white/10 text-white font-bold rounded-xl text-sm transition-all">CEK TUGAS</a>
                </div>
            </div>
            <!-- Abstract architectural shapes for visual depth -->
            <div class="absolute right-[-10%] top-[-20%] w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute right-20 bottom-[-30%] w-64 h-64 bg-emerald-400/20 rounded-full blur-2xl"></div>
        </section>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-container-lowest p-6 rounded-3xl relative overflow-hidden group hover:shadow-md transition-all border border-outline-variant/10">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-primary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined">auto_stories</span>
                    </div>
                </div>
                <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Mata Kuliah</h3>
                <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['enrolled_courses'] }}</p>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-3xl relative overflow-hidden group hover:shadow-md transition-all border border-outline-variant/10">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-tertiary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-tertiary/10 text-tertiary rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined">assignment</span>
                    </div>
                </div>
                <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Total Tugas</h3>
                <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_assignments'] }}</p>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-3xl relative overflow-hidden group hover:shadow-md transition-all border border-outline-variant/10">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-secondary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                </div>
                <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Selesai</h3>
                <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['submitted_assignments'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: My Courses -->
            <div class="lg:col-span-8 space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-extrabold font-headline text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">ifl</span>
                        Mata Kuliah Saya
                    </h3>
                    <a href="{{ route('mahasiswa.courses.index') }}" class="text-primary text-sm font-bold hover:underline">Lihat Semua</a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($enrolled_courses as $course)
                        <a href="{{ route('mahasiswa.courses.show', $course) }}" class="group bg-surface-container-lowest rounded-[2rem] overflow-hidden shadow-sm hover:shadow-xl transition-all p-2 border border-outline-variant/10">
                            <div class="relative h-40 rounded-[1.5rem] overflow-hidden bg-slate-100">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                                <div class="absolute top-4 left-4 bg-primary text-on-primary text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-widest">Active</div>
                                <div class="absolute bottom-4 left-4 right-4 text-white">
                                    <span class="text-[10px] font-bold uppercase tracking-wider opacity-80">{{ $course->kode_matkul }}</span>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start border-l-4 border-secondary pl-3 mb-4">
                                    <h4 class="font-bold font-headline text-lg group-hover:text-primary transition-colors line-clamp-1 ">{{ $course->nama_matkul }}</h4>
                                </div>
                                <div class="flex gap-4 text-[10px] font-black uppercase tracking-widest text-on-surface-variant/70 ">
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]">description</span>
                                        {{ $course->materials_count }} Materials
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]">assignment</span>
                                        {{ $course->assignments_count }} Tasks
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-16 bg-surface border-2 border-dashed border-outline-variant/30 rounded-[2rem] flex flex-col items-center justify-center text-center">
                            <span class="material-symbols-outlined text-outline-variant text-5xl mb-4">auto_stories</span>
                            <h3 class="text-xl font-bold text-on-surface font-headline mb-2">Belum Terdaftar Matakuliah</h3>
                            <a href="{{ route('mahasiswa.courses.index') }}" class="text-primary font-bold text-sm hover:underline">Cari Matakuliah Sekarang</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Deadlines -->
            <div id="assignments" class="lg:col-span-4 space-y-8">
                <div class="bg-surface-container-low rounded-[2rem] p-8 border border-outline-variant/10">
                    <h3 class="text-xl font-bold font-headline mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-error">event_upcoming</span>
                        Tugas Terdekat
                    </h3>
                    <div class="space-y-6">
                        @forelse($upcoming_assignments as $assignment)
                            <div class="flex gap-4 group">
                                <div class="flex-shrink-0 w-12 h-14 bg-surface-container-lowest rounded-xl flex flex-col items-center justify-center shadow-sm">
                                    <span class="text-[10px] font-black text-on-surface-variant uppercase">{{ $assignment->deadline->format('M') }}</span>
                                    <span class="text-lg font-bold text-primary">{{ $assignment->deadline->format('d') }}</span>
                                </div>
                                <div class="flex-1 border-b border-outline-variant/30 pb-4 group-last:border-0">
                                    <h4 class="text-sm font-bold font-headline line-clamp-1">{{ $assignment->title }}</h4>
                                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider line-clamp-1 ">{{ $assignment->course->nama_matkul }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-xs font-semibold text-on-surface-variant ">Semua tugas telah diselesaikan!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Small Info / Announcements Mock -->
                <div class="bg-white rounded-[2rem] p-8 border border-outline-variant/20 shadow-sm">
                    <h3 class="text-lg font-bold font-headline mb-6">Pengumuman</h3>
                    <div class="space-y-6">
                        @forelse($announcements as $announcement)
                        <div class="relative pl-6 before:content-[''] before:absolute before:left-0 before:top-1 before:bottom-0 before:w-1.5 before:bg-primary before:rounded-full">
                            <span class="text-[10px] font-bold text-primary mb-1 block uppercase">{{ $announcement->author->role ?? 'SISTEM' }} • {{ $announcement->created_at->diffForHumans() }}</span>
                            <h4 class="text-sm font-bold font-body leading-snug mb-1">{{ $announcement->title }}</h4>
                            <p class="text-[11px] text-on-surface-variant line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($announcement->content), 100) }}</p>
                        </div>
                        @empty
                        <div class="text-center text-sm text-on-surface-variant py-4">
                            Belum ada pengumuman
                        </div>
                        @endforelse
                    </div>
                    <a href="{{ route('announcements.index') }}" class="mt-8 py-3 bg-surface-container-low text-primary text-xs font-bold rounded-xl hover:bg-surface-container transition-colors block text-center">
                        LIHAT SEMUA UPDATE
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
