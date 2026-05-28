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

    public function roomUrl(string $room, string $jwt, string $subject): string
    {
        $domain = config('services.jitsi.domain');
        $appName = config('app.name');
        $logoUrl = url('favicon.svg');
        $appUrl = config('app.url');

        $overrides = [
            'config.subject' => json_encode($subject),
            'interfaceConfig.APP_NAME' => json_encode($appName),
            'interfaceConfig.NATIVE_APP_NAME' => json_encode($appName),
            'interfaceConfig.PROVIDER_NAME' => json_encode($appName),
            'interfaceConfig.SHOW_JITSI_WATERMARK' => 'false',
            'interfaceConfig.JITSI_WATERMARK_LINK' => json_encode($appUrl),
            'interfaceConfig.DEFAULT_LOGO_URL' => json_encode($logoUrl),
            'interfaceConfig.DEFAULT_WELCOME_PAGE_LOGO_URL' => json_encode($logoUrl),
        ];

        $hash = http_build_query($overrides, '', '&', PHP_QUERY_RFC3986);

        return "https://{$domain}/{$room}?jwt={$jwt}#{$hash}";
    }
}
