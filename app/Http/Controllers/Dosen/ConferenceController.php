<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Course;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class ConferenceController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeDosen($course);
        $conferences = $course->conferences()->orderByDesc('scheduled_at')->paginate(10);

        return view('dosen.conferences.index', compact('course', 'conferences'));
    }

    public function create(Course $course)
    {
        $this->authorizeDosen($course);

        return view('dosen.conferences.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeDosen($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $conference = $course->conferences()->create([
            ...$validated,
            'dosen_id' => auth()->id(),
            'room_name' => 'room-' . $course->id . '-' . Str::uuid(),
            'status' => 'scheduled',
        ]);

        // Notify enrolled students
        $students = User::whereHas('enrollments', function($q) use ($course) {
            $q->where('course_id', $course->id);
        })->get();
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                'Jadwal Kelas Virtual Baru',
                "Kelas virtual '{$conference->title}' telah dijadwalkan pada mata kuliah {$course->nama_matkul}.",
                route('mahasiswa.conferences.index', $course)
            ));
        }

        return redirect()
            ->route('dosen.conferences.index', $course)
            ->with('success', 'Jadwal konferensi berhasil dibuat.');
    }

    public function edit(Conference $conference)
    {
        $course = $conference->course;
        $this->authorizeDosen($course);

        return view('dosen.conferences.edit', compact('conference', 'course'));
    }

    public function update(Request $request, Conference $conference)
    {
        $this->authorizeDosen($conference->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',
        ]);

        $conference->update($validated);

        // Notify enrolled students
        $students = User::whereHas('enrollments', function($q) use ($conference) {
            $q->where('course_id', $conference->course_id);
        })->get();
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                'Jadwal Kelas Virtual Diperbarui',
                "Jadwal kelas virtual '{$conference->title}' pada mata kuliah {$conference->course->nama_matkul} telah diperbarui.",
                route('mahasiswa.conferences.index', $conference->course)
            ));
        }

        return redirect()
            ->route('dosen.conferences.index', $conference->course)
            ->with('success', 'Jadwal konferensi berhasil diperbarui.');
    }

    public function destroy(Conference $conference)
    {
        $course = $conference->course;
        $this->authorizeDosen($course);

        $conference->delete();

        return redirect()
            ->route('dosen.conferences.index', $course)
            ->with('success', 'Konferensi berhasil dihapus.');
    }

    public function start(Conference $conference)
    {
        $this->authorizeDosen($conference->course);

        $conference->update(['status' => 'live']);

        return redirect()
            ->route('dosen.conferences.room', $conference)
            ->with('success', 'Sesi konferensi telah dimulai.');
    }

    public function end(Conference $conference)
    {
        $this->authorizeDosen($conference->course);

        $conference->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return redirect()
            ->route('dosen.conferences.index', $conference->course)
            ->with('success', 'Sesi konferensi telah diakhiri.');
    }

    public function room(Conference $conference)
    {
        $this->authorizeDosen($conference->course);

        if ($conference->isEnded()) {
            return redirect()
                ->route('dosen.conferences.index', $conference->course)
                ->with('error', 'Sesi konferensi sudah berakhir.');
        }

        return view('dosen.conferences.room', compact('conference'));
    }

    public function token(Conference $conference)
    {
        $this->authorizeDosen($conference->course);

        $user = auth()->user();
        $token = $this->generateToken($conference->room_name, $user->name . ' (Dosen)', true);

        return response()->json(['token' => $token]);
    }

    private function generateToken(string $roomName, string $participantName, bool $canPublish = true): string
    {
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($participantName)
            ->setTtl(3600);

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName)
            ->setCanPublish($canPublish)
            ->setCanSubscribe();

        return (new AccessToken(
            config('services.livekit.api_key'),
            config('services.livekit.api_secret')
        ))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();
    }

    private function authorizeDosen(Course $course): void
    {
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
    }
}
