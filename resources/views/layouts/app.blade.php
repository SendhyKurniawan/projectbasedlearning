<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PBL') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased h-screen overflow-hidden bg-gray-100 dark:bg-gray-900">
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
                 class="fixed inset-0 bg-gray-900/50 z-20 lg:hidden">
            </div>

            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content Wrapper -->
            <div class="flex-1 flex flex-col h-full overflow-hidden relative [transform:translateZ(0)]">
                
                <!-- Mobile Toggle (Floating) -->
                <div class="lg:hidden absolute top-4 left-4 z-50">
                    <button @click="open = !open" class="p-2 rounded-md text-gray-500 bg-white dark:bg-gray-800 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white dark:bg-gray-800 shadow relative z-30">
                        <div class="py-6 px-4 sm:px-6 lg:px-8 pt-16 lg:pt-6 flex justify-between items-center">
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
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900 p-6 {{ !isset($header) ? 'pt-16 lg:pt-6' : '' }}">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @livewireScripts

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
