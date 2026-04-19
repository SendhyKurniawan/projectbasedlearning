<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <div class="flex items-center gap-4">
 <a href="{{ route('admin.dashboard') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Dashboard
 </a>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Manajemen Jurusan') }}
 </h2>
 </div>
 <a href="{{ route('admin.departments.create') }}" class="px-4 py-2 architectural-gradient text-on-primary text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 transition ease-in-out duration-150">
 Tambah Jurusan
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 @if(session('success'))
 <div class="mb-4 font-medium text-sm text-secondary">
 {{ session('success') }}
 </div>
 @endif

 <table class="min-w-full divide-y divide-surface-container-low">
 <thead>
 <tr>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Kode</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Nama Jurusan</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Jumlah Prodi</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Aksi</th>
 </tr>
 </thead>
 <tbody class="bg-surface-container-lowest divide-y divide-surface-container-low">
 @forelse ($departments as $dept)
 <tr>
 <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
 {{ $dept->code }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 {{ $dept->name }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 {{ $dept->study_programs_count }} Prodi
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <a href="{{ route('admin.departments.edit', $dept) }}" class="text-primary hover:text-primary-container mr-3">Edit</a>
 <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="inline-block">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-error/80" onclick="return confirm('Apakah Anda yakin ingin menghapus jurusan ini? Ini juga akan menghapus program studi dan kelas terkait.')">Hapus</button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-6 py-4 text-center text-on-surface-variant">Belum ada data jurusan.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
