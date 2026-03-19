<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Materi - {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('dosen.dashboard') }}" 
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daftar Materi</h3>
                        <a href="{{ route('dosen.materials.create', $course) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            + Tambah Materi
                        </a>
                    </div>

                    <div id="sortable-materials" class="space-y-4">
                        @forelse($materials as $material)
                            <div class="border dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 sortable-item group" data-id="{{ $material->id }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 flex items-start">
                                        <!-- Drag Handle -->
                                        <div class="cursor-grab text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 mr-4 mt-1 drag-handle opacity-50 group-hover:opacity-100 transition-opacity" title="Geser untuk mengubah urutan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="order-badge bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded text-xs font-mono">
                                                    #<span class="order-number">{{ $material->order }}</span>
                                                </span>
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $material->title }}</h4>
                                            </div>
                                            
                                            @if($material->content)
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
                                                    {!! Str::limit(strip_tags($material->content), 200) !!}
                                                </div>
                                            @endif
                                            
                                            @if($material->file_path)
                                                <a href="{{ Storage::url($material->file_path) }}" 
                                                   target="_blank"
                                                   class="inline-block mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                    {{ basename($material->file_path) }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-2 ml-4">
                                        <a href="{{ route('dosen.materials.edit', $material) }}" 
                                           class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">
                                            Edit
                                        </a>
                                        <form action="{{ route('dosen.materials.destroy', $material) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus materi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                                Belum ada materi. Klik "Tambah Materi" untuk menambahkan materi baru.
                            </p>
                        @endforelse
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
            if (el && el.children.length > 0 && !el.querySelector('.text-center.py-8')) {
                var sortable = Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-50',
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
                            toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transition-opacity duration-300';
                            toast.innerText = 'Urutan materi berhasil disimpan!';
                            document.body.appendChild(toast);
                            setTimeout(() => {
                                toast.classList.add('opacity-0');
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
