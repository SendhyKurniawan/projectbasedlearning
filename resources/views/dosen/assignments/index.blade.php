<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Tugas - {{ $course->nama_matkul }}
 </h2>
 <a href="{{ route('dosen.dashboard') }}" 
 class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Dashboard
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 @if(session('success'))
 <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
 {{ session('success') }}
 </div>
 @endif

 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6">
 <div class="flex justify-between items-center mb-6">
 <h3 class="text-lg font-semibold text-on-surface">Daftar Tugas & Quiz</h3>
 <div class="flex gap-2">
 <a href="{{ route('dosen.assignments.create', $course) }}" 
 class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
 + Tambah Tugas / Quiz
 </a>
 </div>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
 <!-- Column 1: Tugas -->
 <div>
 <h4 class="text-md font-bold text-blue-700 mb-4 border-b pb-2">📋 Tugas Utama</h4>
 <div id="sortable-tugas" class="space-y-4">
 @forelse($assignments->where('type', 'tugas') as $assignment)
 <x-assignment-card :assignment="$assignment" />
 @empty
 <p class="text-on-surface-variant text-center py-8 bg-surface-container-low/50 rounded-lg">
 Belum ada tugas.
 </p>
 @endforelse
 </div>
 </div>
 
 <!-- Column 2: Quiz & Exercise -->
 <div>
 <h4 class="text-md font-bold text-orange-600 mb-4 border-b pb-2">🎯 Quiz & Latihan</h4>
 <div id="sortable-quiz" class="space-y-4">
 @forelse($assignments->whereIn('type', ['quiz', 'exercise']) as $assignment)
 <x-assignment-card :assignment="$assignment" />
 @empty
 <p class="text-on-surface-variant text-center py-8 bg-surface-container-low/50 rounded-lg">
 Belum ada quiz atau latihan.
 </p>
 @endforelse
 </div>
 </div>
 </div>
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
 toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300';
 toast.innerText = 'Urutan berhasil disimpan!';
 document.body.appendChild(toast);
 setTimeout(() => {
 toast.classList.add('opacity-0');
 setTimeout(() => toast.remove(), 300);
 }, 3000);
 })
 .catch(error => {
 console.error('Error:', error);
 alert('Gagal menyimpan urutan tugas.');
 });
 }

 var elTugas = document.getElementById('sortable-tugas');
 if (elTugas && elTugas.children.length > 0 && !elTugas.querySelector('.text-center.py-8')) {
 Sortable.create(elTugas, {
 handle: '.drag-handle',
 animation: 150,
 ghostClass: 'opacity-50',
 onEnd: function (evt) { saveOrder(evt, elTugas); }
 });
 }

 var elQuiz = document.getElementById('sortable-quiz');
 if (elQuiz && elQuiz.children.length > 0 && !elQuiz.querySelector('.text-center.py-8')) {
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
