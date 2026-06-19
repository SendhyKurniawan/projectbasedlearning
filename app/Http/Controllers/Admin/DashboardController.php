<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\MaterialView;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;

// Controller dashboard admin: ringkasan statistik, data terbaru, dan seri grafik aktivitas.
class DashboardController extends Controller
{
    // Susun kartu statistik, daftar terbaru, seri aktivitas 30 hari, dan distribusi peran.
    public function index()
    {
        // Hitung jumlah user per peran sekali query, lalu pakai ulang untuk statistik & donut.
        $userCounts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $stats = [
            'total_users' => $userCounts->sum(),
            'total_admin' => $userCounts->get('admin', 0),
            'total_dosen' => $userCounts->get('dosen', 0),
            'total_mahasiswa' => $userCounts->get('mahasiswa', 0),
            'total_courses' => Course::count(),
        ];

        $recent_users = User::select('id', 'name', 'email', 'role', 'created_at')->latest()->take(5)->get();
        $recent_courses = Course::with('dosen:id,name')->select('id', 'kode_matkul', 'nama_matkul', 'dosen_id', 'created_at')->withCount('students')->latest()->take(5)->get();

        // Seri aktivitas 30 hari terakhir (untuk grafik garis)
        $start = now()->subDays(29)->startOfDay();
        $labels = collect(range(0, 29))->map(fn ($i) => $start->copy()->addDays($i)->format('Y-m-d'));

        $subs = Submission::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
        $views = MaterialView::where('viewed_at', '>=', $start)
            ->selectRaw('DATE(viewed_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');

        $activitySeries = [
            'labels' => $labels->map(fn ($d) => Carbon::parse($d)->format('d M'))->all(),
            'submissions' => $labels->map(fn ($d) => (int) ($subs[$d] ?? 0))->all(),
            'materials' => $labels->map(fn ($d) => (int) ($views[$d] ?? 0))->all(),
        ];

        // Distribusi peran pengguna (untuk grafik donut)
        $roleDistribution = [
            'labels' => ['Mahasiswa', 'Dosen', 'Admin'],
            'values' => [
                (int) $userCounts->get('mahasiswa', 0),
                (int) $userCounts->get('dosen', 0),
                (int) $userCounts->get('admin', 0),
            ],
        ];

        return view('admin.dashboard', compact(
            'stats', 'recent_users', 'recent_courses',
            'activitySeries', 'roleDistribution'
        ));
    }
}
