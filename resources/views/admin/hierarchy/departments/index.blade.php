@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Struktur Akademik</h1>
                <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Tahun akademik, hierarki institusi, dan grup kelas mahasiswa.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                    <span class="material-symbols-outlined text-base">add</span> Tambah Jurusan
                </a>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2 border-b border-outline-variant/10 pb-3">
            <a href="{{ route('admin.academic-years.index') }}" class="px-3 py-1.5 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-low">Tahun Akademik</a>
            <a href="{{ route('admin.semesters.index') }}" class="px-3 py-1.5 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-low">Semester</a>
            <span class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary text-xs font-bold">Jurusan & Prodi</span>
            <a href="{{ route('admin.student-classes.index') }}" class="px-3 py-1.5 rounded-lg text-on-surface-variant text-xs font-bold hover:bg-surface-container-low">Kelas Mahasiswa</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Tree Navigation Hint --}}
            <aside class="lg:col-span-4">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                    <h3 class="font-headline text-base font-bold mb-3">Navigasi Hierarki</h3>
                    <p class="text-[11px] text-on-surface-variant mb-3">Klik kartu jurusan di sebelah kanan untuk drill-down ke prodi, semester, dan kelas.</p>
                    <div class="space-y-1 text-xs font-mono">
                        <div class="px-2 py-1.5 rounded-md bg-primary/5 text-primary font-bold">▾ Universitas</div>
                        <div class="px-2 py-1.5 pl-6 text-on-surface-variant">▸ Jurusan ({{ $departments->count() }})</div>
                        <div class="px-2 py-1.5 pl-10 text-on-surface-variant">▸ Program Studi</div>
                        <div class="px-2 py-1.5 pl-14 text-on-surface-variant">▸ Semester</div>
                        <div class="px-2 py-1.5 pl-[4.5rem] text-on-surface-variant">▸ Kelas</div>
                    </div>
                </section>
            </aside>

            {{-- RIGHT: Department Cards --}}
            <main class="lg:col-span-8">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <header class="flex items-center justify-between mb-5">
                        <h2 class="font-headline text-lg font-bold">Daftar Jurusan</h2>
                        <span class="text-xs text-on-surface-variant">{{ $departments->count() }} jurusan</span>
                    </header>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($departments as $d)
                            <a href="{{ route('admin.hierarchy.departments.show', $d) }}" class="group p-5 rounded-xl border border-outline-variant/20 hover:border-primary hover:bg-primary/5 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined">account_tree</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-headline text-base font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-1">{{ $d->name }}</h3>
                                        <p class="text-xs text-on-surface-variant mt-0.5">{{ $d->study_programs_count }} Program Studi</p>
                                    </div>
                                    <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">arrow_forward</span>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full p-8 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-2">account_tree</span>
                                <p class="text-sm text-on-surface-variant">Belum ada jurusan terdaftar.</p>
                                <a href="{{ route('admin.departments.create') }}" class="mt-3 inline-block text-xs font-bold text-primary hover:underline">+ Tambah Jurusan</a>
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-app-layout>
