<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.study-programs.index') }}" class="hover:text-primary transition-colors">Program Studi</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Tambah</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Tambah Program Studi</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Buat prodi baru dan tetapkan jurusan induk.</p>
 </div>
 <a href="{{ route('admin.study-programs.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
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
