<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Course;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use Illuminate\Support\Facades\DB;

class ConferenceController extends Controller
{
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

        return view('mahasiswa.conferences.room', compact('conference'));
    }

    public function token(Conference $conference)
    {
        $this->authorizeEnrolled($conference->course);

        if (!$conference->isLive()) {
            abort(403, 'Sesi belum aktif.');
        }

        $user = auth()->user();
        $token = $this->generateToken($conference->room_name, $user->name);

        return response()->json(['token' => $token]);
    }

    private function generateToken(string $roomName, string $participantName): string
    {
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($participantName)
            ->setTtl(3600);

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName)
            ->setCanPublish()
            ->setCanSubscribe();

        return (new AccessToken(
            config('services.livekit.api_key'),
            config('services.livekit.api_secret')
        ))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();
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
