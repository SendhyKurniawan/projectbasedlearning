<x-app-layout>
 <div class="space-y-6">
 <!-- Page Header -->
 <div class="flex justify-between items-center">
 <h2 class="text-2xl font-extrabold text-on-surface tracking-tight font-headline">Edit User</h2>
 <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-primary-container transition-colors">
 <span class="material-symbols-outlined text-lg">arrow_back</span>
 Back to Users
 </a>
 </div>

 <!-- Form Card -->
 <div class="bg-surface-container-lowest rounded-2xl shadow-sm p-8 max-w-2xl">
 <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
 @csrf
 @method('PUT')

 <div class="form-group">
 <label class="form-label" for="name">Name</label>
 <input class="form-input" id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus>
 @error('name') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group">
 <label class="form-label" for="email">Email</label>
 <input class="form-input" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
 @error('email') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group" x-data="{ role: '{{ old('role', $user->role) }}' }">
 <label class="form-label" for="role">Role</label>
 <select class="form-select" id="role" name="role" required x-model="role">
 <option value="mahasiswa">Mahasiswa</option>
 <option value="dosen">Dosen</option>
 <option value="admin">Admin</option>
 </select>
 @error('role') <span class="form-error">{{ $message }}</span> @enderror

 <div class="mt-4" x-show="role === 'mahasiswa'">
 <label class="form-label" for="nim">NIM</label>
 <input class="form-input" id="nim" type="text" name="nim" value="{{ old('nim', $user->nim) }}">
 @error('nim') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="mt-4" x-show="role === 'dosen'">
 <label class="form-label" for="nip">NIP</label>
 <input class="form-input" id="nip" type="text" name="nip" value="{{ old('nip', $user->nip) }}">
 @error('nip') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="mt-4" x-show="role === 'mahasiswa'">
 <label class="form-label" for="student_class_id">Kelas</label>
 <select class="form-select" id="student_class_id" name="student_class_id">
 <option value="">Pilih Kelas</option>
 @foreach($classes as $class)
 <option value="{{ $class->id }}" {{ old('student_class_id', $user->student_class_id) == $class->id ? 'selected' : '' }}>
 {{ $class->name }} ({{ $class->studyProgram->name }})
 </option>
 @endforeach
 </select>
 @error('student_class_id') <span class="form-error">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="pt-4 border-t border-surface-container-low">
 <h3 class="text-lg font-bold font-headline text-on-surface mb-1">Change Password</h3>
 <p class="text-xs text-on-surface-variant mb-4">Leave blank to keep current password.</p>
 </div>

 <div class="form-group">
 <label class="form-label" for="password">New Password</label>
 <input class="form-input" id="password" type="password" name="password" autocomplete="new-password">
 @error('password') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-group">
 <label class="form-label" for="password_confirmation">Confirm New Password</label>
 <input class="form-input" id="password_confirmation" type="password" name="password_confirmation">
 </div>

 <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container-low">
 <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors">Cancel</a>
 <button type="submit" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">Update User</button>
 </div>
 </form>
 </div>
 </div>
</x-app-layout>
