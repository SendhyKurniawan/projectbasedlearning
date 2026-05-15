{{-- Modal: Assign Mahasiswa ke Kelas --}}
<div x-show="activeModal === 'assign'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-lg w-full shadow-xl max-h-[90vh] flex flex-col"
         @click.outside="close()">
        <div class="flex items-center justify-between mb-4 shrink-0">
            <div>
                <h3 class="text-lg font-bold text-on-surface">Assign Mahasiswa</h3>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    Kelas: <strong x-text="modalPayload.class_name ?? '-'"></strong>
                </p>
            </div>
            <button @click="close()" class="text-outline hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form :action="`/admin/akademik/classes/${modalPayload.class_id}/students`"
              method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf

            {{-- Search filter (Alpine) --}}
            <div x-data="{ search: '' }" class="flex-1 overflow-hidden flex flex-col gap-3">
                <x-text-input type="search" x-model="search"
                              placeholder="Cari nama atau NIM mahasiswa..."
                              class="block w-full shrink-0" />

                <div class="overflow-y-auto flex-1 border border-outline-variant rounded-lg divide-y divide-outline-variant/30">
                    @forelse($availableStudents as $student)
                        <label class="flex items-center gap-3 px-4 py-3 hover:bg-surface-container cursor-pointer"
                               x-show="search === '' || '{{ strtolower($student->name . ' ' . $student->nim) }}'.includes(search.toLowerCase())">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                   class="rounded border-outline text-primary focus:ring-primary">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-on-surface truncate">{{ $student->name }}</p>
                                <p class="text-xs text-on-surface-variant">
                                    {{ $student->nim ?? 'NIM belum diset' }}
                                    @if($student->student_class_id)
                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] bg-warning/10 text-warning font-medium">
                                            Sudah di kelas lain
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </label>
                    @empty
                        <div class="py-8 text-center text-sm text-on-surface-variant">
                            <span class="material-symbols-outlined text-3xl block mb-2">person_off</span>
                            Belum ada mahasiswa terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 shrink-0 border-t border-outline-variant/30 mt-3">
                <button type="button" @click="close()"
                        class="px-4 py-2 text-sm rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors">
                    Batal
                </button>
                <x-primary-button>Assign Mahasiswa</x-primary-button>
            </div>
        </form>
    </div>
</div>
