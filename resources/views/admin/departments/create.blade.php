<x-app-layout>
 <x-slot name="header">
 <div class="flex items-center gap-4">
 <a href="{{ route('admin.departments.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Tambah Jurusan') }}
 </h2>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form action="{{ route('admin.departments.store') }}" method="POST">
 @csrf
 <div class="mb-4">
 <x-input-label for="code" :value="__('Kode Jurusan')" />
 <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required autofocus />
 <x-input-error :messages="$errors->get('code')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="name" :value="__('Nama Jurusan')" />
 <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
 <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button class="ml-4">
 {{ __('Simpan') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
