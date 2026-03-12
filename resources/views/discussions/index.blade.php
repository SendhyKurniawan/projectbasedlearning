<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Forum Diskusi') }} {{ $topic ? '- ' . $topic : '' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <form method="GET" action="{{ route('discussions.index') }}" class="flex items-center gap-2">
                                <x-text-input type="text" name="topic" value="{{ request('topic') }}" placeholder="Cari Topik Diskusi..." class="block w-64 text-sm" />
                                <x-primary-button type="submit">Cari</x-primary-button>
                                @if(request('topic'))
                                    <a href="{{ route('discussions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Reset</a>
                                @endif
                            </form>
                            <a href="{{ route('discussions.create', ['topic' => request('topic')]) }}" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Buat Diskusi Baru
                            </a>
                        </div>

                    @forelse ($discussions as $discussion)
                        <div class="mb-4 p-4 border rounded-lg border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold">
                                <a href="{{ route('discussions.show', $discussion) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $discussion->title }}
                                </a>
                            </h3>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Oleh {{ $discussion->user->name }} &bull; Topik: <span class="font-medium">{{ $discussion->topic }}</span> &bull; {{ $discussion->created_at->diffForHumans() }} &bull; {{ $discussion->comments_count }} Komentar
                            </div>
                            <p class="mt-2 text-gray-700 dark:text-gray-300">
                                {{ Str::limit($discussion->content, 150) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center">Belum ada diskusi.</p>
                    @endforelse

                    <div class="mt-4">
                        {{ $discussions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
