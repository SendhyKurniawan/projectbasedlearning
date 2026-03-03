<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Manajemen User
                </h2>
                @if($pendingDosen > 0)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        {{ $pendingDosen }} dosen menunggu persetujuan
                    </span>
                @endif
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Pending Dosen Alert --}}
            @if($pendingDosen > 0)
                <div class="mb-6 px-4 py-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        Ada <strong>{{ $pendingDosen }} akun dosen</strong> yang menunggu persetujuan. Filter dengan role <strong>Dosen</strong> dan status <strong>Tidak Aktif</strong> untuk meninjau.
                    </p>
                    <a href="{{ route('admin.users.index', ['role' => 'dosen', 'status' => 'inactive']) }}"
                       class="ml-auto whitespace-nowrap text-xs font-semibold text-yellow-800 dark:text-yellow-200 underline hover:no-underline">
                        Lihat Sekarang
                    </a>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    {{-- Search & Filter --}}
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama, email, NIM, atau NIP..."
                               class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:ring-indigo-500 text-sm">

                        <select name="role"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:ring-indigo-500 text-sm">
                            <option value="">Semua Role</option>
                            <option value="admin"     {{ request('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
                            <option value="dosen"     {{ request('role') === 'dosen'     ? 'selected' : '' }}>Dosen</option>
                            <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>

                        <select name="status"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:ring-indigo-500 text-sm">
                            <option value="">Semua Status</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>

                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-md transition">
                            Filter
                        </button>
                        @if(request()->hasAny(['search','role','status']))
                            <a href="{{ route('admin.users.index') }}"
                               class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-md transition">
                                Reset
                            </a>
                        @endif
                    </form>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">NIM/NIP</th>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bergabung</th>
                                    <th class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($users as $user)
                                    <tr class="{{ !$user->is_active ? 'opacity-70' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            @if($user->nim)
                                                <span class="font-mono text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-1.5 py-0.5 rounded">{{ $user->nim }}</span>
                                            @elseif($user->nip)
                                                <span class="font-mono text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-1.5 py-0.5 rounded">{{ $user->nip }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @php
                                                $roleClass = match($user->role) {
                                                    'admin'     => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                                    'dosen'     => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
                                                    'mahasiswa' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                                    default     => 'bg-gray-100 text-gray-700',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $roleClass }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($user->is_active)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 inline-block"></span>
                                                    Menunggu
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $user->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                {{-- Toggle Active --}}
                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                                class="px-2 py-1 text-xs font-semibold rounded {{ $user->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-300' : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-300' }} transition">
                                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                                    Edit
                                                </a>

                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                onclick="return confirm('Hapus user {{ addslashes($user->name) }}?')"
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada user yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
