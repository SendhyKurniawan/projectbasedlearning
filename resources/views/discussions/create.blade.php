@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('discussions.index', ['topic' => request('topic')]) }}" class="hover:text-primary transition-colors">Diskusi</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Topik Baru</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Buat Diskusi</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Mulai topik baru untuk komunitas.</p>
 </div>
 <a href="{{ route('discussions.index', ['topic' => request('topic')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="max-w-3xl">
 <div class="bg-surface-container-lowest overflow-hidden border border-outline-variant/10 rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="POST" action="{{ route('discussions.store') }}">
 @csrf

 <!-- Topic -->
 <div>
 <x-input-label for="topic" :value="__('Topik Diskusi')" />
 <x-text-input id="topic" class="block mt-1 w-full" type="text" name="topic" :value="old('topic', $topic)" required autofocus placeholder="Contoh: Pemrograman Web, Pertanyaan Umum..." />
 <x-input-error :messages="$errors->get('topic')" class="mt-2" />
 </div>

 <!-- Title -->
 <div class="mt-4">
 <x-input-label for="title" :value="__('Judul Pertanyaan/Diskusi')" />
 <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required />
 <x-input-error :messages="$errors->get('title')" class="mt-2" />
 </div>

 <!-- Content -->
 <div class="mt-4">
 <x-input-label for="content" :value="__('Isi Diskusi')" />
 <textarea id="content" name="content" rows="6" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>{{ old('content') }}</textarea>
 <x-input-error :messages="$errors->get('content')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <a href="{{ url()->previous() }}" class="text-on-surface-variant hover:text-on-surface mr-4">
 {{ __('Batal') }}
 </a>
 <x-primary-button>
 {{ __('Kirim') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
