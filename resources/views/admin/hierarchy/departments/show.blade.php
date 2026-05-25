@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header with Breadcrumbs --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('admin.hierarchy.departments.index') }}" class="hover:text-primary transition-colors">Struktur</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">{{ $department->name }}</span>
                </nav>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">{{ $department->name }}</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Daftar program studi pada jurusan ini.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.study-programs.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                    <span class="material-symbols-outlined text-base">add</span> Tambah Prodi
                </a>
            </div>
        </div>

        {{-- Stats summary --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Total Prodi', $studyPrograms->count(), 'Aktif & arsip', 'school', 'primary'],
                ['Total Kelas', $studyPrograms->sum('student_classes_count'), 'Tersebar di semua prodi', 'groups', 'secondary'],
                ['Jenjang', $studyPrograms->pluck('level')->unique()->count(), 'Variasi level', 'stairs', 'tertiary'],
                ['Status', 'Aktif', 'Jurusan operasional', 'verified', 'primary'],
            ] as [$label, $value, $hint, $icon, $accent])
                <div class="bg-surface-container-lowest p-5 rounded-2xl relative overflow-hidden border border-outline-variant/10">
                    <div class="absolute top-0 left-0 w-1 h-full bg-{{ $accent }}"></div>
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-{{ $accent }}/10 text-{{ $accent }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">{{ $label }}</p>
                    <p class="font-headline text-3xl font-extrabold mt-1 leading-none">{{ $value }}</p>
                    <p class="text-[11px] text-on-surface-variant mt-2">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        {{-- Prodi cards --}}
        <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
            <header class="flex items-center justify-between mb-5">
                <h2 class="font-headline text-lg font-bold">Program Studi</h2>
                <span class="text-xs text-on-surface-variant">{{ $studyPrograms->count() }} prodi</span>
            </header>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($studyPrograms as $prodi)
                    <a href="{{ route('admin.hierarchy.study-programs.show', $prodi) }}" class="group p-5 rounded-xl border border-outline-variant/20 hover:border-primary hover:bg-primary/5 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-headline text-base font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2">{{ $prodi->name }}</h3>
                            <span class="px-2 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold uppercase tracking-widest shrink-0">{{ $prodi->level }}</span>
                        </div>
                        <p class="text-xs font-mono text-on-surface-variant mb-3">{{ $prodi->code }}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-outline-variant/10">
                            <span class="text-xs text-on-surface-variant">{{ $prodi->student_classes_count }} kelas</span>
                            <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors text-base">arrow_forward</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full p-8 text-center">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">school</span>
                        <p class="text-sm text-on-surface-variant">Belum ada program studi di jurusan ini.</p>
                        <a href="{{ route('admin.study-programs.create') }}" class="mt-3 inline-block text-xs font-bold text-primary hover:underline">+ Tambah Prodi</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
