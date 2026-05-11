@push('styles')
    @vite('resources/css/pages/admin/courses.css')
@endpush
<x-app-layout>
 <div class="space-y-6">
 <!-- Section Header -->
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Manajemen Mata Kuliah</h1>
 <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Kurikulum, plot dosen pengampu, dan periode tahun ajar mata kuliah.</p>
 </div>
 <div class="flex items-center gap-2">
 <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
 <span class="material-symbols-outlined text-base">add</span>
 Tambah Mata Kuliah
 </a>
 </div>
 </div>

 @if(session('success'))
 <div class="px-5 py-4 bg-secondary-container border-l-4 border-secondary text-secondary rounded-xl text-sm font-medium flex items-center gap-3">
 <span class="material-symbols-outlined text-lg">check_circle</span>
 {{ session('success') }}
 </div>
 @endif

 <!-- Main Content Card -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
 <!-- Search & Filter -->
 <div class="p-6 border-b border-surface-container-low">
 <form method="GET" action="{{ route('admin.courses.index') }}" class="flex flex-col sm:flex-row gap-3">
 <div class="relative flex-1">
 <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, atau dosen..."
 class="w-full pl-10 pr-4 py-3 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 outline-none text-on-surface text-sm placeholder:text-outline/60">
 </div>
 <select name="semester_id" class="py-3 px-4 bg-surface-container-highest rounded-xl border-none text-on-surface text-sm focus:ring-2 focus:ring-primary/20 outline-none">
 <option value="">Semua Semester</option>
 @foreach($availableSemesters as $sem)
 <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>
 {{ $sem->name }} ({{ $sem->academicYear->year_start ?? '' }}/{{ $sem->academicYear->year_end ?? '' }})
 </option>
 @endforeach
 </select>
 <select name="dosen_id" class="py-3 px-4 bg-surface-container-highest rounded-xl border-none text-on-surface text-sm focus:ring-2 focus:ring-primary/20 outline-none">
 <option value="">Semua Dosen</option>
 @foreach($availableDosens as $dosen)
 <option value="{{ $dosen->id }}" {{ request('dosen_id') == $dosen->id ? 'selected' : '' }}>
 {{ $dosen->name }}
 </option>
 @endforeach
 </select>
 <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-on-primary text-sm font-bold rounded-xl hover:bg-primary-hover transition-colors">
 <span class="material-symbols-outlined text-sm">filter_list</span>
 Filter
 </button>
 @if(request()->hasAny(['search','semester_id','dosen_id']))
 <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors">
 Reset
 </a>
 @endif
 </form>
 </div>

 <!-- Course Table -->
 <div class="overflow-x-auto">
 <table class="w-full text-left">
 <thead>
 <tr class="bg-surface-container-low/50">
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Kode</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Nama</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Dosen</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Semester</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant text-center">Mahasiswa</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant text-right">Aksi</th>
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
 {{ $course->dosen ? $course->dosen->name : 'Belum Ditentukan' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 @if($course->semester)
 <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-primary-container text-on-primary">
 {{ $course->semester->name }}
 </span>
 @else
 <span class="text-outline">-</span>
 @endif
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-center">
 <span class="badge badge-info">{{ $course->students_count }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right">
 <div class="flex items-center justify-end gap-3">
 <a href="{{ route('admin.courses.edit', $course) }}" class="text-primary hover:text-primary-container text-xs font-bold transition-colors">Edit & Enroll</a>
 <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-error/80 text-xs font-bold transition-colors">Hapus</button>
 </form>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-6 space-y-6 text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-3 block">auto_stories</span>
 <p class="text-on-surface-variant font-medium text-sm">Tidak ada mata kuliah yang ditemukan.</p>
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

