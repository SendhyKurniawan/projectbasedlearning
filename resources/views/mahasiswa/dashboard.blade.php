<x-app-layout>
 <div class="space-y-8">
 <!-- Header -->
 <div>
 <h2 class="text-3xl font-extrabold text-on-surface tracking-tight font-headline">Student Dashboard</h2>
 <p class="text-on-surface-variant mt-2 font-medium">Track your learning progress and upcoming assignments.</p>
 </div>

 <!-- Statistics Cards -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-blue-50 text-primary rounded-lg">
 <span class="material-symbols-outlined">auto_stories</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Enrolled Courses</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['enrolled_courses'] }}</p>
 </div>

 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-tertiary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-tertiary-fixed text-tertiary rounded-lg">
 <span class="material-symbols-outlined">assignment</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Total Assignments</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_assignments'] }}</p>
 </div>

 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-secondary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-emerald-50 text-secondary rounded-lg">
 <span class="material-symbols-outlined">check_circle</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Submitted</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['submitted_assignments'] }}</p>
 </div>
 </div>

 <!-- Content Grid -->
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
 <!-- Enrolled Courses -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
 <div class="p-6 border-b border-surface-container-low flex justify-between items-center">
 <h3 class="font-bold text-xl text-on-surface font-headline flex items-center gap-2">
 <span class="material-symbols-outlined text-primary">auto_stories</span>
 My Courses
 </h3>
 <a href="{{ route('mahasiswa.courses.index') }}" class="flex items-center gap-1 text-primary text-xs font-bold hover:underline">
 View All
 <span class="material-symbols-outlined text-sm">arrow_forward</span>
 </a>
 </div>
 <div class="divide-y divide-surface-container-low">
 @forelse($enrolled_courses as $course)
 <a href="{{ route('mahasiswa.courses.show', $course) }}" class="block p-5 hover:bg-surface-bright transition-colors group">
 <div class="flex justify-between items-start mb-2">
 <h4 class="font-bold text-on-surface group-hover:text-primary transition-colors">{{ $course->nama_matkul }}</h4>
 <span class="text-[10px] font-black text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full uppercase">{{ $course->kode_matkul }}</span>
 </div>
 <div class="flex gap-4 text-xs text-on-surface-variant">
 <span class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">description</span>
 {{ $course->materials_count }} Materials
 </span>
 <span class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">assignment</span>
 {{ $course->assignments_count }} Assignments
 </span>
 </div>
 </a>
 @empty
 <div class="p-12 text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-4 block">auto_stories</span>
 <p class="text-on-surface-variant mb-2 font-medium">No courses enrolled yet.</p>
 <a href="{{ route('mahasiswa.courses.index') }}" class="text-primary font-bold text-sm hover:underline">Browse available courses</a>
 </div>
 @endforelse
 </div>
 </div>

 <!-- Upcoming Assignments -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
 <div class="p-6 border-b border-surface-container-low">
 <h3 class="font-bold text-xl text-on-surface font-headline flex items-center gap-2">
 <span class="material-symbols-outlined text-tertiary">assignment_late</span>
 Upcoming Assignments
 </h3>
 </div>
 <div class="divide-y divide-surface-container-low">
 @forelse($upcoming_assignments as $assignment)
 <div class="p-5 hover:bg-surface-bright transition-colors">
 <h4 class="font-bold text-on-surface mb-1">{{ $assignment->title }}</h4>
 <p class="text-xs text-on-surface-variant mb-3">{{ $assignment->course->nama_matkul }}</p>
 <div class="flex items-center gap-2 text-xs font-bold text-error">
 <span class="material-symbols-outlined text-sm">schedule</span>
 Due: {{ $assignment->deadline->format('d M Y, H:i') }}
 </div>
 </div>
 @empty
 <div class="p-12 text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-4 block">task_alt</span>
 <p class="text-on-surface-variant font-medium">No upcoming assignments.</p>
 </div>
 @endforelse
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
