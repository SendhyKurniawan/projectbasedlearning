<x-guest-layout>
 <!-- Session Status -->
 <x-auth-session-status class="mb-4" :status="session('status')" />

 <div class="mb-6 text-center">
 <h2 class="text-2xl font-bold text-on-surface">Admin Portal</h2>
 <p class="text-sm text-gray-600">Please sign in to continue</p>
 </div>

 <form method="POST" action="{{ route('admin.login.store') }}">
 @csrf

 <!-- Email Address -->
 <div>
 <label for="email" class="block font-medium text-sm text-on-surface-variant">{{ __('Email') }}</label>
 <input id="email" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-indigo-500:ring-indigo-600 rounded-md shadow-sm" 
 type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
 @error('email')
 <p class="text-sm text-error mt-2">{{ $message }}</p>
 @enderror
 </div>

 <!-- Password -->
 <div class="mt-4" x-data="{ show: false }">
 <label for="password" class="block font-medium text-sm text-on-surface-variant">{{ __('Password') }}</label>
 <div class="relative">
 <input id="password" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-indigo-500:ring-indigo-600 rounded-md shadow-sm pr-12" 
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
 <input id="remember_me" type="checkbox" class="rounded border-outline-variant/30 text-indigo-600 shadow-sm focus:ring-indigo-500:ring-indigo-600" name="remember">
 <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
 </label>
 </div>

 <div class="flex items-center justify-end mt-4">
 <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700:bg-white focus:bg-gray-700:bg-white active:bg-gray-900:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2:ring-offset-gray-800 transition ease-in-out duration-150 ml-3">
 {{ __('Log in') }}
 </button>
 </div>
 </form>
</x-guest-layout>
