<x-app-layout>
 <div class="space-y-6">
 <!-- Page Header -->
 <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
 <h2 class="text-2xl font-extrabold text-on-surface tracking-tight font-headline">Manajemen Mata Kuliah</h2>
 <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">
 <span class="material-symbols-outlined text-lg">add</span>
 Add New Course
 </a>
 </div>

 @if(session('success'))
 <div class="px-5 py-4 bg-emerald-50 border-l-4 border-secondary text-secondary rounded-xl text-sm font-medium flex items-center gap-3">
 <span class="material-symbols-outlined text-lg">check_circle</span>
 {{ session('success') }}
 </div>
 @endif

 <!-- Main Content Card -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
 <!-- Search -->
 <div class="p-6 border-b border-surface-container-low">
 <form method="GET" action="{{ route('admin.courses.index') }}" class="flex gap-3">
 <div class="relative flex-1">
 <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or lecturer..."
 class="w-full pl-10 pr-4 py-3 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 outline-none text-on-surface text-sm placeholder:text-outline/60">
 </div>
 <button type="submit" class="px-5 py-3 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-container transition-colors">Search</button>
 </form>
 </div>

 <!-- Course Table -->
 <div class="overflow-x-auto">
 <table class="w-full text-left">
 <thead>
 <tr class="bg-surface-container-low/50">
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Code</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Name</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Lecturer</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant text-center">Students</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-surface-container-low">
 @forelse ($courses as $course)
 <tr class="hover:bg-surface-bright transition-colors">
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="font-mono text-xs font-bold bg-surface-container px-2.5 py-1 rounded-full text-on-surface-variant">{{ $course->kode_matkul }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="text-sm font-bold text-on-surface">{{ $course->nama_matkul }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant">
 {{ $course->dosen ? $course->dosen->name : 'Unassigned' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-center">
 <span class="badge badge-info">{{ $course->students_count }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right">
 <div class="flex items-center justify-end gap-3">
 <a href="{{ route('admin.courses.edit', $course) }}" class="text-primary hover:text-primary-container text-xs font-bold transition-colors">Edit & Enroll</a>
 <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this course?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-red-700 text-xs font-bold transition-colors">Delete</button>
 </form>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 space-y-6 text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-3 block">auto_stories</span>
 <p class="text-on-surface-variant font-medium text-sm">No courses found.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="p-6 border-t border-surface-container-low">
 {{ $courses->withQueryString()->links() }}
 </div>
 </div>
 </div>
</x-app-layout>
