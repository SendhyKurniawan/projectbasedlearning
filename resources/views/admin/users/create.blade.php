<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Create New User') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="form-group">
                            <label class="form-label" for="name">Name</label>
                            <input class="form-input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div class="form-group" x-data="{ role: '{{ old('role') }}' }">
                            <label class="form-label" for="role">Role</label>
                            <select class="form-select" id="role" name="role" required x-model="role">
                                <option value="" disabled selected>Select Role</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role')
                                <span class="form-error">{{ $message }}</span>
                            @enderror

                            <!-- NIM (Student Only) -->
                            <div class="mt-4" x-show="role === 'mahasiswa'">
                                <label class="form-label" for="nim">NIM</label>
                                <input class="form-input" id="nim" type="text" name="nim" value="{{ old('nim') }}">
                                @error('nim')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- NIP (Dosen Only) -->
                            <div class="mt-4" x-show="role === 'dosen'">
                                <label class="form-label" for="nip">NIP</label>
                                <input class="form-input" id="nip" type="text" name="nip" value="{{ old('nip') }}">
                                @error('nip')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Class (Student Only) -->
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
                                @error('student_class_id')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-input" id="password" type="password" name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input class="form-input" id="password_confirmation" type="password" name="password_confirmation" required>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mr-3">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Create User
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
