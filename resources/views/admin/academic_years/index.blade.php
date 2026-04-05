<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <div class="flex items-center gap-4">
 <a href="{{ route('admin.dashboard') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Dashboard
 </a>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Manajemen Tahun Akademik') }}
 </h2>
 </div>
 <a href="{{ route('admin.academic-years.create') }}" class="px-4 py-2 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 transition ease-in-out duration-150">
 Tambah Tahun Akademik
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
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
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
 Aktif
 </span>
 @else
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
 Tidak Aktif
 </span>
 @endif
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <a href="{{ route('admin.academic-years.edit', $year) }}" class="text-primary hover:text-primary-container mr-3">Edit</a>
 <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="inline-block">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus tahun akademik ini?')">Hapus</button>
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
