{{-- Modal: Konfirmasi Hapus (generic) --}}
<div x-show="activeModal === 'delete'" x-cloak
     class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
     @keydown.escape.window="close()">
    <div class="bg-surface-container-lowest rounded-2xl p-6 max-w-sm w-full shadow-xl"
         @click.outside="close()">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-10 h-10 rounded-full bg-error-container flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">delete</span>
            </div>
            <div>
                <h3 class="text-base font-bold text-on-surface">Konfirmasi Hapus</h3>
                <p class="text-sm text-on-surface-variant mt-1">
                    Hapus <strong x-text="deleteConfirm.label" class="text-on-surface"></strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
        </div>

        <form :action="deleteConfirm.url" method="POST">
            @csrf
            <input type="hidden" name="_method" value="DELETE">

            <div class="flex justify-end gap-2">
                <button type="button" @click="close()"
                        class="px-4 py-2 text-sm rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg bg-error text-on-error font-medium hover:bg-error/90 transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
