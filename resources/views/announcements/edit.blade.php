{{-- Form edit pengumuman. --}}
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('announcements.index') }}" class="hover:text-primary transition-colors">Pengumuman</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary truncate max-w-[160px]">Edit {{ $announcement->title }}</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Edit Pengumuman</h1>
 <p class="mt-1 text-sm text-on-surface-variant">{{ $announcement->title }}</p>
 </div>
 <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>
 <div class="max-w-4xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <form method="POST" action="{{ route('announcements.update', $announcement) }}" enctype="multipart/form-data">
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
 <select id="target_audience" name="target_audience" class="border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm block mt-1 w-full" onchange="toggleSpecificUsers()">
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
 <input type="checkbox" name="specific_users[]" value="{{ $user->id }}" class="rounded border-outline-variant/30 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary/20" {{ $isChecked ? 'checked' : '' }}>
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
 <textarea id="content" name="content" rows="8" class="border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm block mt-1 w-full" required>{{ old('content', $announcement->content) }}</textarea>
 <x-input-error :messages="$errors->get('content')" class="mt-2" />
 </div>

 <!-- Attachment -->
 <div class="mb-4">
 <x-input-label for="attachment" :value="__('Lampiran (Gambar atau PDF, opsional)')" />

 @if($announcement->hasAttachment())
 <div class="mt-2 mb-2 p-3 rounded-md border border-outline-variant/30 bg-surface-container-low/30 flex items-center justify-between gap-3">
 <div class="flex items-center gap-3 min-w-0">
 @if($announcement->attachmentIsImage())
 <img src="{{ Storage::url($announcement->attachment_path) }}" alt="Lampiran saat ini" loading="lazy" width="48" height="48" class="h-12 w-12 object-cover rounded" />
 @else
 <span class="inline-flex items-center justify-center h-12 w-12 rounded bg-primary/10 text-primary font-bold">PDF</span>
 @endif
 <div class="min-w-0">
 <p class="text-sm text-on-surface truncate">{{ $announcement->attachment_name }}</p>
 <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" rel="noopener" class="text-xs text-primary hover:underline">Lihat lampiran saat ini</a>
 </div>
 </div>
 <label class="flex items-center gap-2 text-sm text-on-surface-variant whitespace-nowrap">
 <input type="checkbox" name="remove_attachment" value="1" class="rounded border-outline-variant/30 text-error shadow-sm focus:ring focus:ring-error/20" />
 Hapus lampiran
 </label>
 </div>
 @endif

 <input id="attachment" name="attachment" type="file" accept="image/*,application/pdf"
 class="block mt-1 w-full text-sm text-on-surface file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
 <p class="mt-1 text-xs text-on-surface-variant">
 @if($announcement->hasAttachment())
 Mengunggah file baru akan mengganti lampiran saat ini.
 @else
 Format yang diizinkan: JPG, JPEG, PNG, GIF, WEBP, atau PDF. Maksimal 10 MB.
 @endif
 </p>
 <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
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
