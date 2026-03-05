<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswaId = Auth::id();
        
        $courses = Course::whereHas('students', function($query) use ($mahasiswaId) {
            $query->where('mahasiswa_id', $mahasiswaId);
        })->with(['semester', 'dosen', 'assignments.submissions' => function($query) use ($mahasiswaId) {
            $query->where('mahasiswa_id', $mahasiswaId)->select('id', 'assignment_id', 'mahasiswa_id', 'score', 'status');
        }])->orderBy('created_at', 'desc')->get();

        return view('mahasiswa.grades.index', compact('courses'));
    }
}
