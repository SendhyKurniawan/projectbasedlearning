{{-- Halaman daftar seluruh notifikasi milik user. --}}
@push('styles')
    @vite('resources/css/pages/shared/notifications.css')
@endpush
<x-app-layout>
    @php
        $unreadCount = auth()->user()->unreadNotifications->count();
    @endphp

    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Notifikasi</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Pembaruan tugas, pengumuman, dan aktivitas yang relevan untuk Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                @if($unreadCount > 0)
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                            <span class="material-symbols-outlined text-base">done_all</span> Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Filter sidebar --}}
            <aside class="lg:col-span-3">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-4">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant px-3 mb-2">Kategori</h3>
                    <div class="space-y-0.5">
                        @php
                            $totalCount = auth()->user()->notifications->count();
                        @endphp
                        @foreach([
                            ['Semua', $totalCount, true],
                            ['Belum Dibaca', $unreadCount, false],
                            ['Tugas', null, false],
                            ['Pengumuman', null, false],
                            ['Diskusi', null, false],
                        ] as [$label, $count, $active])
                            <div class="flex items-center justify-between px-3 py-2 rounded-lg {{ $active ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                                <span class="text-xs">{{ $label }}</span>
                                @if($count !== null)
                                    <span class="text-[10px] font-bold {{ $active ? '' : 'text-on-surface-variant' }}">{{ $count }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>

            {{-- RIGHT: Notification list --}}
            <main class="lg:col-span-9">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    <div class="divide-y divide-outline-variant/10">
                        @forelse($notifications as $n)
                            @php
                                $type = $n->data['type'] ?? '';
                                $iconMap = [
                                    'academic_update' => ['menu_book', 'primary'],
                                    'submission' => ['task_alt', 'secondary'],
                                    'announcement' => ['campaign', 'tertiary'],
                                ];
                                [$icon, $accent] = $iconMap[$type] ?? ['notifications', 'on-surface-variant'];
                            @endphp
                            <div class="flex items-start gap-4 p-5 {{ $n->unread() ? 'bg-primary-container/10' : '' }} hover:bg-surface-bright transition-colors">
                                <div class="w-10 h-10 rounded-full bg-{{ $accent }}/10 text-{{ $accent }} flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="text-sm font-bold text-on-surface flex items-center gap-2">
                                            @if($n->unread())
                                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                            @endif
                                            {{ $n->data['title'] ?? 'Pemberitahuan Sistem' }}
                                        </h4>
                                        <span class="text-[10px] text-on-surface-variant whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-on-surface-variant line-clamp-2">{{ $n->data['message'] ?? '' }}</p>
                                    <div class="mt-2 flex items-center gap-3">
                                        @if(isset($n->data['url']))
                                            <a href="{{ route('notifications.readAndRedirect', $n->id) }}" class="text-xs font-bold text-primary hover:underline">Buka →</a>
                                        @endif
                                        @if($n->unread() && !isset($n->data['url']))
                                            <form action="{{ route('notifications.markRead', $n->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-on-surface-variant hover:text-on-surface">Tandai Dibaca</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <span class="material-symbols-outlined text-5xl text-outline mb-3">notifications_off</span>
                                <h3 class="font-headline text-lg font-bold">Belum ada notifikasi</h3>
                                <p class="text-xs text-on-surface-variant mt-1">Pemberitahuan akademik dan pengumuman akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                    @if($notifications->hasPages())
                        <div class="p-4 border-t border-outline-variant/10">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</x-app-layout>
