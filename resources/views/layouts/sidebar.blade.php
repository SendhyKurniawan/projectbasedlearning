<aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-72 bg-surface-container-low transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col h-full shrink-0 py-8 px-6 overflow-y-auto border-r border-outline-variant/10">
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
 <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shrink-0">
 <span class="material-symbols-outlined text-on-primary" style="font-variation-settings: 'FILL' 1;">school</span>
 </div>
 <div>
 <h1 class="text-xl font-bold tracking-tight text-primary font-headline">PBL Workspace</h1>
 <p class="text-[0.6875rem] font-label text-on-surface-variant tracking-wider uppercase">Pembelajaran Berbasis Proyek</p>
 </div>
 </a>

 <!-- Mobile Close Button -->
 <button @click="open = false" class="lg:hidden ml-auto text-on-surface-variant hover:text-on-surface focus:outline-none transition-colors">
 <span class="material-symbols-outlined">close</span>
 </button>
 </div>

 <!-- Navigation -->
 <nav class="flex-1 space-y-1">
 @php
 $activeClass = 'flex items-center gap-3 px-4 py-3 text-primary font-bold bg-primary/5 border-r-4 border-secondary transition-all rounded-l-xl';
 $inactiveClass = 'flex items-center gap-3 px-4 py-3 text-on-surface-variant hover:text-primary hover:bg-surface-container transition-colors duration-200 rounded-xl';
 $sectionLabelClass = 'pt-6 pb-2 pl-4 pr-3 text-[10px] font-black text-on-surface-variant/60 uppercase tracking-widest';
 @endphp
 @auth
 @if(auth()->user()->role === 'admin')
 <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
 <span class="text-sm font-body">Dasbor</span>
 </a>

 <div class="{{ $sectionLabelClass }}">Manajemen</div>

 <a href="{{ route('admin.grades.index') }}" class="{{ request()->routeIs('admin.grades.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.grades.*')) style="font-variation-settings: 'FILL' 1;" @endif>grade</span>
 <span class="text-sm font-body">Manajemen Nilai</span>
 </a>
 <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.users.*')) style="font-variation-settings: 'FILL' 1;" @endif>group</span>
 <span class="text-sm font-body">Pengguna</span>
 </a>
 <a href="{{ route('admin.courses.index') }}" class="{{ request()->routeIs('admin.courses.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.courses.*')) style="font-variation-settings: 'FILL' 1;" @endif>auto_stories</span>
 <span class="text-sm font-body">Mata Kuliah</span>
 </a>
 <a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.academic-years.*')) style="font-variation-settings: 'FILL' 1;" @endif>calendar_today</span>
 <span class="text-sm font-body">Tahun Akademik</span>
 </a>
 <a href="{{ route('admin.semesters.index') }}" class="{{ request()->routeIs('admin.semesters.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.semesters.*')) style="font-variation-settings: 'FILL' 1;" @endif>date_range</span>
 <span class="text-sm font-body">Semester</span>
 </a>
 <a href="{{ route('admin.hierarchy.index') }}" class="{{ request()->routeIs('admin.hierarchy.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('admin.hierarchy.*')) style="font-variation-settings: 'FILL' 1;" @endif>account_tree</span>
 <span class="text-sm font-body">Data Akademik</span>
 </a>

 @elseif(auth()->user()->role === 'dosen')
 <a href="{{ route('dosen.dashboard') }}" class="{{ request()->routeIs('dosen.dashboard') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('dosen.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
 <span class="text-sm font-body">Dasbor</span>
 </a>

 <div class="{{ $sectionLabelClass }}">Manajemen</div>

 <a href="{{ route('dosen.grades.index') }}" class="{{ request()->routeIs('dosen.grades.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('dosen.grades.*')) style="font-variation-settings: 'FILL' 1;" @endif>grade</span>
 <span class="text-sm font-body">Manajemen Nilai</span>
 </a>

 <div class="{{ $sectionLabelClass }}">Materi & Tugas</div>

 @if(isset($dosenCourses) && $dosenCourses->count() > 0)
 <div class="space-y-3 pb-2">
 @foreach($dosenCourses as $course)
 @php
 $reqCourseId = request()->route('course') instanceof \App\Models\Course ? request()->route('course')->id : request()->route('course');
 $isCourseActive = request()->routeIs('dosen.materials.*', 'dosen.assignments.*', 'dosen.conferences.*') && $reqCourseId == $course->id;
 @endphp
 <div class="w-full">
 <div class="flex items-center gap-2 px-4 py-1.5 text-sm font-bold text-on-surface font-headline">
 <span class="material-symbols-outlined text-primary text-base">book</span>
 <span class="truncate pr-2">{{ Str::limit($course->nama_matkul, 24) }}</span>
 </div>

 <div class="mt-1 ml-4 space-y-0.5 border-l-2 border-outline-variant/30 pl-3">
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
 </div>
 @endforeach
 </div>
 @else
 <div class="px-4 py-2 text-xs text-on-surface-variant flex items-center gap-2">
 <span class="material-symbols-outlined text-sm">info</span>
 Belum ada mata kuliah
 </div>
 @endif

 @elseif(auth()->user()->role === 'mahasiswa')
 <a href="{{ route('mahasiswa.dashboard') }}" class="{{ request()->routeIs('mahasiswa.dashboard') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
 <span class="text-sm font-body">Dasbor</span>
 </a>

 <div class="{{ $sectionLabelClass }}">Pembelajaran</div>

 <a href="{{ route('mahasiswa.grades.index') }}" class="{{ request()->routeIs('mahasiswa.grades.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.grades.*')) style="font-variation-settings: 'FILL' 1;" @endif>grade</span>
 <span class="text-sm font-body">Nilai Saya</span>
 </a>
 <a href="{{ route('mahasiswa.courses.index') }}" class="{{ request()->routeIs('mahasiswa.courses.*', 'mahasiswa.materials.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.courses.*', 'mahasiswa.materials.*')) style="font-variation-settings: 'FILL' 1;" @endif>auto_stories</span>
 <span class="text-sm font-body">Mata Kuliah</span>
 </a>
 @php
 $activeCourseParam = request()->route('course');
 $resolvedCourseForConference = $activeCourseParam instanceof \App\Models\Course ? $activeCourseParam : \App\Models\Course::whereHas('students', fn($q) => $q->where('mahasiswa_id', auth()->id()))->first();
 @endphp
 @if($resolvedCourseForConference)
 <a href="{{ route('mahasiswa.conferences.index', $resolvedCourseForConference) }}" class="{{ request()->routeIs('mahasiswa.conferences.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('mahasiswa.conferences.*')) style="font-variation-settings: 'FILL' 1;" @endif>videocam</span>
 <span class="text-sm font-body">Kelas Virtual</span>
 </a>
 @endif
 @endif
 @endauth

 <div class="{{ $sectionLabelClass }}">Komunitas</div>

 <a href="{{ route('discussions.index') }}" class="{{ request()->routeIs('discussions.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('discussions.*')) style="font-variation-settings: 'FILL' 1;" @endif>forum</span>
 <span class="text-sm font-body">Forum Diskusi</span>
 </a>
 <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? $activeClass : $inactiveClass }}">
 <span class="material-symbols-outlined" @if(request()->routeIs('announcements.*')) style="font-variation-settings: 'FILL' 1;" @endif>campaign</span>
 <span class="text-sm font-body">Pengumuman</span>
 </a>
 </nav>

 <!-- Bottom Section: Profil Pengguna + Aksi -->
 <div class="mt-auto pt-6 border-t border-outline-variant/20 space-y-4">
 <div class="flex items-center gap-3">
 <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center font-bold text-primary text-sm shrink-0">
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
 <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-primary transition-colors rounded-xl hover:bg-surface-container">
 <span class="material-symbols-outlined text-sm">person</span>
 <span class="text-xs font-label font-medium">Profil Saya</span>
 </a>
 <form method="POST" action="{{ route('logout') }}" class="w-full">
 @csrf
 <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-error transition-colors rounded-xl hover:bg-error/5">
 <span class="material-symbols-outlined text-sm">logout</span>
 <span class="text-xs font-label font-medium">Keluar</span>
 </button>
 </form>
 </div>
 </div>
</aside>
