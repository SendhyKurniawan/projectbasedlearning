<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <div>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ $discussion->title }}
 </h2>
 <div class="text-sm text-on-surface-variant mt-1">
 Topik: <span class="font-medium text-primary">{{ $discussion->topic ?? 'Umum' }}</span>
 </div>
 </div>
 <a href="{{ route('discussions.index', ['topic' => $discussion->topic]) }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl mb-6">
 <div class="p-6 text-on-surface">
 <div class="flex items-center mb-4">
 <div class="mr-3">
 <!-- Avatar Placeholder -->
 <div class="h-10 w-10 rounded-full bg-surface-container flex items-center justify-center">
 {{ substr($discussion->user->name, 0, 1) }}
 </div>
 </div>
 <div>
 <div class="font-semibold">{{ $discussion->user->name }}</div>
 <div class="text-xs text-on-surface-variant">{{ $discussion->created_at->format('d M Y H:i') }}</div>
 </div>
 </div>

 <div class="prose ">
 {!! nl2br(e($discussion->content)) !!}
 </div>

 @if(auth()->id() === $discussion->user_id)
 <div class="mt-4 flex space-x-2">
 <a href="{{ route('discussions.edit', $discussion) }}" class="text-primary hover:text-primary-container text-sm">Edit</a>
 <form action="{{ route('discussions.destroy', $discussion) }}" method="POST" class="inline">
 @csrf
 @method('DELETE')
 <button type="submit" class="text-error hover:text-error/80 text-sm" onclick="return confirm('Hapus diskusi ini?')">Hapus</button>
 </form>
 </div>
 @endif
 </div>
 </div>

 <!-- Comments Section -->
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <h3 class="text-lg font-semibold mb-4">Komentar</h3>

 @foreach($discussion->comments as $comment)
 <div class="mb-4 pb-4 border-b border-surface-container-low last:border-0 last:pb-0">
 <div class="flex items-start">
 <div class="mr-3 flex-shrink-0">
 <div class="h-8 w-8 rounded-full bg-surface-container flex items-center justify-center text-sm">
 {{ substr($comment->user->name, 0, 1) }}
 </div>
 </div>
 <div class="flex-1">
 <div class="flex justify-between items-center">
 <div class="font-semibold text-sm">{{ $comment->user->name }}</div>
 <div class="text-xs text-on-surface-variant">{{ $comment->created_at->diffForHumans() }}</div>
 </div>
 <div class="mt-1 text-sm text-on-surface-variant">
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
