{{-- Lonceng notifikasi (dropdown Alpine): badge jumlah belum dibaca + 10 notifikasi terbaru,
     aksi tandai-dibaca per item / semua, dan tautan ke halaman semua notifikasi. --}}
<div x-data="{ open: false }" class="relative z-50">
 <button @click="open = !open" class="text-on-surface-variant hover:text-on-surface focus:outline-none relative transition-colors duration-200 p-2 rounded-xl hover:bg-surface-container-low">
 <span class="material-symbols-outlined text-2xl">notifications</span>
 {{-- Badge jumlah notifikasi belum dibaca (maks tampil "99+") --}}
 @if(auth()->user()->unreadNotifications->count() > 0)
 <span class="absolute top-0.5 right-0.5 inline-flex items-center justify-center min-w-[18px] px-1 py-0.5 text-[10px] font-bold leading-none text-white bg-error rounded-full">
 {{ auth()->user()->unreadNotifications->count() > 99 ? '99+' : auth()->user()->unreadNotifications->count() }}
 </span>
 @endif
 </button>

 <div x-show="open" @click.away="open = false" 
 x-transition:enter="transition ease-out duration-200"
 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
 x-transition:leave="transition ease-in duration-75"
 x-transition:leave-start="opacity-100 scale-100"
 x-transition:leave-end="opacity-0 scale-95"
 class="absolute right-0 mt-3 w-80 sm:w-96 bg-surface-container-lowest rounded-2xl shadow-xl overflow-hidden z-50 border border-outline-variant/20" style="display: none;">
 
 <div class="py-4 px-5 bg-surface-container-low flex justify-between items-center">
 <h3 class="text-sm font-bold text-on-surface font-headline">Notifikasi</h3>
 @if(auth()->user()->unreadNotifications->count() > 0)
 <form action="{{ route('notifications.markAllRead') }}" method="POST">
 @csrf
 <button type="submit" class="text-[11px] font-bold text-primary hover:text-primary-container transition-colors">Tandai semua dibaca</button>
 </form>
 @endif
 </div>
 
 {{-- Daftar 10 notifikasi terbaru; ikon disesuaikan dengan tipe notifikasi --}}
 <div class="max-h-[24rem] overflow-y-auto w-full">
 @forelse(auth()->user()->notifications()->take(10)->get() as $notification)
 <div class="p-4 border-b border-surface-container-low hover:bg-surface-bright transition-colors {{ $notification->unread() ? 'bg-primary/3' : '' }}">
 <div class="flex gap-3">
 <div class="flex-shrink-0 mt-0.5">
 @if(($notification->data['type'] ?? '') === 'academic_update')
 <div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-primary">
 <span class="material-symbols-outlined text-lg">auto_stories</span>
 </div>
 @elseif(($notification->data['type'] ?? '') === 'submission')
 <div class="w-9 h-9 rounded-full bg-secondary-container flex items-center justify-center text-secondary">
 <span class="material-symbols-outlined text-lg">check_circle</span>
 </div>
 @elseif(($notification->data['type'] ?? '') === 'announcement')
 <div class="w-9 h-9 rounded-full bg-warning-light flex items-center justify-center text-on-warning">
 <span class="material-symbols-outlined text-lg">campaign</span>
 </div>
 @else
 <div class="w-9 h-9 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant">
 <span class="material-symbols-outlined text-lg">info</span>
 </div>
 @endif
 </div>
 <div class="flex-1 min-w-0">
 <p class="text-sm text-on-surface font-bold truncate">
 {{ $notification->data['title'] ?? 'Pemberitahuan' }}
 </p>
 <p class="text-[13px] text-on-surface-variant mt-0.5 line-clamp-2 leading-snug">
 {{ $notification->data['message'] ?? '' }}
 </p>
 <div class="mt-2 flex justify-between items-center">
 <span class="text-[11px] text-outline">{{ $notification->created_at->diffForHumans() }}</span>
 @if(isset($notification->data['url']))
 <a href="{{ route('notifications.readAndRedirect', $notification->id) }}" class="text-[11px] font-bold text-primary hover:text-primary-container transition-colors">Lihat detail &rarr;</a>
 @elseif($notification->unread())
 <form action="{{ route('notifications.markRead', $notification->id) }}" method="POST">
 @csrf
 <button type="submit" class="text-[11px] font-bold text-primary hover:text-primary-container transition-colors">Tandai dibaca</button>
 </form>
 @endif
 </div>
 </div>
 </div>
 </div>
 @empty
 <div class="p-10 flex flex-col items-center justify-center text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-3">notifications_off</span>
 <p class="text-sm font-medium text-on-surface-variant">Belum ada notifikasi.</p>
 </div>
 @endforelse
 </div>
 
 <div class="py-3 px-4 bg-surface-container-low border-t border-surface-container text-center">
 <a href="{{ route('notifications.index') }}" class="text-sm font-bold text-primary hover:text-primary-container transition-colors">
 Tampilkan semua notifikasi
 </a>
 </div>
 </div>
</div>
