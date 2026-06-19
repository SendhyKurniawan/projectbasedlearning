{{-- Partial daftar pengumpulan kelompok pada halaman submission tugas (dosen). --}}
@if($groups->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm">
        <span class="material-symbols-outlined text-[80px] text-primary/20 mb-4">groups</span>
        <h3 class="text-xl font-bold font-headline text-on-surface">Belum Ada Kelompok</h3>
        <p class="text-on-surface-variant mt-2 text-sm max-w-sm text-center">Belum ada mahasiswa yang membentuk kelompok dan mengumpulkan tugas ini.</p>
    </div>
@else
    <div x-data="{ activeGroup: {{ $groups->first()->id }} }" class="flex flex-col lg:flex-row gap-6 lg:h-[calc(100vh-160px)] lg:min-h-[600px] mb-8">

        <!-- Sidebar -->
        <div class="w-full lg:w-[350px] shrink-0 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl flex flex-col shadow-sm overflow-hidden h-[400px] lg:h-full">
            <div class="p-4 border-b border-outline-variant/20 bg-surface-container-low/50">
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Daftar Kelompok</p>
                <p class="text-sm font-black text-on-surface mt-1">{{ $groups->count() }} Kelompok</p>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-surface">
                @foreach($groups as $group)
                    @php
                        $totalScored = $group->submissions->whereNotNull('score')->count();
                        $totalMembers = $group->submissions->count();
                        $allGraded = $totalMembers > 0 && $totalScored === $totalMembers;
                    @endphp
                    <button @click="activeGroup = {{ $group->id }}"
                            :class="{'bg-primary/10 border-primary shadow-sm': activeGroup === {{ $group->id }}, 'bg-surface-container-lowest border-outline-variant/20 hover:border-outline-variant/60 hover:bg-surface-container-low': activeGroup !== {{ $group->id }}}"
                            class="w-full text-left p-4 rounded-xl border transition-all group flex gap-3 relative overflow-hidden">

                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-tertiary text-white flex items-center justify-center font-bold font-headline shrink-0 uppercase shadow-inner">
                            <span class="material-symbols-outlined text-[20px]">groups</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm text-on-surface truncate group-hover:text-primary transition-colors">
                                {{ $group->group_name }}
                            </h4>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs text-on-surface-variant truncate">
                                    {{ $totalMembers }} anggota
                                </p>
                                @if($allGraded)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-secondary-container text-secondary">
                                        GRADED
                                    </span>
                                @elseif($totalScored > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-primary-container text-on-primary">
                                        PARTIAL
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-warning-light text-on-warning">
                                        UNGRADED
                                    </span>
                                @endif
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Right Pane -->
        <div class="flex-1 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl flex flex-col shadow-sm relative lg:h-full lg:overflow-hidden min-h-[600px]">
            @foreach($groups as $group)
                @php
                    $sample = $group->submissions->first();
                @endphp
                <div x-show="activeGroup === {{ $group->id }}" style="display: none;" class="h-full flex flex-col">

                    <!-- Header -->
                    <div class="p-6 border-b border-outline-variant/20 shrink-0 bg-surface-container-low/30">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-extrabold font-headline text-on-surface mb-1">{{ $group->group_name }}</h3>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-on-surface-variant font-medium">
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">person</span> Pembuat: {{ $group->creator->name ?? '-' }}</span>
                                    @if($sample)
                                        <span class="text-outline-variant">•</span>
                                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">schedule</span> Dikumpulkan {{ $sample->submitted_at?->format('d M Y, H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-surface-container border border-outline-variant/30 px-4 py-2 rounded-xl text-center shadow-inner">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant leading-tight">Mode Penilaian</p>
                                <p class="text-sm font-black font-headline mt-1 text-on-surface">{{ $assignment->grading_mode === 'individual' ? 'Per Individu' : 'Sama Rata' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto p-6 bg-surface space-y-6">

                        <!-- Members -->
                        <div>
                            <h4 class="text-sm font-bold font-headline text-on-surface mb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
                                Anggota Kelompok ({{ $group->submissions->count() }})
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($group->submissions as $sub)
                                    <div class="flex items-center justify-between bg-surface-container-low rounded-xl px-4 py-3 border border-outline-variant/20">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-tertiary text-white flex items-center justify-center font-bold uppercase">
                                                {{ substr($sub->mahasiswa->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-on-surface">{{ $sub->mahasiswa->name ?? '-' }}</p>
                                                <p class="text-xs text-on-surface-variant">{{ $sub->mahasiswa->nim ?? $sub->mahasiswa->email ?? '-' }}</p>
                                            </div>
                                        </div>
                                        @if($group->created_by_mahasiswa_id === $sub->mahasiswa_id)
                                            <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary/10 px-2 py-1 rounded-full">Pembuat</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($sample && $sample->notes)
                            <div class="bg-primary-container/30 border-l-4 border-primary p-5 rounded-r-xl shadow-sm">
                                <h5 class="text-xs font-bold text-primary uppercase tracking-widest mb-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">format_quote</span> Catatan Kelompok
                                </h5>
                                <p class="text-sm text-on-surface leading-relaxed font-medium">{{ $sample->notes }}</p>
                            </div>
                        @endif

                        @if($sample)
                            @include('dosen.assignments._submission_attachment', ['item' => $sample])
                        @endif

                    </div>

                    <!-- Grade Form -->
                    <div class="p-6 bg-surface-container-lowest border-t border-outline-variant/30 shrink-0">
                        <form action="{{ route('dosen.groups.grade', $group) }}" method="POST">
                            @csrf

                            @if($assignment->grading_mode === 'equal')
                                @php
                                    $sharedScore = $group->submissions->pluck('score')->filter()->first();
                                    $sharedFeedback = $group->submissions->pluck('feedback')->filter()->first();
                                @endphp
                                <div class="bg-primary/5 border border-primary/20 rounded-xl p-3 mb-4 text-xs font-bold text-primary flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">info</span>
                                    Mode Sama Rata: nilai akan diterapkan ke seluruh anggota kelompok.
                                </div>
                                <div class="flex flex-col md:flex-row gap-4 items-end">
                                    <div class="w-full md:w-32 shrink-0">
                                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Nilai (Maks: {{ $assignment->max_score }})</label>
                                        <input type="number" name="score" value="{{ old('score', $sharedScore) }}" min="0" max="{{ $assignment->max_score }}"
                                            class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-4 py-3 font-extrabold text-on-surface text-lg focus:ring-2 focus:ring-primary shadow-inner"
                                            placeholder="0" required>
                                    </div>
                                    <div class="flex-1 w-full">
                                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-1.5">Feedback (Opsional)</label>
                                        <input type="text" name="feedback" value="{{ old('feedback', $sharedFeedback) }}"
                                            class="w-full bg-white border border-outline-variant/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary shadow-sm"
                                            placeholder="Catatan untuk semua anggota">
                                    </div>
                                    <button type="submit" class="w-full md:w-auto shrink-0 bg-primary hover:bg-primary-hover text-on-primary font-bold font-headline px-8 py-3 rounded-xl shadow-md flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[20px]">save</span>
                                        Simpan Nilai
                                    </button>
                                </div>
                            @else
                                <div class="bg-primary/5 border border-primary/20 rounded-xl p-3 mb-4 text-xs font-bold text-primary flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">info</span>
                                    Mode Per Individu: berikan nilai berbeda untuk tiap anggota.
                                </div>
                                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                                    @foreach($group->submissions as $sub)
                                        <div class="flex flex-col md:flex-row gap-3 items-end bg-surface-container-low rounded-xl p-3 border border-outline-variant/20">
                                            <div class="flex-1 w-full">
                                                <p class="text-sm font-bold text-on-surface">{{ $sub->mahasiswa->name ?? '-' }}</p>
                                                <p class="text-[10px] text-on-surface-variant">{{ $sub->mahasiswa->nim ?? $sub->mahasiswa->email ?? '-' }}</p>
                                            </div>
                                            <div class="w-full md:w-24 shrink-0">
                                                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Nilai</label>
                                                <input type="number" name="scores[{{ $sub->mahasiswa_id }}]" value="{{ old('scores.' . $sub->mahasiswa_id, $sub->score) }}" min="0" max="{{ $assignment->max_score }}"
                                                    class="w-full bg-white border border-outline-variant/30 rounded-xl px-3 py-2 font-bold text-on-surface text-sm focus:ring-2 focus:ring-primary shadow-inner"
                                                    placeholder="0">
                                            </div>
                                            <div class="flex-1 w-full">
                                                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Feedback</label>
                                                <input type="text" name="feedbacks[{{ $sub->mahasiswa_id }}]" value="{{ old('feedbacks.' . $sub->mahasiswa_id, $sub->feedback) }}"
                                                    class="w-full bg-white border border-outline-variant/30 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary shadow-sm"
                                                    placeholder="Opsional">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex justify-end mt-4">
                                    <button type="submit" class="bg-primary hover:bg-primary-hover text-on-primary font-bold font-headline px-8 py-3 rounded-xl shadow-md flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[20px]">save</span>
                                        Simpan Semua Nilai
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    </div>
@endif
