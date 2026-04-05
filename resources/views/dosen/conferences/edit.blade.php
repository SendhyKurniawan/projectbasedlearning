<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Edit Jadwal Sesi
 </h2>
 <a href="{{ route('dosen.conferences.index', $conference->course) }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Kembali
 </a>
 </div>
 </x-slot>

 <div class="py-8">
 <div class="card">
 <form method="POST" action="{{ route('dosen.conferences.update', $conference) }}">
 @csrf @method('PUT')

 <div class="form-group">
 <label class="form-label" for="title">Judul Sesi</label>
 <input class="form-input" id="title" type="text" name="title" value="{{ old('title', $conference->title) }}" required>
 @error('title') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group">
 <label class="form-label" for="description">Deskripsi (opsional)</label>
 <textarea class="form-input" id="description" name="description" rows="3">{{ old('description', $conference->description) }}</textarea>
 @error('description') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group">
 <label class="form-label" for="scheduled_at">Waktu Mulai</label>
 <input class="form-input" id="scheduled_at" type="datetime-local" name="scheduled_at"
 value="{{ old('scheduled_at', $conference->scheduled_at->format('Y-m-d\TH:i')) }}" required>
 @error('scheduled_at') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="flex items-center justify-end gap-3 mt-6">
 <a href="{{ route('dosen.conferences.index', $conference->course) }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors">Batal</a>
 <button type="submit" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">Simpan Perubahan</button>
 </div>
 </form>
 </div>
 </div>
</x-app-layout>
