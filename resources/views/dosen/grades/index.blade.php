<x-app-layout>
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Nilai') }}
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl mb-6">
 <!-- Filter Section -->
 <div class="p-4 border-b border-gray-100 bg-surface-container-lowest rounded-t-lg">
 <form method="GET" action="{{ route('dosen.grades.index') }}" class="flex flex-col md:flex-row md:items-end gap-4">
 <div class="w-full md:w-64 focus-within:text-blue-600">
 <label for="search" class="block text-xs font-semibold text-on-surface-variant mb-1">Cari Mahasiswa</label>
 <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama..." class="block w-full rounded border-outline-variant/30 focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 transition-colors">
 </div>
 
 <div class="w-full md:flex-1">
 <label for="academic_year_id" class="block text-xs font-semibold text-on-surface-variant mb-1">Tahun Akademik</label>
 <div class="relative">
 <select name="academic_year_id" id="academic_year_id" class="w-full rounded border-outline-variant/30 focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 transition-colors">
 <option value="">Semua Tahun</option>
 @foreach($availableYears as $year)
 <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
 {{ $year->year_start }}/{{ $year->year_end }}
 </option>
 @endforeach
 </select>
 </div>
 </div>
 
 <div class="w-full md:flex-1">
 <label for="semester_id" class="block text-xs font-semibold text-on-surface-variant mb-1">Semester</label>
 <div class="relative">
 <select name="semester_id" id="semester_id" class="w-full rounded border-outline-variant/30 focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 transition-colors">
 <option value="">Semua Semester</option>
 @foreach($availableSemesters as $sem)
 <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }} data-year="{{ $sem->academic_year_id }}">
 {{ $sem->name }}
 </option>
 @endforeach
 </select>
 </div>
 </div>

 <div class="flex items-center gap-2 mt-4 md:mt-0">
 <button type="submit" class="inline-flex justify-center items-center rounded bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm w-full md:w-auto">
 Filter
 </button>
 <a href="{{ route('dosen.grades.index') }}" class="inline-flex justify-center items-center rounded border border-outline-variant/30 bg-surface-container-lowest px-6 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-bright transition-colors shadow-sm w-full md:w-auto">
 Reset
 </a>
 </div>
 </form>
 </div>

 <div class="p-6 text-on-surface">
 <div class="space-y-8">
 @forelse($courses as $course)
 <div class="border border-surface-container-low rounded-lg overflow-hidden">
 <div class="w-full flex justify-between items-center p-4 bg-surface-container-low/50 border-b border-surface-container-low">
 <div class="flex items-center space-x-4">
 <h3 class="font-bold text-lg">{{ $course->kode_matkul }} - {{ $course->nama_matkul }}</h3>
 <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
 {{ $course->semester->name ?? 'Semua Semester' }}
 ({{ $course->semester->academicYear->year_start ?? '' }}/{{ $course->semester->academicYear->year_end ?? '' }})
 </span>
 </div>
 </div>

 <div class="bg-surface-container-lowest">
 @if($course->students->count() > 0 && $course->assignments->count() > 0)
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-surface-container-low">
 <thead class="bg-surface-container-low/50">
 <tr>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider sticky left-0 bg-surface-container-low/50 z-10 w-48">Mahasiswa</th>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Tahun / Semester MASUK</th>
 @foreach($course->assignments as $assignment)
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider" title="{{ $assignment->title }}">
 <a href="{{ route('dosen.assignments.index', $course) }}" class="hover:text-blue-600:text-blue-400 hover:underline">
 {{ Str::limit($assignment->title, 15) }}
 </a>
 </th>
 @endforeach
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider bg-surface-container-low/50">Rata-rata</th>
 </tr>
 </thead>
 <tbody class="bg-surface-container-lowest divide-y divide-surface-container-low">
 @foreach($course->students as $student)
 <tr class="hover:bg-surface-bright transition-colors">
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-on-surface sticky left-0 bg-surface-container-lowest">
 {{ $student->name }} <br><span class="text-xs text-outline">{{ $student->nim }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant">
 {{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('Y') : '-' }} 
 / 
 {{ $student->pivot->enrolled_at && \Carbon\Carbon::parse($student->pivot->enrolled_at)->month > 6 ? 'Ganjil' : 'Genap' }}
 </td>
 @php $totalScore = 0; $count = 0; @endphp
 @foreach($course->assignments as $assignment)
 @php
 $submission = $student->submissions->where('assignment_id', $assignment->id)->first();
 $score = $submission ? $submission->score : null;
 if($score !== null) {
 $totalScore += $score;
 $count++;
 }
 @endphp
 <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant">
 @if($score !== null)
 <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
 {{ $score }}
 </span>
 @else
 <span class="text-xs text-outline font-bold">-</span>
 @endif
 </td>
 @endforeach
 <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-on-surface">
 {{ $count > 0 ? number_format($totalScore / $count, 1) : '-' }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @elseif($course->assignments->count() == 0)
 <div class="p-6 text-center text-sm text-on-surface-variant italic">
 <svg class="mx-auto h-12 w-12 text-outline mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
 </svg>
 Belum ada tugas untuk mata kuliah ini.
 </div>
 @else
 <div class="p-6 text-center text-sm text-on-surface-variant italic">
 <svg class="mx-auto h-12 w-12 text-outline mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
 </svg>
 Tidak ada mahasiswa yang cocok dengan filter pencarian pada mata kuliah ini.
 </div>
 @endif
 </div>
 </div>
 @empty
 <div class="text-center space-y-6">
 <svg class="mx-auto h-12 w-12 text-outline" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
 </svg>
 <h3 class="mt-2 text-sm font-medium text-on-surface">Tidak menemukan data</h3>
 <p class="mt-1 text-sm text-on-surface-variant">Silakan sesuaikan filter pencarian atau pastikan Anda memiliki mata kuliah yang sedang diajar.</p>
 </div>
 @endforelse
 </div>

 </div>
 </div>
 </div>
 </div>
 
 @push('scripts')
 <script>
 document.addEventListener('DOMContentLoaded', function() {
 const yearSelect = document.getElementById('academic_year_id');
 const semesterSelect = document.getElementById('semester_id');
 
 // Basic chaining for semester dropdown based on selected year
 yearSelect.addEventListener('change', function() {
 const yearId = this.value;
 const options = semesterSelect.querySelectorAll('option');
 
 options.forEach(option => {
 if (option.value === '') {
 option.style.display = '';
 return;
 }
 
 if (!yearId || option.dataset.year === yearId) {
 option.style.display = '';
 } else {
 option.style.display = 'none';
 }
 });
 
 // Reset semester if it's no longer visible
 if (semesterSelect.selectedOptions[0].style.display === 'none') {
 semesterSelect.value = '';
 }
 });
 
 // Trigger initially
 if (yearSelect.value) {
 yearSelect.dispatchEvent(new Event('change'));
 }
 });
 </script>
 @endpush
</x-app-layout>
