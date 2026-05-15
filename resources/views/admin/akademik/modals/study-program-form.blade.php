{{-- Modal: Tambah / Edit Program Studi --}}
<div x-show="activeModal === 'prog'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-md w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface"
                x-text="modalMode === 'edit' ? 'Edit Program Studi' : 'Tambah Program Studi'"></h3>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form :action="modalMode === 'edit'
                ? `/admin/akademik/study-programs/${modalPayload.id}`
                : '{{ route('admin.akademik.study-programs.store') }}'"
              method="POST" class="space-y-4">
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <input type="hidden" name="department_id"
                   :value="modalPayload.department_id ?? {{ $selectedDep?->id ?? 'null' }}">

            <div>
                <x-input-label value="Nama Program Studi" />
                <x-text-input name="name" type="text" required
                              placeholder="D3 Teknik Informatika"
                              x-model="modalPayload.name"
                              class="mt-1 block w-full" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Kode" />
                    <x-text-input name="code" type="text" required maxlength="50"
                                  placeholder="D3TI"
                                  x-model="modalPayload.code"
                                  class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label value="Jenjang" />
                    <select name="level" required x-model="modalPayload.level"
                            class="mt-1 block w-full rounded-lg border-outline bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                        <option value="">-- Pilih --</option>
                        <option value="D3">D3</option>
                        <option value="D4">D4</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                    </select>
                </div>
            </div>

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
