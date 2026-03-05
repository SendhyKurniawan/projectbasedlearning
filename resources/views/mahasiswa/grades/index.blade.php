<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nilai') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="space-y-4">
                        @forelse($courses as $course)
                        <div x-data="{ expanded: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                            <button @click="expanded = !expanded" class="w-full flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-750 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-t-lg focus:outline-none transition-colors">
                                <div class="flex items-center space-x-4">
                                    <h3 class="font-bold text-lg">{{ $course->kode_matkul }} - {{ $course->nama_matkul }}</h3>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $course->semester->name ?? 'Semua Semester' }}
                                    </span>
                                </div>
                                <svg :class="{'rotate-180': expanded}" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="expanded" x-collapse x-cloak class="border-t border-gray-200 dark:border-gray-700">
                                @if($course->assignments->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-750">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tugas/Quiz</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">Tenggat Waktu</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @php $totalScore = 0; $count = 0; @endphp
                                            @foreach($course->assignments as $assignment)
                                                @php
                                                    $submission = $assignment->submissions->first();
                                                    $score = $submission ? $submission->score : null;
                                                    if($score !== null) {
                                                        $totalScore += $score;
                                                        $count++;
                                                    }
                                                @endphp
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        <a href="{{ route('mahasiswa.courses.show', $course) }}" class="hover:text-blue-600 dark:hover:text-blue-400 hover:underline">
                                                            {{ $assignment->title }}
                                                        </a> 
                                                        <br><span class="text-xs text-gray-400">{{ ucfirst($assignment->type) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                                        {{ $assignment->deadline ? $assignment->deadline->format('d M Y, H:i') : '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        @if($submission && $score !== null)
                                                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-xs">Dinilai</span>
                                                        @elseif($submission && $submission->status === 'submitted')
                                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-xs">Menunggu Penilaian</span>
                                                        @elseif($submission && $submission->status === 'late')
                                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full text-xs">Terlambat</span>
                                                        @else
                                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-full text-xs">Belum Mengumpulkan</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $score !== null ? $score : '-' }} <span class="text-xs font-normal text-gray-400">/ {{ $assignment->max_score ?? 100 }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50 dark:bg-gray-750">
                                            <tr>
                                                <td colspan="2" class="sm:hidden px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rata-rata:</td>
                                                <td colspan="3" class="hidden sm:table-cell px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rata-rata Nilai:</td>
                                                <td class="px-6 py-3 text-left text-sm font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $count > 0 ? number_format($totalScore / $count, 1) : '-' }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Belum ada tugas atau kuis untuk mata kuliah ini.
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada mata kuliah</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Anda belum mendaftar di mata kuliah apapun.</p>
                        </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
