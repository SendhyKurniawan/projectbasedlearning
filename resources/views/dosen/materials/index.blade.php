<x-app-layout>
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="space-y-2">
                <nav class="flex items-center gap-2 text-xs font-bold text-on-surface-variant/60 uppercase tracking-widest">
                    <span><a href="{{ route('dosen.dashboard') }}" class="hover:text-primary transition-colors">Overview</a></span>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-primary truncate max-w-[200px]">{{ $course->kode_matkul }}</span>
                </nav>
                <h1 class="text-4xl font-extrabold tracking-tight text-on-surface font-headline">Manajemen Materi</h1>
                <p class="text-on-surface-variant max-w-lg font-medium">{{ $course->nama_matkul }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('dosen.materials.create', $course) }}" class="px-6 py-3 bg-secondary-container hover:bg-secondary-fixed transition-colors text-on-secondary-container rounded-xl font-bold flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined">create_new_folder</span>
                    Tambah Materi
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="px-5 py-4 bg-tertiary-fixed border-l-4 border-tertiary text-on-tertiary-fixed-variant rounded-xl text-sm font-bold mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-12 gap-8">
            <!-- Left Column: Materials Sequence -->
            <div class="col-span-12 lg:col-span-8 space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h3 class="font-bold text-on-surface flex items-center gap-2 text-lg font-headline">
                        <span class="material-symbols-outlined text-primary">reorder</span>
                        Urutan Modul
                    </h3>
                    <span class="text-xs font-bold text-on-surface-variant/50 tracking-widest uppercase">{{ $materials->count() }} Modul Aktif</span>
                </div>

                <!-- Sortable Container -->
                <div id="sortable-materials" class="space-y-4">
                    @forelse($materials as $material)
                        <div class="group relative bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant/10 border-l-4 border-l-secondary flex items-start gap-4 transition-all hover:bg-surface-bright hover:shadow-md sortable-item cursor-default" data-id="{{ $material->id }}">
                            <!-- Drag Handle -->
                            <div class="cursor-grab active:cursor-grabbing text-outline-variant hover:text-primary transition-colors py-1 drag-handle">
                                <span class="material-symbols-outlined">drag_indicator</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-secondary uppercase tracking-widest">Modul <span class="order-number">{{ $material->order }}</span></span>
                                    </div>
                                    
                                    <!-- Actions Menu -->
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('dosen.materials.edit', $material) }}" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Edit Materi">
                                            <span class="material-symbols-outlined text-[20px]">edit_document</span>
                                        </a>
                                        <form action="{{ route('dosen.materials.destroy', $material) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus materi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container rounded-lg transition-colors" title="Hapus Materi">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <h4 class="text-xl font-bold mb-2 font-headline text-on-surface truncate pr-4">{{ $material->title }}</h4>
                                
                                @if($material->content)
                                    <div class="text-sm text-on-surface-variant leading-relaxed line-clamp-2 mb-4">
                                        {!! Str::limit(strip_tags($material->content), 150) !!}
                                    </div>
                                @endif

                                @if($material->file_path)
                                    <div class="mt-4 flex items-center">
                                        <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="flex items-center gap-3 p-3 bg-surface-container-low hover:bg-surface-container transition-colors rounded-xl border border-outline-variant/10 w-full md:w-auto overflow-hidden">
                                            <span class="material-symbols-outlined text-error">picture_as_pdf</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-on-surface truncate">{{ basename($material->file_path) }}</p>
                                                <p class="text-[10px] text-on-surface-variant font-medium uppercase tracking-wider">Lampiran Dokumen</p>
                                            </div>
                                            <span class="material-symbols-outlined text-outline-variant ml-2 text-[18px]">open_in_new</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 bg-surface border-2 border-dashed border-outline-variant/30 rounded-2xl flex flex-col items-center justify-center text-center">
                            <span class="material-symbols-outlined text-4xl text-outline-variant mb-4 pointer-events-none">folder_off</span>
                            <h3 class="text-lg font-bold text-on-surface font-headline mb-2">Belum Ada Materi</h3>
                            <p class="text-on-surface-variant font-medium text-sm mb-6 max-w-sm">Materi pembelajaran yang Anda unggah akan muncul di sini dan dapat diakses oleh mahasiswa.</p>
                            <a href="{{ route('dosen.materials.create', $course) }}" class="px-5 py-2.5 bg-primary-container text-on-primary-container font-bold rounded-xl flex items-center gap-2 hover:opacity-90 transition-opacity">
                                <span class="material-symbols-outlined text-[20px]">add</span>
                                Buat Materi Pertama
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Info & Stats -->
            <div class="col-span-12 lg:col-span-4 space-y-6">
                <!-- Course Quick Info Card -->
                <div class="bg-primary text-white p-8 rounded-3xl relative overflow-hidden shadow-xl shadow-primary/20">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                                <span class="material-symbols-outlined text-white">architecture</span>
                            </div>
                            <h4 class="text-xs font-bold opacity-90 uppercase tracking-widest text-[#faf8ff]">Insight Kelas</h4>
                        </div>
                        <h2 class="text-2xl font-extrabold font-headline leading-tight">{{ $course->nama_matkul }}</h2>
                        <div class="flex items-center gap-4 mt-8 pt-6 border-t border-white/20">
                            <div>
                                <p class="text-[10px] tracking-widest uppercase font-bold text-white/70">Total Modul</p>
                                <p class="text-2xl font-black">{{ $materials->count() }}</p>
                            </div>
                            <div class="w-px h-8 bg-white/20"></div>
                            <div>
                                <p class="text-[10px] tracking-widest uppercase font-bold text-white/70">Mahasiswa</p>
                                <p class="text-2xl font-black">{{ $course->students()->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative Circle Abstract -->
                    <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                </div>

                <div class="bg-surface-container-low p-6 rounded-2xl flex items-start gap-4 border border-outline-variant/10 shadow-sm">
                    <span class="material-symbols-outlined text-primary mt-1">lightbulb</span>
                    <div>
                        <h4 class="font-bold text-sm text-on-surface mb-1">Tips Mengelola Materi</h4>
                        <p class="text-xs text-on-surface-variant font-medium leading-relaxed">
                            Gunakan icon <strong>susun</strong> (<span class="material-symbols-outlined text-[12px] inline-block align-middle">drag_indicator</span>) pada materi di sebelah kiri untuk mengubah urutan pembelajaran mahasiswa secara instan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('sortable-materials');
        if (el && el.children.length > 0 && !el.querySelector('.py-12')) {
            var sortable = Sortable.create(el, {
                handle: '.drag-handle',
                animation: 200,
                ghostClass: 'opacity-50',
                dragClass: 'shadow-2xl',
                onEnd: function (evt) {
                    var items = el.querySelectorAll('.sortable-item');
                    var orderedIds = Array.from(items).map(item => item.getAttribute('data-id'));
                    
                    // Update UI Numbers immediately
                    items.forEach((item, index) => {
                        let orderNumSpan = item.querySelector('.order-number');
                        if(orderNumSpan) orderNumSpan.innerText = index + 1;
                    });

                    // AJAX request save order
                    fetch('{{ route('dosen.materials.reorder', $course) }}', {
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
                        toast.className = 'fixed bottom-8 right-8 bg-secondary-container text-on-secondary-container px-6 py-3 rounded-xl shadow-lg border border-secondary/20 transition-all duration-300 font-bold text-sm flex items-center gap-2 z-50';
                        toast.innerHTML = '<span class="material-symbols-outlined text-[20px]">check_circle</span> Urutan modul berhasil disimpan';
                        document.body.appendChild(toast);
                        setTimeout(() => {
                            toast.classList.add('opacity-0', 'translate-y-4');
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal menyimpan urutan materi.');
                    });
                }
            });
        }
    });
    </script>
</x-app-layout>
