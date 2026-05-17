<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

class SidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $dosenCourses = collect();
        $dosenCourseGroups = collect();
        $mahasiswaFirstCourse = null;

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'dosen') {
                $dosenCourses = Cache::remember("sidebar:dosen:{$user->id}", 300, fn () => Course::where('dosen_id', $user->id)
                    ->with('studentClass')
                    ->orderBy('created_at', 'desc')
                    ->get());
                $dosenCourseGroups = $dosenCourses->groupBy('course_group_key');
            } elseif ($user->role === 'mahasiswa') {
                // Single lightweight query instead of whereHas subquery on every page
                $mahasiswaFirstCourse = DB::table('enrollments')
                    ->where('mahasiswa_id', $user->id)
                    ->value('course_id');

                if ($mahasiswaFirstCourse) {
                    $mahasiswaFirstCourse = Course::find($mahasiswaFirstCourse);
                }
            }
        }

        $view->with('dosenCourses', $dosenCourses);
        $view->with('dosenCourseGroups', $dosenCourseGroups);
        $view->with('mahasiswaFirstCourse', $mahasiswaFirstCourse);
    }
}

