@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Forum Diskusi') }}
 </h2>
 <a href="{{ route('discussions.create') }}" class="px-4 py-2 architectural-gradient text-on-primary text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 transition ease-in-out duration-150">
 + Buat Diskusi Baru
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="GET" action="{{ route('discussions.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
     <input
         type="text"
         name="search"
         value="{{ request('search') }}"
         placeholder="Cari judul atau isi diskusi..."
         class="flex-1 px-4 py-2 rounded-xl border border-outline/30 bg-surface-container text-on-surface placeholder-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm"
     >
     <button type="submit" class="px-5 py-2 architectural-gradient text-on-primary text-sm font-bold rounded-xl shadow shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition ease-in-out duration-150">
         Cari
     </button>
     @if(request('search'))
     <a href="{{ route('discussions.index') }}" class="px-4 py-2 rounded-xl border border-outline/30 text-on-surface-variant text-sm font-medium hover:bg-surface-container transition ease-in-out duration-150 text-center">
         Reset
     </a>
     @endif
 </form>

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
