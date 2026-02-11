<div>
    <form wire:submit.prevent="addComment" class="mt-4">
        <div>
            <x-input-label for="newComment" :value="__('Tambahkan Komentar')" />
            <textarea wire:model="newComment" id="newComment" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
            @error('newComment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Kirim Komentar') }}
            </x-primary-button>
        </div>
    </form>
</div>
