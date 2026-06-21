{{-- Halaman verifikasi OTP saat registrasi: input kode 6 digit + tombol kirim ulang. --}}
<x-guest-layout title="Verifikasi OTP">
 <!-- Header halaman -->
 <div class="mb-8">
 <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight mb-2">Verifikasi Email</h2>
 <p class="text-on-surface-variant text-sm leading-relaxed">
 Kami telah mengirim kode 6 digit ke
 <span class="font-semibold text-on-surface">{{ $email }}</span>.
 Masukkan kode di bawah untuk menyelesaikan pendaftaran.
 </p>
 </div>

 <!-- Session Status -->
 <x-auth-session-status class="mb-6" :status="session('status')" />

 <form method="POST" action="{{ route('verification.otp.store') }}" class="space-y-6">
 @csrf

 <!-- OTP Code -->
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="code">Kode Verifikasi</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">pin</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none text-on-surface placeholder:text-outline/60 text-center text-2xl font-mono tracking-[0.5em]"
 id="code" name="code" type="text" inputmode="numeric" pattern="\d{6}" maxlength="6"
 autocomplete="one-time-code" placeholder="000000" required autofocus />
 </div>
 <x-input-error :messages="$errors->get('code')" class="mt-1" />
 </div>

 <!-- Submit Button -->
 <button class="w-full architectural-gradient py-4 rounded-xl text-white font-headline font-bold text-base shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all" type="submit">
 Verifikasi
 </button>
 </form>

 <!-- Resend -->
 <form method="POST" action="{{ route('verification.otp.resend') }}" class="mt-6 text-center">
 @csrf
 <p class="text-on-surface-variant text-sm">
 Tidak menerima kode?
 <button type="submit" class="text-primary font-bold hover:underline underline-offset-4 decoration-2">
 Kirim ulang
 </button>
 </p>
 </form>

 <footer class="mt-8 text-center">
 <a class="text-primary font-bold text-sm hover:underline underline-offset-4 decoration-2" href="{{ route('login') }}">
 Kembali ke Login
 </a>
 </footer>
</x-guest-layout>
