<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 <head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
 <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
 <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
 <script>
  (function(){
   document.documentElement.classList.remove('dark');
   window.setTheme=function(){};
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
 @livewireStyles
 @stack('styles')
 @stack('head')
 </head>
 <body class="font-body antialiased h-screen overflow-hidden bg-background text-on-surface">
 <div x-data="{ open: false }" class="flex h-full">
 <!-- Mobile Backdrop -->
 <div x-show="open" 
 x-transition:enter="transition-opacity ease-linear duration-300"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="transition-opacity ease-linear duration-300"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0"
 @click="open = false"
 class="fixed inset-0 bg-on-surface/30 backdrop-blur-sm z-20 lg:hidden">
 </div>

 <!-- Sidebar -->
 @include('layouts.sidebar')

 <!-- Main Content Wrapper -->
 <div class="flex-1 flex flex-col h-full overflow-hidden relative [transform:translateZ(0)]">
 
 <!-- Mobile Toggle (Floating) -->
 <div class="lg:hidden absolute top-5 left-4 z-50">
 <button @click="open = !open" class="p-2.5 rounded-xl text-on-surface-variant bg-surface-container-lowest shadow-ambient hover:bg-surface-container-low focus:outline-none transition-all">
 <span class="material-symbols-outlined text-xl">menu</span>
 </button>
 </div>

 <!-- Page Heading -->
 @if (isset($header))
 <header class="bg-surface-container-lowest/80 backdrop-blur-md shadow-sm shadow-outline-variant/10 relative z-30 border-b border-outline-variant/10">
 <div class="py-5 px-4 sm:px-6 lg:px-8 pt-16 lg:pt-5 flex justify-between items-center">
 <div class="flex-1">
 {{ $header }}
 </div>
 <div class="ml-4 flex items-center">
 @include('layouts.notifications')
 </div>
 </div>
 </header>
 @else
 <!-- Floating Notification Bell if no header -->
 <div class="absolute top-4 right-6 z-50">
 @include('layouts.notifications')
 </div>
 @endif

 <!-- Page Content -->
 <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6 lg:p-8 {{ !isset($header) ? 'pt-24 lg:pt-28' : '' }}">
 {{ $slot }}
 </main>
 </div>
 </div>
 @livewireScripts
 @stack('scripts')

 <script>
 function urlBase64ToUint8Array(base64String) {
 const padding = '='.repeat((4 - base64String.length % 4) % 4);
 const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
 const rawData = window.atob(base64);
 return new Uint8Array([...rawData].map((char) => char.charCodeAt(0)));
 }
 if ('serviceWorker' in navigator && 'PushManager' in window) {
 window.addEventListener('load', function() {
 navigator.serviceWorker.register('/sw.js').then(function(registration) {
 Notification.requestPermission().then(function(permission) {
 if (permission === 'granted') {
 const vapidPublicKey = "{{ env('VAPID_PUBLIC_KEY') }}";
 if (vapidPublicKey) {
 registration.pushManager.subscribe({
 userVisibleOnly: true,
 applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
 }).then(function(subscription) {
 fetch('/push-subscribe', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
 'Accept': 'application/json'
 },
 body: JSON.stringify(subscription)
 });
 }).catch(function(err) {
 console.log('Push subscription error: ', err);
 });
 }
 }
 });
 }).catch(function(err) {
 console.log('SW registration error: ', err);
 });
 });
 }
 </script>
 </body>
</html>
