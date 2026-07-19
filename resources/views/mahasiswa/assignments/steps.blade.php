{{-- Halaman stepper tugas ber-step (mahasiswa): progres step + form pengumpulan step aktif. --}}
<x-app-layout>
 <div class="max-w-3xl mx-auto space-y-6">
 {{-- Header --}}
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('mahasiswa.courses.show', $assignment->course) }}" class="hover:text-primary transition-colors">{{ $assignment->course->nama_matkul }}</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Tugas Berjenjang</span>
 </nav>
 <h1 class="font-headline text-2xl font-extrabold tracking-tight text-on-surface">{{ $assignment->title }}</h1>
 <p class="mt-1 text-sm text-on-surface-variant">
 Progres: <span class="font-bold">{{ $mySubmissions->count() }}/{{ $steps->count() }} step</span>
 · Deadline tugas: {{ $assignment->deadline->format('d M Y H:i') }}
 @if($assignment->step_grading_mode === 'per_step')
 · <span class="font-bold">Dinilai per step</span>
 @endif
 </p>
 @if($assignment->description)
 <p class="mt-2 text-sm text-on-surface-variant whitespace-pre-line">{{ $assignment->description }}</p>
 @endif
 </div>

 @if(session('success'))
 <div class="bg-primary/10 border border-primary/20 text-on-surface text-sm p-4 rounded-xl">{{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div class="bg-error/10 border border-error/20 text-error text-sm p-4 rounded-xl">{{ session('error') }}</div>
 @endif

 {{-- Info kelompok --}}
 @if($assignment->is_group)
 <div class="bg-surface-container-lowest shadow-sm rounded-2xl p-5">
 <h2 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Kelompok</h2>
 @if($existingGroup)
 <p class="font-bold text-on-surface">{{ $existingGroup->group_name }}</p>
 <ul class="mt-2 space-y-1 text-sm text-on-surface-variant">
 @foreach($existingGroup->members as $member)
 <li class="flex items-center gap-2">
 <span class="material-symbols-outlined text-sm">person</span>
 {{ $member->mahasiswa->name ?? '-' }}
 @if($member->mahasiswa_id === $existingGroup->created_by_mahasiswa_id)
 <span class="text-[10px] font-bold uppercase tracking-widest text-primary">Ketua</span>
 @endif
 </li>
 @endforeach
 </ul>
 @else
 <p class="text-sm text-on-surface-variant">
 Tugas ini dikerjakan berkelompok{{ $assignment->max_group_size ? ' (maks ' . $assignment->max_group_size . ' orang termasuk Anda)' : '' }}.
 Pilih anggota kelompok saat mengumpulkan Step 1 — progres step berlaku untuk seluruh anggota.
 </p>
 @endif
 </div>
 @endif

 {{-- Stepper --}}
 <div class="space-y-3">
 @foreach($steps as $step)
 @php
 $submission = $mySubmissions->get($step->id);
 $isActive = $currentStep && $currentStep->id === $step->id;
 $isDone = (bool) $submission;
 $format = $step->effectiveSubmissionFormat();
 @endphp
 <div class="bg-surface-container-lowest shadow-sm rounded-2xl p-5 {{ !$isDone && !$isActive ? 'opacity-60' : '' }} {{ $isActive ? 'ring-2 ring-primary/40' : '' }}">
 <div class="flex items-start gap-3">
 <span class="flex-shrink-0 w-9 h-9 rounded-full font-black text-sm flex items-center justify-center
 {{ $isDone ? 'bg-primary text-on-primary' : ($isActive ? 'bg-primary/10 text-primary' : 'bg-surface-container-low text-on-surface-variant') }}">
 @if($isDone)
 <span class="material-symbols-outlined text-lg">check</span>
 @elseif(!$isActive)
 <span class="material-symbols-outlined text-lg">lock</span>
 @else
 {{ $step->step_number }}
 @endif
 </span>
 <div class="flex-1 min-w-0">
 <div class="flex flex-wrap items-center gap-2">
 <h3 class="font-bold text-on-surface">Step {{ $step->step_number }}: {{ $step->title }}</h3>
 @if($step->deadline)
 <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded {{ now()->greaterThan($step->deadline) && !$isDone ? 'bg-error/10 text-error' : 'bg-surface-container-low text-on-surface-variant' }}">
 Deadline: {{ $step->deadline->format('d M Y H:i') }}
 </span>
 @endif
 @if($submission && $submission->status === 'late')
 <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded bg-error/10 text-error">Terlambat</span>
 @endif
 </div>
 @if($step->description)
 <p class="text-sm text-on-surface-variant mt-1 whitespace-pre-line">{{ $step->description }}</p>
 @endif

 {{-- Step selesai: tampilkan hasil + nilai --}}
 @if($isDone)
 <div class="mt-3 text-sm text-on-surface-variant space-y-1">
 <p class="flex items-center gap-1">
 <span class="material-symbols-outlined text-sm">schedule</span>
 Dikumpulkan {{ $submission->submitted_at?->format('d M Y H:i') }}
 </p>
 @if($submission->url_link)
 <p><a href="{{ $submission->url_link }}" target="_blank" rel="noopener" class="text-primary hover:underline break-all">{{ $submission->url_link }}</a></p>
 @endif
 @if($submission->file_path)
 <p><a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="text-primary hover:underline">Lihat berkas</a></p>
 @endif
 @if($assignment->step_grading_mode === 'per_step')
 @if($submission->score !== null)
 <p class="font-bold text-on-surface">Nilai: {{ $submission->score }}/{{ $step->max_score }}</p>
 @if($submission->feedback)
 <p class="italic">Feedback: {{ $submission->feedback }}</p>
 @endif
 @else
 <p class="italic">Belum dinilai dosen.</p>
 @endif
 @endif
 </div>
 @endif

 {{-- Step aktif: form pengumpulan --}}
 @if($isActive)
 <form action="{{ route('mahasiswa.assignments.steps.submit', [$assignment, $step]) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
 @csrf
 @if($assignment->is_group && !$existingGroup)
 <div class="border border-outline-variant/30 rounded-xl p-4 space-y-3">
 <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Bentuk Kelompok</p>
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Nama Kelompok (opsional)</label>
 <input type="text" name="group_name" value="{{ old('group_name') }}" maxlength="120" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 </div>
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Pilih Anggota <span class="text-error">*</span></label>
 @forelse($classmates as $classmate)
 <label class="flex items-center gap-2 py-1 text-sm text-on-surface cursor-pointer">
 <input type="checkbox" name="member_ids[]" value="{{ $classmate->id }}" class="w-4 h-4 text-primary rounded border-outline-variant/30 focus:ring-primary" {{ collect(old('member_ids', []))->contains($classmate->id) ? 'checked' : '' }}>
 {{ $classmate->name }} <span class="text-on-surface-variant">({{ $classmate->nim }})</span>
 </label>
 @empty
 <p class="text-sm text-on-surface-variant italic">Tidak ada teman sekelas yang tersedia.</p>
 @endforelse
 @error('member_ids')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 </div>
 @endif

 @if($format === 'url')
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Link URL <span class="text-error">*</span></label>
 <input type="url" name="url_link" value="{{ old('url_link') }}" required placeholder="https://..." class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 @error('url_link')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 @else
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Berkas (Max 10MB) <span class="text-error">*</span></label>
 <input type="file" name="file" required class="w-full text-sm text-on-surface-variant file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-bold">
 @error('file')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 @endif

 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Catatan</label>
 <textarea name="notes" rows="2" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">{{ old('notes') }}</textarea>
 </div>

 <button type="submit" class="bg-primary hover:bg-primary-hover text-on-primary text-sm font-bold px-6 py-2.5 rounded-lg">
 Kumpulkan Step {{ $step->step_number }}
 </button>
 </form>
 @endif
 </div>
 </div>
 </div>
 @endforeach
 </div>

 {{-- Setelah semua step selesai --}}
 @if(!$currentStep)
 <div class="bg-surface-container-lowest shadow-sm rounded-2xl p-6 text-center space-y-3">
 @if($assignment->step_grading_mode === 'final')
 @if($finalSubmission)
 <p class="text-sm text-on-surface-variant">Tugas akhir sudah dikumpulkan.</p>
 @if($finalSubmission->score !== null)
 <p class="text-2xl font-black text-on-surface">{{ $finalSubmission->score }}<span class="text-sm font-bold text-on-surface-variant">/{{ $assignment->max_score }}</span></p>
 @endif
 @else
 <p class="text-sm text-on-surface-variant">Semua step selesai! Silakan kumpulkan tugas akhir Anda.</p>
 <a href="{{ route('mahasiswa.submissions.create', ['assignment_id' => $assignment->id]) }}" class="inline-block bg-primary hover:bg-primary-hover text-on-primary text-sm font-bold px-6 py-2.5 rounded-lg">
 Kumpulkan Tugas Akhir
 </a>
 @endif
 @else
 @if($finalSubmission && $finalSubmission->score !== null)
 <p class="text-sm text-on-surface-variant">Nilai akhir (akumulasi semua step):</p>
 <p class="text-2xl font-black text-on-surface">{{ $finalSubmission->score }}<span class="text-sm font-bold text-on-surface-variant">/{{ $assignment->max_score }}</span></p>
 @else
 <p class="text-sm text-on-surface-variant">Semua step selesai — menunggu penilaian dosen per step.</p>
 @endif
 @endif
 </div>
 @endif
 </div>
</x-app-layout>
