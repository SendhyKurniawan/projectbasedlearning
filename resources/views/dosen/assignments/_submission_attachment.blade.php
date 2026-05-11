@php
    /** @var \App\Models\Submission|null $item */
    $item = $item ?? null;
    $filePath = $item?->file_path;
    $fileExt = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : null;
    $isPdf = $fileExt === 'pdf';
    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    $fileUrl = $filePath ? Storage::url($filePath) : null;

    $urlLink = $item?->url_link;
    $embedUrl = null;
    if ($urlLink) {
        if (preg_match('~https?://drive\.google\.com/file/d/([^/?#]+)~i', $urlLink, $m)) {
            $embedUrl = "https://drive.google.com/file/d/{$m[1]}/preview";
        } elseif (preg_match('~https?://docs\.google\.com/(document|spreadsheets|presentation)/d/([^/?#]+)~i', $urlLink, $m)) {
            $embedUrl = "https://docs.google.com/{$m[1]}/d/{$m[2]}/preview";
        } elseif (preg_match('~https?://(?:www\.)?youtube\.com/watch\?v=([^&]+)~i', $urlLink, $m)) {
            $embedUrl = "https://www.youtube.com/embed/{$m[1]}";
        } elseif (preg_match('~https?://youtu\.be/([^?#]+)~i', $urlLink, $m)) {
            $embedUrl = "https://www.youtube.com/embed/{$m[1]}";
        } else {
            $embedUrl = $urlLink;
        }
    }
@endphp

@if($filePath)
    <div>
        <h4 class="text-sm font-bold font-headline text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">attach_file</span>
            Lampiran File
        </h4>
        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-3 px-5 py-3 bg-white border border-outline-variant/30 rounded-xl hover:bg-surface-container-low hover:border-primary transition-all shadow-sm group">
            <div class="w-10 h-10 rounded-lg bg-error-container text-error flex items-center justify-center">
                <span class="material-symbols-outlined">{{ $isPdf ? 'picture_as_pdf' : ($isImage ? 'image' : 'description') }}</span>
            </div>
            <div>
                <p class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Buka di Tab Baru / Unduh</p>
                <p class="text-xs text-on-surface-variant break-all truncate max-w-[200px] md:max-w-md">{{ basename($filePath) }}</p>
            </div>
        </a>

        @if($isPdf)
            <div class="mt-4 rounded-2xl overflow-hidden border border-outline-variant/20 shadow-sm bg-surface-container-low">
                <iframe src="{{ $fileUrl }}#toolbar=1&view=FitH"
                        class="w-full h-[700px] block"
                        title="Preview PDF: {{ basename($filePath) }}"
                        loading="lazy"></iframe>
            </div>
        @elseif($isImage)
            <div class="mt-4 border border-outline-variant/20 rounded-2xl overflow-hidden shadow-sm">
                <img src="{{ $fileUrl }}" class="w-full object-contain max-h-[500px] bg-surface-container-low" alt="Preview gambar: {{ basename($filePath) }}">
            </div>
        @endif
    </div>
@endif

@if($urlLink)
    <div>
        <h4 class="text-sm font-bold font-headline text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">link</span>
            Tautan / Repositori
        </h4>
        <a href="{{ $urlLink }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 px-5 py-3 bg-white border border-outline-variant/30 rounded-xl hover:bg-surface-container-low hover:border-primary transition-all shadow-sm group w-full md:w-auto">
            <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined">public</span>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Buka di Tab Baru</p>
                <p class="text-xs text-on-surface-variant truncate w-full group-hover:underline">{{ $urlLink }}</p>
            </div>
        </a>

        @if($embedUrl)
            <div class="mt-4 rounded-2xl overflow-hidden border border-outline-variant/20 shadow-sm bg-surface-container-low">
                <iframe src="{{ $embedUrl }}"
                        class="w-full h-[600px] block"
                        sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-presentation"
                        referrerpolicy="no-referrer-when-downgrade"
                        loading="lazy"
                        title="Preview tautan"
                        allow="autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
            <p class="mt-2 text-xs text-on-surface-variant flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px]">info</span>
                Jika preview kosong, situs target memblokir embed &mdash; klik <strong>Buka di Tab Baru</strong> di atas.
            </p>
        @endif
    </div>
@endif
