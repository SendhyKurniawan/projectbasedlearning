<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    public function solve(Assignment $assignment)
    {
        $mahasiswa = auth()->user();
        
        // Check if exercise type
        if ($assignment->type !== 'exercise') {
            abort(404);
        }
        
        // Check if student is enrolled (direct pivot query)
        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$isEnrolled) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }
        
        // Check if already submitted
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();
        
        $assignment->load('course');
        
        return view('mahasiswa.exercises.solve', compact('assignment', 'existing'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'code_answer' => 'required|string|max:50000',
        ]);
        
        $mahasiswa = auth()->user();
        $assignment = Assignment::findOrFail($request->assignment_id);
        
        // Check if enrolled (direct pivot query)
        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$isEnrolled) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }
        
        // Check if already submitted
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();
        
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah mengumpulkan exercise ini.');
        }

        if (now()->greaterThan($assignment->deadline)) {
            return redirect()->back()->with('error', 'Deadline telah terlewat.');
        }

        // Validate code server-side
        $validationResult = $this->validateCode($assignment, $request->code_answer);
        
        // Create submission
        Submission::create([
            'assignment_id' => $assignment->id,
            'mahasiswa_id' => $mahasiswa->id,
            'code_answer' => $request->code_answer,
            'validation_result' => $validationResult,
            'auto_graded' => true,
            'score' => $validationResult['score'],
            'feedback' => $validationResult['feedback'],
            'submitted_at' => now(),
        ]);
        
        return redirect()->route('mahasiswa.courses.show', $assignment->course_id)
            ->with('success', 'Code berhasil dikumpulkan! Skor: ' . $validationResult['score'] . '/' . $assignment->max_score);
    }

    private function validateCode($assignment, $code)
    {
        $config = $assignment->exercise_config;
        $maxScore = $assignment->max_score;
        $score = 0;
        $passed = true;
        $feedback = [];
        
        // Check required keywords
        if (isset($config['required_keywords']) && !empty($config['required_keywords'])) {
            $keywords = array_values(array_filter(
                array_map('trim', $config['required_keywords']),
                fn ($k) => $k !== ''
            ));
            $totalKeywords = count($keywords);
            $foundKeywords = 0;

            foreach ($keywords as $keyword) {
                if (stripos($code, $keyword) !== false) {
                    $foundKeywords++;
                } else {
                    $passed = false;
                    $feedback[] = "❌ Missing required element: {$keyword}";
                }
            }
            
            // Calculate score based on found keywords
            $score = floor(($foundKeywords / $totalKeywords) * $maxScore);
            
            if ($foundKeywords === $totalKeywords) {
                $feedback[] = "✅ All required elements found!";
            } else {
                $feedback[] = "⚠️ Found {$foundKeywords}/{$totalKeywords} required elements.";
            }
        } else {
            // If no validation rules, give full score
            $score = $maxScore;
            $feedback[] = "✅ Code submitted successfully!";
        }
        
        return [
            'passed' => $score === $maxScore,
            'score' => $score,
            'feedback' => implode("\n", $feedback),
            'validated_at' => now()->toDateTimeString(),
        ];
    }
}
