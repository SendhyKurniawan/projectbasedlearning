<header class="bg-surface-container-lowest/80 backdrop-blur-md shadow-sm h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 sticky top-0 border-b border-surface-container-low/50">
 <!-- Mobile Hamburger -->
 <button @click="open = !open" class="lg:hidden p-2 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low focus:outline-none transition-colors">
 <span class="material-symbols-outlined text-2xl">menu</span>
 </button>

 <!-- Page Title Placeholder -->
 <div class="flex-1 px-4"></div>

 <!-- Right Actions -->
 <div class="flex items-center gap-3">
 {{-- Dark mode toggle --}}
 <button
  onclick="(function(btn){var t=localStorage.getItem('theme')||'system';var next=t==='system'?'dark':t==='dark'?'light':'system';window.setTheme(next);btn.querySelector('.theme-icon').textContent=next==='dark'?'dark_mode':next==='light'?'light_mode':'contrast';})(this)"
  class="p-2 rounded-xl text-on-surface-variant bg-surface-container-lowest hover:bg-surface-container-low focus:outline-none transition-colors"
  title="Toggle dark mode"
 >
  <span class="material-symbols-outlined text-xl theme-icon">contrast</span>
 </button>

 {{-- Notification Bell --}}
 @include('layouts.notifications')

 <!-- User Dropdown -->
 <x-dropdown align="right" width="48">
 <x-slot name="trigger">
 <button class="flex items-center gap-2 text-sm font-bold text-on-surface-variant hover:text-on-surface focus:outline-none transition-colors p-2 rounded-xl hover:bg-surface-container-low">
 <div class="w-8 h-8 rounded-full bg-primary-fixed text-primary flex items-center justify-center font-bold text-xs">
 {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
 </div>
 <div class="hidden sm:block">{{ Auth::user()->name }}</div>
 <span class="material-symbols-outlined text-base hidden sm:block">expand_more</span>
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
</header>
