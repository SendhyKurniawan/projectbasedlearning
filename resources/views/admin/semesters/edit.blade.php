{{-- Form edit semester (admin). --}}
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('admin.semesters.index') }}" class="hover:text-primary transition-colors">Semester</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Edit</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Edit Semester</h1>
 <p class="mt-1 text-sm text-on-surface-variant">{{ $semester->name }} · TA {{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }}</p>
 </div>
 <a href="{{ route('admin.semesters.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="POST" action="{{ route('admin.semesters.update', $semester) }}">
 @csrf
 @method('PUT')

 <!-- Academic Year -->
 <div>
 <x-input-label for="academic_year_id" :value="__('Tahun Akademik')" />
 <select id="academic_year_id" name="academic_year_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm">
 @foreach($academicYears as $year)
 <option value="{{ $year->id }}" {{ old('academic_year_id', $semester->academic_year_id) == $year->id ? 'selected' : '' }}>
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
 <option value="Ganjil" {{ old('name', $semester->name) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
 <option value="Genap" {{ old('name', $semester->name) == 'Genap' ? 'selected' : '' }}>Genap</option>
 </select>
 <x-input-error :messages="$errors->get('name')" class="mt-2" />
 </div>

 <div class="grid grid-cols-2 gap-4 mt-4">
 <!-- Start Date -->
 <div>
 <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
 <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $semester->start_date->format('Y-m-d'))" required />
 <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
 </div>

 <!-- End Date -->
 <div>
 <x-input-label for="end_date" :value="__('Tanggal Selesai')" />
 <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $semester->end_date->format('Y-m-d'))" required />
 <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
 </div>
 </div>

 <!-- Is Active -->
 <div class="block mt-4">
 <label for="is_active" class="inline-flex items-center">
 <input id="is_active" type="checkbox" class="rounded border-outline-variant/30 text-primary shadow-sm focus:ring-primary/20" name="is_active" {{ old('is_active', $semester->is_active) ? 'checked' : '' }}>
 <span class="ms-2 text-sm text-on-surface-variant">{{ __('Set as Active Semester') }}</span>
 </label>
 </div>

 <div class="flex items-center justify-end mt-4">
 <a href="{{ route('admin.semesters.index') }}" class="text-on-surface-variant hover:text-on-surface mr-4">
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
