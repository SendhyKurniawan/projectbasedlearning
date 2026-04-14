<x-app-layout>
    <div class="space-y-8">
        <!-- Page Header -->
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-headline tracking-tight text-on-surface">Ikhtisar Pengajar</h1>
                <p class="text-on-surface-variant mt-1">Ringkasan mata kuliah dan aktivitas akademik Anda.</p>
            </div>
        </div>

        <!-- Course Management Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Stat: Mata Kuliah -->
            <div class="md:col-span-1 bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group shadow-sm border border-outline-variant/10">
                <div class="absolute top-0 left-0 h-full w-1 bg-primary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">auto_stories</span>
                    </div>
                </div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Mata Kuliah</p>
                <p class="text-3xl font-black font-headline text-on-surface mt-1">{{ $stats['total_courses'] }}</p>
                <p class="text-xs text-on-surface-variant mt-4">Ditugaskan semester ini</p>
            </div>

            <!-- Stat: Total Mahasiswa -->
            <div class="md:col-span-1 bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group shadow-sm border border-outline-variant/10">
                <div class="absolute top-0 left-0 h-full w-1 bg-secondary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="h-12 w-12 rounded-xl bg-secondary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-secondary">groups</span>
                    </div>
                </div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Total Mahasiswa</p>
                <p class="text-3xl font-black font-headline text-on-surface mt-1">{{ $stats['total_students'] }}</p>
                <p class="text-xs text-on-surface-variant mt-4">Di semua mata kuliah aktif</p>
            </div>

            <!-- CTA Gradient Card -->
            <div class="md:col-span-2 bg-primary text-on-primary p-6 rounded-2xl relative overflow-hidden shadow-xl shadow-primary/20">
                <div class="relative z-10">
                    <div class="flex justify-between items-center mb-6">
                        <span class="bg-on-primary/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Berlangsung</span>
                        <span class="material-symbols-outlined text-on-primary/60">assignment</span>
                    </div>
                    <h3 class="text-2xl font-bold font-headline">Tugas Aktif</h3>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-4xl font-extrabold">{{ $stats['total_assignments'] }}</span>
                        <span class="text-on-primary/70 text-sm">Tugas Berjalan</span>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <a href="{{ route('dosen.grades.index') }}" class="bg-on-primary text-primary px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-primary-fixed transition-colors inline-block">
                            Manajemen Nilai
                        </a>
                    </div>
                </div>
                <!-- Decorative background icon -->
                <span class="material-symbols-outlined absolute -bottom-8 -right-8 text-[180px] text-on-primary/5 pointer-events-none">rate_review</span>
            </div>
        </div>

        <!-- Daftar Mata Kuliah -->
        <div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/10">
            <div class="px-8 py-6 flex justify-between items-center bg-surface-container-low/30 border-b border-outline-variant/10">
                <h2 class="text-xl font-bold font-headline text-on-surface">Detail Mata Kuliah Aktif</h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @forelse($courses as $course)
                    <div class="bg-surface-container-low p-6 rounded-2xl relative overflow-hidden transition-all hover:bg-surface-container hover:shadow-md border-l-4 border-primary">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest">{{ $course->kode_matkul }}</span>
                                <h3 class="text-lg font-bold font-headline text-on-surface mt-2">{{ $course->nama_matkul }}</h3>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 my-6">
                            <div class="bg-surface-container-lowest p-2 text-center rounded-xl border border-outline-variant/10">
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase">Mahasiswa</p>
                                <p class="font-bold text-on-surface">{{ $course->students_count }}</p>
                            </div>
                            <div class="bg-surface-container-lowest p-2 text-center rounded-xl border border-outline-variant/10">
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase">Materi</p>
                                <p class="font-bold text-on-surface">{{ $course->materials_count }}</p>
                            </div>
                            <div class="bg-surface-container-lowest p-2 text-center rounded-xl border border-outline-variant/10">
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase">Tugas</p>
                                <p class="font-bold text-on-surface">{{ $course->assignments_count }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('dosen.materials.index', $course) }}" class="flex-1 py-2 bg-surface-container-lowest hover:bg-primary/5 text-primary text-xs font-bold rounded-xl text-center transition-colors border border-outline-variant/20 shadow-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">menu_book</span> Materi
                            </a>
                            <a href="{{ route('dosen.assignments.index', $course) }}" class="flex-1 py-2 bg-surface-container-lowest hover:bg-secondary/5 text-secondary text-xs font-bold rounded-xl text-center transition-colors border border-outline-variant/20 shadow-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">assignment</span> Tugas
                            </a>
                            <a href="{{ route('dosen.conferences.index', $course) }}" class="flex-1 py-2 bg-surface-container-lowest hover:bg-tertiary/5 text-tertiary text-xs font-bold rounded-xl text-center transition-colors border border-outline-variant/20 shadow-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">videocam</span> Virtual
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="w-16 h-16 bg-surface-container mx-auto rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-outline-variant text-3xl">inbox</span>
                        </div>
                        <p class="text-on-surface-variant font-medium">Belum ada mata kuliah yang ditugaskan saat ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
