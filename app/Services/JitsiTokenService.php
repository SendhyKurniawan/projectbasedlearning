<?php

namespace App\Services;

use Firebase\JWT\JWT;
use RuntimeException;

class JitsiTokenService
{
    public function mint(string $room, int $userId, string $name, bool $moderator, ?string $email = null): string
    {
        $appId = config('services.jitsi.jwt_app_id');
        $secret = config('services.jitsi.jwt_app_secret');
        $domain = config('services.jitsi.domain');

        if (!$appId || !$secret) {
            throw new RuntimeException('Jitsi JWT credentials not configured (JITSI_JWT_APP_ID / JITSI_JWT_APP_SECRET).');
        }

        $now = time();
        $payload = [
            'aud' => $appId,
            'iss' => $appId,
            'sub' => $domain,
            'room' => $room,
            'iat' => $now,
            'nbf' => $now - 10,
            'exp' => $now + 7200,
            'context' => [
                'user' => [
                    'id' => (string) $userId,
                    'name' => $name,
                    'avatar' => '',
                    'email' => $email ?? '',
                    'moderator' => $moderator ? 'true' : 'false',
                ],
            ],
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }
}
