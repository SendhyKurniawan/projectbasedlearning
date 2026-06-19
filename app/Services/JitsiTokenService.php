<?php

namespace App\Services;

use Firebase\JWT\JWT;
use RuntimeException;

// Service pembuat token & URL ruangan untuk Jitsi self-hosted.
// Token ditandatangani HS256 dengan secret yang harus sama dengan JWT_APP_SECRET di server Jitsi.
class JitsiTokenService
{
    // Buat (mint) JWT HS256 untuk satu user pada satu ruangan. Berlaku 2 jam.
    // Flag moderator dibawa di context.user.moderator (dosen/admin true, mahasiswa false).
    public function mint(string $room, int $userId, string $name, bool $moderator, ?string $email = null): string
    {
        $appId = config('services.jitsi.jwt_app_id');
        $secret = config('services.jitsi.jwt_app_secret');
        $domain = config('services.jitsi.domain');

        // Tanpa kredensial JWT, konferensi tidak bisa berjalan — hentikan dengan error jelas.
        if (!$appId || !$secret) {
            throw new RuntimeException('Jitsi JWT credentials not configured (JITSI_JWT_APP_ID / JITSI_JWT_APP_SECRET).');
        }

        $now = time();
        $payload = [
            'aud' => $appId,        // aud = iss = jwt_app_id
            'iss' => $appId,
            'sub' => $domain,       // sub = domain server Jitsi
            'room' => $room,
            'iat' => $now,
            'nbf' => $now - 10,     // beri toleransi 10 detik untuk selisih jam
            'exp' => $now + 7200,   // masa berlaku token 2 jam
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

    // Susun URL ruangan Jitsi lengkap dengan token + override branding (nama & logo aplikasi).
    public function roomUrl(string $room, string $jwt, string $subject): string
    {
        $domain = config('services.jitsi.domain');
        $appName = config('app.name');
        $logoUrl = url('favicon.svg');
        $appUrl = config('app.url');

        $overrides = [
            'config.subject' => json_encode($subject),
            // Nama produk — memengaruhi header in-call, <title> dokumen, dan teks deep-link mobile.
            'interfaceConfig.APP_NAME' => json_encode($appName),
            'interfaceConfig.NATIVE_APP_NAME' => json_encode($appName),
            'interfaceConfig.PROVIDER_NAME' => json_encode($appName),
            // Tampilkan logo kita sebagai watermark kiri-atas (true = render elemen; src = logo kita).
            'interfaceConfig.SHOW_JITSI_WATERMARK' => 'true',
            'interfaceConfig.JITSI_WATERMARK_LINK' => json_encode($appUrl),
            'interfaceConfig.DEFAULT_LOGO_URL' => json_encode($logoUrl),
            'interfaceConfig.DEFAULT_WELCOME_PAGE_LOGO_URL' => json_encode($logoUrl),
            // Sembunyikan branding bawaan Jitsi agar hanya nama/logo kita yang tampil.
            'interfaceConfig.SHOW_BRAND_WATERMARK' => 'false',
            'interfaceConfig.SHOW_WATERMARK_FOR_GUESTS' => 'false',
            'interfaceConfig.SHOW_POWERED_BY' => 'false',
        ];

        $hash = http_build_query($overrides, '', '&', PHP_QUERY_RFC3986);

        return "https://{$domain}/{$room}?jwt={$jwt}#{$hash}";
    }
}
