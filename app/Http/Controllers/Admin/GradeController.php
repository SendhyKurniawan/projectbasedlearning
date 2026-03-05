<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::with(['semester', 'dosen', 'assignments', 'students' => function($query) {
            $query->with(['submissions' => function($subQuery) {
                // We only need basic fields to show grades
                $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score');
            }]);
        }])->orderBy('created_at', 'desc')->get();

        return view('admin.grades.index', compact('courses'));
    }
}
