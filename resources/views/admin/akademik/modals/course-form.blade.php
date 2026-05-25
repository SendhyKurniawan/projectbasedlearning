{{-- Modal: Tambah Mata Kuliah (Semester atau Kelas) --}}
<div x-show="activeModal === 'course'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-lg w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface">
                <span x-text="(modalPayload.scope === 'class') ? 'Tambah MK Khusus Kelas' : 'Tambah MK Semester'"></span>
            </h3>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('admin.akademik.courses.store') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="scope" :value="modalPayload.scope ?? 'semester'">
            <input type="hidden" name="semester_id" :value="modalPayload.semester_id ?? {{ $selectedSem?->id ?? 'null' }}">

            {{-- Dropdown kelas (hanya muncul saat scope=class) --}}
            <div x-show="modalPayload.scope === 'class'" x-transition>
                <x-input-label value="Kelas" />
                <select name="student_class_id"
                        x-bind:required="modalPayload.scope === 'class'"
                        class="mt-1 block w-full rounded-lg border-outline bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Kode Matkul" />
                    <x-text-input name="kode_matkul" type="text" required maxlength="50"
                                  placeholder="IF101"
                                  class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label value="SKS" />
                    <x-text-input name="sks" type="number" required min="1" max="12"
                                  placeholder="3"
                                  class="mt-1 block w-full" />
                </div>
            </div>

            <div>
                <x-input-label value="Nama Mata Kuliah" />
                <x-text-input name="nama_matkul" type="text" required
                              placeholder="Pemrograman Web"
                              class="mt-1 block w-full" />
            </div>

            <div>
                <x-input-label value="Dosen Pengampu" />
                <select name="dosen_id" required
                        class="mt-1 block w-full rounded-lg border-outline bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                    <option value="">-- Pilih Dosen --</option>
                    @foreach($dosens as $dosen)
                        <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label value="Deskripsi (opsional)" />
                <textarea name="description" rows="2"
                          placeholder="Deskripsi singkat mata kuliah..."
                          class="mt-1 block w-full rounded-lg border-outline bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary px-3 py-2 text-sm resize-none"></textarea>
            </div>

            <div x-show="modalPayload.scope === 'class'" class="p-3 rounded-lg bg-primary/5 text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-sm align-middle mr-1 text-primary">class</span>
                MK ini bersifat <strong class="text-primary">khusus kelas</strong> — tidak akan diwarisi kelas lain.
            </div>
            <div x-show="modalPayload.scope !== 'class'" class="p-3 rounded-lg bg-tertiary/5 text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-sm align-middle mr-1 text-tertiary">hub</span>
                MK ini bersifat <strong class="text-tertiary">semester-level</strong> — diwarisi oleh semua kelas di semester ini.
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
