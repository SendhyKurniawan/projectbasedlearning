@push('styles')
    @vite('resources/css/pages/dosen/conferences.css')
@endpush
<x-app-layout>
    <div class="space-y-8 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest">
                    <span><a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span><a href="{{ route('dosen.conferences.index', $course) }}" class="hover:text-primary transition-colors">Conferences</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary">Baru</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline">Jadwalkan Live Session</h1>
                <p class="text-on-surface-variant max-w-lg font-medium">{{ $course->nama_matkul }} ({{ $course->kode_matkul }})</p>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/10 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-primary to-secondary"></div>
            
            <div class="p-8">
                <form method="POST" action="{{ route('dosen.conferences.store', $course) }}" class="space-y-6">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="title" class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-[18px] text-primary">title</span>
                            Topik / Judul Sesi <span class="text-error">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Contoh: Pertemuan 1 - Konsep Dasar UI/UX" class="w-full bg-surface-container text-on-surface border-none rounded-xl focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all shadow-inner px-4 py-3" required>
                        @error('title') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-[18px] text-secondary">notes</span>
                            Deskripsi (opsional)
                        </label>
                        <textarea name="description" id="description" rows="3" placeholder="Informasi singkat atau persiapan mahasiswa..." class="w-full bg-surface-container text-on-surface border-none rounded-xl focus:ring-2 focus:ring-secondary focus:bg-surface-container-lowest transition-all shadow-inner px-4 py-3">{{ old('description') }}</textarea>
                        @error('description') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="scheduled_at" class="flex items-center gap-2 text-sm font-bold text-on-surface">
                            <span class="material-symbols-outlined text-[18px] text-tertiary">calendar_clock</span>
                            Waktu Mulai <span class="text-error">*</span>
                        </label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full md:w-1/2 bg-surface-container text-on-surface border-none rounded-xl focus:ring-2 focus:ring-tertiary focus:bg-surface-container-lowest transition-all shadow-inner px-4 py-3" required>
                        @error('scheduled_at') <p class="text-error text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    @include('dosen.partials.sibling-kelas-picker')

                    <div class="pt-6 mt-6 border-t border-outline-variant/10 flex justify-end gap-3">
                        <a href="{{ route('dosen.conferences.index', $course) }}" class="px-6 py-2.5 rounded-xl text-sm font-bold text-on-surface-variant hover:bg-surface-container transition-colors">Batal</a>
                        <button type="submit" class="px-8 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:bg-primary/90 flex items-center gap-2 shadow-sm transition-all focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <span class="material-symbols-outlined text-[18px]">event_available</span>
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
