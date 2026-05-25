@push('styles')
    @vite('resources/css/pages/admin/debug-push.css')
@endpush
<x-app-layout>
    @php
        $subscribedCount = 0;
        foreach($users as $u) { if($u->pushSubscriptions->count() > 0) $subscribedCount++; }
        $totalUsers = $users->count();
    @endphp

    <div class="space-y-6">
        {{-- Section Header --}}
        <div>
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Debug Push Notification</h1>
            <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Tool teknis untuk menguji pengiriman web push notifications ke browser klien.</p>
        </div>

        @if(session('success'))
            <div class="px-5 py-3 bg-secondary-container/30 border-l-4 border-secondary text-secondary rounded-xl text-sm font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-5 py-3 bg-error-container/30 border-l-4 border-error text-error rounded-xl text-sm font-bold">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- LEFT: Config Form --}}
            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <h2 class="font-headline text-lg font-bold mb-1">Konfigurasi Push</h2>
                <p class="text-xs text-on-surface-variant mb-5">Pilih target dan susun payload notifikasi.</p>

                <form action="{{ route('admin.debug.push.send') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Target User</label>
                        <select id="user_id" name="user_id" required
                                class="w-full px-3 py-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-low text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">-- Pilih User --</option>
                            @php $roles = ['admin' => 'Admin', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa']; @endphp
                            @foreach($roles as $roleKey => $roleName)
                                <optgroup label="{{ $roleName }}">
                                    @foreach($users->where('role', $roleKey) as $u)
                                        @php $sub = $u->pushSubscriptions->count() > 0; @endphp
                                        <option value="{{ $u->id }}">
                                            {{ $u->name }} ({{ $u->email }}) {{ $sub ? '✓ subscribed' : '— no sub' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Judul Notifikasi</label>
                        <input type="text" name="title" value="Test Push Notification" placeholder="Masukkan judul..."
                               class="w-full px-3 py-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-low text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Body</label>
                        <textarea name="message" rows="4" placeholder="Masukkan pesan..."
                                  class="w-full px-3 py-2.5 rounded-xl border border-outline-variant/20 bg-surface-container-low text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">Ini adalah pesan percobaan untuk push notification.</textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-1" />
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                        <span class="material-symbols-outlined text-base">send</span>
                        Kirim Test Push
                    </button>
                </form>
            </section>

            {{-- RIGHT: Status + Log --}}
            <div class="space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-lg font-bold mb-4">Status Subscriber</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-secondary/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-secondary">Subscribed</p>
                            <p class="font-headline text-3xl font-extrabold text-secondary mt-1">{{ $subscribedCount }}</p>
                            <p class="text-[11px] text-on-surface-variant mt-2">{{ $totalUsers ? round(($subscribedCount/$totalUsers)*100, 1) : 0 }}% dari user</p>
                        </div>
                        <div class="p-4 rounded-xl bg-tertiary/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-tertiary">Total User</p>
                            <p class="font-headline text-3xl font-extrabold text-tertiary mt-1">{{ $totalUsers }}</p>
                            <p class="text-[11px] text-on-surface-variant mt-2">Termasuk non-subscribe</p>
                        </div>
                    </div>
                </section>

                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-lg font-bold mb-3">Log Pengiriman</h2>
                    <div class="rounded-xl bg-on-surface text-secondary-container p-4 font-mono text-[11px] space-y-1">
                        <div class="text-secondary">[VAPID] ✓ Public key configured</div>
                        <div class="text-on-surface-variant">[INFO] {{ $subscribedCount }} subscription(s) active</div>
                        <div class="text-on-surface-variant">[INFO] Listening for test push events…</div>
                        <div class="text-tertiary">[NOTE] Submit form ke kiri untuk mengirim test push.</div>
                    </div>
                </section>

                <section class="bg-tertiary-fixed/40 rounded-2xl border border-tertiary/20 p-5">
                    <h3 class="font-headline text-sm font-bold mb-2">⚠ Catatan</h3>
                    <p class="text-xs text-on-surface-variant">Halaman dev tools — hanya untuk Super Admin. Pastikan browser klien sudah granted permission &amp; service worker terdaftar.</p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
