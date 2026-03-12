<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $announcement->title }}
            </h2>
            <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Oleh:</span> 
                                {{ $announcement->author->name }} ({{ ucfirst($announcement->author->role) }})
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Dipublikasikan:</span> 
                                {{ $announcement->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>

                        @if(Auth::user()->role === 'admin' || Auth::id() === $announcement->user_id)
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Target Audiens: {{ ucfirst($announcement->target_audience) }}
                            </div>
                        @endif
                    </div>

                    <div class="prose prose-blue max-w-none text-gray-800 dark:text-gray-200 whitespace-pre-line">
                        {{ $announcement->content }}
                    </div>

                    @if(Auth::id() === $announcement->user_id || Auth::user()->role === 'admin')
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
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
