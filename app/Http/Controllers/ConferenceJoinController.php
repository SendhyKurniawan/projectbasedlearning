<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use App\Services\JitsiTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConferenceJoinController extends Controller
{
    public function __construct(private JitsiTokenService $jitsi)
    {
    }

    /**
     * SSO target for Jitsi's `config.tokenAuthUrl`.
     *
     * A tokenless visitor (e.g. someone who opened Jitsi's in-room "Share" link)
     * is redirected here by Jitsi with `?room={room}`. Because this route is
     * behind `auth`, a logged-out visitor first lands on the PBL login page and
     * is returned here afterwards (Laravel's intended() redirect). We then verify
     * the user is actually allowed in this room — using the same rules as the
     * role-specific room controllers — mint a per-user JWT, and bounce them back
     * to Jitsi with the token so they join. The moderator flag mirrors the
     * in-app rooms: admin/owning-dosen = moderator, enrolled mahasiswa = not.
     */
    public function jitsiAuth(Request $request): RedirectResponse
    {
        $room = (string) $request->query('room');
        abort_if($room === '', 400, 'Parameter room tidak ada.');

        $conference = Conference::where('room_name', $room)->first();
        abort_unless($conference, 404, 'Konferensi tidak ditemukan.');
        abort_if($conference->isEnded(), 410, 'Sesi konferensi sudah berakhir.');

        $user = auth()->user();

        // Authorize + decide moderator, matching the per-role room controllers.
        switch ($user->role) {
            case 'admin':
                $moderator = true;
                $name = $user->name . ' (Admin)';
                break;

            case 'dosen':
                abort_unless($conference->course->dosen_id === $user->id, 403, 'Anda bukan pengampu kelas ini.');
                $moderator = true;
                $name = $user->name;
                break;

            case 'mahasiswa':
                $enrolled = DB::table('enrollments')
                    ->where('mahasiswa_id', $user->id)
                    ->where('course_id', $conference->course_id)
                    ->exists();
                abort_unless($enrolled, 403, 'Anda tidak terdaftar di kelas ini.');
                abort_unless($conference->isLive(), 403, 'Sesi konferensi belum dimulai.');
                $moderator = false;
                $name = $user->name;
                break;

            default:
                abort(403);
        }

        $jwt = $this->jitsi->mint(
            room: $conference->room_name,
            userId: $user->id,
            name: $name,
            moderator: $moderator,
            email: $user->email,
        );

        // Bounce back to the Jitsi room with the freshly minted token. Use the
        // canonical room_name (not the raw query value) so the token's `room`
        // claim always matches the room being joined.
        $domain = config('services.jitsi.domain');

        return redirect()->away("https://{$domain}/{$conference->room_name}?jwt={$jwt}");
    }
}
