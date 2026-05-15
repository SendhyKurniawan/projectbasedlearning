@push('styles')
    @vite('resources/css/pages/shared/discussions.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('discussions.index') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">forum</span> Diskusi
                    </a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('discussions.show', $discussion) }}" class="hover:text-primary transition-colors truncate max-w-[160px]">{{ $discussion->title }}</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">Edit</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Edit Diskusi</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Perbarui konten diskusi Anda.</p>
            </div>
            <a href="{{ route('discussions.show', $discussion) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Form --}}
            <div class="lg:col-span-8">
                <div class="bg-surface-container-lowest overflow-hidden border border-outline-variant/10 rounded-2xl">
                    <div class="px-6 py-4 border-b border-outline-variant/10 bg-surface-container-low/30">
                        <h2 class="font-headline text-base font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">edit</span>
                            Edit Detail Diskusi
                        </h2>
                    </div>
                    <div class="p-6 text-on-surface">
                        <form method="POST" action="{{ route('discussions.update', $discussion) }}">
                            @csrf
                            @method('PUT')

                            <!-- Topic (readonly) -->
                            <div>
                                <x-input-label for="topic" :value="__('Topik Diskusi')" />
                                <p class="text-[10px] text-on-surface-variant mt-0.5 mb-1.5">Topik tidak dapat diubah setelah dibuat.</p>
                                <x-text-input id="topic" class="block mt-1 w-full bg-surface-container-low text-on-surface-variant cursor-not-allowed" type="text" name="topic" :value="old('topic', $discussion->topic ?? 'Umum')" readonly />
                            </div>

                            <!-- Title -->
                            <div class="mt-5">
                                <x-input-label for="title" :value="__('Judul Pertanyaan/Diskusi')" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $discussion->title)" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <!-- Content -->
                            <div class="mt-5">
                                <x-input-label for="content" :value="__('Isi Diskusi')" />
                                <textarea id="content" name="content" rows="8" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-primary focus:ring-primary/20 rounded-xl shadow-sm text-sm leading-relaxed" required>{{ old('content', $discussion->content) }}</textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-6 pt-5 border-t border-outline-variant/10 gap-3">
                                <a href="{{ route('discussions.show', $discussion) }}" class="px-4 py-2.5 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low text-sm font-bold transition-all">
                                    {{ __('Batal') }}
                                </a>
                                <x-primary-button class="!rounded-xl !px-6 !py-2.5">
                                    <span class="material-symbols-outlined text-sm mr-1.5">save</span>
                                    {{ __('Simpan Perubahan') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-4">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-sm font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base">info</span>
                        Informasi Diskusi
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Dibuat pada</span>
                            <span class="text-xs font-bold text-on-surface">{{ $discussion->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Terakhir diubah</span>
                            <span class="text-xs font-bold text-on-surface">{{ $discussion->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant">Topik</span>
                            <span class="px-2 py-0.5 rounded-md bg-primary/8 text-primary text-[10px] font-bold">#{{ $discussion->topic ?? 'Umum' }}</span>
                        </div>
                    </div>
                </section>

                <section class="bg-warning-light/30 rounded-2xl border border-warning/20 p-5">
                    <h3 class="font-headline text-sm font-bold mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-warning text-base">warning</span>
                        Perhatian
                    </h3>
                    <p class="text-xs text-on-surface-variant">Perubahan pada diskusi akan langsung terlihat oleh semua pengguna. Pastikan konten sudah benar sebelum menyimpan.</p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
