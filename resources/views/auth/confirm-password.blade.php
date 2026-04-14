<x-guest-layout>
 <div class="mb-4 text-sm text-gray-600">
 {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
 </div>

 <form method="POST" action="{{ route('password.confirm') }}">
 @csrf

 <!-- Password -->
 <div x-data="{ show: false }">
 <x-input-label for="password" :value="__('Password')" />

 <div class="relative">
 <x-text-input id="password" class="block mt-1 w-full pr-12"
 type="password" x-bind:type="show ? 'text' : 'password'"
 name="password"
 required autocomplete="current-password" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>

 <x-input-error :messages="$errors->get('password')" class="mt-2" />
 </div>

 <div class="flex justify-end mt-4">
 <x-primary-button>
 {{ __('Confirm') }}
 </x-primary-button>
 </div>
 </form>
</x-guest-layout>
