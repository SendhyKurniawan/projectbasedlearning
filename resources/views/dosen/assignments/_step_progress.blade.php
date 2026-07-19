{{-- Partial: progres & penilaian step tugas ber-step (dipakai di halaman Review Submissions dosen).
     Variabel: $assignment, $steps (with submissions.mahasiswa, submissions.group). --}}
@php $isPerStep = $assignment->step_grading_mode === 'per_step'; @endphp

<div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-headline text-xl font-extrabold text-on-surface">Progres Step</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">
                {{ $steps->count() }} step · {{ $isPerStep ? 'Dinilai per step (nilai akhir = akumulasi)' : 'Nilai akhir dari pengumpulan tugas akhir' }}
            </p>
        </div>
        <a href="{{ route('dosen.assignments.steps.index', $assignment) }}" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
            <span class="material-symbols-outlined text-sm">stairs</span> Kelola Step
        </a>
    </div>

    @foreach($steps as $step)
        <div class="border border-outline-variant/20 rounded-xl overflow-hidden" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-3 p-4 hover:bg-surface-container-low/50 transition text-left">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-primary/10 text-primary font-black text-xs flex items-center justify-center">{{ $step->step_number }}</span>
                    <span class="font-bold text-sm text-on-surface truncate">{{ $step->title }}</span>
                    @if($isPerStep)
                        <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded bg-primary/10 text-primary whitespace-nowrap">Bobot {{ $step->max_score }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-xs font-bold text-on-surface-variant">{{ $step->submissions->count() }} pengumpulan</span>
                    <span class="material-symbols-outlined text-base text-on-surface-variant" x-text="open ? 'expand_less' : 'expand_more'">expand_more</span>
                </div>
            </button>

            <div x-show="open" x-cloak class="border-t border-outline-variant/20 divide-y divide-outline-variant/10">
                @if($step->submissions->isEmpty())
                    <p class="p-4 text-sm text-on-surface-variant italic">Belum ada pengumpulan untuk step ini.</p>
                @elseif($assignment->is_group)
                    {{-- Kelompok: satu blok per kelompok (baris step_submissions per anggota berbagi berkas). --}}
                    @foreach($step->submissions->groupBy('group_id') as $groupSubs)
                        @php
                            $group = $groupSubs->first()->group;
                            $sample = $groupSubs->first();
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-bold text-sm text-on-surface">{{ $group->group_name ?? 'Kelompok' }}</p>
                                    <p class="text-xs text-on-surface-variant">
                                        {{ $groupSubs->pluck('mahasiswa.name')->filter()->join(', ') }}
                                    </p>
                                </div>
                                <div class="text-xs text-on-surface-variant text-right">
                                    <p>{{ $sample->submitted_at?->format('d M Y, H:i') }}
                                    @if($sample->status === 'late')<span class="text-error font-bold">· Terlambat</span>@endif</p>
                                    @if($sample->url_link)
                                        <a href="{{ $sample->url_link }}" target="_blank" rel="noopener" class="text-primary hover:underline">Buka Link</a>
                                    @elseif($sample->file_path)
                                        <a href="{{ Storage::url($sample->file_path) }}" target="_blank" class="text-primary hover:underline">Lihat Berkas</a>
                                    @endif
                                </div>
                            </div>
                            @if($sample->notes)
                                <p class="text-xs text-on-surface-variant italic">Catatan: {{ $sample->notes }}</p>
                            @endif

                            @if($isPerStep && $group)
                                <form action="{{ route('dosen.assignments.steps.grade-group', [$step, $group]) }}" method="POST" class="bg-surface-container-low/40 rounded-lg p-3 space-y-2">
                                    @csrf
                                    @if($assignment->grading_mode === 'individual')
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nilai per Anggota (maks {{ $step->max_score }})</p>
                                        @foreach($groupSubs as $ss)
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-on-surface w-40 truncate">{{ $ss->mahasiswa->name ?? '-' }}</span>
                                                <input type="number" name="scores[{{ $ss->mahasiswa_id }}]" value="{{ $ss->score }}" min="0" max="{{ $step->max_score }}" class="w-20 text-sm border-outline-variant/30 rounded-md">
                                                <input type="text" name="feedbacks[{{ $ss->mahasiswa_id }}]" value="{{ $ss->feedback }}" placeholder="Feedback" class="flex-1 text-sm border-outline-variant/30 rounded-md">
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="flex items-center gap-2">
                                            <label class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nilai (maks {{ $step->max_score }})</label>
                                            <input type="number" name="score" value="{{ $sample->score }}" min="0" max="{{ $step->max_score }}" required class="w-20 text-sm border-outline-variant/30 rounded-md">
                                            <input type="text" name="feedback" value="{{ $sample->feedback }}" placeholder="Feedback" class="flex-1 text-sm border-outline-variant/30 rounded-md">
                                        </div>
                                    @endif
                                    <button type="submit" class="bg-primary hover:bg-primary-hover text-on-primary text-xs font-bold px-4 py-1.5 rounded-lg">Simpan Nilai Step</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                @else
                    {{-- Individu: satu baris per mahasiswa. --}}
                    @foreach($step->submissions as $ss)
                        <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-on-surface truncate">{{ $ss->mahasiswa->name ?? '-' }}</p>
                                <p class="text-xs text-on-surface-variant">
                                    {{ $ss->submitted_at?->format('d M Y, H:i') }}
                                    @if($ss->status === 'late')<span class="text-error font-bold">· Terlambat</span>@endif
                                    @if($ss->url_link)
                                        · <a href="{{ $ss->url_link }}" target="_blank" rel="noopener" class="text-primary hover:underline">Buka Link</a>
                                    @elseif($ss->file_path)
                                        · <a href="{{ Storage::url($ss->file_path) }}" target="_blank" class="text-primary hover:underline">Lihat Berkas</a>
                                    @endif
                                </p>
                                @if($ss->notes)
                                    <p class="text-xs text-on-surface-variant italic mt-1">Catatan: {{ $ss->notes }}</p>
                                @endif
                            </div>
                            @if($isPerStep)
                                <form action="{{ route('dosen.step-submissions.grade', $ss) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="score" value="{{ $ss->score }}" min="0" max="{{ $step->max_score }}" required placeholder="0-{{ $step->max_score }}" class="w-20 text-sm border-outline-variant/30 rounded-md">
                                    <input type="text" name="feedback" value="{{ $ss->feedback }}" placeholder="Feedback" class="w-40 text-sm border-outline-variant/30 rounded-md">
                                    <button type="submit" class="bg-primary hover:bg-primary-hover text-on-primary text-xs font-bold px-3 py-1.5 rounded-lg">Simpan</button>
                                </form>
                            @elseif($ss->score !== null)
                                <span class="text-sm font-black text-on-surface">{{ $ss->score }}/{{ $step->max_score ?? $assignment->max_score }}</span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>
