{{-- Modal: Tambah / Edit Kelas --}}
<div x-show="activeModal === 'class'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-sm w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface"
                x-text="modalMode === 'edit' ? 'Edit Kelas' : 'Tambah Kelas'"></h3>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form :action="modalMode === 'edit'
                ? `/admin/akademik/classes/${modalPayload.id}`
                : '{{ route('admin.akademik.classes.store') }}'"
              method="POST" class="space-y-4">
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <input type="hidden" name="study_program_id"
                   :value="modalPayload.study_program_id ?? {{ $selectedProg?->id ?? 'null' }}">
            <input type="hidden" name="semester_id"
                   :value="modalPayload.semester_id ?? {{ $selectedSem?->id ?? 'null' }}">

            <div>
                <x-input-label value="Nama Kelas" />
                <x-text-input name="name" type="text" required
                              placeholder="Kelas A"
                              x-model="modalPayload.name"
                              class="mt-1 block w-full" />
                <p class="mt-1 text-xs text-on-surface-variant">
                    Contoh: "Kelas A", "IF-3A", "TI-2024-A"
                </p>
            </div>

            @if($selectedProg && $selectedSem)
                <div class="p-3 rounded-lg bg-surface-container text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-sm align-middle mr-1">info</span>
                    Kelas akan dibuat untuk
                    <strong class="text-on-surface">{{ $selectedProg->name }}</strong>
                    — Semester <strong class="text-on-surface">{{ $selectedSem->name }}</strong>
                </div>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="close()"
                        class="px-4 py-2 text-sm rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors">
                    Batal
                </button>
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </form>
    </div>
</div>
