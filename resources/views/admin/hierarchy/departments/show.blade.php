@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
 <x-slot name="header">
 <div class="flex items-center gap-2">
 <a href="{{ route('admin.hierarchy.departments.index') }}" class="text-primary hover:text-primary-hover">
 Data Akademik
 </a>
 <span class="text-on-surface-variant">/</span>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ $department->name }}
 </h2>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <h3 class="text-lg font-medium mb-4">Pilih Program Studi</h3>
 
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @forelse($studyPrograms as $prodi)
 <a href="{{ route('admin.hierarchy.study-programs.show', $prodi) }}" class="block p-6 bg-primary-container/50 border border-primary/10 rounded-lg hover:shadow-md hover:bg-primary-container/80 transition duration-150">
 <div class="flex justify-between items-start mb-2">
 <h4 class="text-xl font-bold text-on-surface">{{ $prodi->name }}</h4>
 <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-container text-on-primary">
 {{ $prodi->level }}
 </span>
 </div>
 <p class="text-sm font-mono text-on-surface-variant mb-4">{{ $prodi->code }}</p>
 <p class="text-sm text-on-surface-variant">
 {{ $prodi->student_classes_count }} Kelas terdaftar
 </p>
 </a>
 @empty
 <p class="text-on-surface-variant col-span-full">Belum ada program studi di jurusan ini. <a href="{{ route('admin.study-programs.create') }}" class="text-primary underline">Tambah Prodi</a>.</p>
 @endforelse
 </div>

 </div>
 </div>
 </div>
 </div>
</x-app-layout>
