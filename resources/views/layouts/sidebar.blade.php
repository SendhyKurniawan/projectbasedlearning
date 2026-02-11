<aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-72 bg-white dark:bg-gray-800 shadow-xl transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:shadow-none lg:border-r lg:border-gray-200 dark:lg:border-gray-700 flex flex-col h-full shrink-0">
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-700">
        @php
            $dashboardRoute = match(auth()->user()->role ?? '') {
                'admin' => 'admin.dashboard',
                'dosen' => 'dosen.dashboard',
                'mahasiswa' => 'mahasiswa.dashboard',
                default => 'login',
            };
        @endphp
        <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2 font-bold text-xl text-blue-600 dark:text-blue-400">
            <x-application-logo class="block h-8 w-auto fill-current" />
            <span>PJBL App</span>
        </a>
        
        <!-- Mobile Close Button -->
        <button @click="open = false" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        @auth
            @if(auth()->user()->role === 'admin')
                <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="w-full justify-start">
                    {{ __('Dashboard') }}
                </x-nav-link>
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</div>
                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="w-full justify-start">
                    {{ __('Users') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')" class="w-full justify-start">
                    {{ __('Courses') }}
                </x-nav-link>
                 <x-nav-link :href="route('admin.academic-years.index')" :active="request()->routeIs('admin.academic-years.*')" class="w-full justify-start">
                    {{ __('Tahun Akademik') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.semesters.index')" :active="request()->routeIs('admin.semesters.*')" class="w-full justify-start">
                    {{ __('Semester') }}
                </x-nav-link>
            @elseif(auth()->user()->role === 'dosen')
                <x-nav-link :href="route('dosen.dashboard')" :active="request()->routeIs('dosen.dashboard')" class="w-full justify-start">
                    {{ __('Dashboard') }}
                </x-nav-link>
            @elseif(auth()->user()->role === 'mahasiswa')
                <x-nav-link :href="route('mahasiswa.dashboard')" :active="request()->routeIs('mahasiswa.dashboard')" class="w-full justify-start">
                    {{ __('Dashboard') }}
                </x-nav-link>
                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Learning</div>
                <x-nav-link :href="route('mahasiswa.courses.index')" :active="request()->routeIs('mahasiswa.courses.*')" class="w-full justify-start">
                    {{ __('Mata Kuliah') }}
                </x-nav-link>
            @endif
        @endauth
        
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Community</div>
        <x-nav-link :href="route('discussions.index')" :active="request()->routeIs('discussions.*')" class="w-full justify-start">
            {{ __('Forum Diskusi') }}
        </x-nav-link>
    </div>

    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-3">
             <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-bold">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ ucfirst(Auth::user()->role) }}
                </p>
            </div>
        </div>
        
        <div class="mt-6 flex flex-col gap-3">
            <a href="{{ route('profile.edit') }}" class="w-full text-center text-sm font-medium bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 py-2.5 rounded-lg text-gray-700 dark:text-gray-300 transition-colors">
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full text-center text-sm font-medium bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 py-2.5 rounded-lg text-red-600 dark:text-red-400 transition-colors">
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>
