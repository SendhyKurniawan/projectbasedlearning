<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 Kelas Virtual &mdash; {{ $course->nama_matkul }}
 </h2>
 <a href="{{ route('dosen.conferences.create', $course) }}" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">
 + Jadwalkan Sesi
 </a>
 </div>
 </x-slot>

 <div class="py-8">
 @if(session('success'))
 <div class="px-5 py-4 bg-emerald-50 border-l-4 border-secondary text-secondary rounded-xl text-sm font-medium mb-6">{{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div class="px-5 py-4 bg-red-50 border-l-4 border-error text-error rounded-xl text-sm font-medium mb-6">{{ session('error') }}</div>
 @endif

 @forelse($conferences as $conference)
 <div class="card mb-4">
 <div class="flex items-start justify-between gap-4">
 <div class="flex-1">
 <div class="flex items-center gap-3 mb-1">
 <h3 class="font-bold text-lg text-on-surface">{{ $conference->title }}</h3>
 @if($conference->status === 'live')
 <span class="badge badge-success animate-pulse">LIVE</span>
 @elseif($conference->status === 'ended')
 <span class="badge badge-gray">Selesai</span>
 @else
 <span class="badge badge-warning">Dijadwalkan</span>
 @endif
 </div>
 @if($conference->description)
 <p class="text-sm text-on-surface-variant mb-2">{{ $conference->description }}</p>
 @endif
 <p class="text-xs text-on-surface-variant flex items-center gap-1">
 <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
 </svg>
 {{ $conference->scheduled_at->format('d M Y, H:i') }}
 </p>
 </div>

 <div class="flex items-center gap-2 flex-shrink-0">
 @if($conference->status === 'scheduled')
 <form method="POST" action="{{ route('dosen.conferences.start', $conference) }}" class="inline">
 @csrf
 <button type="submit" class="btn btn-success btn-sm">Mulai Sesi</button>
 </form>
 <a href="{{ route('dosen.conferences.edit', $conference) }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors btn-sm">Edit</a>
 <form method="POST" action="{{ route('dosen.conferences.destroy', $conference) }}" class="inline" onsubmit="return confirm('Hapus konferensi ini?')">
 @csrf @method('DELETE')
 <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
 </form>
 @elseif($conference->status === 'live')
 <a href="{{ route('dosen.conferences.room', $conference) }}" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all btn-sm">Masuk Ruangan</a>
 <form method="POST" action="{{ route('dosen.conferences.end', $conference) }}" class="inline" onsubmit="return confirm('Akhiri sesi ini?')">
 @csrf
 <button type="submit" class="btn btn-danger btn-sm">Akhiri Sesi</button>
 </form>
 @else
 <span class="text-xs text-outline">Berakhir: {{ $conference->ended_at?->format('H:i') }}</span>
 @endif
 </div>
 </div>
 </div>
 @empty
 <div class="empty-state">
 <svg class="w-16 h-16 mx-auto mb-4 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
 </svg>
 <p class="empty-state-text">Belum ada sesi konferensi. Jadwalkan sesi pertama Anda!</p>
 <a href="{{ route('dosen.conferences.create', $course) }}" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all mt-4">Jadwalkan Sesi</a>
 </div>
 @endforelse

 {{ $conferences->links() }}
 </div>
</x-app-layout>
