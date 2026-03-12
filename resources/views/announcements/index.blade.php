<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pusat Pengumuman') }}
            </h2>
            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'dosen')
                <a href="{{ route('announcements.create') }}" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + Buat Pengumuman
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="space-y-6">
                    @forelse ($announcements as $announcement)
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm relative">
                            <h3 class="text-xl font-bold mb-2">
                                <a href="{{ route('announcements.show', $announcement) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    {{ $announcement->title }}
                                </a>
                            </h3>
                            
                            <div class="mb-4 text-xs font-semibold uppercase tracking-wide">
                                <span class="text-gray-500 dark:text-gray-400">Oleh: {{ $announcement->author->name }} ({{ ucfirst($announcement->author->role) }})</span>
                                <span class="mx-2 text-gray-300 dark:text-gray-500">|</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $announcement->created_at->format('d M Y, H:i') }}</span>
                                <span class="mx-2 text-gray-300 dark:text-gray-500">|</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Target: {{ ucfirst($announcement->target_audience) }}
                                </span>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">
                                {{ Str::limit(strip_tags($announcement->content), 200) }}
                            </p>

                            @if(Auth::id() === $announcement->user_id || Auth::user()->role === 'admin')
                            <div class="absolute top-4 right-4 flex space-x-2">
                                <a href="{{ route('announcements.edit', $announcement) }}" class="text-sm text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">Edit</a>
                                <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                                </form>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
