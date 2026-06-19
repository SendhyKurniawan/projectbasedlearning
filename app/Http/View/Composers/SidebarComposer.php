<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

// View composer untuk sidebar: menyuntikkan data matkul yang relevan dengan peran user
// ke setiap render layouts.sidebar (didaftarkan di AppServiceProvider::boot).
class SidebarComposer
{
    public function compose(View $view): void
    {
        $dosenCourses = collect();
        $dosenCourseGroups = collect();
        $mahasiswaFirstCourse = null;

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'dosen') {
                // Dosen: ambil matkulnya (di-cache 5 menit, di-flush oleh hook Course::booted)
                // lalu kelompokkan per course_group_key (siblings) untuk tampilan sidebar.
                $dosenCourses = Cache::remember("sidebar:dosen:{$user->id}", 300, fn () => Course::where('dosen_id', $user->id)
                    ->with('studentClass')
                    ->orderBy('created_at', 'desc')
                    ->get());
                $dosenCourseGroups = $dosenCourses->groupBy('course_group_key');
            } elseif ($user->role === 'mahasiswa') {
                // Mahasiswa: ambil satu matkul pertama yang diikuti (untuk shortcut sidebar).
                $mahasiswaFirstCourse = DB::table('enrollments')
                    ->where('mahasiswa_id', $user->id)
                    ->value('course_id');

                if ($mahasiswaFirstCourse) {
                    $mahasiswaFirstCourse = Course::find($mahasiswaFirstCourse);
                }
            }
        }

        // Bagikan variabel ke view sidebar.
        $view->with('dosenCourses', $dosenCourses);
        $view->with('dosenCourseGroups', $dosenCourseGroups);
        $view->with('mahasiswaFirstCourse', $mahasiswaFirstCourse);
    }
}
