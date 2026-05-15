<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class JaasTokenService
{
    public function mint(string $room, int $userId, string $name, bool $moderator, ?string $email = null): string
    {
        $appId = config('services.jitsi.app_id');
        $kid = config('services.jitsi.kid');

        if (!$appId || !$kid) {
            throw new RuntimeException('JaaS credentials not configured (JITSI_APP_ID / JITSI_KID).');
        }

        $now = time();
        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'sub' => $appId,
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
                'features' => [
                    'livestreaming' => 'false',
                    'outbound-call' => 'false',
                    'transcription' => 'false',
                    'recording' => 'false',
                ],
            ],
        ];

        return JWT::encode($payload, $this->loadPrivateKey(), 'RS256', $kid);
    }

    private function loadPrivateKey(): string
    {
        return Cache::driver('array')->rememberForever('jaas.private_key', function () {
            $path = config('services.jitsi.private_key_path');
            if (!$path) {
                throw new RuntimeException('JITSI_PRIVATE_KEY_PATH is not set.');
            }

            $absolute = base_path($path);
            if (!is_file($absolute)) {
                throw new RuntimeException("JaaS private key not found at: {$absolute}");
            }

            $contents = file_get_contents($absolute);
            if ($contents === false) {
                throw new RuntimeException("Failed to read JaaS private key at: {$absolute}");
            }

            return $contents;
        });
    }
}
