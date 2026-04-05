<nav x-data="{ open: false }" class="bg-surface-container-lowest border-b border-surface-container-low">
 <!-- Primary Navigation Menu -->
 <div class="px-4 sm:px-6 lg:px-8">
 <div class="flex justify-between h-16">
 <div class="flex">
 <!-- Logo -->
 <div class="shrink-0 flex items-center">
 <a href="{{ route('admin.dashboard') }}">
 <x-application-logo class="block h-9 w-auto fill-current text-on-surface" />
 </a>
 </div>

 <!-- Navigation Links -->
 <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
 @auth
 @if(auth()->user()->role === 'admin')
 <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
 {{ __('Dashboard') }}
 </x-nav-link>
 <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
 {{ __('Users') }}
 </x-nav-link>
 <x-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')">
 {{ __('Courses') }}
 </x-nav-link>
 <x-nav-link :href="route('admin.academic-years.index')" :active="request()->routeIs('admin.academic-years.*')">
 {{ __('Tahun Akademik') }}
 </x-nav-link>
 <x-nav-link :href="route('admin.semesters.index')" :active="request()->routeIs('admin.semesters.*')">
 {{ __('Semester') }}
 </x-nav-link>
 <x-nav-link :href="route('admin.hierarchy.index')" :active="request()->routeIs('admin.hierarchy.*')">
 {{ __('Data Akademik') }}
 </x-nav-link>
 <x-nav-link :href="route('admin.grades.index')" :active="request()->routeIs('admin.grades.*')">
 {{ __('Manajemen Nilai') }}
 </x-nav-link>
 @elseif(auth()->user()->role === 'dosen')
 <x-nav-link :href="route('dosen.dashboard')" :active="request()->routeIs('dosen.dashboard')">
 {{ __('Dashboard') }}
 </x-nav-link>
 <x-nav-link :href="route('dosen.grades.index')" :active="request()->routeIs('dosen.grades.*')">
 {{ __('Manajemen Nilai') }}
 </x-nav-link>
 @elseif(auth()->user()->role === 'mahasiswa')
 <x-nav-link :href="route('mahasiswa.dashboard')" :active="request()->routeIs('mahasiswa.dashboard')">
 {{ __('Dashboard') }}
 </x-nav-link>
 <x-nav-link :href="route('mahasiswa.courses.index')" :active="request()->routeIs('mahasiswa.courses.*')">
 {{ __('Mata Kuliah') }}
 </x-nav-link>
 <x-nav-link :href="route('mahasiswa.grades.index')" :active="request()->routeIs('mahasiswa.grades.*')">
 {{ __('Manajemen Nilai') }}
 </x-nav-link>
 @endif
 @endauth
 </div>
 </div>

 <!-- Settings Dropdown -->
 <div class="hidden sm:flex sm:items-center sm:ms-6">
 <x-dropdown align="right" width="48">
 <x-slot name="trigger">
 <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-xl text-on-surface-variant bg-surface-container-lowest hover:text-on-surface focus:outline-none transition ease-in-out duration-150">
 <div>{{ Auth::user()->name }}</div>
 <div class="ms-1">
 <span class="material-symbols-outlined text-base">expand_more</span>
 </div>
 </button>
 </x-slot>

 <x-slot name="content">
 <x-dropdown-link :href="route('profile.edit')">
 {{ __('Profile') }}
 </x-dropdown-link>
 <form method="POST" action="{{ route('logout') }}">
 @csrf
 <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
 {{ __('Log Out') }}
 </x-dropdown-link>
 </form>
 </x-slot>
 </x-dropdown>
 </div>

 <!-- Hamburger -->
 <div class="-me-2 flex items-center sm:hidden">
 <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low focus:outline-none transition duration-150 ease-in-out">
 <span class="material-symbols-outlined text-2xl" x-text="open ? 'close' : 'menu'">menu</span>
 </button>
 </div>
 </div>
 </div>

 <!-- Responsive Navigation Menu -->
 <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
 <div class="pt-2 pb-3 space-y-1">
 @auth
 @if(auth()->user()->role === 'admin')
 <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
 {{ __('Dashboard') }}
 </x-responsive-nav-link>
 <x-responsive-nav-link :href="route('admin.grades.index')" :active="request()->routeIs('admin.grades.*')">
 {{ __('Manajemen Nilai') }}
 </x-responsive-nav-link>
 <x-responsive-nav-link :href="route('admin.hierarchy.index')" :active="request()->routeIs('admin.hierarchy.*')">
 {{ __('Data Akademik') }}
 </x-responsive-nav-link>
 @elseif(auth()->user()->role === 'dosen')
 <x-responsive-nav-link :href="route('dosen.dashboard')" :active="request()->routeIs('dosen.dashboard')">
 {{ __('Dashboard') }}
 </x-responsive-nav-link>
 <x-responsive-nav-link :href="route('dosen.grades.index')" :active="request()->routeIs('dosen.grades.*')">
 {{ __('Manajemen Nilai') }}
 </x-responsive-nav-link>
 @elseif(auth()->user()->role === 'mahasiswa')
 <x-responsive-nav-link :href="route('mahasiswa.dashboard')" :active="request()->routeIs('mahasiswa.dashboard')">
 {{ __('Dashboard') }}
 </x-responsive-nav-link>
 <x-responsive-nav-link :href="route('mahasiswa.grades.index')" :active="request()->routeIs('mahasiswa.grades.*')">
 {{ __('Manajemen Nilai') }}
 </x-responsive-nav-link>
 @endif
 @endauth
 </div>

 <!-- Responsive Settings Options -->
 <div class="pt-4 pb-1 border-t border-surface-container-low">
 <div class="px-4">
 <div class="font-bold text-base text-on-surface">{{ Auth::user()->name }}</div>
 <div class="font-medium text-sm text-on-surface-variant">{{ Auth::user()->email }}</div>
 </div>

 <div class="mt-3 space-y-1">
 <x-responsive-nav-link :href="route('profile.edit')">
 {{ __('Profile') }}
 </x-responsive-nav-link>
 <form method="POST" action="{{ route('logout') }}">
 @csrf
 <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
 {{ __('Log Out') }}
 </x-responsive-nav-link>
 </form>
 </div>
 </div>
 </div>
</nav>
