<x-app-layout>
 <div class="space-y-8">
 <!-- Header Section -->
 <div class="flex justify-between items-end">
 <div>
 <h2 class="text-3xl font-extrabold text-on-surface tracking-tight font-headline">Statistik Sistem</h2>
 <p class="text-on-surface-variant mt-2 font-medium">Ikhtisar real-time infrastruktur akademik Anda.</p>
 </div>
 </div>

 <!-- Bento Grid Statistics -->
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
 <!-- Stat Card 1: Total Pengguna -->
 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-secondary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-primary/10 text-primary rounded-xl">
 <span class="material-symbols-outlined">groups</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Total Pengguna</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_users'] }}</p>
 <p class="text-xs text-on-surface-variant mt-2">Akun aktif terdaftar</p>
 </div>

 <!-- Stat Card 2: Dosen -->
 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-secondary/10 text-secondary rounded-xl">
 <span class="material-symbols-outlined">school</span>
 </div>
 <span class="text-xs font-bold text-primary bg-primary-fixed px-2.5 py-1 rounded-full">Aktif</span>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Dosen</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_dosen'] }}</p>
 <p class="text-xs text-on-surface-variant mt-2">Tenaga pengajar terdaftar</p>
 </div>

 <!-- Stat Card 3: Mahasiswa -->
 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-tertiary"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-tertiary-fixed text-tertiary rounded-xl">
 <span class="material-symbols-outlined">person_book</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Mahasiswa</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_mahasiswa'] }}</p>
 <p class="text-xs text-on-surface-variant mt-2">Mahasiswa terdaftar</p>
 </div>

 <!-- Stat Card 4: Mata Kuliah -->
 <div class="bg-surface-container-lowest p-6 rounded-2xl relative overflow-hidden group hover:bg-surface-bright transition-all shadow-sm">
 <div class="absolute top-0 left-0 w-1 h-full bg-secondary-container"></div>
 <div class="flex justify-between items-start mb-4">
 <div class="p-3 bg-surface-container text-on-surface-variant rounded-xl">
 <span class="material-symbols-outlined">auto_stories</span>
 </div>
 </div>
 <h3 class="text-on-surface-variant text-xs font-bold uppercase tracking-widest">Mata Kuliah Aktif</h3>
 <p class="text-3xl font-black text-on-surface mt-1 font-headline">{{ $stats['total_courses'] }}</p>
 <p class="text-xs text-on-surface-variant mt-2">Kelas aktif berjalan</p>
 </div>
 </div>

 <!-- Content Grid -->
 <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
 <!-- Pengguna Terbaru (2/3 width) -->
 <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm">
 <div class="p-6 flex justify-between items-center border-b border-surface-container-low">
 <h3 class="font-bold text-xl text-on-surface font-headline">Pengguna Terbaru</h3>
 <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-4 py-2 text-primary text-sm font-bold hover:bg-surface-container-low rounded-xl transition-colors">
 <span>Kelola Pengguna</span>
 <span class="material-symbols-outlined text-base">arrow_forward</span>
 </a>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-left">
 <thead>
 <tr class="bg-surface-container-low/50">
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Pengguna</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Peran</th>
 <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Status</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-surface-container-low">
 @foreach($recent_users as $user)
 <tr class="hover:bg-surface-bright transition-colors group">
 <td class="px-6 py-4">
 <div class="flex items-center gap-3">
 @php
 $avatarBg = match($user->role) {
 'admin' => 'bg-error-container text-on-error-container',
 'dosen' => 'bg-primary-fixed text-primary',
 'mahasiswa' => 'bg-secondary/10 text-secondary',
 default => 'bg-surface-container text-on-surface-variant'
 };
 @endphp
 <div class="w-10 h-10 rounded-full {{ $avatarBg }} flex items-center justify-center font-bold text-sm shrink-0">
 {{ strtoupper(substr($user->name, 0, 2)) }}
 </div>
 <div>
 <p class="font-bold text-on-surface">{{ $user->name }}</p>
 <p class="text-xs text-on-surface-variant">{{ $user->email }}</p>
 </div>
 </div>
 </td>
 <td class="px-6 py-4">
 @php
 $roleBadge = match($user->role) {
 'admin' => 'bg-error/10 text-error',
 'dosen' => 'bg-primary/10 text-primary',
 'mahasiswa' => 'bg-secondary/10 text-secondary',
 default => 'bg-surface-container text-on-surface-variant'
 };
 @endphp
 <span class="px-3 py-1 {{ $roleBadge }} text-xs font-bold rounded-full">{{ ucfirst($user->role) }}</span>
 </td>
 <td class="px-6 py-4">
 <div class="flex items-center gap-2 text-xs font-bold {{ $user->is_active ? 'text-secondary' : 'text-on-surface-variant' }}">
 <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-secondary' : 'bg-outline' }}"></span>
 {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 <div class="p-4 bg-surface-container-low/30 border-t border-surface-container-low flex justify-center">
 <a href="{{ route('admin.users.index') }}" class="text-primary font-bold text-sm hover:underline">Lihat Semua Pengguna</a>
 </div>
 </div>

 <!-- Side Panel: Mata Kuliah Terbaru -->
 <div class="bg-surface-container-low p-6 rounded-2xl relative overflow-hidden">
 <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-3xl"></div>
 <h3 class="font-bold text-lg mb-6 flex items-center gap-2 font-headline">
 <span class="material-symbols-outlined text-primary">auto_stories</span>
 Mata Kuliah Terbaru
 </h3>
 <div class="space-y-4">
 @foreach($recent_courses as $course)
 <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm border-l-4 border-primary hover:shadow-md transition-all">
 <div class="flex justify-between items-start mb-2">
 <span class="font-bold text-on-surface text-sm">{{ $course->nama_matkul }}</span>
 <span class="text-[10px] font-black text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full uppercase">{{ $course->kode_matkul }}</span>
 </div>
 <div class="space-y-1 text-xs text-on-surface-variant">
 <div class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">person</span>
 {{ $course->dosen ? $course->dosen->name : 'Belum Ditugaskan' }}
 </div>
 <div class="flex items-center gap-1">
 <span class="material-symbols-outlined text-xs">group</span>
 {{ $course->students_count ?? 0 }} Mahasiswa
 </div>
 </div>
 </div>
 @endforeach
 </div>
 <a href="{{ route('admin.courses.index') }}" class="block w-full mt-6 py-3 bg-surface-container-lowest text-primary text-xs font-bold rounded-xl text-center hover:bg-surface-container transition-colors">
 Lihat Semua Mata Kuliah
 </a>
 </div>
 </div>
 </div>
</x-app-layout>
