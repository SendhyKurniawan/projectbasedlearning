@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Data Akademik - Daftar Jurusan
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <h3 class="text-lg font-medium mb-4">Pilih Jurusan</h3>
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @forelse($departments as $department)
 <a href="{{ route('admin.hierarchy.departments.show', $department) }}" class="block p-6 bg-primary-container border border-primary/10 rounded-lg hover:shadow-md hover:bg-primary-container/80 transition duration-150">
 <h4 class="text-xl font-bold text-primary mb-2">{{ $department->name }}</h4>
 <p class="text-sm text-on-surface-variant">
 {{ $department->study_programs_count }} Program Studi
 </p>
 </a>
 @empty
 <p class="text-on-surface-variant col-span-full">Belum ada jurusan yang terdaftar. <a href="{{ route('admin.departments.create') }}" class="text-primary underline">Tambah Jurusan</a>.</p>
 @endforelse
 </div>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
