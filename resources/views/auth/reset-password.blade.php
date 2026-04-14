<x-guest-layout>
 <form method="POST" action="{{ route('password.store') }}">
 @csrf

 <!-- Password Reset Token -->
 <input type="hidden" name="token" value="{{ $request->route('token') }}">

 <!-- Email Address -->
 <div>
 <x-input-label for="email" :value="__('Email')" />
 <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
 :value="old('email', $request->email)" required autofocus
 autocomplete="username" placeholder="emailanda@contoh.com" />
 <x-input-error :messages="$errors->get('email')" class="mt-2" />
 </div>

 <!-- Password -->
 <div class="mt-4" x-data="{ show: false }">
 <x-input-label for="password" :value="__('Password Baru')" />
 <div class="relative">
 <x-text-input id="password" class="block mt-1 w-full pr-12" type="password" x-bind:type="show ? 'text' : 'password'"
 name="password" required autocomplete="new-password"
 placeholder="Minimal 8 karakter" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password')" class="mt-2" />
 </div>

 <!-- Confirm Password -->
 <div class="mt-4" x-data="{ show: false }">
 <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
 <div class="relative">
 <x-text-input id="password_confirmation" class="block mt-1 w-full pr-12" type="password" x-bind:type="show ? 'text' : 'password'"
 name="password_confirmation" required autocomplete="new-password"
 placeholder="Ulangi password baru Anda" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button>
 Simpan Password Baru
 </x-primary-button>
 </div>
 </form>
</x-guest-layout>
