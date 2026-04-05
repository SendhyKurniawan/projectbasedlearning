<x-guest-layout>
 <!-- Page Header -->
 <div class="mb-8">
 <div class="w-16 h-16 architectural-gradient rounded-2xl flex items-center justify-center mb-6">
 <span class="material-symbols-outlined text-white text-3xl">mark_email_read</span>
 </div>
 <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight mb-2">Verifikasi Email</h2>
 <p class="text-on-surface-variant text-sm leading-relaxed">
 Terima kasih telah mendaftar! Sebelum memulai, verifikasi alamat email Anda dengan mengklik link yang telah kami kirimkan. Jika tidak menerima email, kami akan mengirimkan ulang.
 </p>
 </div>

 @if (session('status') == 'verification-link-sent')
 <div class="mb-6 p-4 bg-secondary-container/20 border-l-4 border-secondary rounded-xl text-sm font-medium text-secondary">
 Link verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
 </div>
 @endif

 <div class="flex items-center justify-between gap-4">
 <form method="POST" action="{{ route('verification.send') }}">
 @csrf
 <button class="architectural-gradient px-6 py-3 rounded-xl text-white font-headline font-bold text-sm shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all" type="submit">
 Kirim Ulang Email Verifikasi
 </button>
 </form>

 <form method="POST" action="{{ route('logout') }}">
 @csrf
 <button type="submit" class="text-sm font-bold text-on-surface-variant hover:text-primary transition-colors">
 Log Out
 </button>
 </form>
 </div>
</x-guest-layout>
