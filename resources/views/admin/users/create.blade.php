<x-app-layout>
 <div class="space-y-6">
 <!-- Page Header -->
 <div class="flex justify-between items-center">
 <h2 class="text-2xl font-extrabold text-on-surface tracking-tight font-headline">Create New User</h2>
 <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-primary-container transition-colors">
 <span class="material-symbols-outlined text-lg">arrow_back</span>
 Back to Users
 </a>
 </div>

 <!-- Form Card -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm p-8 max-w-2xl">
 <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
 @csrf

 <div class="form-group">
 <label class="form-label" for="name">Name</label>
 <input class="form-input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
 @error('name') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group">
 <label class="form-label" for="email">Email</label>
 <input class="form-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
 @error('email') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group" x-data="{ role: '{{ old('role') }}' }">
 <label class="form-label" for="role">Role</label>
 <select class="form-select" id="role" name="role" required x-model="role">
 <option value="" disabled selected>Select Role</option>
 <option value="mahasiswa">Mahasiswa</option>
 <option value="dosen">Dosen</option>
 <option value="admin">Admin</option>
 </select>
 @error('role') <span class="form-error">{{ $message }}</span> @enderror

 <div class="mt-4" x-show="role === 'mahasiswa'">
 <label class="form-label" for="nim">NIM</label>
 <input class="form-input" id="nim" type="text" name="nim" value="{{ old('nim') }}">
 @error('nim') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="mt-4" x-show="role === 'dosen'">
 <label class="form-label" for="nip">NIP</label>
 <input class="form-input" id="nip" type="text" name="nip" value="{{ old('nip') }}">
 @error('nip') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="mt-4" x-show="role === 'mahasiswa'">
 <label class="form-label" for="student_class_id">Kelas</label>
 <select class="form-select" id="student_class_id" name="student_class_id">
 <option value="">Pilih Kelas</option>
 @foreach($classes as $class)
 <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
 {{ $class->name }} ({{ $class->studyProgram->name }})
 </option>
 @endforeach
 </select>
 @error('student_class_id') <span class="form-error">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="form-group" x-data="{ show: false }">
 <label class="form-label" for="password">Password</label>
 <div class="relative">
 <input class="form-input pr-12" id="password" type="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password">
 <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 @error('password') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group" x-data="{ show: false }">
 <label class="form-label" for="password_confirmation">Confirm Password</label>
 <div class="relative">
 <input class="form-input pr-12" id="password_confirmation" type="password" :type="show ? 'text' : 'password'" name="password_confirmation" required>
 <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors focus:outline-none flex items-center justify-center">
 <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
 </button>
 </div>
 </div>

 <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container-low">
 <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors">Cancel</a>
 <button type="submit" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">Create User</button>
 </div>
 </form>
 </div>
 </div>
</x-app-layout>
