<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Kelas Virtual &mdash; {{ $course->nama_matkul }}
            </h2>
            <a href="{{ route('mahasiswa.courses.show', $course) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Kembali ke Course
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        @if($conferences->isEmpty())
            <div class="empty-state">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p class="empty-state-text">Belum ada sesi kelas virtual yang dijadwalkan.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($conferences as $conference)
                    <div class="card">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $conference->title }}</h3>
                                    @if($conference->status === 'live')
                                        <span class="badge badge-success animate-pulse">LIVE</span>
                                    @else
                                        <span class="badge badge-warning">Dijadwalkan</span>
                                    @endif
                                </div>
                                @if($conference->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $conference->description }}</p>
                                @endif
                                <p class="text-xs text-gray-500">
                                    Dosen: {{ $conference->dosen->name }}
                                    &bull;
                                    {{ $conference->scheduled_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                            <div class="shrink-0">
                                @if($conference->status === 'live')
                                    <a href="{{ route('mahasiswa.conferences.room', $conference) }}"
                                       class="btn btn-primary btn-sm">
                                        Masuk Ruangan
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">Menunggu sesi dimulai...</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
