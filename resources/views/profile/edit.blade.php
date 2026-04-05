<x-app-layout>
 <div class="space-y-6">
 <h2 class="text-2xl font-extrabold text-on-surface tracking-tight font-headline">Profile</h2>

 <div class="space-y-6 max-w-2xl">
 <div class="p-6 sm:p-8 bg-surface-container-lowest rounded-2xl shadow-sm">
 @include('profile.partials.update-profile-information-form')
 </div>

 <div class="p-6 sm:p-8 bg-surface-container-lowest rounded-2xl shadow-sm">
 @include('profile.partials.update-password-form')
 </div>

 <div class="p-6 sm:p-8 bg-surface-container-lowest rounded-2xl shadow-sm">
 @include('profile.partials.delete-user-form')
 </div>
 </div>
 </div>
</x-app-layout>
