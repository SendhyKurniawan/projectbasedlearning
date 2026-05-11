@push('styles')
    @vite('resources/css/pages/dosen/dashboard.css')
@endpush
<x-app-layout>
    <div class="space-y-8">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Selamat datang, {{ Auth::user()->name }} 👩‍🏫</h1>
                <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Kelas Anda hari ini dan tugas yang menunggu review.</p>
            </div>
            <div class="flex items-center gap-2">
                @if($courses->first())
                    <a href="{{ route('dosen.materials.index', $courses->first()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-sm font-bold hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-base">upload_file</span> Upload Materi
                    </a>
                    <a href="{{ route('dosen.assignments.index', $courses->first()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                        <span class="material-symbols-outlined text-base">add</span> Buat Tugas/Kuis
                    </a>
                @endif
            </div>
        </div>

        {{-- 4-Stat Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Kelas Diampu', $stats['total_courses'], 'Semester ini', 'auto_stories', 'primary'],
                ['Total Mahasiswa', $stats['total_students'], 'Seluruh kelas', 'groups', 'secondary'],
                ['Total Tugas', $stats['total_assignments'], 'Aktif & lampau', 'assignment', 'tertiary'],
                ['Belum Dinilai', '—', 'Antrian review', 'rate_review', 'primary'],
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

        {{-- Main 2-col --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT --}}
            <div class="lg:col-span-8 space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    <header class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/10">
                        <h2 class="font-headline text-lg font-bold">Kelas yang Diampu</h2>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-surface-container-low/40 text-[10px] uppercase tracking-widest text-on-surface-variant">
                                    <th class="text-left px-6 py-3 font-bold">Mata Kuliah</th>
                                    <th class="text-left px-4 py-3 font-bold">Kode</th>
                                    <th class="text-left px-4 py-3 font-bold">Mahasiswa</th>
                                    <th class="text-left px-4 py-3 font-bold">Materi</th>
                                    <th class="text-left px-4 py-3 font-bold">Tugas</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @forelse($courses as $c)
                                    <tr class="hover:bg-surface-bright">
                                        <td class="px-6 py-3 font-bold">{{ $c->nama_matkul }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant font-mono text-xs">{{ $c->kode_matkul }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">{{ $c->students_count }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">{{ $c->materials_count }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">{{ $c->assignments_count }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('dosen.materials.index', $c) }}" class="text-xs font-bold text-primary hover:underline">Buka</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-on-surface-variant">Belum ada kelas diampu.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-lg font-bold mb-4">Aktivitas Submission Mingguan</h2>
                    <div class="h-44 rounded-xl bg-surface-container-low/40 border border-dashed border-outline-variant/20 flex items-center justify-center text-xs text-on-surface-variant uppercase tracking-widest">
                        Grafik submission per hari
                    </div>
                </section>
            </div>

            {{-- RIGHT --}}
            <aside class="lg:col-span-4 space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-lg font-bold mb-4">Antrian Review</h2>
                    <div class="space-y-1">
                        @forelse($courses->take(5) as $c)
                            <a href="{{ route('dosen.assignments.index', $c) }}" class="flex items-center gap-3 py-2.5 border-b border-dashed border-outline-variant/20 last:border-0 hover:bg-surface-bright -mx-2 px-2 rounded">
                                <div class="w-8 h-8 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($c->nama_matkul, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold truncate">{{ $c->nama_matkul }}</p>
                                    <p class="text-[10px] text-on-surface-variant truncate">{{ $c->assignments_count }} tugas · {{ $c->students_count }} mhs</p>
                                </div>
                                <span class="text-[10px] font-bold text-primary">Nilai →</span>
                            </a>
                        @empty
                            <p class="text-sm text-on-surface-variant">Tidak ada antrian.</p>
                        @endforelse
                    </div>
                </section>

                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-lg font-bold mb-4">Live Conference Hari Ini</h2>
                    <div class="space-y-3">
                        @foreach($courses->take(2) as $c)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold">{{ $c->nama_matkul }}</p>
                                    <p class="text-[10px] text-on-surface-variant">{{ $c->kode_matkul }}</p>
                                </div>
                                <a href="{{ route('dosen.conferences.index', $c) }}" class="text-[10px] font-bold px-2 py-1 rounded-md bg-secondary/10 text-secondary">Buka</a>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
