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
        $stats = [
            'total_users' => User::count(),
            'total_admin' => User::where('role', 'admin')->count(),
            'total_dosen' => User::where('role', 'dosen')->count(),
            'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'total_courses' => Course::count(),
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_courses = Course::with('dosen')->withCount('students')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_courses'));
    }
}
