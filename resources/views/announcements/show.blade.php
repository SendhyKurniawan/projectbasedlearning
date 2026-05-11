<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('announcements.index') }}" class="hover:text-primary transition-colors">Pengumuman</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary truncate max-w-[200px]">{{ $announcement->title }}</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">{{ $announcement->title }}</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Detail pengumuman.</p>
 </div>
 <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
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
 <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-container text-on-primary">
 Target Audiens: {{ ucfirst($announcement->target_audience) }}
 </div>
 @endif
 </div>

 <div class="prose prose-blue max-w-none text-on-surface whitespace-pre-line">
 {{ $announcement->content }}
 </div>

 @if($announcement->hasAttachment())
 <div class="mt-6 pt-6 border-t border-surface-container-low">
 <h4 class="text-sm font-semibold text-on-surface-variant uppercase tracking-wide mb-3">Lampiran</h4>
 @if($announcement->attachmentIsImage())
 <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" rel="noopener" class="inline-block">
 <img src="{{ Storage::url($announcement->attachment_path) }}" alt="{{ $announcement->attachment_name }}" class="max-w-full max-h-[70vh] rounded-lg border border-outline-variant/30 shadow-sm" />
 </a>
 <p class="mt-2 text-xs text-on-surface-variant">{{ $announcement->attachment_name }}</p>
 @elseif($announcement->attachmentIsPdf())
 <div class="rounded-lg overflow-hidden border border-outline-variant/30 bg-surface-container-low/30">
 <embed src="{{ Storage::url($announcement->attachment_path) }}" type="application/pdf" class="w-full h-[70vh]" />
 </div>
 <div class="mt-3 flex items-center gap-3">
 <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-primary/10 text-primary hover:bg-primary/20">
 Buka di tab baru
 </a>
 <a href="{{ Storage::url($announcement->attachment_path) }}" download="{{ $announcement->attachment_name }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-surface-container-low text-on-surface hover:bg-surface-container">
 Unduh
 </a>
 <span class="text-xs text-on-surface-variant truncate">{{ $announcement->attachment_name }}</span>
 </div>
 @else
 <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-primary/10 text-primary hover:bg-primary/20">
 Unduh {{ $announcement->attachment_name }}
 </a>
 @endif
 </div>
 @endif

 @if(Auth::id() === $announcement->user_id || Auth::user()->role === 'admin')
 <div class="mt-8 pt-6 border-t border-surface-container-low flex justify-end gap-3">
 <a href="{{ route('announcements.edit', $announcement) }}" class="px-4 py-2 bg-warning hover:bg-warning/90 border border-transparent rounded-md font-semibold text-xs text-on-surface uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-warning/30 focus:ring-offset-2 transition ease-in-out duration-150">
 Edit Pengumuman
 </a>
 </div>
 @endif

 </div>
 </div>
 </div>
 </div>
</x-app-layout>
