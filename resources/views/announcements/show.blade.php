<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ $announcement->title }}
 </h2>
 <a href="{{ route('announcements.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-4xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-8">
 
 <div class="mb-6 pb-6 border-b border-surface-container-low">
 <div class="flex items-center justify-between text-sm text-on-surface-variant mb-4">
 <div>
 <span class="font-semibold text-on-surface-variant">Oleh:</span> 
 {{ $announcement->author->name }} ({{ ucfirst($announcement->author->role) }})
 </div>
 <div>
 <span class="font-semibold text-on-surface-variant">Dipublikasikan:</span> 
 {{ $announcement->created_at->format('d M Y, H:i') }}
 </div>
 </div>

 @if(Auth::user()->role === 'admin' || Auth::id() === $announcement->user_id)
 <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
 Target Audiens: {{ ucfirst($announcement->target_audience) }}
 </div>
 @endif
 </div>

 <div class="prose prose-blue max-w-none text-on-surface whitespace-pre-line">
 {{ $announcement->content }}
 </div>

 @if(Auth::id() === $announcement->user_id || Auth::user()->role === 'admin')
 <div class="mt-8 pt-6 border-t border-surface-container-low flex justify-end gap-3">
 <a href="{{ route('announcements.edit', $announcement) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
 Edit Pengumuman
 </a>
 </div>
 @endif

 </div>
 </div>
 </div>
 </div>
</x-app-layout>
