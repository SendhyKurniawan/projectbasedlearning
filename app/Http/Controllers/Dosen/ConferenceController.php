<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Course;
use App\Models\User;
use App\Notifications\AcademicUpdateNotification;
use App\Services\JitsiTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class ConferenceController extends Controller
{
    public function __construct(private JitsiTokenService $jitsi)
    {
    }

    public function index(Course $course)
    {
        $this->authorizeDosen($course);

        $activeConferences = $course->conferences()
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('scheduled_at')
            ->get();

        $endedConferences = $course->conferences()
            ->where('status', 'ended')
            ->orderByDesc('scheduled_at')
            ->paginate(10);

        $siblings = $course->siblings();

        return view('dosen.conferences.index', compact('course', 'activeConferences', 'endedConferences', 'siblings'));
    }

    public function create(Course $course)
    {
        $this->authorizeDosen($course);
        $siblings = $course->siblings();
        return view('dosen.conferences.create', compact('course', 'siblings'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeDosen($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'sibling_ids' => 'nullable|array',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids ?? [])
            ->map(fn($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        $conference = $course->conferences()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'dosen_id' => auth()->id(),
            'room_name' => 'room-' . $course->id . '-' . Str::uuid(),
            'status' => 'scheduled',
        ]);

        $students = User::whereHas('enrollments', fn($q) => $q->where('course_id', $course->id))->get();
        if ($students->isNotEmpty()) {
            Notification::send($students, new AcademicUpdateNotification(
                'Jadwal Kelas Virtual Baru',
                "Kelas virtual '{$conference->title}' telah dijadwalkan pada mata kuliah {$course->nama_matkul}.",
                route('mahasiswa.conferences.index', $course)
            ));
        }

        $targetCourses = $targetIds->isNotEmpty() ? Course::whereIn('id', $targetIds)->get() : collect();
        foreach ($targetCourses as $sibling) {
            $sibConf = $sibling->conferences()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'scheduled_at' => $validated['scheduled_at'],
                'dosen_id' => auth()->id(),
                'room_name' => 'room-' . $sibling->id . '-' . Str::uuid(),
                'status' => 'scheduled',
            ]);
            $sibStudents = User::whereHas('enrollments', fn($q) => $q->where('course_id', $sibling->id))->get();
            if ($sibStudents->isNotEmpty()) {
                Notification::send($sibStudents, new AcademicUpdateNotification(
                    'Jadwal Kelas Virtual Baru',
                    "Kelas virtual '{$sibConf->title}' telah dijadwalkan pada mata kuliah {$sibling->nama_matkul}.",
                    route('mahasiswa.conferences.index', $sibling)
                ));
            }
        }

        $msg = 'Jadwal konferensi berhasil dibuat.';
        if ($targetIds->count()) {
            $msg .= " Disalin ke {$targetIds->count()} kelas lain.";
        }

        return redirect()
            ->route('dosen.conferences.index', $course)
            ->with('success', $msg);
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

    public function copy(Request $request, Conference $conference)
    {
        $course = $conference->course;
        $this->authorizeDosen($course);

        $request->validate([
            'sibling_ids' => 'required|array|min:1',
            'sibling_ids.*' => 'integer|exists:courses,id',
        ]);

        $allowedSiblingIds = $course->siblings()->pluck('id');
        $targetIds = collect($request->sibling_ids)
            ->map(fn($id) => (int) $id)
            ->intersect($allowedSiblingIds);

        if ($targetIds->isEmpty()) {
            return back()->with('error', 'Pilih kelas tujuan yang valid.');
        }

        $targetCourses = Course::whereIn('id', $targetIds)->get();
        foreach ($targetCourses as $sibling) {
            $sibling->conferences()->create([
                'title'        => $conference->title,
                'description'  => $conference->description,
                'scheduled_at' => $conference->scheduled_at,
                'dosen_id'     => auth()->id(),
                'room_name'    => 'room-' . $sibling->id . '-' . Str::uuid(),
                'status'       => 'scheduled',
            ]);
        }

        return back()->with('success', "Jadwal '{$conference->title}' disalin ke {$targetIds->count()} kelas lain.");
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

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'ok']);
        }

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

        $user = auth()->user();
        $jwt = $this->jitsi->mint(
            room: $conference->room_name,
            userId: $user->id,
            name: $user->name,
            moderator: true,
            email: $user->email,
        );
        $meetUrl = $this->jitsi->roomUrl($conference->room_name, $jwt, $conference->title);

        return view('dosen.conferences.room', compact('conference', 'meetUrl'));
    }

    private function authorizeDosen(Course $course): void
    {
        if ($course->dosen_id !== auth()->id()) {
            abort(403);
        }
    }
}
