@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('discussions.index', ['topic' => request('topic')]) }}" class="hover:text-primary transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">forum</span> Diskusi
                    </a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">Topik Baru</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Buat Diskusi</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Mulai topik baru untuk komunitas.</p>
            </div>
            <a href="{{ route('discussions.index', ['topic' => request('topic')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Form --}}
            <div class="lg:col-span-8">
                <div class="bg-surface-container-lowest overflow-hidden border border-outline-variant/10 rounded-2xl">
                    <div class="px-6 py-4 border-b border-outline-variant/10 bg-surface-container-low/30">
                        <h2 class="font-headline text-base font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">edit_note</span>
                            Detail Diskusi
                        </h2>
                    </div>
                    <div class="p-6 text-on-surface">
                        <form method="POST" action="{{ route('discussions.store') }}">
                            @csrf

                            <!-- Topic -->
                            <div>
                                <x-input-label for="topic" :value="__('Topik Diskusi')" />
                                <p class="text-[10px] text-on-surface-variant mt-0.5 mb-1.5">Pilih kategori yang sesuai agar mudah ditemukan.</p>
                                <x-text-input id="topic" class="block mt-1 w-full" type="text" name="topic" :value="old('topic', $topic)" required autofocus placeholder="Contoh: Pemrograman Web, Pertanyaan Umum..." />
                                <x-input-error :messages="$errors->get('topic')" class="mt-2" />
                            </div>

                            <!-- Title -->
                            <div class="mt-5">
                                <x-input-label for="title" :value="__('Judul Pertanyaan/Diskusi')" />
                                <p class="text-[10px] text-on-surface-variant mt-0.5 mb-1.5">Buat judul yang jelas dan deskriptif.</p>
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required placeholder="Contoh: Bagaimana cara menggunakan Laravel Eloquent?" />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <!-- Content -->
                            <div class="mt-5">
                                <x-input-label for="content" :value="__('Isi Diskusi')" />
                                <p class="text-[10px] text-on-surface-variant mt-0.5 mb-1.5">Jelaskan pertanyaan atau topik diskusi secara detail.</p>
                                <textarea id="content" name="content" rows="8" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-xl shadow-sm text-sm leading-relaxed" required placeholder="Tulis isi diskusi di sini...">{{ old('content') }}</textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-6 pt-5 border-t border-outline-variant/10 gap-3">
                                <a href="{{ url()->previous() }}" class="px-4 py-2.5 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low text-sm font-bold transition-all">
                                    {{ __('Batal') }}
                                </a>
                                <x-primary-button class="!rounded-xl !px-6 !py-2.5">
                                    <span class="material-symbols-outlined text-sm mr-1.5">send</span>
                                    {{ __('Kirim Diskusi') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tips Sidebar --}}
            <aside class="lg:col-span-4 space-y-4">
                <section class="bg-primary/5 rounded-2xl border border-primary/15 p-5">
                    <h3 class="font-headline text-sm font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base">lightbulb</span>
                        Tips Membuat Diskusi
                    </h3>
                    <ul class="space-y-2.5">
                        @foreach([
                            ['Gunakan judul yang spesifik dan jelas', 'title'],
                            ['Pilih topik yang relevan', 'label'],
                            ['Jelaskan konteks pertanyaan Anda', 'description'],
                            ['Sertakan contoh kode jika perlu', 'code'],
                            ['Periksa apakah sudah ada diskusi serupa', 'search'],
                        ] as [$tip, $icon])
                            <li class="flex items-start gap-2.5 text-xs text-on-surface-variant">
                                <span class="material-symbols-outlined text-primary text-sm mt-0.5 shrink-0">{{ $icon }}</span>
                                {{ $tip }}
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="bg-tertiary-fixed/40 rounded-2xl border border-tertiary/20 p-5">
                    <h3 class="font-headline text-sm font-bold mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-tertiary text-base">gavel</span>
                        Aturan Forum
                    </h3>
                    <ul class="space-y-2">
                        @foreach([
                            'Hormati sesama pengguna',
                            'Hindari spam atau promosi',
                            'Gunakan bahasa yang sopan',
                        ] as $rule)
                            <li class="flex items-start gap-2 text-xs text-on-surface-variant">
                                <span class="material-symbols-outlined text-tertiary text-xs mt-0.5 shrink-0">check_circle</span>
                                {{ $rule }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
