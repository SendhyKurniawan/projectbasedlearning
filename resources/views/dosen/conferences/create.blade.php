<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Jadwalkan Sesi Kelas Virtual
            </h2>
            <a href="{{ route('dosen.conferences.index', $course) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="card">
            <form method="POST" action="{{ route('dosen.conferences.store', $course) }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="title">Judul Sesi</label>
                    <input class="form-input" id="title" type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Pertemuan 1 - Pengenalan HTML" required>
                    @error('title') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi (opsional)</label>
                    <textarea class="form-input" id="description" name="description" rows="3" placeholder="Topik yang akan dibahas...">{{ old('description') }}</textarea>
                    @error('description') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="scheduled_at">Waktu Mulai</label>
                    <input class="form-input" id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required>
                    @error('scheduled_at') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="{{ route('dosen.conferences.index', $course) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
