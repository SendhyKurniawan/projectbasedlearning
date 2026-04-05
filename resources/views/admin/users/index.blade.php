<x-app-layout>
 <div class="space-y-6">
 <!-- Page Header -->
 <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
 <div class="flex items-center gap-3">
 <h2 class="text-2xl font-extrabold text-on-surface tracking-tight font-headline">Manajemen User</h2>
 @if($pendingDosen > 0)
 <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/50">
 <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
 {{ $pendingDosen }} menunggu
 </span>
 @endif
 </div>
 <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">
 <span class="material-symbols-outlined text-lg">person_add</span>
 Tambah User
 </a>
 </div>

 {{-- Flash Messages --}}
 @if(session('success'))
 <div class="px-5 py-4 bg-emerald-50 border-l-4 border-secondary text-secondary rounded-xl text-sm font-medium flex items-center gap-3">
 <span class="material-symbols-outlined text-lg">check_circle</span>
 {{ session('success') }}
 </div>
 @endif
 @if(session('error'))
 <div class="px-5 py-4 bg-red-50 border-l-4 border-error text-error rounded-xl text-sm font-medium flex items-center gap-3">
 <span class="material-symbols-outlined text-lg">error</span>
 {{ session('error') }}
 </div>
 @endif

 {{-- Pending Dosen Alert --}}
 @if($pendingDosen > 0)
 <div class="px-5 py-4 bg-amber-50 border-l-4 border-amber-400 rounded-xl flex items-center gap-3">
 <span class="material-symbols-outlined text-amber-600 text-lg">warning</span>
 <p class="text-sm text-amber-800 flex-1">
 Ada <strong>{{ $pendingDosen }} akun dosen</strong> yang menunggu persetujuan.
 </p>
 <a href="{{ route('admin.users.index', ['role' => 'dosen', 'status' => 'inactive']) }}" class="text-xs font-bold text-amber-800 underline hover:no-underline whitespace-nowrap">Lihat Sekarang</a>
 </div>
 @endif

 <!-- Main Content Card -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
 {{-- Search & Filter --}}
 <div class="p-6 border-b border-surface-container-low">
 <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
 <div class="relative flex-1">
 <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, NIM, atau NIP..."
 class="w-full pl-10 pr-4 py-3 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 outline-none text-on-surface text-sm placeholder:text-outline/60">
 </div>
 <select name="role" class="py-3 px-4 bg-surface-container-highest rounded-xl border-none text-on-surface text-sm focus:ring-2 focus:ring-primary/20 outline-none">
 <option value="">Semua Role</option>
 <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
 <option value="dosen" {{ request('role') === 'dosen' ? 'selected' : '' }}>Dosen</option>
 <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
 </select>
 <select name="status" class="py-3 px-4 bg-surface-container-highest rounded-xl border-none text-on-surface text-sm focus:ring-2 focus:ring-primary/20 outline-none">
 <option value="">Semua Status</option>
 <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
 <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
 </select>
 <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary-container transition-colors">
 <span class="material-symbols-outlined text-sm">filter_list</span>
 Filter
 </button>
 @if(request()->hasAny(['search','role','status']))
 <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors">
 Reset
 </a>
 @endif
 </form>
 </div>

 {{-- Bulk Actions + Table --}}
 <div x-data="{
 selectAll: false,
 selectedUsers: [],
 toggleAll() {
 if (this.selectAll) {
 this.selectedUsers = Array.from(document.querySelectorAll('.user-checkbox')).map(cb => cb.value);
 } else {
 this.selectedUsers = [];
 }
 },
 checkSelection() {
 const checkboxes = document.querySelectorAll('.user-checkbox');
 this.selectAll = checkboxes.length > 0 && checkboxes.length === this.selectedUsers.length;
 }
 }">
 <div x-show="selectedUsers.length > 0" x-transition.opacity
 class="mx-6 mt-4 p-3 bg-primary-fixed rounded-xl flex items-center justify-between" style="display: none;">
 <span class="text-sm font-bold text-primary">
 <span x-text="selectedUsers.length"></span> user terpilih
 </span>
 <form action="{{ route('admin.users.bulk-destroy') }}" method="POST" x-ref="bulkDeleteForm">
 @csrf
 @method('DELETE')
 <template x-for="id in selectedUsers" :key="id">
 <input type="hidden" name="user_ids[]" :value="id">
 </template>
 <button type="button" @click="if(confirm('Yakin ingin menghapus ' + selectedUsers.length + ' user terpilih?')) $refs.bulkDeleteForm.submit()"
 class="px-4 py-2 bg-error hover:bg-red-800 text-white text-xs font-bold rounded-lg transition shadow-sm">
 Hapus Terpilih
 </button>
 </form>
 </div>

 {{-- Table --}}
 <div class="overflow-x-auto">
 <table class="w-full text-left">
 <thead>
 <tr class="bg-surface-container-low/50">
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant w-10">
 <input type="checkbox" x-model="selectAll" @change="toggleAll" class="rounded border-outline-variant text-primary focus:ring-primary/20">
 </th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Nama</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Email</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">NIM/NIP</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Kelas / Prodi</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Role</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Status</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Bergabung</th>
 <th class="px-5 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Aksi</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-surface-container-low">
 @forelse($users as $user)
 <tr class="{{ !$user->is_active ? 'opacity-60' : '' }} hover:bg-surface-bright transition-colors">
 <td class="px-5 py-4 whitespace-nowrap">
 @if($user->id !== auth()->id())
 <input type="checkbox" value="{{ $user->id }}" x-model="selectedUsers" @change="checkSelection" class="user-checkbox rounded border-outline-variant text-primary focus:ring-primary/20">
 @else
 <input type="checkbox" disabled class="rounded border-outline-variant text-outline bg-surface-container opacity-50 cursor-not-allowed">
 @endif
 </td>
 <td class="px-5 py-4 whitespace-nowrap">
 <div class="flex items-center gap-3">
 @php
 $avatarBg = match($user->role) {
 'admin' => 'bg-red-100 text-red-700',
 'dosen' => 'bg-primary-fixed text-primary',
 'mahasiswa' => 'bg-emerald-100 text-secondary',
 default => 'bg-surface-container text-on-surface-variant'
 };
 @endphp
 <div class="w-8 h-8 rounded-full {{ $avatarBg }} flex items-center justify-center font-bold text-xs">
 {{ strtoupper(substr($user->name, 0, 2)) }}
 </div>
 <span class="font-bold text-on-surface text-sm">{{ $user->name }}</span>
 </div>
 </td>
 <td class="px-5 py-4 whitespace-nowrap text-sm text-on-surface-variant">{{ $user->email }}</td>
 <td class="px-5 py-4 whitespace-nowrap">
 @if($user->nim)
 <span class="font-mono text-xs bg-primary-fixed text-primary px-2 py-0.5 rounded-full font-bold">{{ $user->nim }}</span>
 @elseif($user->nip)
 <span class="font-mono text-xs bg-tertiary-fixed text-tertiary px-2 py-0.5 rounded-full font-bold">{{ $user->nip }}</span>
 @else
 <span class="text-outline">-</span>
 @endif
 </td>
 <td class="px-5 py-4 whitespace-nowrap text-sm text-on-surface-variant">
 @if($user->role === 'mahasiswa' && $user->studentClass)
 <div class="flex flex-col">
 <span class="font-bold text-on-surface text-xs">{{ $user->studentClass->name }}</span>
 <span class="text-xs text-on-surface-variant">{{ $user->studentClass->studyProgram->name ?? '-' }} ({{ $user->studentClass->studyProgram->level ?? '-' }})</span>
 </div>
 @else
 <span class="text-outline">-</span>
 @endif
 </td>
 <td class="px-5 py-4 whitespace-nowrap">
 @php
 $roleClass = match($user->role) {
 'admin' => 'bg-red-50 text-red-700',
 'dosen' => 'bg-blue-50 text-primary',
 'mahasiswa' => 'bg-emerald-50 text-secondary',
 default => 'bg-surface-container text-on-surface-variant',
 };
 @endphp
 <span class="px-3 py-1 text-xs font-bold rounded-full {{ $roleClass }}">{{ ucfirst($user->role) }}</span>
 </td>
 <td class="px-5 py-4 whitespace-nowrap">
 @if($user->is_active)
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-secondary">
 <span class="w-1.5 h-1.5 rounded-full bg-secondary inline-block"></span>
 Aktif
 </span>
 @else
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700">
 <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
 Menunggu
 </span>
 @endif
 </td>
 <td class="px-5 py-4 whitespace-nowrap text-xs text-on-surface-variant">{{ $user->created_at->format('d M Y') }}</td>
 <td class="px-5 py-4 whitespace-nowrap">
 <div class="flex items-center gap-2">
 @if($user->id !== auth()->id())
 <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST">
 @csrf
 @method('PATCH')
 <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
 class="px-3 py-1.5 text-xs font-bold rounded-lg {{ $user->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-secondary hover:bg-emerald-100' }} transition">
 {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
 </button>
 </form>
 @endif
 <a href="{{ route('admin.users.edit', $user) }}" class="text-primary hover:text-primary-container text-xs font-bold transition-colors">Edit</a>
 @if($user->id !== auth()->id())
 <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
 @csrf
 @method('DELETE')
 <button type="submit" onclick="return confirm('Hapus user {{ addslashes($user->name) }}?')" class="text-error hover:text-red-700 text-xs font-bold transition-colors">Hapus</button>
 </form>
 @endif
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="9" class="px-6 space-y-6 text-center">
 <span class="material-symbols-outlined text-4xl text-outline mb-3 block">person_off</span>
 <p class="text-on-surface-variant font-medium text-sm">Tidak ada user yang ditemukan.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 {{-- Pagination --}}
 <div class="p-6 border-t border-surface-container-low">
 {{ $users->links() }}
 </div>
 </div>
 </div>
</x-app-layout>
