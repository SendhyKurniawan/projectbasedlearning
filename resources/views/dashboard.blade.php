<x-app-layout>
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Dashboard') }}
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 {{ __("You're logged in!") }}
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
