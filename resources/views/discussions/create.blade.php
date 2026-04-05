<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Buat Diskusi Baru') }}
 </h2>
 <a href="{{ route('discussions.index', ['topic' => request('topic')]) }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
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
 <textarea id="content" name="content" rows="6" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-primary/20:ring-indigo-600 rounded-md shadow-sm" required>{{ old('content') }}</textarea>
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
