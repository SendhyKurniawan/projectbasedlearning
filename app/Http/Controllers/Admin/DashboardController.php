<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Single query to get user counts grouped by role (instead of 4 separate COUNT queries)
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

        $recent_users = User::latest()->take(5)->get();
        $recent_courses = Course::with('dosen')->withCount('students')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_courses'));
    }
}
