<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Course;
use App\Services\JaasTokenService;
use Illuminate\Support\Facades\DB;

class ConferenceController extends Controller
{
    public function __construct(private JaasTokenService $jaas)
    {
    }

    public function index(Course $course)
    {
        $this->authorizeEnrolled($course);

        $conferences = $course->conferences()
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('scheduled_at')
            ->get();

        return view('mahasiswa.conferences.index', compact('course', 'conferences'));
    }

    public function room(Conference $conference)
    {
        $this->authorizeEnrolled($conference->course);

        if (!$conference->isLive()) {
            return redirect()
                ->route('mahasiswa.conferences.index', $conference->course)
                ->with('error', 'Sesi konferensi ini belum dimulai atau sudah berakhir.');
        }

        $user = auth()->user();
        $jwt = $this->jaas->mint(
            room: $conference->room_name,
            userId: $user->id,
            name: $user->name,
            moderator: false,
            email: $user->email,
        );

        return view('mahasiswa.conferences.room', compact('conference', 'jwt'));
    }

    private function authorizeEnrolled(Course $course): void
    {
        $isEnrolled = DB::table('enrollments')
            ->where('mahasiswa_id', auth()->id())
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di course ini.');
        }
    }
}
