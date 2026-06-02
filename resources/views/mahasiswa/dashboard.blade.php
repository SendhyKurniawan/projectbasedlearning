@push('styles')
    @vite('resources/css/pages/mahasiswa/dashboard.css')
@endpush
<x-app-layout>
    @php
        $pendingCount = max(0, ($stats['total_assignments'] ?? 0) - ($stats['submitted_assignments'] ?? 0));
        $thisWeekDue = $upcoming_assignments->filter(fn($a) => $a->deadline && $a->deadline->lte(now()->addWeek()))->count();
    @endphp

    <div class="space-y-8">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Halo, {{ Auth::user()->name }}</h1>
                <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Berikut ringkasan progres belajar dan agenda kamu hari ini.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('mahasiswa.courses.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-sm font-bold text-on-surface hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-base">add</span>
                    Gabung Kelas
                </a>
                <a href="{{ route('mahasiswa.schedule.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm hover:shadow transition-all">
                    <span class="material-symbols-outlined text-base">calendar_month</span>
                    Lihat Jadwal
                </a>
            </div>
        </div>

        {{-- 4-Stat Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Kelas Aktif', $stats['enrolled_courses'] ?? 0, 'Semester ini', 'auto_stories', 'primary'],
                ['Tugas Pending', $pendingCount, $thisWeekDue . ' due minggu ini', 'assignment', 'tertiary'],
                ['Tugas Selesai', $stats['submitted_assignments'] ?? 0, 'Total submit', 'check_circle', 'secondary'],
                ['Total Tugas', $stats['total_assignments'] ?? 0, 'Semua MK', 'fact_check', 'primary'],
            ] as [$label, $value, $hint, $icon, $accent])
                <div class="bg-surface-container-lowest p-5 rounded-2xl relative overflow-hidden border border-outline-variant/10">
                    <div class="absolute top-0 left-0 w-1 h-full bg-{{ $accent }}"></div>
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-{{ $accent }}/10 text-{{ $accent }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">{{ $label }}</p>
                    <p class="font-headline text-3xl font-extrabold text-on-surface mt-1 leading-none">{{ $value }}</p>
                    <p class="text-[11px] text-on-surface-variant mt-2">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2 bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <h2 class="font-headline text-lg font-bold mb-4">Aktivitas Submission Saya (30 hari)</h2>
                <div class="relative h-56 w-full"><canvas id="mhsActivityChart"></canvas></div>
            </section>
            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <h2 class="font-headline text-lg font-bold mb-4">Distribusi Nilai</h2>
                <div class="relative h-56 w-full"><canvas id="mhsScoreChart"></canvas></div>
            </section>
        </div>

        {{-- Main 2-Col --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT: Upcoming + Progress --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Tugas Mendatang --}}
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
                    <header class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/10">
                        <h2 class="font-headline text-lg font-bold">Tugas Mendatang</h2>
                        <a href="#" class="text-xs font-bold text-primary hover:underline">Lihat semua →</a>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-surface-container-low/40 text-[10px] uppercase tracking-widest text-on-surface-variant">
                                    <th class="text-left px-6 py-3 font-bold">Tugas</th>
                                    <th class="text-left px-4 py-3 font-bold">Mata Kuliah</th>
                                    <th class="text-left px-4 py-3 font-bold">Tipe</th>
                                    <th class="text-left px-4 py-3 font-bold">Deadline</th>
                                    <th class="text-left px-4 py-3 font-bold">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @forelse($upcoming_assignments as $a)
                                    @php
                                        $hours = $a->deadline ? now()->diffInHours($a->deadline, false) : 9999;
                                        $tagClass = $hours < 48 ? 'bg-error/10 text-error' : ($hours < 168 ? 'bg-tertiary/10 text-tertiary' : 'bg-surface-container text-on-surface-variant');
                                        $submitted = $submittedAssignmentIds->contains($a->id);
                                    @endphp
                                    <tr class="hover:bg-surface-bright transition-colors">
                                        <td class="px-6 py-3 font-bold text-on-surface">{{ $a->title }}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">{{ $a->course->nama_matkul ?? '—' }}</td>
                                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-md bg-surface-container text-[10px] font-bold uppercase">{{ ucfirst($a->type ?? 'tugas') }}</span></td>
                                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tagClass }}">{{ $a->deadline ? $a->deadline->diffForHumans() : 'Tanpa batas' }}</span></td>
                                        <td class="px-4 py-3">
                                            @if($submitted)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-secondary/10 text-secondary">Sudah Submit</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-surface-container text-on-surface-variant">Belum Submit</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('mahasiswa.submissions.create', ['assignment_id' => $a->id]) }}" class="text-xs font-bold text-primary hover:underline">
                                                {{ $submitted ? 'Lihat' : 'Buka' }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-on-surface-variant">Tidak ada tugas mendatang.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Progres Mata Kuliah --}}
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-lg font-bold mb-4">Progres Mata Kuliah</h2>
                    <div class="space-y-4">
                        @forelse($enrolled_courses as $c)
                            @php
                                $pct = $courseProgress[$c->id] ?? 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="font-bold text-on-surface">{{ $c->nama_matkul }}</span>
                                    <span class="text-on-surface-variant">{{ $pct }}%</span>
                                </div>
                                <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-on-surface-variant">Belum terdaftar mata kuliah.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- RIGHT: Schedule + Announcement + Activity --}}
            <aside class="lg:col-span-4 space-y-6">
                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-headline text-lg font-bold">Jadwal Hari Ini</h2>
                        <a href="{{ route('mahasiswa.schedule.index') }}" class="text-xs font-bold text-primary hover:underline">Semua →</a>
                    </div>
                    <div class="space-y-1">
                        @forelse($todayConferences as $conf)
                            <div class="flex items-center gap-3 py-2.5 border-b border-dashed border-outline-variant/20 last:border-0">
                                <span class="font-mono text-[11px] text-on-surface-variant w-12">{{ $conf->scheduled_at->format('H:i') }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold truncate">{{ $conf->course->nama_matkul }}</p>
                                    <p class="text-[10px] text-on-surface-variant truncate">{{ $conf->dosen->name }}</p>
                                </div>
                                @if($conf->isLive())
                                    <span class="relative flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-secondary/10 text-secondary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-ping absolute left-1.5"></span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-secondary ml-0.5 relative"></span>
                                        Live
                                    </span>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-on-surface-variant">Tidak ada jadwal hari ini.</p>
                        @endforelse
                    </div>
                </section>

                <section class="bg-tertiary-fixed/40 rounded-2xl border border-tertiary/20 p-6">
                    <h2 class="font-headline text-base font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-tertiary">campaign</span>
                        Pengumuman Terbaru
                    </h2>
                    <div class="space-y-3">
                        @forelse($announcements as $ann)
                            <div>
                                <p class="text-xs font-bold text-on-surface line-clamp-1">{{ $ann->title }}</p>
                                <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-0.5">{{ Str::limit(strip_tags($ann->content), 80) }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant">Belum ada pengumuman.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('announcements.index') }}" class="mt-4 block text-xs font-bold text-primary hover:underline">Lihat semua →</a>
                </section>

                <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                    <h2 class="font-headline text-base font-bold mb-3">Aktivitas Forum</h2>
                    <a href="{{ route('discussions.index') }}" class="flex items-center justify-between text-xs font-bold text-primary hover:underline">
                        Buka Forum Diskusi
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </section>
            </aside>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/charts.js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const C = window.PJBLChart;
    const { primary, secondary } = window.PJBLChartColors;

    // ── 30-day personal activity line chart ─────────────────────────────
    const actData = @json($activitySeries);
    new C(document.getElementById('mhsActivityChart'), {
        type: 'line',
        data: {
            labels: actData.labels,
            datasets: [{
                label: 'Submission',
                data: actData.values,
                borderColor: primary,
                backgroundColor: primary + '22',
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 2,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    // ── Score histogram bar chart ────────────────────────────────────
    const scoreData = @json($scoreDistribution);
    new C(document.getElementById('mhsScoreChart'), {
        type: 'bar',
        data: {
            labels: scoreData.labels,
            datasets: [{
                label: 'Jumlah Submission',
                data: scoreData.values,
                backgroundColor: [
                    secondary + 'cc',
                    primary + 'cc',
                    primary + 'aa',
                    primary,
                ],
                borderRadius: 6,
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
});
</script>
    @endpush
</x-app-layout>
