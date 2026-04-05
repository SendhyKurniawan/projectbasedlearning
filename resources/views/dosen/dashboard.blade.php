<x-app-layout>
 <div class="space-y-8">
 <!-- Header -->
 <div>
 <h2 class="text-3xl font-extrabold text-on-surface tracking-tight font-headline">Lecturer Dashboard</h2>
 <p class="text-on-surface-variant mt-2 font-medium">Overview of your courses and teaching activity.</p>
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
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">My Courses</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_courses'] }}</p>
 </div>

 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-secondary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-emerald-50 text-secondary rounded-lg">
 <span class="material-symbols-outlined">groups</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Total Students</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_students'] }}</p>
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
 </div>

 <!-- My Courses Section -->
 <div>
 <div class="flex justify-between items-center mb-6">
 <h3 class="text-xl font-bold font-headline text-on-surface">My Courses</h3>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 @forelse($courses as $course)
 <div class="group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all p-6 border-l-4 border-primary">
 <div class="mb-4">
 <h4 class="font-bold font-headline text-lg group-hover:text-primary transition-colors text-on-surface">{{ $course->nama_matkul }}</h4>
 <span class="text-[10px] font-black text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full uppercase mt-1 inline-block">{{ $course->kode_matkul }}</span>
 </div>
 <div class="flex flex-wrap gap-4 text-xs text-on-surface-variant mb-4">
 <span class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">description</span>
 {{ $course->materials_count }} Materials
 </span>
 <span class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">assignment</span>
 {{ $course->assignments_count }} Assignments
 </span>
 <span class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">group</span>
 {{ $course->students_count }} Students
 </span>
 </div>
 <div class="flex gap-3">
 <a href="{{ route('dosen.materials.index', $course) }}" 
 class="flex items-center gap-1.5 px-4 py-2 bg-primary/10 text-primary text-xs font-bold rounded-lg hover:bg-primary/20 transition-colors">
 <span class="material-symbols-outlined text-sm">description</span>
 Materials
 </a>
 <a href="{{ route('dosen.assignments.index', $course) }}" 
 class="flex items-center gap-1.5 px-4 py-2 bg-secondary/10 text-secondary text-xs font-bold rounded-lg hover:bg-secondary/20 transition-colors">
 <span class="material-symbols-outlined text-sm">assignment</span>
 Assignments
 </a>
 </div>
 </div>
 @empty
 <div class="col-span-2 bg-surface-container-low rounded-2xl p-12 text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-4 block">auto_stories</span>
 <p class="text-on-surface-variant font-medium">No courses assigned yet.</p>
 </div>
 @endforelse
 </div>
 </div>
 </div>
</x-app-layout>
