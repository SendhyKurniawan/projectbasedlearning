<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class SidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $dosenCourses = [];

        if (Auth::check() && Auth::user()->role === 'dosen') {
            $dosenCourses = Course::where('dosen_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $view->with('dosenCourses', $dosenCourses);
    }
}
