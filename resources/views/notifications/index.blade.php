<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Semua Notifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 border-gray-100">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 pb-2 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Notifikasi</h3>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.markAllRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-md font-medium text-xs text-indigo-700 uppercase hover:bg-indigo-100 transition ease-in-out duration-150 dark:bg-indigo-900/50 dark:border-indigo-700/50 dark:text-indigo-300 dark:hover:bg-indigo-900">
                                Tandai Semua Telah Dibaca
                            </button>
                        </form>
                    @endif
                </div>
                
                <div class="flex flex-col">
                    @forelse($notifications as $notification)
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 transition {{ $notification->unread() ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1">
                                    @if(($notification->data['type'] ?? '') === 'academic_update')
                                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'submission')
                                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center text-green-600 dark:text-green-400">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                    @elseif(($notification->data['type'] ?? '') === 'announcement')
                                        <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-base font-bold text-gray-900 dark:text-gray-100 {{ $notification->unread() ? 'pl-0 border-transparent' : '' }}">
                                            @if($notification->unread())
                                                <span class="inline-block w-2.5 h-2.5 bg-blue-600 dark:bg-blue-400 rounded-full mr-1" title="Belum Dibaca"></span>
                                            @endif
                                            {{ $notification->data['title'] ?? 'Pembersitahuan Sistem' }}
                                        </h4>
                                        <span class="text-xs text-gray-500 whitespace-nowrap ml-4">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</span>
                                    </div>
                                    
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>
                                    
                                    <div class="mt-4 flex gap-4">
                                        @if(isset($notification->data['url']))
                                            <a href="{{ route('notifications.readAndRedirect', $notification->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                Lihat Rincian &rarr;
                                            </a>
                                        @endif
                                        
                                        @if($notification->unread() && !isset($notification->data['url']))
                                            <form action="{{ route('notifications.markRead', $notification->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                    Tandai Sudah Dibaca
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Belum ada notifikasi</h3>
                            <p class="mt-1 text-gray-500 dark:text-gray-400">Pemberitahuan akademik dan pengumuman akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                @if($notifications->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
