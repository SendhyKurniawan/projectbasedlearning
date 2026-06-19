{{-- Halaman login khusus portal admin. --}}
<x-guest-layout>
 <!-- Status sesi (mis. pesan setelah aksi tertentu) -->
 <x-auth-session-status class="mb-4" :status="session('status')" />

 <div class="mb-6 text-center">
 <h2 class="text-2xl font-bold text-on-surface">Admin Portal</h2>
 <p class="text-sm text-on-surface-variant">Please sign in to continue</p>
 </div>

 <form method="POST" action="{{ route('admin.login.store') }}">
 @csrf

 <!-- Email Address -->
 <div>
 <label for="email" class="block font-medium text-sm text-on-surface-variant">{{ __('Email') }}</label>
 <input id="email" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm" 
 type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
 @error('email')
 <p class="text-sm text-error mt-2">{{ $message }}</p>
 @enderror
 </div>

 <!-- Password -->
 <div class="mt-4" x-data="{ show: false }">
 <label for="password" class="block font-medium text-sm text-on-surface-variant">{{ __('Password') }}</label>
 <div class="relative">
 <input id="password" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-md shadow-sm pr-12" 
 type="password" x-bind:type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" />
 <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 @error('password')
 <p class="text-sm text-error mt-2">{{ $message }}</p>
 @enderror
 </div>

 <!-- Remember Me -->
 <div class="block mt-4">
 <label for="remember_me" class="inline-flex items-center">
 <input id="remember_me" type="checkbox" class="rounded border-outline-variant/30 text-primary shadow-sm focus:ring-primary/20" name="remember">
 <span class="ms-2 text-sm text-on-surface-variant">{{ __('Remember me') }}</span>
 </label>
 </div>

 <div class="flex items-center justify-end mt-4">
 <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-on-primary uppercase tracking-widest hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2 transition ease-in-out duration-150 ml-3">
 {{ __('Log in') }}
 </button>
 </div>
 </form>
</x-guest-layout>
