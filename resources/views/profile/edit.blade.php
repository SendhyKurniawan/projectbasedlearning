@push('styles')
    @vite('resources/css/pages/shared/profile.css')
@endpush
<x-app-layout>
    <div class="space-y-8">
        {{-- Section Header --}}
        <div>
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Profil Saya</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Kelola informasi pribadi, keamanan akun, dan preferensi.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Identity Card --}}
            <aside class="lg:col-span-4 space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6 text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-primary-fixed text-primary flex items-center justify-center font-headline font-extrabold text-3xl">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <h2 class="font-headline text-xl font-bold mt-4">{{ Auth::user()->name }}</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">{{ Auth::user()->email }}</p>
                    <span class="inline-block mt-3 px-3 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-widest">{{ ucfirst(Auth::user()->role) }}</span>
                </section>

                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h3 class="font-headline text-base font-bold mb-3">Info Akun</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between"><span class="text-on-surface-variant">Bergabung</span><b>{{ Auth::user()->created_at->format('M Y') }}</b></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Verifikasi Email</span>
                            @if(Auth::user()->email_verified_at)
                                <span class="text-secondary font-bold inline-flex items-center gap-1"><span class="material-symbols-outlined text-sm">check_circle</span> Aktif</span>
                            @else
                                <span class="text-tertiary font-bold">Belum</span>
                            @endif
                        </div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Last Update</span><b>{{ Auth::user()->updated_at->diffForHumans() }}</b></div>
                    </div>
                </section>
            </aside>

            {{-- RIGHT: Tabs Form --}}
            <div class="lg:col-span-8 space-y-6" x-data="{ tab: 'profile' }">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <div class="flex flex-wrap gap-2 border-b border-outline-variant/10 -mx-6 px-6 pb-4 mb-6">
                        <button @click="tab = 'profile'" :class="tab === 'profile' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Informasi Pribadi</button>
                        <button @click="tab = 'password'" :class="tab === 'password' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Ubah Password</button>
                        <button @click="tab = 'delete'" :class="tab === 'delete' ? 'bg-error/10 text-error' : 'text-on-surface-variant hover:bg-surface-container-low'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Hapus Akun</button>
                    </div>
                    <div x-show="tab === 'profile'">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                    <div x-show="tab === 'password'" style="display:none">
                        <h3 class="font-headline text-lg font-bold mb-4">Ubah Password</h3>
                        @include('profile.partials.update-password-form')
                    </div>
                    <div x-show="tab === 'delete'" style="display:none">
                        <h3 class="font-headline text-lg font-bold mb-4 text-error">Hapus Akun</h3>
                        @include('profile.partials.delete-user-form')
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
