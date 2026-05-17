{{-- Per-kelas grade table partial --}}
{{-- Variables: $course (Course model, already loaded with assignments + students.submissions) --}}

<div class="bg-surface-container-lowest">

    {{-- Kelas sub-header: export button + student/assignment counts --}}
    <div class="flex items-center justify-between px-6 py-3 border-b border-outline-variant/20 bg-surface-container-low/20">
        <div class="flex items-center gap-3 text-xs text-on-surface-variant">
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">group</span>
                <span class="font-bold">{{ $course->students->count() }}</span> mahasiswa
            </span>
            <span class="w-px h-4 bg-outline-variant/30"></span>
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">assignment</span>
                <span class="font-bold">{{ $course->assignments->count() }}</span> tugas
            </span>
        </div>
        <a href="{{ route('dosen.grades.export', $course) }}"
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant/30 bg-surface text-xs font-bold text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-[14px]">download</span>
            Ekspor CSV
        </a>
    </div>

    @if($course->students->count() > 0 && $course->assignments->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-outline-variant/20">
                <thead class="bg-surface">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest sticky left-0 bg-surface z-10 w-48 shadow-[1px_0_0_0_rgba(0,0,0,0.05)] border-r border-outline-variant/10">Mahasiswa</th>
                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest whitespace-nowrap bg-surface">Masuk</th>
                        @foreach($course->assignments as $assignment)
                            <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest bg-surface relative group" title="{{ $assignment->title }}">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">assignment</span>
                                    <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-primary transition-colors truncate max-w-[120px]">
                                        {{ Str::limit($assignment->title, 15) }}
                                    </a>
                                </div>
                                <div class="text-[9px] text-on-surface-variant/50 mt-0.5 font-mono">max: {{ $assignment->max_score }}</div>
                            </th>
                        @endforeach
                        <th scope="col" class="px-6 py-4 text-right text-[10px] font-bold text-primary uppercase tracking-widest bg-primary/5 border-l border-outline-variant/10">Rata-rata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 bg-surface-container-lowest">
                    @foreach($course->students as $student)
                        <tr class="hover:bg-surface-container-low/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-surface-container-lowest group-hover:bg-surface-container-low/50 shadow-[1px_0_0_0_rgba(0,0,0,0.05)] border-r border-outline-variant/10 transition-colors z-10">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-secondary/20">
                                        {{ substr($student->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-on-surface">{{ $student->name }}</div>
                                        <div class="text-[10px] font-mono font-medium text-on-surface-variant">{{ $student->nim }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-on-surface-variant">
                                <span class="bg-surface border border-outline-variant/30 px-2.5 py-1 rounded-md text-xs">
                                    {{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('Y') : '-' }}
                                    <span class="text-outline-variant/50 mx-1">|</span>
                                    <span class="{{ $student->pivot->enrolled_at && \Carbon\Carbon::parse($student->pivot->enrolled_at)->month > 6 ? 'text-primary' : 'text-warning' }}">
                                        {{ $student->pivot->enrolled_at && \Carbon\Carbon::parse($student->pivot->enrolled_at)->month > 6 ? 'Ganjil' : 'Genap' }}
                                    </span>
                                </span>
                            </td>

                            @php $totalScore = 0; $count = 0; @endphp
                            @foreach($course->assignments as $assignment)
                                @php
                                    $submission = $student->submissions->where('assignment_id', $assignment->id)->first();
                                    $score = $submission ? $submission->score : null;
                                    if ($score !== null) { $totalScore += $score; $count++; }
                                @endphp

                                {{-- Inline-editable grade cell --}}
                                <td x-data="gradeCell({{ $assignment->id }}, {{ $student->id }}, {{ $score ?? 'null' }}, {{ $assignment->max_score }})"
                                    @click.outside="editing = false"
                                    class="px-3 py-4 whitespace-nowrap">
                                    <template x-if="!editing">
                                        <button @click="editing = true"
                                                class="min-w-[3rem] px-2.5 py-1 rounded-lg text-sm font-extrabold transition-all border cursor-pointer"
                                                :class="display !== '–'
                                                    ? 'bg-secondary-container text-secondary border-secondary/20 hover:border-secondary/50'
                                                    : 'text-outline-variant/50 border-transparent hover:border-outline-variant/30 hover:bg-surface-container'"
                                                x-text="display">
                                        </button>
                                    </template>
                                    <template x-if="editing">
                                        <div class="flex flex-col gap-1">
                                            <input type="number"
                                                   x-model.number="value"
                                                   :max="max"
                                                   min="0"
                                                   @keydown.enter.prevent="save()"
                                                   @keydown.escape="editing = false"
                                                   x-ref="input"
                                                   x-init="$nextTick(() => $refs.input?.focus())"
                                                   class="w-20 px-2 py-1 rounded-lg border border-primary text-sm font-bold text-on-surface bg-surface focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                                            <span x-show="error" x-text="error" class="text-[10px] text-error font-bold"></span>
                                        </div>
                                    </template>
                                </td>
                            @endforeach

                            <td class="px-6 py-4 whitespace-nowrap text-right bg-primary/5 border-l border-outline-variant/10">
                                @if($count > 0)
                                    @php $avg = $totalScore / $count; @endphp
                                    <span class="inline-flex items-center justify-center min-w-[3.5rem] px-3 py-1.5 {{ $avg >= 80 ? 'bg-primary text-on-primary shadow-md' : ($avg >= 60 ? 'bg-warning-light text-warning border border-warning/30' : 'bg-error-container text-on-error-container border border-error/30') }} rounded-xl text-sm font-extrabold">
                                        {{ number_format($avg, 1) }}
                                    </span>
                                @else
                                    <span class="text-outline-variant/50 font-bold px-2">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($course->assignments->count() == 0)
        <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[32px] text-on-surface-variant/50">assignment_late</span>
            </div>
            <p class="text-sm font-bold text-on-surface">Belum ada tugas</p>
            <p class="text-xs text-on-surface-variant mt-1">Tidak ada tugas yang terdaftar untuk kelas ini.</p>
        </div>
    @else
        <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[32px] text-on-surface-variant/50">search_off</span>
            </div>
            <p class="text-sm font-bold text-on-surface">Tidak ada hasil</p>
            <p class="text-xs text-on-surface-variant mt-1">Tidak ada mahasiswa yang cocok dengan filter pencarian pada kelas ini.</p>
        </div>
    @endif
</div>

@once
@push('scripts')
<script>
(function () {
    // Build the quickGrade URL template once
    const urlTemplate = @json(route('dosen.grades.quickGrade', ['assignment' => '__A__', 'mahasiswa' => '__M__']));
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    window.gradeCell = function (assignmentId, studentId, initialScore, max) {
        return {
            editing:  false,
            value:    initialScore,
            max:      max,
            error:    '',
            get display() {
                return this.value !== null && this.value !== undefined ? String(this.value) : '–';
            },
            save() {
                if (this.value === null || this.value === undefined || this.value === '') {
                    this.error = 'Isi nilai';
                    return;
                }
                if (this.value < 0 || this.value > this.max) {
                    this.error = `0 – ${this.max}`;
                    return;
                }
                this.error = '';
                const url  = urlTemplate.replace('__A__', assignmentId).replace('__M__', studentId);
                fetch(url, {
                    method:  'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ score: this.value }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        this.value   = data.score;
                        this.editing = false;
                    } else {
                        this.error = data.message ?? 'Gagal menyimpan';
                    }
                })
                .catch(() => { this.error = 'Network error'; });
            },
        };
    };
})();
</script>
@endpush
@endonce
