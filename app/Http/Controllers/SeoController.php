<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

// Menyajikan robots.txt & sitemap.xml secara dinamis agar domain selalu mengikuti
// APP_URL (config('app.url')) — tidak perlu file statis yang di-hardcode per environment.
class SeoController extends Controller
{
    /**
     * robots.txt: izinkan halaman publik, larang area ber-login, tunjuk sitemap.
     */
    public function robots(): Response
    {
        $sitemap = url('/sitemap.xml');

        $body = implode("\n", [
            'User-agent: *',
            'Allow: /$',
            'Allow: /login',
            'Allow: /register',
            'Disallow: /admin',
            'Disallow: /dosen',
            'Disallow: /mahasiswa',
            'Disallow: /profile',
            'Disallow: /notifications',
            'Disallow: /discussions',
            'Disallow: /announcements',
            'Disallow: /conferences',
            '',
            "Sitemap: {$sitemap}",
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * sitemap.xml: hanya URL publik (root, login, register).
     */
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => url('/'),         'changefreq' => 'monthly', 'priority' => '1.0'],
            ['loc' => url('/login'),    'changefreq' => 'yearly',  'priority' => '0.8'],
            ['loc' => url('/register'), 'changefreq' => 'yearly',  'priority' => '0.6'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc>' . "\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
