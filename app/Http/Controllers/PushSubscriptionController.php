<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PushSubscriptionController extends Controller
{
    use ValidatesRequests;

    /**
     * Update user's subscription to push notifications.
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

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true], 200);
    }

    /**
     * Delete user's push notification subscription.
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
