<x-app-layout>
 <x-slot name="header">
 <div class="flex items-center gap-4">
 <a href="{{ route('admin.study-programs.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Tambah Program Studi') }}
 </h2>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form action="{{ route('admin.study-programs.store') }}" method="POST">
 @csrf
 <div class="mb-4">
 <x-input-label for="department_id" :value="__('Jurusan')" />
 <select id="department_id" name="department_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>
 <option value="">Pilih Jurusan</option>
 @foreach($departments as $dept)
 <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="level" :value="__('Jenjang')" />
 <select id="level" name="level" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" required>
 <option value="D3" {{ old('level') == 'D3' ? 'selected' : '' }}>D3</option>
 <option value="D4" {{ old('level') == 'D4' ? 'selected' : '' }}>D4</option>
 <option value="S1" {{ old('level') == 'S1' ? 'selected' : '' }}>S1</option>
 <option value="S2" {{ old('level') == 'S2' ? 'selected' : '' }}>S2</option>
 <option value="S3" {{ old('level') == 'S3' ? 'selected' : '' }}>S3</option>
 </select>
 <x-input-error :messages="$errors->get('level')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="code" :value="__('Kode Prodi')" />
 <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required />
 <x-input-error :messages="$errors->get('code')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="name" :value="__('Nama Program Studi')" />
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
