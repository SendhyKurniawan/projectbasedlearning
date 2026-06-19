{{-- Partial form ganti password. --}}
<section>
 <header>
 <h2 class="text-lg font-medium text-on-surface">
 {{ __('Update Password') }}
 </h2>

 <p class="mt-1 text-sm text-on-surface-variant">
 {{ __('Ensure your account is using a long, random password to stay secure.') }}
 </p>
 </header>

 <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
 @csrf
 @method('put')

 <div>
 <div x-data="{ show: false }" class="relative">
 <x-text-input id="update_password_current_password" name="current_password" type="password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full pr-12" autocomplete="current-password" />
 <button type="button" @click="show = !show" class="absolute right-3 top-[55%] -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
 </div>

 <div>
 <div x-data="{ show: false }" class="relative">
 <x-text-input id="update_password_password" name="password" type="password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full pr-12" autocomplete="new-password" />
 <button type="button" @click="show = !show" class="absolute right-3 top-[55%] -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
 </div>

 <div>
 <div x-data="{ show: false }" class="relative">
 <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full pr-12" autocomplete="new-password" />
 <button type="button" @click="show = !show" class="absolute right-3 top-[55%] -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
 </div>

 <div class="flex items-center gap-4">
 <x-primary-button>{{ __('Save') }}</x-primary-button>

 @if (session('status') === 'password-updated')
 <p
 x-data="{ show: true }"
 x-show="show"
 x-transition
 x-init="setTimeout(() => show = false, 2000)"
 class="text-sm text-on-surface-variant"
 >{{ __('Saved.') }}</p>
 @endif
 </div>
 </form>
</section>
