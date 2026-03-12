<div x-data="{ open: false }" class="relative z-50">
    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none relative transition-colors duration-200">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                {{ auth()->user()->unreadNotifications->count() > 99 ? '99+' : auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden z-50 border border-gray-200 dark:border-gray-700" style="display: none;">
        
        <div class="py-3 px-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notifikasi</h3>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[11px] font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">Tandai semua dibaca</button>
                </form>
            @endif
        </div>
        
        <div class="max-h-[24rem] overflow-y-auto w-full">
            @forelse(auth()->user()->notifications()->take(10)->get() as $notification)
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $notification->unread() ? 'bg-blue-50/50 dark:bg-indigo-900/10' : '' }}">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            @if(($notification->data['type'] ?? '') === 'academic_update')
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                            @elseif(($notification->data['type'] ?? '') === 'submission')
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center text-green-600 dark:text-green-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            @elseif(($notification->data['type'] ?? '') === 'announcement')
                                <div class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium truncate">
                                {{ $notification->data['title'] ?? 'Pemberitahuan' }}
                            </p>
                            <p class="text-[13px] text-gray-600 dark:text-gray-400 mt-0.5 line-clamp-2 leading-snug">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-[11px] text-gray-500 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                @if(isset($notification->data['url']))
                                    <a href="{{ route('notifications.readAndRedirect', $notification->id) }}" class="text-[11px] font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">Lihat detail &rarr;</a>
                                @elseif($notification->unread())
                                    <form action="{{ route('notifications.markRead', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">Tandai dibaca</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada notifikasi.</p>
                </div>
            @endforelse
        </div>
        
        <div class="py-2.5 px-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 text-center">
            <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                Tampilkan semua notifikasi
            </a>
        </div>
    </div>
</div>
