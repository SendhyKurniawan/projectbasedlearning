<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Services\JitsiTokenService;

class ConferenceController extends Controller
{
    public function __construct(private JitsiTokenService $jitsi)
    {
    }

    public function index()
    {
        $activeConferences = Conference::with(['course:id,kode_matkul,nama_matkul,dosen_id', 'course.dosen:id,name', 'dosen:id,name'])
            ->whereIn('status', ['scheduled', 'live'])
            ->orderByRaw("CASE status WHEN 'live' THEN 0 WHEN 'scheduled' THEN 1 ELSE 2 END")
            ->orderBy('scheduled_at')
            ->get();

        $endedConferences = Conference::with(['course:id,kode_matkul,nama_matkul', 'dosen:id,name'])
            ->where('status', 'ended')
            ->orderByDesc('scheduled_at')
            ->paginate(15);

        return view('admin.conferences.index', compact('activeConferences', 'endedConferences'));
    }

    public function room(Conference $conference)
    {
        if (!$conference->isLive()) {
            return redirect()
                ->route('admin.conferences.index')
                ->with('error', 'Sesi konferensi belum dimulai atau sudah berakhir.');
        }

        $user = auth()->user();
        $jwt = $this->jitsi->mint(
            room: $conference->room_name,
            userId: $user->id,
            name: $user->name . ' (Admin)',
            moderator: true,
            email: $user->email,
        );
        $meetUrl = $this->jitsi->roomUrl($conference->room_name, $jwt, $conference->title);

        return view('admin.conferences.room', compact('conference', 'meetUrl'));
    }

    public function end(Conference $conference)
    {
        $conference->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()
            ->route('admin.conferences.index')
            ->with('success', 'Sesi konferensi telah diakhiri.');
    }
}
