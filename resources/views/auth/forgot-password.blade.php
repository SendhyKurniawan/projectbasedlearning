<x-guest-layout title="Lupa Kata Sandi">
 <!-- Page Header -->
 <div class="mb-8">
 <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight mb-2">Lupa Password</h2>
 <p class="text-on-surface-variant text-sm leading-relaxed">Masukkan alamat email Anda dan kami akan mengirimkan link untuk membuat password baru.</p>
 </div>

 <!-- Session Status -->
 <x-auth-session-status class="mb-6" :status="session('status')" />

 <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
 @csrf

 <!-- Email Address -->
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="email">Email Address</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">alternate_email</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="email" name="email" type="email" :value="old('email')" placeholder="emailanda@contoh.com" required autofocus />
 </div>
 <x-input-error :messages="$errors->get('email')" class="mt-1" />
 </div>

 <!-- Submit Button -->
 <button class="w-full architectural-gradient py-4 rounded-xl text-white font-headline font-bold text-base shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all" type="submit">
 Kirim Link Reset Password
 </button>
 </form>

 <footer class="mt-8 text-center">
 <a class="text-primary font-bold text-sm hover:underline underline-offset-4 decoration-2" href="{{ route('login') }}">
 Kembali ke Login
 </a>
 </footer>
</x-guest-layout>
