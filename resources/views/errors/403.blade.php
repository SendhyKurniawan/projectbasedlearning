<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-surface px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-surface-container-lowest p-8 rounded-[2rem] shadow-xl border border-outline-variant/10 text-center">
            
            <div class="flex justify-center">
                <div class="w-24 h-24 bg-error-container text-error rounded-3xl flex items-center justify-center mb-2 shadow-inner">
                    <span class="material-symbols-outlined text-[48px]" style="font-variation-settings: 'FILL' 1;">block</span>
                </div>
            </div>

            <div class="space-y-3 relative z-10">
                <!-- Decorative Elements -->
                <div class="absolute -top-24 -left-10 w-24 h-24 bg-error/20 rounded-full blur-2xl -z-10"></div>
                <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-primary/10 rounded-full blur-3xl -z-10"></div>
                
                <h1 class="text-6xl font-headline font-black text-on-surface tracking-tighter">403</h1>
                <h2 class="text-xl font-bold text-on-surface-variant uppercase tracking-widest">Akses Ditolak</h2>
                <p class="text-sm text-on-surface-variant/80 font-medium leading-relaxed">
                    Maaf, Anda tidak memiliki izin untuk mengakses halaman ini atau Anda belum terdaftar pada mata kuliah ini.
                </p>
            </div>

            <div class="pt-8">
                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-6 py-4 border border-transparent text-sm font-black uppercase tracking-widest rounded-xl text-on-primary bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-95">
                    <span class="material-symbols-outlined text-lg">home</span>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
