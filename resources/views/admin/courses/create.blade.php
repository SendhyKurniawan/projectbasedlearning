<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Create New Course') }}
            </h2>
            <a href="{{ route('admin.courses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Courses
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('admin.courses.store') }}">
                        @csrf

                        <!-- Code -->
                        <div class="form-group">
                            <label class="form-label" for="kode_matkul">Course Code</label>
                            <input class="form-input" id="kode_matkul" type="text" name="kode_matkul" value="{{ old('kode_matkul') }}" placeholder="e.g. WEB101" required autofocus>
                            @error('kode_matkul')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="form-group">
                            <label class="form-label" for="nama_matkul">Course Name</label>
                            <input class="form-input" id="nama_matkul" type="text" name="nama_matkul" value="{{ old('nama_matkul') }}" placeholder="e.g. Web Development Basics" required>
                            @error('nama_matkul')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-textarea" id="description" name="description">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Dosen -->
                        <div class="form-group">
                            <label class="form-label" for="dosen_id">Assign Lecturer</label>
                            <select class="form-select" id="dosen_id" name="dosen_id" required>
                                <option value="" disabled selected>Select Lecturer</option>
                                @foreach($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->name }} ({{ $dosen->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('dosen_id')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary mr-3">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Create Course
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
