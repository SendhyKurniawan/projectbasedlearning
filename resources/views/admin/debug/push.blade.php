<x-app-layout>
 <x-slot name="header">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Debug Push Notifications') }}
 </h2>
 </x-slot>

 <div class="space-y-6">
 <div class="max-w-7xl mx-auto ">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <h3 class="text-lg font-medium mb-4">Kirim Test Push Notification</h3>
 
 @if (session('success'))
 <div class="mb-4 text-green-600">
 {{ session('success') }}
 </div>
 @endif

 <form action="{{ route('admin.debug.push.send') }}" method="POST">
 @csrf
 <div class="mb-4">
 <x-input-label for="user_id" :value="__('Pilih User (Hanya yang tersubscribe)')" />
 <select id="user_id" name="user_id" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-primary/20:ring-indigo-600 rounded-md shadow-sm" required>
 <option value="">-- Pilih User --</option>
 @php
 $roles = ['admin' => 'Admin', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'];
 @endphp
 @foreach($roles as $roleKey => $roleName)
 <optgroup label="{{ $roleName }}">
 @foreach($users->where('role', $roleKey) as $user)
 @php $isSubscribed = $user->pushSubscriptions->count() > 0; @endphp
 <option value="{{ $user->id }}">
 {{ $user->name }} ({{ $user->email }}) 
 [{{ $isSubscribed ? 'SUBSCRIBED' : 'NO PUSH SUBSCRIPTION' }}]
 </option>
 @endforeach
 </optgroup>
 @endforeach
 </select>
 <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="title" :value="__('Judul Notifikasi')" />
 <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" value="Test Push Notification" placeholder="Masukkan judul..." />
 <x-input-error :messages="$errors->get('title')" class="mt-2" />
 </div>

 <div class="mb-4">
 <x-input-label for="message" :value="__('Pesan Notifikasi')" />
 <textarea id="message" name="message" class="block mt-1 w-full border-outline-variant/30 text-on-surface focus:border-indigo-500:border-indigo-600 focus:ring-primary/20:ring-indigo-600 rounded-md shadow-sm" rows="3" placeholder="Masukkan pesan...">Ini adalah pesan percobaan untuk push notification.</textarea>
 <x-input-error :messages="$errors->get('message')" class="mt-2" />
 </div>

 <div class="flex items-center justify-end mt-4">
 <x-primary-button class="ml-4">
 {{ __('Kirim Test Push') }}
 </x-primary-button>
 </div>
 </form>
 </div>
 </div>

 <div class="mt-8 bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 <h3 class="text-lg font-medium mb-4">Informasi Tambahan</h3>
 <p class="text-sm text-on-surface-variant">
 Pastikan browser Anda sudah memberikan izin (permission) untuk notifikasi dan user sudah melakukan registrasi service worker. Daftar user di atas hanya menampilkan user yang memiliki record di tabel <code>push_subscriptions</code>.
 </p>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
