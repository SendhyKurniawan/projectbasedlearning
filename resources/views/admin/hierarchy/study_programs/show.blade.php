@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
 <x-slot name="header">
 <div class="flex items-center gap-2 text-sm sm:text-base">
 <a href="{{ route('admin.hierarchy.departments.index') }}" class="text-primary hover:text-primary-hover">
 Data Akademik
 </a>
 <span class="text-on-surface-variant">/</span>
 <a href="{{ route('admin.hierarchy.departments.show', $studyProgram->department_id) }}" class="text-primary hover:text-primary-hover">
 {{ $studyProgram->department->name }}
 </a>
 <span class="text-on-surface-variant">/</span>
 <h2 class="font-semibold text-on-surface leading-tight">
 {{ $studyProgram->name }}
 </h2>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <h3 class="text-lg font-medium mb-4">Pilih Semester Aktif/Tersedia</h3>
 
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
 @forelse($semesters as $semester)
 <a href="{{ route('admin.hierarchy.study-programs.semesters.show', [$studyProgram, $semester]) }}" 
 class="block p-4 border rounded-lg hover:shadow-md transition duration-150 {{ $semester->is_active ? 'bg-secondary-container/30 border-secondary/30 hover:bg-secondary-container/60' : 'bg-surface-container-low/50 border-surface-container-low hover:bg-surface-container-low' }}">
 <h4 class="text-lg font-bold mb-1 {{ $semester->is_active ? 'text-secondary' : 'text-on-surface' }}">
 {{ $semester->name }}
 </h4>
 <p class="text-sm font-medium text-on-surface-variant mb-2">
 TA: {{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }}
 </p>
 @if($semester->is_active)
 <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-secondary-container text-secondary">
 <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> Aktif
 </span>
 @endif
 </a>
 @empty
 <p class="text-on-surface-variant col-span-full">Belum ada data semester.</p>
 @endforelse
 </div>

 </div>
 </div>
 </div>
 </div>
</x-app-layout>
