@php
    $hints = data_get($assignment->exercise_config, 'hints', []) ?? [];
    $requiredKeywords = data_get($assignment->exercise_config, 'required_keywords', []) ?? [];
@endphp

<div class="px-5 py-6 space-y-6">
    {{-- Header --}}
    <div>
        <span class="text-[10px] font-black uppercase text-primary tracking-widest block mb-1.5">
            {{ $assignment->course->nama_matkul }}
        </span>
        <h2 class="font-headline text-xl font-extrabold tracking-tight text-on-surface leading-snug">
            {{ $assignment->title }}
        </h2>
    </div>

    {{-- Meta strip --}}
    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold">
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-primary/10 text-primary uppercase tracking-wider">
            <span class="material-symbols-outlined text-[14px]">code</span>
            {{ $languageLabel ?: $language }}
        </span>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-tertiary/10 text-tertiary uppercase tracking-wider">
            <span class="material-symbols-outlined text-[14px]">military_tech</span>
            {{ $assignment->max_score }} pts
        </span>
        @if($assignment->deadline)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-surface-container text-on-surface-variant uppercase tracking-wider">
                <span class="material-symbols-outlined text-[14px]">timer</span>
                {{ $assignment->deadline->format('d M Y, H:i') }}
            </span>
        @endif
    </div>

    {{-- Description --}}
    @if($assignment->description)
        <div>
            <h3 class="text-[10px] font-black uppercase text-on-surface-variant tracking-widest mb-2 opacity-70">
                Instruksi
            </h3>
            <div class="text-sm text-on-surface leading-relaxed">
                {!! nl2br(e($assignment->description)) !!}
            </div>
        </div>
    @endif

    {{-- Hints --}}
    @if(!empty($hints))
        <div>
            <h3 class="text-[10px] font-black uppercase text-on-surface-variant tracking-widest mb-2 opacity-70">
                Petunjuk
            </h3>
            <ul class="space-y-2">
                @foreach($hints as $hint)
                    <li class="flex items-start gap-2.5 p-3 rounded-xl bg-warning-light/30 border border-warning/20">
                        <span class="material-symbols-outlined text-warning text-[18px] shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1;">lightbulb</span>
                        <span class="text-xs text-on-surface leading-relaxed">{{ $hint }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Required keywords --}}
    @if(!empty($requiredKeywords))
        <div>
            <h3 class="text-[10px] font-black uppercase text-on-surface-variant tracking-widest mb-2 opacity-70">
                Elemen Wajib
            </h3>
            <div class="flex flex-wrap gap-1.5">
                @foreach($requiredKeywords as $keyword)
                    <code class="px-2 py-1 rounded-md bg-surface-container text-[11px] font-mono text-on-surface border border-outline-variant/30">{{ $keyword }}</code>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Submission status --}}
    @if($existing)
        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-[10px] font-black uppercase text-on-surface-variant tracking-widest opacity-70">
                    Status Pengumpulan
                </h3>
                @php
                    $statusLabel = match($existing->status) {
                        'graded'    => ['Dinilai',  'bg-secondary-container text-secondary'],
                        'submitted' => ['Terkirim', 'bg-primary/10 text-primary'],
                        default     => [ucfirst($existing->status ?? 'Draft'), 'bg-surface-container text-on-surface-variant'],
                    };
                @endphp
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $statusLabel[1] }}">
                    {{ $statusLabel[0] }}
                </span>
            </div>

            @if($existing->submitted_at)
                <p class="text-[11px] text-on-surface-variant">
                    Dikirim {{ $existing->submitted_at->diffForHumans() }} · {{ $existing->submitted_at->format('d M Y, H:i') }}
                </p>
            @endif

            @if(!is_null($existing->score))
                <div class="flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-tertiary text-[18px]" style="font-variation-settings:'FILL' 1;">grade</span>
                    <span class="font-bold text-on-surface">{{ $existing->score }} / {{ $assignment->max_score }}</span>
                </div>
            @endif

            @if(!empty($existing->validation_result['feedback']))
                <div class="pt-2 border-t border-outline-variant/15">
                    <p class="text-[10px] font-bold uppercase text-on-surface-variant tracking-widest mb-1.5 opacity-70">
                        Validasi Mesin (hint, bukan nilai akhir)
                    </p>
                    <p class="text-xs text-on-surface-variant whitespace-pre-line leading-relaxed">{{ $existing->validation_result['feedback'] }}</p>
                </div>
            @endif

            @if($existing->feedback)
                <div class="pt-2 border-t border-outline-variant/15">
                    <p class="text-[10px] font-bold uppercase text-on-surface-variant tracking-widest mb-1.5 opacity-70">
                        Catatan Dosen
                    </p>
                    <p class="text-xs text-on-surface leading-relaxed whitespace-pre-line">{{ $existing->feedback }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
