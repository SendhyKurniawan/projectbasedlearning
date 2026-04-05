<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Pusat Pengumuman') }}
 </h2>
 @if(Auth::user()->role === 'admin' || Auth::user()->role === 'dosen')
 <a href="{{ route('announcements.create') }}" class="px-4 py-2 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 transition ease-in-out duration-150">
 + Buat Pengumuman
 </a>
 @endif
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">

 @if(session('success'))
 <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
 <p>{{ session('success') }}</p>
 </div>
 @endif

 <div class="space-y-6">
 @forelse ($announcements as $announcement)
 <div class="bg-surface-container-low/50 p-6 rounded-lg border border-surface-container-low shadow-sm relative">
 <h3 class="text-xl font-bold mb-2">
 <a href="{{ route('announcements.show', $announcement) }}" class="text-primary hover:text-primary-container:text-indigo-300">
 {{ $announcement->title }}
 </a>
 </h3>
 
 <div class="mb-4 text-xs font-semibold uppercase tracking-wide">
 <span class="text-on-surface-variant">Oleh: {{ $announcement->author->name }} ({{ ucfirst($announcement->author->role) }})</span>
 <span class="mx-2 text-outline">|</span>
 <span class="text-on-surface-variant">{{ $announcement->created_at->format('d M Y, H:i') }}</span>
 <span class="mx-2 text-outline">|</span>
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
 Target: {{ ucfirst($announcement->target_audience) }}
 </span>
 </div>

 <p class="text-on-surface-variant">
 {{ Str::limit(strip_tags($announcement->content), 200) }}
 </p>

 @if(Auth::id() === $announcement->user_id || Auth::user()->role === 'admin')
 <div class="absolute top-4 right-4 flex space-x-2">
 <a href="{{ route('announcements.edit', $announcement) }}" class="text-sm text-yellow-600 hover:text-yellow-900:text-yellow-300">Edit</a>
 <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-sm text-error hover:text-red-700:text-red-300">Hapus</button>
 </form>
 </div>
 @endif
 </div>
 @empty
 <div class="text-center space-y-6 text-on-surface-variant">
 <svg class="mx-auto h-12 w-12 text-outline" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
 </svg>
 <h3 class="mt-2 text-sm font-medium">Belum ada pengumuman</h3>
 </div>
 @endforelse
 </div>

 <div class="mt-6">
 {{ $announcements->links() }}
 </div>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
