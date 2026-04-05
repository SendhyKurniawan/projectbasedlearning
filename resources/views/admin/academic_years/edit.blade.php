<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Edit Tahun Akademik') }}
 </h2>
 <a href="{{ route('admin.academic-years.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
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
