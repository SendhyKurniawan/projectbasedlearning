@push('styles')
    @vite('resources/css/pages/admin/academic-hierarchy.css')
@endpush
<x-app-layout>
    <div class="space-y-6">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                    <a href="{{ route('admin.hierarchy.departments.index') }}" class="hover:text-primary transition-colors">Struktur</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <a href="{{ route('admin.hierarchy.departments.show', $studyProgram->department_id) }}" class="hover:text-primary transition-colors">{{ $studyProgram->department->name }}</a>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-primary">{{ $studyProgram->name }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">{{ $studyProgram->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-md bg-tertiary/10 text-tertiary text-[10px] font-bold uppercase tracking-widest">{{ $studyProgram->level }}</span>
                </div>
                <p class="mt-1 text-sm text-on-surface-variant">Kode <span class="font-mono">{{ $studyProgram->code }}</span> · Pilih semester untuk drill-down ke kelas.</p>
            </div>
        </div>

        <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
            <header class="flex items-center justify-between mb-5">
                <h2 class="font-headline text-lg font-bold">Periode Akademik</h2>
                <span class="text-xs text-on-surface-variant">{{ $semesters->count() }} semester</span>
            </header>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($semesters as $sem)
                    <a href="{{ route('admin.hierarchy.study-programs.semesters.show', [$studyProgram, $sem]) }}" class="group p-5 rounded-xl border {{ $sem->is_active ? 'border-secondary bg-secondary/5' : 'border-outline-variant/20' }} hover:border-primary hover:bg-primary/5 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-headline text-base font-bold text-on-surface group-hover:text-primary transition-colors">{{ $sem->name }}</h3>
                            @if($sem->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-secondary text-on-secondary text-[10px] font-bold uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-on-secondary"></span> Aktif
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-on-surface-variant">TA {{ $sem->academicYear->year_start }}/{{ $sem->academicYear->year_end }}</p>
                        <div class="flex items-center justify-between pt-3 mt-3 border-t border-outline-variant/10 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">
                            <span>Drill-down</span>
                            <span class="material-symbols-outlined text-base">arrow_forward</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full p-8 text-center">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">date_range</span>
                        <p class="text-sm text-on-surface-variant">Belum ada semester terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
