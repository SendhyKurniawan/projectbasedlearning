{{-- Modal: Tambah / Edit Jurusan --}}
<div x-show="activeModal === 'dep'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-sm w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface"
                x-text="modalMode === 'edit' ? 'Edit Jurusan' : 'Tambah Jurusan'"></h3>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form :action="modalMode === 'edit'
                ? `/admin/akademik/departments/${modalPayload.id}`
                : '{{ route('admin.akademik.departments.store') }}'"
              method="POST" class="space-y-4">
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div>
                <x-input-label value="Nama Jurusan" />
                <x-text-input name="name" type="text" required
                              placeholder="Teknik Informatika"
                              x-model="modalPayload.name"
                              class="mt-1 block w-full" />
            </div>

            <div>
                <x-input-label value="Kode Jurusan" />
                <x-text-input name="code" type="text" required maxlength="50"
                              placeholder="TI"
                              x-model="modalPayload.code"
                              class="mt-1 block w-full" />
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
