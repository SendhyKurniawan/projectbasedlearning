{{-- Partial pemilih kelas siblings (checkbox) untuk fan-out saat membuat materi/tugas/konferensi.
     Controller harus mengoper $siblings (Collection<Course> dengan studentClass ter-load). --}}
@if(isset($siblings) && $siblings->count() > 0)
<div class="rounded-xl border border-primary/20 bg-primary/5 p-4">
    <div class="flex items-center gap-2 mb-2">
        <span class="material-symbols-outlined text-primary text-base leading-none">library_add</span>
        <span class="text-sm font-bold text-on-surface">Salin ke Kelas Lain</span>
        <span class="text-[10px] text-on-surface-variant font-medium ml-1">(opsional)</span>
    </div>
    <p class="text-xs text-on-surface-variant mb-3">Konten ini akan otomatis dibuat untuk kelas yang dipilih.</p>
    <div class="flex flex-wrap gap-2">
        @foreach($siblings as $sibling)
        <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border border-outline-variant/30 bg-surface hover:border-primary/40 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/10">
            <input type="checkbox" name="sibling_ids[]" value="{{ $sibling->id }}"
                class="text-primary focus:ring-primary rounded"
                {{ in_array($sibling->id, (array) old('sibling_ids', [])) ? 'checked' : '' }}>
            <span class="text-xs font-bold text-on-surface">{{ $sibling->studentClass->name ?? 'Kelas '.$sibling->id }}</span>
        </label>
        @endforeach
    </div>
    @error('sibling_ids.*')
        <p class="text-error text-xs font-bold mt-2">{{ $message }}</p>
    @enderror
</div>
@endif
