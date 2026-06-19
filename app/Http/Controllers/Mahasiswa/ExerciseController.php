<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Controller pengerjaan exercise (latihan koding) oleh mahasiswa.
// Tidak ada auto-grading: validasi keyword hanya jadi "hint" untuk dosen, skor tetap dinilai manual.
class ExerciseController extends Controller
{
    // Tampilkan halaman pengerjaan exercise (sertakan submission yang sudah ada, jika ada).
    public function solve(Assignment $assignment)
    {
        $mahasiswa = auth()->user();

        // Pastikan assignment memang bertipe 'exercise'.
        if ($assignment->type !== 'exercise') {
            abort(404);
        }

        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$isEnrolled) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        $assignment->load('course');

        return view('mahasiswa.exercises.solve', compact('assignment', 'existing'));
    }

    // Kumpulkan jawaban kode. Simpan validation_result sebagai hint; skor dibiarkan null
    // (status 'submitted', auto_graded=false) untuk dinilai manual oleh dosen.
    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'code_answer' => 'required|string',
        ]);

        $mahasiswa = auth()->user();
        $assignment = Assignment::findOrFail($request->assignment_id);

        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('course_id', $assignment->course_id)
            ->exists();
        if (!$isEnrolled) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak terdaftar di course ini.');
        }

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah mengumpulkan exercise ini.');
        }

        // Validasi keyword dijalankan sebagai petunjuk (hint) untuk dosen, bukan untuk penilaian.
        $validationResult = $this->validateCode($assignment, $request->code_answer);

        Submission::create([
            'assignment_id' => $assignment->id,
            'mahasiswa_id' => $mahasiswa->id,
            'code_answer' => $request->code_answer,
            'validation_result' => $validationResult,
            'auto_graded' => false,
            'score' => null,
            'feedback' => null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('mahasiswa.courses.show', $assignment->course_id)
            ->with('success', 'Code berhasil dikumpulkan! Menunggu penilaian dosen.');
    }

    // Hitung kecocokan kode terhadap required_keywords di exercise_config; hasilnya berupa
    // ringkasan (lolos/skor/umpan balik) yang disimpan sebagai hint, BUKAN nilai resmi.
    private function validateCode($assignment, $code)
    {
        $config = $assignment->exercise_config;
        $maxScore = $assignment->max_score;
        $score = 0;
        $passed = true;
        $feedback = [];

        if (isset($config['required_keywords']) && !empty($config['required_keywords'])) {
            $totalKeywords = count($config['required_keywords']);
            $foundKeywords = 0;

            foreach ($config['required_keywords'] as $keyword) {
                if (stripos($code, $keyword) !== false) {
                    $foundKeywords++;
                } else {
                    $passed = false;
                    $feedback[] = "Missing required element: {$keyword}";
                }
            }

            $score = floor(($foundKeywords / $totalKeywords) * $maxScore);

            if ($foundKeywords === $totalKeywords) {
                $feedback[] = "All required elements found.";
            } else {
                $feedback[] = "Found {$foundKeywords}/{$totalKeywords} required elements.";
            }
        } else {
            $score = $maxScore;
            $feedback[] = "Code submitted successfully.";
        }

        return [
            'passed' => $score === $maxScore,
            'score' => $score,
            'feedback' => implode("\n", $feedback),
            'validated_at' => now()->toDateTimeString(),
        ];
    }
}
