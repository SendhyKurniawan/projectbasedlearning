<aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-72 bg-surface-container-low transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col h-full shrink-0 py-8 px-6 overflow-y-auto">
 <!-- Brand Header -->
 <div class="mb-10 flex items-center gap-3">
 @php
 $dashboardRoute = match(auth()->user()->role ?? '') {
 'admin' => 'admin.dashboard',
 'dosen' => 'dosen.dashboard',
 'mahasiswa' => 'mahasiswa.dashboard',
 default => 'login',
 };
 @endphp
 <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-3">
 <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
 <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">school</span>
 </div>
 <div>
 <h1 class="text-xl font-bold tracking-tight text-primary font-headline">PBL Workspace</h1>
 <p class="text-[0.6875rem] font-label text-on-surface-variant tracking-wider uppercase">Project-Based Learning</p>
 </div>
 </a>
 
 <!-- Mobile Close Button -->
 <button @click="open = false" class="lg:hidden ml-auto text-outline hover:text-on-surface-variant focus:outline-none">
 <span class="material-symbols-outlined">close</span>
 </button>
 </div>

 <!-- Navigation -->
 <nav class="flex-1 space-y-1">
 @php
 $activeClass = 'flex items-center gap-3 px-4 py-3 text-on-primary font-bold bg-primary-container transition-all rounded-lg';
 $inactiveClass = 'flex items-center gap-3 px-4 py-3 text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors duration-200 rounded-lg';
 @endphp
 @auth
 @if(auth()->user()->role === 'admin')
 <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
 <span class="text-sm font-body">Dashboard</span>
 </a>
 
 <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-black text-outline uppercase tracking-widest">Manajemen</div>
 
 <a href="{{ route('admin.grades.index') }}" class="{{ request()->routeIs('admin.grades.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.grades.*')) style="font-variation-settings: 'FILL' 1;" @endif>grade</span>
 <span class="text-sm font-body">Manajemen Nilai</span>
 </a>
 <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.users.*')) style="font-variation-settings: 'FILL' 1;" @endif>group</span>
 <span class="text-sm font-body">Users</span>
 </a>
 <a href="{{ route('admin.courses.index') }}" class="{{ request()->routeIs('admin.courses.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.courses.*')) style="font-variation-settings: 'FILL' 1;" @endif>auto_stories</span>
 <span class="text-sm font-body">Courses</span>
 </a>
 <a href="{{ route('admin.akademik.index') }}" class="{{ request()->routeIs('admin.akademik.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.akademik.*')) style="font-variation-settings: 'FILL' 1;" @endif>account_tree</span>
 <span class="text-sm font-body">Akademik</span>
 </a>
 <a href="{{ route('admin.conferences.index') }}" class="{{ request()->routeIs('admin.conferences.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.conferences.*')) style="font-variation-settings: 'FILL' 1;" @endif>videocam</span>
 <span class="text-sm font-body">Kelas Virtual</span>
 </a>

 @elseif(auth()->user()->role === 'dosen')
 <a href="{{ route('dosen.dashboard') }}" class="{{ request()->routeIs('dosen.dashboard') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('dosen.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
 <span class="text-sm font-body">Dashboard</span>
 </a>
 
 <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-black text-outline uppercase tracking-widest">Manajemen</div>
 
 <a href="{{ route('dosen.grades.index') }}" class="{{ request()->routeIs('dosen.grades.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('dosen.grades.*')) style="font-variation-settings: 'FILL' 1;" @endif>grade</span>
 <span class="text-sm font-body">Manajemen Nilai</span>
 </a>
 
 <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-black text-outline uppercase tracking-widest">Materi & Tugas</div>
 
 @if(isset($dosenCourseGroups) && $dosenCourseGroups->count() > 0)
 @php
 $reqCourseId = request()->route('course') instanceof \App\Models\Course ? request()->route('course')->id : request()->route('course');
 $inContentRoutes = request()->routeIs('dosen.materials.*', 'dosen.assignments.*', 'dosen.conferences.*');
 @endphp
 <div class="space-y-1 pb-2">
 @foreach($dosenCourseGroups as $groupKey => $groupCourses)
 @php
 $groupActive = $inContentRoutes && $groupCourses->contains(fn($c) => $reqCourseId == $c->id);
 $multiKelas = $groupCourses->count() > 1;
 @endphp
 <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
 <button @click="open = !open" class="w-full flex items-center gap-2 px-4 py-1.5 text-on-surface hover:text-primary hover:bg-surface-container transition-colors rounded-lg">
 <span class="material-symbols-outlined text-primary text-base">book</span>
 <span class="flex-1 text-sm font-bold text-left truncate">{{ Str::limit($groupCourses->first()->nama_matkul, 22) }}</span>
 @if($multiKelas)
 <span class="text-[9px] font-bold text-on-surface-variant bg-surface-container px-1.5 py-0.5 rounded shrink-0">{{ $groupCourses->count() }}K</span>
 @endif
 <span class="material-symbols-outlined text-sm text-on-surface-variant transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''">expand_more</span>
 </button>
 <div x-show="open"
 x-transition:enter="transition ease-out duration-150"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="transition ease-in duration-100"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0">
 @foreach($groupCourses as $course)
 @php $courseActive = $inContentRoutes && $reqCourseId == $course->id; @endphp
 <div class="ml-4 mt-0.5 border-l-2 {{ $courseActive ? 'border-primary/60' : 'border-outline-variant/30' }} pl-3 mb-1.5">
 @if($multiKelas)
 <div class="py-0.5 mb-0.5">
 <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-primary/10 text-primary">{{ $course->studentClass->name ?? 'Kelas' }}</span>
 </div>
 @endif
 <a href="{{ route('dosen.materials.index', $course) }}" class="{{ request()->routeIs('dosen.materials.*') && $reqCourseId == $course->id ? $activeClass : $inactiveClass }} !py-2 !text-xs">
 <span class="material-symbols-outlined text-base">description</span>
 <span>Kelola Materi</span>
 </a>
 <a href="{{ route('dosen.assignments.index', $course) }}" class="{{ request()->routeIs('dosen.assignments.*') && $reqCourseId == $course->id ? $activeClass : $inactiveClass }} !py-2 !text-xs">
 <span class="material-symbols-outlined text-base">assignment</span>
 <span>Kelola Tugas</span>
 </a>
 <a href="{{ route('dosen.conferences.index', $course) }}" class="{{ request()->routeIs('dosen.conferences.*') && $reqCourseId == $course->id ? $activeClass : $inactiveClass }} !py-2 !text-xs">
 <span class="material-symbols-outlined text-base">videocam</span>
 <span>Kelas Virtual</span>
 </a>
 </div>
 @endforeach
 </div>
 </div>
 @endforeach
 </div>
 @else
 <div class="px-4 py-2 text-xs text-outline flex items-center gap-2">
 <span class="material-symbols-outlined text-sm">info</span>
 Belum ada mata kuliah
 </div>
 @endif
 
 @elseif(auth()->user()->role === 'mahasiswa')
 <a href="{{ route('mahasiswa.dashboard') }}" class="{{ request()->routeIs('mahasiswa.dashboard') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
 <span class="text-sm font-body">Dashboard</span>
 </a>
 
 <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-black text-outline uppercase tracking-widest">Learning</div>
 
 <a href="{{ route('mahasiswa.grades.index') }}" class="{{ request()->routeIs('mahasiswa.grades.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.grades.*')) style="font-variation-settings: 'FILL' 1;" @endif>grade</span>
 <span class="text-sm font-body">Nilai Saya</span>
 </a>
 <a href="{{ route('mahasiswa.courses.index') }}" class="{{ request()->routeIs('mahasiswa.courses.*', 'mahasiswa.materials.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.courses.*', 'mahasiswa.materials.*')) style="font-variation-settings: 'FILL' 1;" @endif>auto_stories</span>
 <span class="text-sm font-body">Mata Kuliah</span>
 </a>
 @php
 $resolvedCourseForConference = $mahasiswaFirstCourse ?? null;
 @endphp
 @if($resolvedCourseForConference)
 <a href="{{ route('mahasiswa.conferences.index', $resolvedCourseForConference) }}" class="{{ request()->routeIs('mahasiswa.conferences.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.conferences.*')) style="font-variation-settings: 'FILL' 1;" @endif>videocam</span>
 <span class="text-sm font-body">Kelas Virtual</span>
 </a>
 @endif
 @endif
 @endauth
 
 <div class="pt-6 pb-2 pl-4 pr-3 text-[10px] font-black text-outline uppercase tracking-widest">Community</div>
 
 <a href="{{ route('discussions.index') }}" class="{{ request()->routeIs('discussions.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('discussions.*')) style="font-variation-settings: 'FILL' 1;" @endif>forum</span>
 <span class="text-sm font-body">Forum Diskusi</span>
 
 </a>
 <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('announcements.*')) style="font-variation-settings: 'FILL' 1;" @endif>campaign</span>
 <span class="text-sm font-body">Pengumuman</span>
 </a>
 </nav>

 <!-- Bottom Section: User Profile + Actions -->
 <div class="mt-auto pt-6 border-t border-outline-variant/30 space-y-4">
 <div class="flex items-center gap-3">
 <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-primary text-sm">
 {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
 </div>
 <div class="flex-1 min-w-0">
 <p class="text-sm font-bold text-on-surface truncate">
 {{ Auth::user()->name }}
 </p>
 <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">
 {{ ucfirst(Auth::user()->role) }}
 </p>
 </div>
 </div>
 
 <div class="space-y-1">
 <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-primary transition-colors rounded-lg hover:bg-surface-container-low">
 <span class="material-symbols-outlined text-sm">person</span>
 <span class="text-xs font-label font-medium">Profile</span>
 </a>
 <form method="POST" action="{{ route('logout') }}" class="w-full">
 @csrf
 <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-error transition-colors rounded-lg hover:bg-error-container">
 <span class="material-symbols-outlined text-sm">logout</span>
 <span class="text-xs font-label font-medium">Logout</span>
 </button>
 </form>
 </div>
 </div>
</aside>

