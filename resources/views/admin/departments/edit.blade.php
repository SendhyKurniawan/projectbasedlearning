<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.hierarchy.departments.index') }}" class="hover:text-primary transition-colors">Struktur</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Edit Jurusan</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Edit Jurusan</h1>
 <p class="mt-1 text-sm text-on-surface-variant">Perbarui informasi jurusan.</p>
 </div>
 <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form action="{{ route('admin.departments.update', $department) }}" method="POST">
 @csrf
 @method('PUT')
 <div class="mb-4">
 <x-input-label for="code" :value="__('Kode Jurusan')" />
 <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $department->code)" required autofocus />
 <x-input-error :messages="$errors->get('code')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="name" :value="__('Nama Jurusan')" />
 <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $department->name)" required />
 <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button class="ml-4">
 {{ __('Perbarui') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
