/**
 * LiveKit Service - Integration with Laravel 12 Backend
 *
 * This service handles communication with your Laravel 12 API to:
 * 1. Generate LiveKit tokens (required for joining rooms)
 * 2. Create/manage meeting rooms
 * 3. Handle meeting authentication
 *
 * Laravel 12 Backend Setup:
 * -------------------------
 * 1. Install LiveKit PHP SDK: composer require agence104/livekit-server-sdk
 * 2. Add to .env:
 *    LIVEKIT_URL=wss://your-livekit-server.livekit.cloud
 *    LIVEKIT_API_KEY=your_api_key
 *    LIVEKIT_API_SECRET=your_api_secret
 *
 * 3. Create route in routes/api.php:
 *    Route::post('/meeting/token', [MeetingController::class, 'getToken'])->middleware('auth:sanctum');
 *    Route::post('/meeting/create', [MeetingController::class, 'create'])->middleware('auth:sanctum');
 *
 * 4. MeetingController@getToken example:
 *    $accessToken = new AccessToken(env('LIVEKIT_API_KEY'), env('LIVEKIT_API_SECRET'));
 *    $accessToken->setIdentity($request->user()->name);
 *    $grant = new VideoGrant();
 *    $grant->setRoomJoin(true);
 *    $grant->setRoomName($request->room_name);
 *    $accessToken->addGrant($grant);
 *    return response()->json(['token' => $accessToken->toJwt(), 'url' => env('LIVEKIT_URL')]);
 */

// Replace with your actual Laravel 12 API URL
const LARAVEL_API_URL = "http://localhost:8000/api";

export interface TokenResponse {
  token: string;
  url: string; // LiveKit WebSocket URL (e.g., wss://your-server.livekit.cloud)
}

export interface CreateRoomResponse {
  room_id: string;
  room_name: string;
  meeting_url: string;
}

/**
 * Get LiveKit token from Laravel 12 backend
 * POST /api/meeting/token
 */
export async function getLiveKitToken(
  roomName: string,
  participantName: string,
  authToken?: string
): Promise<TokenResponse> {
  const response = await fetch(`${LARAVEL_API_URL}/meeting/token`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      // Authorization: `Bearer ${authToken}`, // Uncomment when using Laravel Sanctum
    },
    body: JSON.stringify({
      room_name: roomName,
      participant_name: participantName,
    }),
  });

  if (!response.ok) {
    throw new Error(`Failed to get token: ${response.statusText}`);
  }

  return response.json();
}

/**
 * Create a new meeting room via Laravel 12
 * POST /api/meeting/create
 */
export async function createMeeting(
  meetingName: string,
  authToken?: string
): Promise<CreateRoomResponse> {
  const response = await fetch(`${LARAVEL_API_URL}/meeting/create`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      // Authorization: `Bearer ${authToken}`,
    },
    body: JSON.stringify({ meeting_name: meetingName }),
  });

  if (!response.ok) {
    throw new Error(`Failed to create meeting: ${response.statusText}`);
  }

  return response.json();
}

/**
 * Generate a random meeting room ID
 */
export function generateRoomId(): string {
  const chars = "abcdefghijklmnopqrstuvwxyz";
  const seg = (n: number) =>
    Array.from({ length: n }, () =>
      chars[Math.floor(Math.random() * chars.length)]
    ).join("");
  return `${seg(3)}-${seg(4)}-${seg(3)}`;
}
