{{-- Metadata SEO terpusat untuk semua layout (app & guest).
     Override per-halaman dengan menyetel variabel sebelum @include:
       @include('layouts.partials.seo', [
         'seoTitle'       => 'Judul Spesifik',
         'seoDescription' => 'Deskripsi halaman ini.',
         'seoNoindex'     => true,   // true untuk halaman privat/ber-login
       ])
--}}
@php
    $appName        = config('app.name', 'PBL Workspace');
    $seoTitle       = $seoTitle ?? $appName;
    $fullTitle      = $seoTitle === $appName ? $appName : ($seoTitle . ' — ' . $appName);
    $seoDescription = $seoDescription ?? 'PBL Workspace — platform pembelajaran berbasis proyek (Project-Based Learning) untuk dosen dan mahasiswa. Kelola mata kuliah, tugas, kuis, materi, kelas virtual, dan kolaborasi akademik dalam satu tempat.';
    $seoNoindex     = $seoNoindex ?? false;
    $seoUrl         = url()->current();
    $seoImage       = asset('og-image.png');
    // Normalisasi kode locale Laravel (mis. "id") ke format Open Graph (mis. "id_ID").
    $ogLocaleMap    = ['id' => 'id_ID', 'en' => 'en_US'];
    $localeRaw      = app()->getLocale();
    $seoLocale      = $ogLocaleMap[$localeRaw] ?? (str_contains($localeRaw, '_') ? $localeRaw : 'id_ID');

    // JSON-LD dibangun di blok @php agar Blade tidak salah menafsirkan kunci
    // "@context"/"@type" sebagai direktif Blade.
    $jsonLd = $seoNoindex ? null : json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'EducationalOrganization',
        'name'        => $appName,
        'url'         => config('app.url'),
        'logo'        => asset('favicon.svg'),
        'description' => $seoDescription,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="author" content="{{ $appName }}">
<meta name="theme-color" content="#004ac6">
<link rel="canonical" href="{{ $seoUrl }}">

@if ($seoNoindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif

{{-- Open Graph --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">

@if ($jsonLd)
{{-- Structured data: dikeluarkan hanya untuk halaman publik (indeksabel). --}}
<script type="application/ld+json">
{!! $jsonLd !!}
</script>
@endif
