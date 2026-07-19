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

        // Simpan dengan content encoding modern (RFC 8291). Tanpa ini, kolom
        // content_encoding tersimpan NULL dan minishlink/web-push (1) melempar
        // "Subscription should have a content encoding" saat payload ada, dan
        // (2) melewati header Authorization VAPID → FCM balas 401.
        $user->updatePushSubscription($endpoint, $key, $token, 'aes128gcm');

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
