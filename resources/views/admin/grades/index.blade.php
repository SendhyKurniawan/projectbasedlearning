<x-app-layout>
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Nilai') }}
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 
 <div class="space-y-4">
 @forelse($courses as $course)
 <div x-data="{ expanded: false }" class="border border-surface-container-low rounded-lg">
 <button @click="expanded = !expanded" class="w-full flex justify-between items-center p-4 bg-surface-container-low/50 hover:bg-surface-container-low:bg-surface-container-high rounded-t-lg focus:outline-none transition-colors">
 <div class="flex items-center space-x-4">
 <h3 class="font-bold text-lg">{{ $course->kode_matkul }} - {{ $course->nama_matkul }}</h3>
 <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
 {{ $course->semester->name ?? 'Semua Semester' }}
 </span>
 <span class="text-sm text-on-surface-variant hidden sm:inline-block">
 Dosen: {{ $course->dosen->name ?? 'Belum Ditentukan' }}
 </span>
 </div>
 <svg :class="{'rotate-180': expanded}" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
 </button>

 <div x-show="expanded" x-collapse x-cloak class="border-t border-surface-container-low">
 @if($course->students->count() > 0 && $course->assignments->count() > 0)
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-surface-container-low">
 <thead class="bg-surface-container-low/50">
 <tr>
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider sticky left-0 bg-surface-container-low/50 z-10 w-48">Mahasiswa</th>
 @foreach($course->assignments as $assignment)
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-on-surface-variant uppercase tracking-wider" title="{{ $assignment->title }}">
 <a href="{{ route('admin.courses.show', $course) }}" class="hover:text-blue-600:text-blue-400 hover:underline">
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
 @else
 <div class="p-6 text-center text-sm text-on-surface-variant ">
 <svg class="mx-auto h-12 w-12 text-outline mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
 </svg>
 Belum ada mahasiswa atau tugas untuk mata kuliah ini.
 </div>
 @endif
 </div>
 </div>
 @empty
 <div class="text-center space-y-6">
 <svg class="mx-auto h-12 w-12 text-outline" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
 </svg>
 <h3 class="mt-2 text-sm font-medium text-on-surface">Tidak ada mata kuliah</h3>
 <p class="mt-1 text-sm text-on-surface-variant">Belum ada mata kuliah yang tersedia.</p>
 </div>
 @endforelse
 </div>

 </div>
 </div>
 </div>
 </div>
</x-app-layout>
