<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Course;
use App\Models\User;
use App\Services\NotificationService;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConferenceController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $activeConferences = $course->conferences()
            ->where('status', '!=', 'ended')
            ->orderByDesc('scheduled_at')
            ->get();

        $endedConferences = $course->conferences()
            ->where('status', 'ended')
            ->orderByDesc('ended_at')
            ->paginate(10);

        return view('dosen.conferences.index', compact('course', 'activeConferences', 'endedConferences'));
    }

    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('dosen.conferences.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

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

        $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
        $this->notifications->sendConferenceCreatedNotification($students, $conference, $course);

        return redirect()
            ->route('dosen.conferences.index', $course)
            ->with('success', 'Jadwal konferensi berhasil dibuat.');
    }

    public function edit(Conference $conference)
    {
        $this->authorize('update', $conference->course);

        $course = $conference->course;

        return view('dosen.conferences.edit', compact('conference', 'course'));
    }

    public function update(Request $request, Conference $conference)
    {
        $this->authorize('update', $conference->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $conference->update($validated);

        if ($conference->wasChanged(['title', 'scheduled_at'])) {
            $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $conference->course_id))->get();
            $this->notifications->sendConferenceUpdatedNotification($students, $conference, $conference->course);
        }

        return redirect()
            ->route('dosen.conferences.index', $conference->course)
            ->with('success', 'Jadwal konferensi berhasil diperbarui.');
    }

    public function destroy(Conference $conference)
    {
        $course = $conference->course;
        $this->authorize('update', $course);

        $conference->delete();

        return redirect()
            ->route('dosen.conferences.index', $course)
            ->with('success', 'Konferensi berhasil dihapus.');
    }

    public function start(Conference $conference)
    {
        $this->authorize('update', $conference->course);

        $conference->update(['status' => 'live']);

        return redirect()
            ->route('dosen.conferences.room', $conference)
            ->with('success', 'Sesi konferensi telah dimulai.');
    }

    public function end(Conference $conference)
    {
        $this->authorize('update', $conference->course);

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
        $this->authorize('update', $conference->course);

        if ($conference->isEnded()) {
            return redirect()
                ->route('dosen.conferences.index', $conference->course)
                ->with('error', 'Sesi konferensi sudah berakhir.');
        }

        return view('dosen.conferences.room', compact('conference'));
    }

    public function token(Conference $conference)
    {
        $this->authorize('update', $conference->course);

        $user = auth()->user();
        $identity = $user->id . ':' . $user->name . ' (Dosen)';
        $token = $this->generateToken($conference->room_name, $identity, true);

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
}
