<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 <head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <script>
  (function(){
   var t=localStorage.getItem('theme')||'system';
   var prefersDark=window.matchMedia('(prefers-color-scheme: dark)').matches;
   if(t==='dark'||(t==='system'&&prefersDark))document.documentElement.classList.add('dark');
   window.setTheme=function(mode){
    localStorage.setItem('theme',mode);
    var dark=mode==='dark'||(mode==='system'&&window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark',dark);
   };
  })();
 </script>

 <title>{{ config('app.name', 'PBL Workspace') }}</title>

 <!-- Fonts: Scholar Tech Design System -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <!-- Inter (body font, LCP-critical) — loaded normally -->
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 <!-- Manrope (heading font only) + Material Symbols — deferred, non-blocking -->
 <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" onload="this.rel='stylesheet'">
 <noscript><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"></noscript>
 <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" onload="this.rel='stylesheet'">
 <noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"></noscript>

 <!-- Scripts -->
 @vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js'])
 @stack('styles')
 </head>
 <body class="font-body text-on-surface antialiased min-h-screen bg-surface">
 <main class="flex w-full min-h-screen overflow-hidden">
 <!-- Left Side: Visual Anchor (Desktop Only) -->
 <section class="hidden lg:flex lg:w-1/2 relative architectural-gradient flex-col justify-between p-16 text-white/90">
 <div class="z-10">
 <div class="flex items-center gap-3 mb-12">
 <div class="bg-white/10 backdrop-blur-md p-2.5 rounded-xl">
 <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">school</span>
 </div>
 <span class="font-headline font-bold text-2xl tracking-tighter">PBL Workspace</span>
 </div>
 <h1 class="font-headline text-5xl font-extrabold leading-tight tracking-tight mb-6">
 Building the future of <br/>Academic Excellence.
 </h1>
 <p class="text-white/70 text-lg max-w-md leading-relaxed">
 A digital workspace for Project-Based Learning, where rigorous structure meets high-tech collaboration.
 </p>
 </div>
 <!-- Bento-style Stats -->
 <div class="relative z-10 grid grid-cols-2 gap-4 mt-12 items-start">
 <div class="bg-white/5 backdrop-blur-xl p-6 rounded-2xl border border-white/10">
 <div class="text-secondary-container font-headline font-bold text-3xl mb-1">PBL</div>
 <div class="text-white/50 text-sm">Project-Based Learning</div>
 </div>
 <div class="bg-white/5 backdrop-blur-xl p-6 rounded-2xl border border-white/10">
 <div class="text-secondary-container font-headline font-bold text-3xl mb-1">Live</div>
 <div class="text-white/50 text-sm">Real-time Collaboration</div>
 </div>
 </div>
 <!-- Background Overlay -->
 <div class="absolute inset-0 architectural-gradient opacity-80 z-0"></div>
 </section>

 <!-- Right Side: Form -->
 <section class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8 sm:p-12 lg:p-24 bg-surface">
 <div class="w-full max-w-md">
 <!-- Brand Mobile Header -->
 <div class="flex items-center gap-2 mb-12 lg:hidden">
 <span class="material-symbols-outlined text-primary text-3xl">school</span>
 <span class="font-headline font-extrabold text-xl text-primary tracking-tighter">PBL Workspace</span>
 </div>

 {{ $slot }}

 <!-- Global Footer Links -->
 <div class="mt-auto pt-12 flex gap-6 text-xs font-medium text-on-surface-variant/60">
 <span>&copy; {{ date('Y') }} PBL Workspace</span>
 </div>
 </div>
 </section>
 </main>
 </body>
</html>

