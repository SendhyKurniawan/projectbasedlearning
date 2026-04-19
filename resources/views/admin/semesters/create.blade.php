<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Tambah Semester') }}
 </h2>
 <a href="{{ route('admin.semesters.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="POST" action="{{ route('admin.semesters.store') }}">
 @csrf

 <!-- Academic Year -->
 <div>
 <x-input-label for="academic_year_id" :value="__('Tahun Akademik')" />
 <select id="academic_year_id" name="academic_year_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm">
 @foreach($academicYears as $year)
 <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
 {{ $year->year_start }}/{{ $year->year_end }}
 </option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
 </div>

 <!-- Semester Name -->
 <div class="mt-4">
 <x-input-label for="name" :value="__('Semester')" />
 <select id="name" name="name" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm">
 <option value="Ganjil" {{ old('name') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
 <option value="Genap" {{ old('name') == 'Genap' ? 'selected' : '' }}>Genap</option>
 </select>
 <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <div class="grid grid-cols-2 gap-4 mt-4">
 <!-- Start Date -->
 <div>
 <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
 <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
 <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
 </div>

 <!-- End Date -->
 <div>
 <x-input-label for="end_date" :value="__('Tanggal Selesai')" />
 <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
 <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
 </div>
 </div>

 <!-- Is Active -->
 <div class="block mt-4">
 <label for="is_active" class="inline-flex items-center">
 <input id="is_active" type="checkbox" class="rounded border-outline-variant/30 text-primary shadow-sm focus:ring-primary/20" name="is_active" {{ old('is_active') ? 'checked' : '' }}>
 <span class="ms-2 text-sm text-on-surface-variant">{{ __('Set as Active Semester') }}</span>
 </label>
 </div>

 <div class="flex items-center justify-end mt-4">
 <a href="{{ route('admin.semesters.index') }}" class="text-on-surface-variant hover:text-on-surface mr-4">
 {{ __('Batal') }}
 </a>
 <x-primary-button>
 {{ __('Simpan') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
