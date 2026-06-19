<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

// Controller langganan Web Push: daftarkan/hapus subscription browser milik user (dipanggil via JS).
class PushSubscriptionController extends Controller
{
    use ValidatesRequests;

    /**
     * Simpan/perbarui langganan push notification milik user (endpoint + kunci dari browser).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required',
            'keys.auth' => 'required',
            'keys.p256dh' => 'required'
        ]);

        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];

        $user = $request->user();

        // Pastikan user sudah terautentikasi.
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true], 200);
    }

    /**
     * Hapus langganan push notification milik user (mis. saat menonaktifkan notifikasi).
     */
    public function destroy(Request $request)
    {
        $this->validate($request, ['endpoint' => 'required']);

        $user = $request->user();

        if ($user) {
            $user->deletePushSubscription($request->endpoint);
        }

        return response()->json(['success' => true], 200);
    }
}
