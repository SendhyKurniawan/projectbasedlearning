<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Course;
use App\Services\JitsiTokenService;
use Illuminate\Support\Facades\DB;

// Controller konferensi sisi mahasiswa: lihat daftar sesi & masuk room sebagai peserta biasa.
class ConferenceController extends Controller
{
    // Inject service pembuat token Jitsi.
    public function __construct(private JitsiTokenService $jitsi)
    {
    }

    // Daftar konferensi sebuah matkul (live di atas, lalu terjadwal, lalu berakhir).
    public function index(Course $course)
    {
        $this->authorizeEnrolled($course);

        $conferences = $course->conferences()
            ->orderByRaw("CASE status WHEN 'live' THEN 0 WHEN 'scheduled' THEN 1 WHEN 'ended' THEN 2 ELSE 3 END")
            ->orderBy('scheduled_at')
            ->get();

        return view('mahasiswa.conferences.index', compact('course', 'conferences'));
    }

    // Masuk room sebagai peserta (moderator=false). Hanya bila sesi sedang berlangsung.
    public function room(Conference $conference)
    {
        $this->authorizeEnrolled($conference->course);

        if (!$conference->isLive()) {
            return redirect()
                ->route('mahasiswa.conferences.index', $conference->course)
                ->with('error', 'Sesi konferensi ini belum dimulai atau sudah berakhir.');
        }

        $user = auth()->user();
        $jwt = $this->jitsi->mint(
            room: $conference->room_name,
            userId: $user->id,
            name: $user->name,
            moderator: false,
            email: $user->email,
        );
        $meetUrl = $this->jitsi->roomUrl($conference->room_name, $jwt, $conference->title);

        return view('mahasiswa.conferences.room', compact('conference', 'meetUrl'));
    }

    // Penjaga: pastikan mahasiswa terdaftar di matkul sebelum boleh mengakses konferensinya.
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
