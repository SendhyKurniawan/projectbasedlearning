<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Program Studi</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Manajemen Program Studi</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Daftar prodi pada seluruh jurusan.</p>
 </div>
 <a href="{{ route('admin.study-programs.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
 <span class="material-symbols-outlined text-base">add</span> Tambah Prodi
 </a>
 </div>
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
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Jenjang</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Nama Prodi</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Jurusan</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Aksi</th>
 </tr>
 </thead>
 <tbody class="bg-surface-container-lowest divide-y divide-surface-container-low">
 @forelse ($studyPrograms as $prodi)
 <tr>
 <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
 {{ $prodi->code }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-container text-on-primary">
 {{ $prodi->level }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 {{ $prodi->name }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm">
 {{ $prodi->department->name }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <a href="{{ route('admin.study-programs.edit', $prodi) }}" class="text-primary hover:text-primary-container mr-3">Edit</a>
 <form action="{{ route('admin.study-programs.destroy', $prodi) }}" method="POST" class="inline-block">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-error/80" onclick="return confirm('Apakah Anda yakin ingin menghapus prodi ini? Ini juga akan menghapus kelas terkait.')">Hapus</button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-4 text-center text-on-surface-variant">Belum ada data program studi.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
