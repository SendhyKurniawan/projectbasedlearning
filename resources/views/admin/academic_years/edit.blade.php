{{-- Form edit tahun ajaran (admin). --}}
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('admin.academic-years.index') }}" class="hover:text-primary transition-colors">Tahun Akademik</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Edit</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Edit Tahun Akademik</h1>
 <p class="mt-1 text-sm text-on-surface-variant">{{ $academicYear->year_start }}/{{ $academicYear->year_end }}</p>
 </div>
 <a href="{{ route('admin.academic-years.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="POST" action="{{ route('admin.academic-years.update', $academicYear) }}">
 @csrf
 @method('PUT')

 <div class="grid grid-cols-2 gap-4">
 <!-- Year Start -->
 <div>
 <x-input-label for="year_start" :value="__('Tahun Mulai')" />
 <x-text-input id="year_start" class="block mt-1 w-full" type="text" name="year_start" :value="old('year_start', $academicYear->year_start)" required autofocus />
 <x-input-error :messages="$errors->get('year_start')" class="mt-2" />
 </div>

 <!-- Year End -->
 <div>
 <x-input-label for="year_end" :value="__('Tahun Selesai')" />
 <x-text-input id="year_end" class="block mt-1 w-full" type="text" name="year_end" :value="old('year_end', $academicYear->year_end)" required />
 <x-input-error :messages="$errors->get('year_end')" class="mt-2" />
 </div>
 </div>

 <!-- Is Active -->
 <div class="block mt-4">
 <label for="is_active" class="inline-flex items-center">
 <input id="is_active" type="checkbox" class="rounded border-outline-variant/30 text-primary shadow-sm focus:ring-primary/20:ring-indigo-600:ring-offset-gray-800" name="is_active" {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}>
 <span class="ms-2 text-sm text-on-surface-variant">{{ __('Set as Active Academic Year') }}</span>
 </label>
 </div>

 <div class="flex items-center justify-end mt-4">
 <a href="{{ route('admin.academic-years.index') }}" class="text-on-surface-variant hover:text-on-surface mr-4">
 {{ __('Batal') }}
 </a>
 <x-primary-button>
 {{ __('Update') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
