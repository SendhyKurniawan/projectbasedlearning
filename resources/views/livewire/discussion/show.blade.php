<div>
 <form wire:submit.prevent="addComment" class="mt-4">
 <div>
 <x-input-label for="newComment" :value="__('Tambahkan Komentar')" />
 <textarea wire:model="newComment" id="newComment" rows="3" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm"></textarea>
 @error('newComment') <span class="text-error text-sm">{{ $message }}</span> @enderror
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button>
 {{ __('Kirim Komentar') }}
 </x-primary-button>
 </div>
 </form>
</div>
