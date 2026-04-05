<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Edit Pengumuman') }}
 </h2>
 <a href="{{ route('announcements.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-4xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="POST" action="{{ route('announcements.update', $announcement) }}">
 @csrf
 @method('PUT')

 <!-- Title -->
 <div class="mb-4">
 <x-input-label for="title" :value="__('Judul Pengumuman')" />
 <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $announcement->title)" required autofocus />
 <x-input-error :messages="$errors->get('title')" class="mt-2" />
 </div>

 <!-- Target Audience -->
 <div class="mb-4">
 <x-input-label for="target_audience" :value="__('Target Pengguna (Audiens)')" />
 <select id="target_audience" name="target_audience" class="border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-primary/20:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" onchange="toggleSpecificUsers()">
 @foreach($targets as $value => $label)
 <option value="{{ $value }}" {{ old('target_audience', $announcement->target_audience) === $value ? 'selected' : '' }}>{{ $label }}</option>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('target_audience')" class="mt-2" />
 </div>

 <!-- Specific Users Container -->
 <div id="specific_users_container" class="mb-4 {{ old('target_audience', $announcement->target_audience) === 'specific' ? '' : 'hidden' }}">
 <x-input-label for="specific_users" :value="__('Pilih Pengguna Spesifik')" />
 <div class="mt-2 text-sm text-on-surface-variant h-48 overflow-y-auto border border-outline-variant/30 p-2 rounded-md">
 @forelse($users as $user)
 <label class="flex items-center mb-1">
 @php
 $isChecked = false;
 if (is_array(old('specific_users')) && in_array($user->id, old('specific_users'))) {
 $isChecked = true;
 } elseif (!old('target_audience') && in_array($user->id, $selectedUsers)) {
 $isChecked = true;
 }
 @endphp
 <input type="checkbox" name="specific_users[]" value="{{ $user->id }}" class="rounded border-outline-variant/30 text-primary shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $isChecked ? 'checked' : '' }}>
 <span class="ml-2">{{ $user->name }} ({{ ucfirst($user->role) }})</span>
 </label>
 @empty
 <p>Tidak ada pengguna yang tersedia.</p>
 @endforelse
 </div>
 <x-input-error :messages="$errors->get('specific_users')" class="mt-2" />
 </div>

 <!-- Content -->
 <div class="mb-4">
 <x-input-label for="content" :value="__('Isi Pengumuman')" />
 <textarea id="content" name="content" rows="8" class="border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-primary/20:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" required>{{ old('content', $announcement->content) }}</textarea>
 <x-input-error :messages="$errors->get('content')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4 text-right">
 <x-primary-button>
 {{ __('Perbarui Pengumuman') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 <script>
 function toggleSpecificUsers() {
 var audienceSelect = document.getElementById('target_audience');
 var usersContainer = document.getElementById('specific_users_container');
 if (audienceSelect.value === 'specific') {
 usersContainer.classList.remove('hidden');
 } else {
 usersContainer.classList.add('hidden');
 }
 }
 </script>
</x-app-layout>
