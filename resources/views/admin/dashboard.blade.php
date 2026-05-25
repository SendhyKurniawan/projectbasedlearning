@push('styles')
    @vite('resources/css/pages/admin/dashboard.css')
@endpush
<x-app-layout>
    <div class="space-y-8">
        {{-- Section Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Admin Dashboard</h1>
                <p class="mt-1 text-sm text-on-surface-variant max-w-xl">Ringkasan infrastruktur akademik & pengguna sistem.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-sm font-bold hover:bg-surface-container-low">
                    <span class="material-symbols-outlined text-base">download</span> Ekspor Laporan
                </a>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm">
                    <span class="material-symbols-outlined text-base">person_add</span> Tambah User
                </a>
            </div>
        </div>

        {{-- 4-Stat Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Total User', number_format($stats['total_users']), 'Akun aktif', 'groups', 'primary'],
                ['Mahasiswa', number_format($stats['total_mahasiswa']), $stats['total_users'] ? round(($stats['total_mahasiswa']/$stats['total_users'])*100,1).'% dari total' : '—', 'school', 'secondary'],
                ['Dosen', number_format($stats['total_dosen']), 'Pengajar aktif', 'person_book', 'tertiary'],
                ['Mata Kuliah', number_format($stats['total_courses']), 'Semester aktif', 'auto_stories', 'primary'],
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

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2 bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <header class="flex items-center justify-between mb-4">
                    <h2 class="font-headline text-lg font-bold">Aktivitas Sistem (30 hari)</h2>
                    <div class="flex gap-1.5 text-[10px]">
                        <span class="px-2 py-0.5 rounded-md bg-primary/10 text-primary font-bold">Submission</span>
                        <span class="px-2 py-0.5 rounded-md bg-surface-container text-on-surface-variant font-bold">Materi</span>
                    </div>
                </header>
                <div class="relative h-56 w-full"><canvas id="adminActivityChart"></canvas></div>
            </section>
            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <h2 class="font-headline text-lg font-bold mb-4">Distribusi per Role</h2>
                <div class="relative h-40 w-full mb-4"><canvas id="adminRoleChart"></canvas></div>
                <div class="space-y-2 text-xs">
                    @foreach([['Mahasiswa', $stats['total_mahasiswa'], 'bg-primary'],['Dosen', $stats['total_dosen'], 'bg-secondary'],['Admin', $stats['total_admin'], 'bg-tertiary']] as [$n,$v,$c])
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $c }}"></span>
                                <span>{{ $n }}</span>
                            </div>
                            <b>{{ number_format($v) }}</b>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Bottom Row: Hierarchy + Audit --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <h2 class="font-headline text-lg font-bold mb-4">User Terbaru</h2>
                <div class="divide-y divide-outline-variant/10 -mx-2">
                    @foreach($recent_users as $u)
                        <div class="flex items-center gap-3 px-2 py-2.5">
                            <div class="w-9 h-9 rounded-full bg-primary-fixed text-primary flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold truncate">{{ $u->name }}</p>
                                <p class="text-[10px] text-on-surface-variant truncate">{{ $u->email }}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-surface-container">{{ ucfirst($u->role) }}</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('admin.users.index') }}" class="mt-3 block text-xs font-bold text-primary hover:underline">Lihat semua →</a>
            </section>
            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-6">
                <h2 class="font-headline text-lg font-bold mb-4">Mata Kuliah Terbaru</h2>
                <div class="divide-y divide-outline-variant/10 -mx-2">
                    @foreach($recent_courses as $c)
                        <div class="flex items-center gap-3 px-2 py-2.5">
                            <div class="w-9 h-9 rounded-xl bg-tertiary-fixed text-tertiary flex items-center justify-center">
                                <span class="material-symbols-outlined text-base">book</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold truncate">{{ $c->nama_matkul }}</p>
                                <p class="text-[10px] text-on-surface-variant truncate">{{ $c->dosen->name ?? 'Belum ditugaskan' }} · {{ $c->students_count }} mhs</p>
                            </div>
                            <span class="text-[10px] font-mono text-on-surface-variant">{{ $c->kode_matkul }}</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('admin.courses.index') }}" class="mt-3 block text-xs font-bold text-primary hover:underline">Lihat semua →</a>
            </section>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/charts.js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const C = window.PJBLChart;
    const { primary, secondary, tertiary } = window.PJBLChartColors;

    // ── Activity line chart (30 days) ──────────────────────────────────────
    const actData = @json($activitySeries);
    new C(document.getElementById('adminActivityChart'), {
        type: 'line',
        data: {
            labels: actData.labels,
            datasets: [
                {
                    label: 'Submission',
                    data: actData.submissions,
                    borderColor: primary,
                    backgroundColor: primary + '22',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true,
                },
                {
                    label: 'Materi',
                    data: actData.materials,
                    borderColor: secondary,
                    backgroundColor: secondary + '22',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    // ── Role donut chart ───────────────────────────────────────────────────
    const roleData = @json($roleDistribution);
    new C(document.getElementById('adminRoleChart'), {
        type: 'doughnut',
        data: {
            labels: roleData.labels,
            datasets: [{
                data: roleData.values,
                backgroundColor: [primary, secondary, tertiary],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { display: false } },
        },
    });
});
</script>
    @endpush
</x-app-layout>
