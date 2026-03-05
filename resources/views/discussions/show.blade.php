<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $discussion->title }}
                </h2>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $discussion->course->nama_matkul }}
                </div>
            </div>
            <a href="{{ route('discussions.index', ['course_id' => $discussion->course_id]) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="mr-3">
                            <!-- Avatar Placeholder -->
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                {{ substr($discussion->user->name, 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <div class="font-semibold">{{ $discussion->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $discussion->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="prose dark:prose-invert ">
                        {!! nl2br(e($discussion->content)) !!}
                    </div>

                    @if(auth()->id() === $discussion->user_id)
                        <div class="mt-4 flex space-x-2">
                             <a href="{{ route('discussions.edit', $discussion) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                             <form action="{{ route('discussions.destroy', $discussion) }}" method="POST" class="inline">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Hapus diskusi ini?')">Hapus</button>
                             </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Komentar</h3>

                    @foreach($discussion->comments as $comment)
                        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                            <div class="flex items-start">
                                <div class="mr-3 flex-shrink-0">
                                     <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-sm">
                                        {{ substr($comment->user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center">
                                        <div class="font-semibold text-sm">{{ $comment->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                        {!! nl2br(e($comment->content)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Comment Form -->
                    <div class="mt-6">
                        @livewire('discussion.show', ['discussion' => $discussion], key($discussion->id))
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
