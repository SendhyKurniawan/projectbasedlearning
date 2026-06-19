{{-- Halaman daftar tugas (dosen). --}}
@push('styles')
    @vite('resources/css/pages/dosen/assignments.css')
@endpush
<x-app-layout>
    <div class="w-full space-y-6">
        @if(session('success'))
            <div class="mb-4 bg-secondary-container border border-secondary text-secondary px-4 py-3 rounded-xl font-medium shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-primary font-bold text-sm mb-2">
                    <span class="material-symbols-outlined text-base">history_edu</span>
                    <span class="tracking-wide">FACULTY DASHBOARD</span>
                </div>
                <h2 class="text-4xl font-extrabold font-headline text-on-background tracking-tight">Manajemen Tugas & Kuis</h2>
                <p class="text-on-surface-variant max-w-xl text-sm leading-relaxed mt-2">Konfigurasi materi pembelajaran berbasis proyek dan evaluasi kompetensi mahasiswa melalui bank soal cerdas untuk <span class="font-bold text-on-surface">{{ $course->nama_matkul }}</span>.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-2 px-6 py-3 bg-surface-container-lowest text-on-surface border border-outline-variant/30 rounded-xl font-bold hover:shadow-md transition-all text-sm">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Dashboard
                </a>
                <a href="{{ route('dosen.exercises.create', $course) }}" class="flex items-center gap-2 px-6 py-3 bg-surface-container-lowest border border-outline-variant/30 text-on-surface rounded-xl font-bold hover:shadow-md transition-all text-sm">
                    <span class="material-symbols-outlined">code</span>
                    Tambah Latihan Kode
                </a>
                <a href="{{ route('dosen.assignments.create', $course) }}" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-br from-secondary to-on-secondary-container text-white rounded-xl font-bold shadow-lg shadow-secondary/20 hover:scale-105 transition-all text-sm">
                    <span class="material-symbols-outlined">add_task</span>
                    Buat Baru
                </a>
            </div>
        </div>

        <!-- Analytics Summary -->
        <div class="grid grid-cols-12 gap-6 mb-8">
            <div class="col-span-12 lg:col-span-4 grid grid-cols-2 gap-4">
                <div class="col-span-2 bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden shadow-sm">
                    <div class="relative z-10">
                        <p class="text-label-sm font-bold text-on-surface-variant/70 uppercase tracking-widest">Total Penilaian</p>
                        <h3 class="text-4xl font-black mt-2 text-primary">{{ $assignments->count() }}</h3>
                    </div>
                    <div class="absolute -right-4 -bottom-4 opacity-5 text-primary pointer-events-none">
                        <span class="material-symbols-outlined text-9xl">analytics</span>
                    </div>
                </div>
                <div class="bg-primary-container p-6 rounded-2xl text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-widest">Aktif</p>
                        <h3 class="text-2xl font-black mt-1">{{ $assignments->where('deadline', '>=', now())->count() }}</h3>
                    </div>
                    <span class="material-symbols-outlined absolute right-2 bottom-2 text-4xl opacity-20 pointer-events-none">clock_loader_40</span>
                </div>
                <div class="bg-secondary-container p-6 rounded-2xl text-on-secondary-container relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-widest">Selesai</p>
                        <h3 class="text-2xl font-black mt-1">{{ $assignments->where('deadline', '<', now())->count() }}</h3>
                    </div>
                    <span class="material-symbols-outlined absolute right-2 bottom-2 text-4xl opacity-20 pointer-events-none">task_alt</span>
                </div>
            </div>
            
            <div class="col-span-12 lg:col-span-8 bg-surface-container-lowest p-8 rounded-2xl relative overflow-hidden shadow-sm flex flex-col justify-center border border-outline-variant/20">
                 <div class="relative z-10 text-on-surface space-y-3">
                      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 text-primary text-xs font-bold border border-primary/20">
                          <span class="material-symbols-outlined text-sm">tips_and_updates</span>
                          Tip Koleksi
                      </div>
                      <h3 class="text-2xl font-bold font-headline tracking-tight">Manajemen Urutan Interaktif</h3>
                      <p class="text-on-surface-variant text-base max-w-lg leading-relaxed">Gunakan interaksi drag-and-drop pada kartu di bawah untuk mengubah urutan tugas atau kuis secara realtime.</p>
                 </div>
                 <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/4 opacity-[0.03] text-primary pointer-events-none">
                      <span class="material-symbols-outlined text-[200px]">format_list_bulleted</span>
                 </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-10">
            <!-- Section 1: Tugas Utama -->
            <div class="bg-surface-container-low/30 border border-outline-variant/30 p-8 rounded-[2rem] backdrop-blur-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-5 border-b border-outline-variant/20 gap-4">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-3xl">assignment</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold font-headline text-on-surface tracking-tight mb-1">Tugas Utama</h3>
                            <p class="text-sm font-bold text-on-surface-variant uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-secondary"></span>
                                Project Based
                            </p>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-surface-container-lowest border border-outline-variant/20 rounded-xl flex items-center gap-2 shadow-sm font-bold text-sm">
                        <span class="text-primary">{{ $assignments->where('type', 'tugas')->count() }}</span> 
                        <span class="text-on-surface-variant">TUGAS</span>
                    </div>
                </div>
                
                <div id="sortable-tugas" class="space-y-4 min-h-[150px] relative">
                    @forelse($assignments->where('type', 'tugas') as $assignment)
                        <x-assignment-card :assignment="$assignment" :siblings="$siblings" />
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 bg-surface-container-lowest/40 rounded-2xl border-2 border-dashed border-outline-variant/40 text-on-surface-variant text-center absolute inset-0">
                            <span class="material-symbols-outlined text-6xl mb-4 opacity-30 text-primary">folder_open</span>
                            <p class="font-bold text-lg text-on-surface">Belum Ada Tugas</p>
                            <p class="text-base mt-1">Buat tugas proyek baru untuk memulai penilaian.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Section 2: Quiz & Exercise -->
            <div class="bg-surface-container-low/30 border border-outline-variant/30 p-8 rounded-[2rem] backdrop-blur-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-5 border-b border-outline-variant/20 gap-4">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-warning-light text-on-warning flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-3xl">quiz</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold font-headline text-on-surface tracking-tight mb-1">Kuis & Latihan</h3>
                            <p class="text-sm font-bold text-on-surface-variant uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-warning"></span>
                                Formative Tests
                            </p>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-surface-container-lowest border border-outline-variant/20 rounded-xl flex items-center gap-2 shadow-sm font-bold text-sm">
                        <span class="text-warning">{{ $assignments->whereIn('type', ['quiz', 'exercise'])->count() }}</span> 
                        <span class="text-on-surface-variant">ITEM</span>
                    </div>
                </div>
                
                <div id="sortable-quiz" class="space-y-4 min-h-[150px] relative">
                    @forelse($assignments->whereIn('type', ['quiz', 'exercise']) as $assignment)
                        <x-assignment-card :assignment="$assignment" :siblings="$siblings" />
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 bg-surface-container-lowest/40 rounded-2xl border-2 border-dashed border-outline-variant/40 text-on-surface-variant text-center absolute inset-0">
                            <span class="material-symbols-outlined text-6xl mb-4 opacity-30 text-warning">quiz</span>
                            <p class="font-bold text-lg text-on-surface">Belum Ada Kuis</p>
                            <p class="text-base mt-1">Buat kuis interaktif atau latihan kode.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

 <!-- SortableJS -->
 <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
 <script>
 document.addEventListener('DOMContentLoaded', function() {
 
 function saveOrder(evt, listElement) {
 var items = listElement.querySelectorAll('.sortable-item');
 var orderedIds = Array.from(items).map(item => item.getAttribute('data-id'));

 // AJAX request save order
 fetch('{{ route('dosen.assignments.reorder', $course) }}', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}',
 'Accept': 'application/json'
 },
 body: JSON.stringify({ ordered_ids: orderedIds })
 })
 .then(response => response.json())
 .then(data => {
 let toast = document.createElement('div');
 toast.className = 'fixed bottom-4 right-4 bg-secondary text-on-secondary px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300';
 toast.innerText = 'Urutan berhasil disimpan!';
 document.body.appendChild(toast);
 setTimeout(() => {
 toast.classList.add('opacity-0');
 setTimeout(() => toast.remove(), 300);
 }, 3000);
 })
 .catch(error => {
 console.error('Error:', error);
 let toast = document.createElement('div');
 toast.className = 'fixed bottom-4 right-4 bg-error-container text-on-error-container px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300 font-bold text-sm flex items-center gap-2';
 toast.innerHTML = '<span class="material-symbols-outlined text-[18px]">error</span> Gagal menyimpan urutan tugas.';
 document.body.appendChild(toast);
 setTimeout(() => {
 toast.classList.add('opacity-0');
 setTimeout(() => toast.remove(), 300);
 }, 3000);
 });
 }

 var elTugas = document.getElementById('sortable-tugas');
 if (elTugas && elTugas.children.length > 0 && !elTugas.querySelector('.border-dashed')) {
 Sortable.create(elTugas, {
 handle: '.drag-handle',
 animation: 150,
 ghostClass: 'opacity-50',
 onEnd: function (evt) { saveOrder(evt, elTugas); }
 });
 }

 var elQuiz = document.getElementById('sortable-quiz');
 if (elQuiz && elQuiz.children.length > 0 && !elQuiz.querySelector('.border-dashed')) {
 Sortable.create(elQuiz, {
 handle: '.drag-handle',
 animation: 150,
 ghostClass: 'opacity-50',
 onEnd: function (evt) { saveOrder(evt, elQuiz); }
 });
 }
 });
 </script>
</x-app-layout>
