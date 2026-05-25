<x-guest-layout>
 <!-- Page Header -->
 <div class="mb-8">
 <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight mb-2">Buat Password Baru</h2>
 <p class="text-on-surface-variant text-sm leading-relaxed">Masukkan email Anda dan password baru untuk menyelesaikan reset password.</p>
 </div>

 <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
 @csrf

 <!-- Password Reset Token -->
 <input type="hidden" name="token" value="{{ $request->route('token') }}">

 <!-- Email Address -->
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="email">Email Address</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">alternate_email</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="email" name="email" type="email" value="{{ old('email', $request->email) }}" placeholder="emailanda@contoh.com" required autofocus autocomplete="username" />
 </div>
 <x-input-error :messages="$errors->get('email')" class="mt-1" />
 </div>

 <!-- Password -->
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="password">Password Baru</label>
 <div class="relative" x-data="{ show: false }">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">lock</span>
 <input class="w-full pl-12 pr-12 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="password" name="password" type="password" :type="show ? 'text' : 'password'" placeholder="Minimal 8 karakter" required autocomplete="new-password" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password')" class="mt-1" />
 </div>

 <!-- Confirm Password -->
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="password_confirmation">Konfirmasi Password Baru</label>
 <div class="relative" x-data="{ show: false }">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">lock</span>
 <input class="w-full pl-12 pr-12 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="password_confirmation" name="password_confirmation" type="password" :type="show ? 'text' : 'password'" placeholder="Ulangi password baru Anda" required autocomplete="new-password" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
 </div>

 <!-- Submit Button -->
 <button class="w-full architectural-gradient py-4 rounded-xl text-white font-headline font-bold text-base shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all" type="submit">
 Simpan Password Baru
 </button>
 </form>

 <footer class="mt-8 text-center">
 <a class="text-primary font-bold text-sm hover:underline underline-offset-4 decoration-2" href="{{ route('login') }}">
 Kembali ke Login
 </a>
 </footer>
</x-guest-layout>
