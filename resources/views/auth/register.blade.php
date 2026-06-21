<x-guest-layout title="Registrasi">
 <!-- Page Header -->
 <div class="mb-8">
 <h2 class="font-headline text-3xl font-extrabold text-on-surface tracking-tight mb-2">Registrasi</h2>
 <p class="text-on-surface-variant text-sm">Bergabung dengan The PBL Workspace.</p>
 </div>

 <form method="POST" action="{{ route('register') }}" class="space-y-5">
 @csrf
 <input type="hidden" name="role" id="role-input" value="{{ old('role', 'mahasiswa') }}">

 {{-- Role Tabs --}}
 <div>
 <label class="block text-sm font-semibold text-on-surface-variant mb-2">Daftar sebagai</label>
 <div class="flex gap-3">
 <button type="button" id="tab-mahasiswa" onclick="switchRole('mahasiswa')"
 class="flex-1 py-3 px-4 text-sm font-bold rounded-xl border-2 transition-all duration-200
 {{ old('role', 'mahasiswa') !== 'dosen' ? 'bg-primary text-on-primary border-primary shadow-lg shadow-primary/20' : 'bg-surface-container-highest text-on-surface-variant border-transparent hover:bg-surface-container-high' }}">
 Mahasiswa
 </button>
 <button type="button" id="tab-dosen" onclick="switchRole('dosen')"
 class="flex-1 py-3 px-4 text-sm font-bold rounded-xl border-2 transition-all duration-200
 {{ old('role') === 'dosen' ? 'bg-primary text-on-primary border-primary shadow-lg shadow-primary/20' : 'bg-surface-container-highest text-on-surface-variant border-transparent hover:bg-surface-container-high' }}">
 Dosen
 </button>
 </div>
 </div>

 {{-- Dosen Approval Notice --}}
 <div id="dosen-notice" class="{{ old('role') === 'dosen' ? '' : 'hidden' }} text-sm text-on-warning bg-warning-light border-l-4 border-warning rounded-xl p-4">
 Akun dosen memerlukan <strong>persetujuan admin</strong> sebelum dapat digunakan.
 </div>

 {{-- Name --}}
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="name">Nama Lengkap</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">person</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="name" name="name" type="text" :value="old('name')" placeholder="Nama lengkap Anda" required autofocus autocomplete="name" />
 </div>
 <x-input-error :messages="$errors->get('name')" class="mt-1" />
 </div>

 {{-- Email --}}
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="email">Email</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">alternate_email</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="email" name="email" type="email" :value="old('email')" placeholder="contoh@email.com" required autocomplete="username" />
 </div>
 <x-input-error :messages="$errors->get('email')" class="mt-1" />
 </div>

 {{-- NIM (Mahasiswa) --}}
 <div id="field-nim" class="space-y-2 {{ old('role') === 'dosen' ? 'hidden' : '' }}">
 <label class="block text-sm font-semibold text-on-surface-variant" for="nim">NIM</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">badge</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="nim" name="nim" type="text" :value="old('nim')" placeholder="Nomor Induk Mahasiswa" autocomplete="off" maxlength="20" />
 </div>
 <x-input-error :messages="$errors->get('nim')" class="mt-1" />
 </div>

 {{-- Kode Kelas (Mahasiswa) --}}
 <div id="field-class" class="space-y-2 {{ old('role') === 'dosen' ? 'hidden' : '' }}">
 <label class="block text-sm font-semibold text-on-surface-variant" for="student_class_id">Kode Kelas</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">meeting_room</span>
 <select id="student_class_id" name="student_class_id"
 class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface appearance-none">
 <option value="">Pilih Kode Kelas</option>
 @isset($studentClasses)
 @foreach($studentClasses as $klass)
 <option value="{{ $klass->id }}" {{ old('student_class_id') == $klass->id ? 'selected' : '' }}>
 {{ optional($klass->studyProgram)->name ? $klass->studyProgram->name . ' — ' : '' }}{{ $klass->name }}
 </option>
 @endforeach
 @endisset
 </select>
 </div>
 <x-input-error :messages="$errors->get('student_class_id')" class="mt-1" />
 </div>

 {{-- NIP (Dosen) --}}
 <div id="field-nip" class="space-y-2 {{ old('role') === 'dosen' ? '' : 'hidden' }}">
 <label class="block text-sm font-semibold text-on-surface-variant" for="nip">NIP</label>
 <div class="relative">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">badge</span>
 <input class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="nip" name="nip" type="text" :value="old('nip')" placeholder="Nomor Induk Pegawai" autocomplete="off" maxlength="20" />
 </div>
 <x-input-error :messages="$errors->get('nip')" class="mt-1" />
 </div>

 {{-- Password --}}
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="password">Password</label>
 <div class="relative" x-data="{ show: false }">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">lock</span>
 <input class="w-full pl-12 pr-12 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="password" name="password" type="password" :type="show ? 'text' : 'password'" placeholder="Minimal 8 karakter" required autocomplete="new-password" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password')" class="mt-1" />
 </div>

 {{-- Confirm Password --}}
 <div class="space-y-2">
 <label class="block text-sm font-semibold text-on-surface-variant" for="password_confirmation">Konfirmasi Password</label>
 <div class="relative" x-data="{ show: false }">
 <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-xl">lock</span>
 <input class="w-full pl-12 pr-12 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all outline-none text-on-surface placeholder:text-outline/60"
 id="password_confirmation" name="password_confirmation" type="password" :type="show ? 'text' : 'password'" placeholder="Ulangi password Anda" required autocomplete="new-password" />
 <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
 </div>

 <!-- Submit Button -->
 <button class="w-full architectural-gradient py-4 rounded-xl text-white font-headline font-bold text-lg shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all" type="submit" id="btn-submit">
 Daftar Sekarang
 </button>
 </form>

 <footer class="mt-8 text-center">
 <p class="text-on-surface-variant text-sm">
 Sudah punya akun?
 <a class="text-primary font-bold hover:underline underline-offset-4 decoration-2" href="{{ route('login') }}">Login</a>
 </p>
 </footer>

 <script>
 function switchRole(role) {
 const roleInput = document.getElementById('role-input');
 const tabMhs = document.getElementById('tab-mahasiswa');
 const tabDosen = document.getElementById('tab-dosen');
 const fieldNim = document.getElementById('field-nim');
 const fieldNip = document.getElementById('field-nip');
 const fieldClass = document.getElementById('field-class');
 const notice = document.getElementById('dosen-notice');
 const nimInput = document.getElementById('nim');
 const nipInput = document.getElementById('nip');
 const classInput = document.getElementById('student_class_id');

 const activeClass = ['bg-primary', 'text-white', 'border-primary', 'shadow-lg', 'shadow-primary/20'];
 const inactiveClass = ['bg-surface-container-highest', 'text-on-surface-variant', 'border-transparent', 'hover:bg-surface-container-high'];

 roleInput.value = role;

 if (role === 'mahasiswa') {
 tabMhs.classList.add(...activeClass);
 tabMhs.classList.remove(...inactiveClass);
 tabDosen.classList.add(...inactiveClass);
 tabDosen.classList.remove(...activeClass);

 fieldNim.classList.remove('hidden');
 fieldNip.classList.add('hidden');
 fieldClass.classList.remove('hidden');
 notice.classList.add('hidden');

 nimInput.required = true;
 nipInput.required = false;
 nipInput.value = '';
 classInput.required = true;
 } else {
 tabDosen.classList.add(...activeClass);
 tabDosen.classList.remove(...inactiveClass);
 tabMhs.classList.add(...inactiveClass);
 tabMhs.classList.remove(...activeClass);

 fieldNip.classList.remove('hidden');
 fieldNim.classList.add('hidden');
 fieldClass.classList.add('hidden');
 notice.classList.remove('hidden');

 nipInput.required = true;
 nimInput.required = false;
 nimInput.value = '';
 classInput.required = false;
 classInput.value = '';
 }
 }
 </script>
</x-guest-layout>
