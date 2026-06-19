{{-- Komponen modal (Alpine) untuk menyalin sebuah konten ke kelas siblings.
     copyRoute = endpoint POST aksi salin; siblings = daftar kelas tujuan; itemTitle = judul konten. --}}
@props([
    'copyRoute',
    'siblings',
    'itemTitle',
])

@if($siblings->count() > 0)
{{-- State Alpine `open` mengatur tampil/sembunyi modal --}}
<div x-data="{ open: false }">
    <button @click.prevent.stop="open = true" type="button"
        class="p-2 text-on-surface-variant hover:text-tertiary hover:bg-tertiary-container/30 rounded-lg transition-colors"
        title="Salin ke Kelas Lain">
        <span class="material-symbols-outlined text-[20px]">library_add</span>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @click.self="open = false"
         style="display:none;">
        <div class="bg-surface-container-lowest rounded-2xl shadow-xl border border-outline-variant/20 w-full max-w-sm p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-on-surface flex items-center gap-2 text-base">
                    <span class="material-symbols-outlined text-primary text-base leading-none">library_add</span>
                    Salin ke Kelas Lain
                </h3>
                <button @click="open = false" type="button" class="p-1 text-on-surface-variant hover:text-on-surface rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <p class="text-sm text-on-surface-variant mb-4">Salin <strong class="text-on-surface">{{ Str::limit($itemTitle, 48) }}</strong> ke:</p>

            <form action="{{ $copyRoute }}" method="POST">
                @csrf
                <div class="space-y-2 mb-5">
                    @foreach($siblings as $sibling)
                    <label class="flex items-center gap-3 cursor-pointer px-3 py-2.5 rounded-xl border border-outline-variant/30 bg-surface hover:border-primary/40 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="checkbox" name="sibling_ids[]" value="{{ $sibling->id }}"
                            class="text-primary focus:ring-primary rounded shrink-0">
                        <span class="text-sm font-bold text-on-surface">{{ $sibling->studentClass->name ?? 'Kelas '.$sibling->id }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-bold text-on-surface-variant hover:bg-surface-container rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-sm font-bold bg-primary text-on-primary rounded-xl hover:bg-primary/90 transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                        Salin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
