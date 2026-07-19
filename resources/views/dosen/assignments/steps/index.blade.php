{{-- Kelola step (tahapan) tugas ber-step (dosen). --}}
<x-app-layout>
 <div class="space-y-6">
 {{-- Section Header --}}
 <div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <nav class="flex items-center gap-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">
 <a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="hover:text-primary transition-colors">Tugas</a>
 <span class="material-symbols-outlined text-xs">chevron_right</span>
 <span class="text-primary">Step</span>
 </nav>
 <h1 class="font-headline text-3xl font-extrabold tracking-tight text-on-surface">Kelola Step</h1>
 <p class="mt-1 text-sm text-on-surface-variant">
 {{ $assignment->title }} · {{ $assignment->course->nama_matkul }} ·
 <span class="font-bold">{{ $assignment->step_grading_mode === 'per_step' ? 'Dinilai per Step' : 'Nilai Akhir Saja' }}</span>
 </p>
 </div>
 <a href="{{ route('dosen.assignments.index', $assignment->course) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface text-sm font-bold hover:bg-surface-container-low transition">
 <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
 </a>
 </div>

 @if(session('success'))
 <div class="bg-primary/10 border border-primary/20 text-on-surface text-sm p-4 rounded-xl">{{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div class="bg-error/10 border border-error/20 text-error text-sm p-4 rounded-xl">{{ session('error') }}</div>
 @endif

 @if($stepsLocked)
 <div class="bg-surface-container-low border border-outline-variant/30 text-on-surface-variant text-sm p-4 rounded-xl flex items-start gap-2">
 <span class="material-symbols-outlined text-base">lock</span>
 <span><span class="font-bold">Terkunci:</span> Struktur step tidak dapat diubah karena sudah ada pengerjaan mahasiswa.</span>
 </div>
 @endif

 @if($assignment->step_grading_mode === 'per_step' && $steps->isNotEmpty())
 @php $totalBobot = $steps->sum('max_score'); @endphp
 <div class="text-xs text-on-surface-variant px-1">
 Total bobot step: <span class="font-bold {{ $totalBobot == $assignment->max_score ? 'text-primary' : 'text-error' }}">{{ $totalBobot }}</span>
 / Nilai maksimal tugas: {{ $assignment->max_score }}.
 @if($totalBobot != $assignment->max_score)
 Sebaiknya jumlah bobot step sama dengan nilai maksimal tugas.
 @endif
 </div>
 @endif

 {{-- Daftar step --}}
 <div class="space-y-3">
 @forelse($steps as $step)
 <div class="bg-surface-container-lowest shadow-sm rounded-2xl p-5" x-data="{ editing: false }">
 <div class="flex items-start justify-between gap-4">
 <div class="flex items-start gap-3 min-w-0">
 <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary font-black text-sm flex items-center justify-center">{{ $step->step_number }}</span>
 <div class="min-w-0">
 <h3 class="font-bold text-on-surface">{{ $step->title }}</h3>
 @if($step->description)
 <p class="text-sm text-on-surface-variant mt-1 whitespace-pre-line">{{ $step->description }}</p>
 @endif
 <div class="flex flex-wrap gap-2 mt-2 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
 <span class="px-2 py-1 rounded bg-surface-container-low">{{ $step->effectiveSubmissionFormat() === 'url' ? 'Link URL' : 'File Upload' }}</span>
 @if($step->deadline)
 <span class="px-2 py-1 rounded bg-surface-container-low">Deadline: {{ $step->deadline->format('d M Y H:i') }}</span>
 @endif
 @if($assignment->step_grading_mode === 'per_step')
 <span class="px-2 py-1 rounded bg-primary/10 text-primary">Bobot: {{ $step->max_score }}</span>
 @endif
 <span class="px-2 py-1 rounded bg-surface-container-low">{{ $step->submissions_count }} pengumpulan</span>
 </div>
 </div>
 </div>
 @unless($stepsLocked)
 <div class="flex items-center gap-2 flex-shrink-0">
 <button type="button" @click="editing = !editing" class="text-xs font-bold text-primary hover:underline">Edit</button>
 <form action="{{ route('dosen.assignments.steps.destroy', $step) }}" method="POST" onsubmit="return confirm('Hapus step ini?')">
 @csrf @method('DELETE')
 <button type="submit" class="text-xs font-bold text-error hover:underline">Hapus</button>
 </form>
 </div>
 @endunless
 </div>

 @unless($stepsLocked)
 <form x-show="editing" x-cloak action="{{ route('dosen.assignments.steps.update', $step) }}" method="POST" class="mt-4 pt-4 border-t border-outline-variant/20 grid gap-3 sm:grid-cols-2">
 @csrf @method('PUT')
 <div class="sm:col-span-2">
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Judul Step</label>
 <input type="text" name="title" value="{{ $step->title }}" required class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 </div>
 <div class="sm:col-span-2">
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Deskripsi</label>
 <textarea name="description" rows="2" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">{{ $step->description }}</textarea>
 </div>
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Deadline Step (opsional)</label>
 <input type="datetime-local" name="deadline" value="{{ $step->deadline?->format('Y-m-d\TH:i') }}" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 </div>
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Format Pengumpulan</label>
 <select name="submission_format" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 <option value="">Ikut format tugas ({{ $assignment->submission_format === 'url' ? 'Link URL' : 'File' }})</option>
 <option value="pdf" {{ $step->submission_format === 'pdf' ? 'selected' : '' }}>File Upload</option>
 <option value="url" {{ $step->submission_format === 'url' ? 'selected' : '' }}>Link URL</option>
 </select>
 </div>
 @if($assignment->step_grading_mode === 'per_step')
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Bobot Nilai <span class="text-error">*</span></label>
 <input type="number" name="max_score" value="{{ $step->max_score }}" min="1" max="100" required class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 </div>
 @endif
 <div class="sm:col-span-2 flex gap-2">
 <button type="submit" class="bg-primary hover:bg-primary-hover text-on-primary text-sm font-bold px-4 py-2 rounded-lg">Simpan</button>
 <button type="button" @click="editing = false" class="bg-surface-container hover:bg-surface-container-high text-on-surface text-sm font-bold px-4 py-2 rounded-lg">Batal</button>
 </div>
 </form>
 @endunless
 </div>
 @empty
 <div class="bg-surface-container-lowest shadow-sm rounded-2xl p-8 text-center text-sm text-on-surface-variant">
 Belum ada step. Tambahkan step pertama di bawah.
 </div>
 @endforelse
 </div>

 {{-- Form tambah step --}}
 @unless($stepsLocked)
 <div class="bg-surface-container-lowest shadow-sm rounded-2xl p-6">
 <h2 class="font-bold text-on-surface mb-4">Tambah Step {{ $steps->count() + 1 }}</h2>
 <form action="{{ route('dosen.assignments.steps.store', $assignment) }}" method="POST" class="grid gap-3 sm:grid-cols-2">
 @csrf
 <div class="sm:col-span-2">
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Judul Step <span class="text-error">*</span></label>
 <input type="text" name="title" value="{{ old('title') }}" required placeholder="cth: Analisis Kebutuhan" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 @error('title')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 <div class="sm:col-span-2">
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Deskripsi</label>
 <textarea name="description" rows="2" placeholder="Jelaskan apa yang dikerjakan pada step ini..." class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">{{ old('description') }}</textarea>
 @error('description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Deadline Step (opsional)</label>
 <input type="datetime-local" name="deadline" value="{{ old('deadline') }}" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 @error('deadline')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Format Pengumpulan</label>
 <select name="submission_format" class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 <option value="">Ikut format tugas ({{ $assignment->submission_format === 'url' ? 'Link URL' : 'File' }})</option>
 <option value="pdf" {{ old('submission_format') === 'pdf' ? 'selected' : '' }}>File Upload</option>
 <option value="url" {{ old('submission_format') === 'url' ? 'selected' : '' }}>Link URL</option>
 </select>
 @error('submission_format')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 @if($assignment->step_grading_mode === 'per_step')
 <div>
 <label class="block text-xs font-medium text-on-surface-variant mb-1">Bobot Nilai <span class="text-error">*</span></label>
 <input type="number" name="max_score" value="{{ old('max_score') }}" min="1" max="100" required class="w-full border-outline-variant/30 text-on-surface rounded-md shadow-sm focus:border-primary focus:ring-primary">
 <p class="text-xs text-on-surface-variant mt-1">Jumlah bobot semua step sebaiknya = nilai maksimal tugas ({{ $assignment->max_score }}).</p>
 @error('max_score')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
 </div>
 @endif
 <div class="sm:col-span-2">
 <button type="submit" class="bg-primary hover:bg-primary-hover text-on-primary text-sm font-bold px-6 py-2.5 rounded-lg">Tambah Step</button>
 </div>
 </form>
 </div>
 @endunless
 </div>
</x-app-layout>
