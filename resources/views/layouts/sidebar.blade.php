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
            <span>PBL App</span>
        </a>
        
        <!-- Mobile Close Button -->
        <button @click="open = false" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <div class="space-y-1">
            @php
                $activeClass = 'bg-purple-100 text-purple-700 font-medium dark:bg-purple-900/50 dark:text-purple-300 rounded-md';
                $inactiveClass = 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-700/50 rounded-md';
            @endphp
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Dashboard') }}
                    </a>
                    
                    <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Manajemen</div>
                    
                    <a href="{{ route('admin.grades.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('admin.grades.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Manajemen Nilai') }}
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Users') }}
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('admin.courses.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Courses') }}
                    </a>
                    <a href="{{ route('admin.academic-years.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('admin.academic-years.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Tahun Akademik') }}
                    </a>
                    <a href="{{ route('admin.semesters.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('admin.semesters.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Semester') }}
                    </a>
                    
                @elseif(auth()->user()->role === 'dosen')
                    <a href="{{ route('dosen.dashboard') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('dosen.dashboard') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Dashboard') }}
                    </a>
                    
                    <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Manajemen</div>
                    
                    <a href="{{ route('dosen.grades.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('dosen.grades.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Manajemen Nilai') }}
                    </a>
                    
                    <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Materi & Tugas</div>
                    
                    @if(isset($dosenCourses) && $dosenCourses->count() > 0)
                        <div class="space-y-2 pb-2">
                            @foreach($dosenCourses as $course)
                                @php
                                    $reqCourseId = request()->route('course') instanceof \App\Models\Course ? request()->route('course')->id : request()->route('course');
                                    $isCourseActive = request()->routeIs('dosen.materials.*', 'dosen.assignments.*') && $reqCourseId == $course->id;
                                @endphp
                                <div class="w-full">
                                    <div class="w-full flex items-center justify-between pl-8 pr-3 py-1 text-sm text-gray-700 font-medium dark:text-gray-300">
                                        <span class="truncate pr-2">{{ Str::limit($course->nama_matkul, 28) }}</span>
                                    </div>
                                    
                                    <div class="mt-1 space-y-1">
                                        <a href="{{ route('dosen.materials.index', $course) }}" class="block w-full py-1.5 pl-12 pr-3 {{ request()->routeIs('dosen.materials.*') && $reqCourseId == $course->id ? $activeClass : $inactiveClass }} text-sm transition-colors">
                                            Kelola Materi
                                        </a>
                                        <a href="{{ route('dosen.assignments.index', $course) }}" class="block w-full py-1.5 pl-12 pr-3 {{ request()->routeIs('dosen.assignments.*') && $reqCourseId == $course->id ? $activeClass : $inactiveClass }} text-sm transition-colors">
                                            Kelola Tugas
                                        </a>
                                        <a href="{{ route('dosen.conferences.index', $course) }}" class="block w-full py-1.5 pl-12 pr-3 {{ request()->routeIs('dosen.conferences.*') && $reqCourseId == $course->id ? $activeClass : $inactiveClass }} text-sm transition-colors">
                                            Kelas Virtual
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="pl-8 pr-3 text-xs text-gray-500 italic">Belum ada mata kuliah</div>
                    @endif
                    
                @elseif(auth()->user()->role === 'mahasiswa')
                    <a href="{{ route('mahasiswa.dashboard') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('mahasiswa.dashboard') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Dashboard') }}
                    </a>
                    
                    <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Learning</div>
                    
                    <a href="{{ route('mahasiswa.grades.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('mahasiswa.grades.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Nilai Saya') }}
                    </a>
                    <a href="{{ route('mahasiswa.courses.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('mahasiswa.courses.*', 'mahasiswa.materials.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Mata Kuliah') }}
                    </a>
                    <a href="{{ route('mahasiswa.conferences.index', request()->route('course') instanceof \App\Models\Course ? request()->route('course') : (\App\Models\Course::whereHas('students', fn($q) => $q->where('mahasiswa_id', auth()->id()))->first() ?? 1)) }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('mahasiswa.conferences.*') ? $activeClass : $inactiveClass }} text-sm transition-colors">
                        {{ __('Kelas Virtual') }}
                    </a>
                @endif
            @endauth
            
            <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Community</div>
            
            <a href="{{ route('discussions.index') }}" class="block w-full pl-8 pr-3 py-2 {{ request()->routeIs('discussions.*') ? $activeClass : $inactiveClass }} text-sm transition-colors mb-4">
                {{ __('Forum Diskusi') }}
            </a>
        </div>
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
