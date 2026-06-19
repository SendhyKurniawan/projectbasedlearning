<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use App\Services\JitsiTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Controller titik masuk SSO Jitsi: menangani pengunjung tanpa token (mis. dari link "Share")
// agar tetap terautentikasi, terotorisasi sesuai peran, lalu dibalikkan ke Jitsi membawa JWT.
class ConferenceJoinController extends Controller
{
    // Inject service pembuat token Jitsi.
    public function __construct(private JitsiTokenService $jitsi)
    {
    }

    /**
     * Target SSO untuk `config.tokenAuthUrl` milik Jitsi.
     *
     * Pengunjung tanpa token (mis. yang membuka link "Share" di dalam ruangan Jitsi)
     * diarahkan ke sini oleh Jitsi dengan `?room={room}`. Karena route ini di balik
     * middleware `auth`, pengunjung yang belum login akan mampir ke halaman login PBL
     * lalu dikembalikan ke sini (redirect intended() Laravel). Setelah itu kita verifikasi
     * apakah user memang boleh masuk ruangan ini — memakai aturan yang sama dengan
     * controller room per-peran — lalu mint JWT per-user dan pantulkan kembali ke Jitsi
     * dengan token tersebut. Flag moderator mengikuti aturan room in-app:
     * admin/dosen pengampu = moderator, mahasiswa terdaftar = bukan.
     */
    public function jitsiAuth(Request $request): RedirectResponse
    {
        $room = (string) $request->query('room');
        abort_if($room === '', 400, 'Parameter room tidak ada.');

        $conference = Conference::where('room_name', $room)->first();
        abort_unless($conference, 404, 'Konferensi tidak ditemukan.');
        abort_if($conference->isEnded(), 410, 'Sesi konferensi sudah berakhir.');

        $user = auth()->user();

        // Otorisasi + tentukan status moderator, selaras dengan controller room per-peran.
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

        // Pantulkan kembali ke ruangan Jitsi membawa token yang baru dibuat. Pakai
        // room_name kanonis (bukan nilai query mentah) agar klaim `room` di token
        // selalu cocok dengan ruangan yang dimasuki.
        $domain = config('services.jitsi.domain');

        return redirect()->away("https://{$domain}/{$conference->room_name}?jwt={$jwt}");
    }
}
