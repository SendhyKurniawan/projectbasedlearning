{{-- Modal: Tambah / Edit Semester --}}
<div x-show="activeModal === 'sem'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-md w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface"
                x-text="modalMode === 'edit' ? 'Edit Semester' : 'Tambah Semester'"></h3>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form :action="modalMode === 'edit'
                ? `/admin/akademik/semesters/${modalPayload.id}`
                : '{{ route('admin.akademik.semesters.store') }}'"
              method="POST" class="space-y-4">
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Hidden: academic_year_id dari context --}}
            <input type="hidden" name="academic_year_id" :value="modalPayload.academic_year_id ?? {{ $selectedAy?->id ?? 'null' }}">

            <div>
                <x-input-label value="Nama Semester" />
                <select name="name" required x-model="modalPayload.name"
                        class="mt-1 block w-full rounded-lg border-outline bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Ganjil">Ganjil</option>
                    <option value="Genap">Genap</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Tanggal Mulai" />
                    <x-text-input name="start_date" type="date" required
                                  x-model="modalPayload.start_date"
                                  class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label value="Tanggal Selesai" />
                    <x-text-input name="end_date" type="date" required
                                  x-model="modalPayload.end_date"
                                  class="mt-1 block w-full" />
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1"
                       x-model="modalPayload.is_active"
                       class="rounded border-outline text-primary focus:ring-primary">
                <span class="text-sm text-on-surface">Set sebagai Semester Aktif</span>
            </label>

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
