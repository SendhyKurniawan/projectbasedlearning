{{-- Modal: Tambah / Edit Tahun Akademik --}}
<div x-show="activeModal === 'ay'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-md w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface"
                x-text="modalMode === 'edit' ? 'Edit Tahun Akademik' : 'Tambah Tahun Akademik'"></h3>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form :action="modalMode === 'edit'
                ? `/admin/akademik/academic-years/${modalPayload.id}`
                : '{{ route('admin.akademik.academic-years.store') }}'"
              method="POST" class="space-y-4">
            @csrf
            <template x-if="modalMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Tahun Mulai" />
                    <x-text-input name="year_start" type="text" maxlength="4" required
                                  placeholder="2025"
                                  x-model="modalPayload.year_start"
                                  class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label value="Tahun Selesai" />
                    <x-text-input name="year_end" type="text" maxlength="4" required
                                  placeholder="2026"
                                  x-model="modalPayload.year_end"
                                  class="mt-1 block w-full" />
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1"
                       x-model="modalPayload.is_active"
                       class="rounded border-outline text-primary focus:ring-primary">
                <span class="text-sm text-on-surface">Set sebagai Tahun Akademik Aktif</span>
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
