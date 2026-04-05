<x-app-layout>
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Forum Diskusi') }} {{ $topic ? '- ' . $topic : '' }}
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <div class="flex items-center justify-between mb-6">
 <form method="GET" action="{{ route('discussions.index') }}" class="flex items-center gap-2">
 <x-text-input type="text" name="topic" value="{{ request('topic') }}" placeholder="Cari Topik Diskusi..." class="block w-64 text-sm" />
 <x-primary-button type="submit">Cari</x-primary-button>
 @if(request('topic'))
 <a href="{{ route('discussions.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface-variant:text-outline">Reset</a>
 @endif
 </form>
 <a href="{{ route('discussions.create', ['topic' => request('topic')]) }}" class="px-4 py-2 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 transition ease-in-out duration-150">
 Buat Diskusi Baru
 </a>
 </div>

 @forelse ($discussions as $discussion)
 <div class="mb-4 p-4 border rounded-lg border-surface-container-low">
 <h3 class="text-lg font-semibold">
 <a href="{{ route('discussions.show', $discussion) }}" class="text-primary hover:text-primary-container">
 {{ $discussion->title }}
 </a>
 </h3>
 <div class="text-sm text-on-surface-variant mt-1">
 Oleh {{ $discussion->user->name }} &bull; Topik: <span class="font-medium">{{ $discussion->topic }}</span> &bull; {{ $discussion->created_at->diffForHumans() }} &bull; {{ $discussion->comments_count }} Komentar
 </div>
 <p class="mt-2 text-on-surface-variant">
 {{ Str::limit($discussion->content, 150) }}
 </p>
 </div>
 @empty
 <p class="text-on-surface-variant text-center">Belum ada diskusi.</p>
 @endforelse

 <div class="mt-4">
 {{ $discussions->links() }}
 </div>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
