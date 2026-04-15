<x-app-layout>
    <div class="space-y-8">
        <!-- Page Header & Action -->
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-headline tracking-tight text-on-surface">Lecturer Overview</h1>
                <p class="text-on-surface-variant mt-1">Overview of your courses and academic activity.</p>
            </div>
        </div>

        <!-- Course Management Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Primary Stats -->
            <div class="md:col-span-1 bg-surface-container-lowest p-6 rounded-xl relative overflow-hidden group shadow-sm border border-outline-variant/10">
                <div class="absolute top-0 left-0 h-full w-1 bg-primary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" data-icon="auto_stories">auto_stories</span>
                    </div>
                </div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">My Courses</p>
                <p class="text-4xl font-extrabold font-headline text-on-surface mt-1">{{ $stats['total_courses'] }}</p>
                <p class="text-xs text-on-surface-variant mt-4">Assigned this semester</p>
            </div>

            <div class="md:col-span-1 bg-surface-container-lowest p-6 rounded-xl relative overflow-hidden group shadow-sm border border-outline-variant/10">
                <div class="absolute top-0 left-0 h-full w-1 bg-secondary"></div>
                <div class="flex justify-between items-start mb-4">
                    <div class="h-12 w-12 rounded-lg bg-secondary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-secondary" data-icon="groups">groups</span>
                    </div>
                </div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">Total Students</p>
                <p class="text-4xl font-extrabold font-headline text-on-surface mt-1">{{ $stats['total_students'] }}</p>
                <p class="text-xs text-on-surface-variant mt-4">Across all active courses</p>
            </div>

            <!-- Gradient Card -->
            <div class="md:col-span-2 bg-gradient-to-br from-slate-900 to-blue-900 text-white p-6 rounded-xl relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                    <div class="flex justify-between items-center mb-6">
                        <span class="bg-tertiary px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Ongoing</span>
                        <span class="material-symbols-outlined text-white/60" data-icon="assignment">assignment</span>
                    </div>
                    <h3 class="text-2xl font-bold font-headline">Assignments</h3>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-4xl font-extrabold">{{ $stats['total_assignments'] }}</span>
                        <span class="text-white/70 text-sm">Active Tasks</span>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <a href="{{ route('dosen.grades.index') }}" class="bg-white text-blue-900 px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-secondary-container transition-colors inline-block">Grade Management</a>
                    </div>
                </div>
                <!-- Decorative background icon -->
                <span class="material-symbols-outlined absolute -bottom-8 -right-8 text-[180px] text-white/5 pointer-events-none" data-icon="rate_review">rate_review</span>
            </div>
        </div>

        <!-- My Courses Section -->
        <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm border border-outline-variant/10">
            <div class="px-8 py-6 flex justify-between items-center bg-surface-container-low/30 border-b border-outline-variant/10">
                <h2 class="text-xl font-bold font-headline text-on-surface">Active Courses Details</h2>
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
                            <div class="bg-white/50 p-2 text-center rounded-lg border border-outline-variant/10 shadow-sm">
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase">Students</p>
                                <p class="font-bold text-on-surface">{{ $course->students_count }}</p>
                            </div>
                            <div class="bg-white/50 p-2 text-center rounded-lg border border-outline-variant/10 shadow-sm">
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase">Materials</p>
                                <p class="font-bold text-on-surface">{{ $course->materials_count }}</p>
                            </div>
                            <div class="bg-white/50 p-2 text-center rounded-lg border border-outline-variant/10 shadow-sm">
                                <p class="text-[10px] font-bold text-on-surface-variant uppercase">Tasks</p>
                                <p class="font-bold text-on-surface">{{ $course->assignments_count }}</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('dosen.materials.index', $course) }}" class="flex-1 py-2 bg-white hover:bg-primary-fixed/20 text-primary text-xs font-bold rounded-xl text-center transition-colors border border-outline-variant/20 shadow-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">menu_book</span> Materials
                            </a>
                            <a href="{{ route('dosen.assignments.index', $course) }}" class="flex-1 py-2 bg-white hover:bg-secondary-fixed/20 text-secondary text-xs font-bold rounded-xl text-center transition-colors border border-outline-variant/20 shadow-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">assignment</span> Assignments
                            </a>
                            <a href="{{ route('dosen.conferences.index', $course) }}" class="flex-1 py-2 bg-white hover:bg-tertiary-fixed/20 text-tertiary text-xs font-bold rounded-xl text-center transition-colors border border-outline-variant/20 shadow-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">videocam</span> Live Lab
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="w-16 h-16 bg-surface-container mx-auto rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-outline-variant text-3xl">inbox</span>
                        </div>
                        <p class="text-on-surface-variant font-medium">You don't have any classes assigned right now.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
