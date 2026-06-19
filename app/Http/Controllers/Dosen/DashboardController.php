<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;

// Controller dashboard dosen: ringkasan matkul yang diampu, statistik, grafik, dan antrean koreksi.
class DashboardController extends Controller
{
    // Susun daftar matkul (dikelompokkan per course_group_key / siblings), statistik,
    // seri submission 7 hari, dan jumlah submission yang belum dinilai.
    public function index()
    {
        $dosen = auth()->user();

        $courses = $dosen->courses()
            ->with('semester', 'studentClass')
            ->withCount(['materials', 'assignments', 'students'])
            ->get();

        $courseGroups = $courses->groupBy('course_group_key')->map(function ($group) {
            return [
                'nama_matkul' => $group->first()->nama_matkul,
                'kode_matkul' => $group->first()->kode_matkul,
                'courses' => $group,
                'total_students' => $group->sum('students_count'),
                'total_materials' => $group->sum('materials_count'),
                'total_assignments' => $group->sum('assignments_count'),
            ];
        })->values();

        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $courses->sum('students_count'),
            'total_assignments' => $courses->sum('assignments_count'),
        ];

        // Seri jumlah submission 7 hari terakhir (untuk grafik)
        $courseIds = $courses->pluck('id');
        $assignmentIds = Assignment::whereIn('course_id', $courseIds)->pluck('id');

        $start = now()->subDays(6)->startOfDay();
        $labels = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));
        $counts = Submission::whereIn('assignment_id', $assignmentIds)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $submissionSeries = [
            'labels' => $labels->map(fn ($d) => $d->isoFormat('ddd'))->all(),
            'values' => $labels->map(fn ($d) => (int) ($counts[$d->format('Y-m-d')] ?? 0))->all(),
        ];

        // Jumlah submission yang masih menunggu penilaian (score belum diisi)
        $pendingReview = Submission::whereIn('assignment_id', $assignmentIds)
            ->whereNull('score')
            ->count();

        return view('dosen.dashboard', compact(
            'courses', 'courseGroups', 'stats',
            'submissionSeries', 'pendingReview'
        ));
    }
}
