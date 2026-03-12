<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::with([
            'semester:id,name', 
            'dosen:id,name', 
            'assignments:id,course_id,title,max_score', 
            'students' => function($query) {
                $query->select('users.id', 'users.name', 'users.nim');
                $query->with(['submissions' => function($subQuery) {
                    $subQuery->select('id', 'mahasiswa_id', 'assignment_id', 'score');
                }]);
        }])
        ->select('id', 'semester_id', 'dosen_id', 'kode_matkul', 'nama_matkul', 'created_at')
        ->orderBy('created_at', 'desc')
        ->get();

        return view('admin.grades.index', compact('courses'));
    }
}
