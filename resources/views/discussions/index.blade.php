<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Forum Diskusi') }} {{ $course ? '- ' . $course->nama_matkul : '' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('discussions.create', ['course_id' => $course?->id]) }}" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
                                Oleh {{ $discussion->user->name }} &bull; {{ $discussion->created_at->diffForHumans() }} &bull; {{ $discussion->comments->count() }} Komentar
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
