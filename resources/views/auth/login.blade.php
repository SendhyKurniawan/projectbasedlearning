<x-guest-layout>
 <!-- Page Header -->
 <div class="mb-10">
 <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight mb-2">Masuk</h2>
 <p class="text-on-surface-variant text-sm">Selamat datang kembali di PBL Workspace.</p>
 </div>

 <!-- Session Status -->
 <x-auth-session-status class="mb-6" :status="session('status')" />

 <!-- Divider -->
 <div class="relative flex items-center justify-center mb-8">
 <div class="w-full border-t border-outline-variant/30"></div>
 <span class="absolute bg-surface px-4 text-xs font-medium text-on-surface-variant uppercase tracking-widest">Masuk dengan email</span>
 </div>

 <!-- Main Login Form -->
 <form method="POST" action="{{ route('login') }}" class="space-y-6">
 @csrf

 <!-- Email Address -->
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="email">Alamat Email</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">alternate_email</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="email" name="email" type="email" :value="old('email')" placeholder="scholar@workspace.edu" required autofocus autocomplete="username" />
 </div>
 <x-input-error :messages="$errors->get('email')" class="mt-1" />
 </div>

 <!-- Password -->
 <div class="space-y-2">
 <div class="flex justify-between items-center">
 <label class="block text-sm font-semibold text-on-surface-variant" for="password">Password</label>
 @if (Route::has('password.request'))
 <a class="text-xs font-bold text-primary hover:text-primary-container transition-colors" href="{{ route('password.request') }}">Lupa Password?</a>
 @endif
 </div>
 <div class="relative" x-data="{ show: false }">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">lock</span>
 <input class="w-full pl-12 pr-12 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="password" name="password" :type="show ? 'text' : 'password'" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password')" class="mt-1" />
 </div>

 <!-- Remember Me -->
 <div class="flex items-center gap-3 pt-2">
 <input class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20" id="remember_me" name="remember" type="checkbox" />
 <label class="text-sm font-medium text-on-surface-variant select-none" for="remember_me">Ingat Akun Saya</label>
 </div>

 <!-- Submit Button -->
 <button class="w-full architectural-gradient py-4 rounded-xl text-white font-headline font-bold text-lg shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all" type="submit">
 Masuk ke Workspace
 </button>
 </form>

 @if (Route::has('register'))
 <footer class="mt-10 text-center">
 <p class="text-on-surface-variant text-sm">
 Belum punya akun?
 <a class="text-primary font-bold hover:underline underline-offset-4 decoration-2" href="{{ route('register') }}">Daftar Sekarang</a>
 </p>
 </footer>
 @endif
</x-guest-layout>
