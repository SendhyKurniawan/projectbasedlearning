<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Tahun Akademik</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Manajemen Tahun Akademik</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Atur tahun ajaran dan aktifkan periode berjalan.</p>
 </div>
 <a href="{{ route('admin.academic-years.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
 <span class="material-symbols-outlined text-base">add</span> Tambah Tahun Akademik
 </a>
 </div>
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <table class="min-w-full divide-y divide-surface-container-low">
 <thead>
 <tr>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Tahun</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Status</th>
 <th class="px-6 py-3 bg-surface-container-low/50 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider">Aksi</th>
 </tr>
 </thead>
 <tbody class="bg-surface-container-lowest divide-y divide-surface-container-low">
 @foreach ($academicYears as $year)
 <tr>
 <td class="px-6 py-4 whitespace-nowrap">
 {{ $year->year_start }} / {{ $year->year_end }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 @if($year->is_active)
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-secondary-container text-secondary">
 Aktif
 </span>
 @else
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-error-container text-error">
 Tidak Aktif
 </span>
 @endif
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <a href="{{ route('admin.academic-years.edit', $year) }}" class="text-primary hover:text-primary-container mr-3">Edit</a>
 <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="inline-block">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-error/80" onclick="return confirm('Apakah Anda yakin ingin menghapus tahun akademik ini?')">Hapus</button>
 </form>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
