<x-app-layout>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Jadwal</h1>
                <p class="mt-1 text-sm text-on-surface-variant">Konferensi dan deadline tugas dari semua kelas kamu.</p>
            </div>
            <a href="{{ route('mahasiswa.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant/20 text-sm font-bold text-on-surface hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Dashboard
            </a>
        </div>

        {{-- No-deadline assignments banner --}}
        @if(isset($assignmentsByDate['no-deadline']) && $assignmentsByDate['no-deadline']->isNotEmpty())
            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-5">
                <h2 class="font-headline text-sm font-bold text-on-surface-variant uppercase tracking-widest mb-3">Tugas Tanpa Batas Waktu</h2>
                <div class="space-y-2">
                    @foreach($assignmentsByDate['no-deadline'] as $a)
                        @php $submitted = $submittedAssignmentIds->contains($a->id); @endphp
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-base text-on-surface-variant">assignment</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-on-surface truncate">{{ $a->title }}</p>
                                <p class="text-[11px] text-on-surface-variant">{{ $a->course->nama_matkul ?? '—' }} &bull; {{ ucfirst($a->type) }}</p>
                            </div>
                            @if($submitted)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-secondary/10 text-secondary">Sudah Submit</span>
                            @else
                                <a href="{{ route('mahasiswa.submissions.create', ['assignment_id' => $a->id]) }}" class="text-xs font-bold text-primary hover:underline">Buka</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Day-by-day timeline --}}
        @php
            $hasAnyEvent = false;
        @endphp
        @foreach($days as $date)
            @php
                $confs = $conferencesByDate[$date] ?? collect();
                $tasks = $assignmentsByDate[$date] ?? collect();
                if ($confs->isEmpty() && $tasks->isEmpty()) continue;
                $hasAnyEvent = true;
                $dateObj = \Carbon\Carbon::parse($date);
                $isToday = $dateObj->isToday();
                $isTomorrow = $dateObj->isTomorrow();
                $label = $isToday ? 'Hari Ini' : ($isTomorrow ? 'Besok' : $dateObj->translatedFormat('l, d M Y'));
            @endphp
            <section class="bg-surface-container-lowest rounded-2xl border {{ $isToday ? 'border-primary/30' : 'border-outline-variant/10' }} overflow-hidden">
                <header class="flex items-center gap-3 px-6 py-3 border-b {{ $isToday ? 'border-primary/20 bg-primary/5' : 'border-outline-variant/10 bg-surface-container-low/40' }}">
                    <div class="flex-1">
                        <span class="text-xs font-bold {{ $isToday ? 'text-primary' : 'text-on-surface-variant' }} uppercase tracking-widest">{{ $label }}</span>
                        @if(!$isToday && !$isTomorrow)
                            <span class="ml-2 text-[10px] text-on-surface-variant">{{ $dateObj->diffForHumans() }}</span>
                        @endif
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant">{{ $confs->count() + $tasks->count() }} event</span>
                </header>
                <div class="divide-y divide-outline-variant/10">
                    {{-- Conferences --}}
                    @foreach($confs as $conf)
                        <div class="flex items-center gap-4 px-6 py-4">
                            <div class="w-14 text-center flex-shrink-0">
                                <span class="font-mono text-sm font-bold text-on-surface">{{ $conf->scheduled_at->format('H:i') }}</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                {{ $conf->isLive() ? 'bg-secondary/10 text-secondary' : 'bg-primary/10 text-primary' }}">
                                <span class="material-symbols-outlined text-base">videocam</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-on-surface truncate">{{ $conf->title }}</p>
                                <p class="text-[11px] text-on-surface-variant truncate">
                                    {{ $conf->course->nama_matkul ?? '—' }} &bull; {{ $conf->dosen->name ?? '—' }}
                                </p>
                            </div>
                            @if($conf->isLive())
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
                                    </span>
                                    <a href="{{ route('mahasiswa.conferences.room', $conf) }}" class="text-xs font-bold text-secondary hover:underline">Masuk</a>
                                </div>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-surface-container text-on-surface-variant flex-shrink-0">Terjadwal</span>
                            @endif
                        </div>
                    @endforeach

                    {{-- Assignment deadlines --}}
                    @foreach($tasks as $a)
                        @php
                            $submitted = $submittedAssignmentIds->contains($a->id);
                            $hours = $a->deadline ? now()->diffInHours($a->deadline, false) : 9999;
                            $urgency = $hours < 0 ? 'lewat' : ($hours < 48 ? 'urgent' : ($hours < 168 ? 'soon' : 'ok'));
                            $urgencyClass = match($urgency) {
                                'lewat'  => 'bg-error/10 text-error',
                                'urgent' => 'bg-error/10 text-error',
                                'soon'   => 'bg-tertiary/10 text-tertiary',
                                default  => 'bg-surface-container text-on-surface-variant',
                            };
                            $iconColor = $submitted ? 'bg-secondary/10 text-secondary' : match($urgency) {
                                'lewat', 'urgent' => 'bg-error/10 text-error',
                                'soon'            => 'bg-tertiary/10 text-tertiary',
                                default           => 'bg-surface-container text-on-surface-variant',
                            };
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4">
                            <div class="w-14 text-center flex-shrink-0">
                                <span class="font-mono text-[11px] text-on-surface-variant">{{ $a->deadline ? $a->deadline->format('H:i') : '—' }}</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $iconColor }}">
                                <span class="material-symbols-outlined text-base">
                                    {{ $a->type === 'quiz' ? 'quiz' : ($a->type === 'exercise' ? 'code' : 'assignment') }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-on-surface truncate {{ $submitted ? 'line-through opacity-60' : '' }}">{{ $a->title }}</p>
                                <p class="text-[11px] text-on-surface-variant truncate">
                                    {{ $a->course->nama_matkul ?? '—' }} &bull; {{ ucfirst($a->type) }}
                                </p>
                            </div>
                            @if($submitted)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-secondary/10 text-secondary flex-shrink-0">Sudah Submit</span>
                            @else
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $urgencyClass }}">
                                        {{ $a->deadline ? $a->deadline->diffForHumans() : 'Tanpa batas' }}
                                    </span>
                                    <a href="{{ route('mahasiswa.submissions.create', ['assignment_id' => $a->id]) }}" class="text-xs font-bold text-primary hover:underline">Buka</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if(!$hasAnyEvent)
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 p-12 text-center">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-3 block">event_available</span>
                <p class="text-sm font-bold text-on-surface-variant">Tidak ada jadwal atau tugas mendatang.</p>
                <p class="text-xs text-on-surface-variant mt-1">Cek kembali setelah dosen menambahkan konferensi atau tugas baru.</p>
            </div>
        @endif
    </div>
</x-app-layout>
