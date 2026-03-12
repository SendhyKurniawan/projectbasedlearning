<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Course & Enrollments') }}
            </h2>
            <a href="{{ route('admin.courses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Courses
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column: Course Details Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium mb-4">Course Details</h3>
                            
                            <form method="POST" action="{{ route('admin.courses.update', $course) }}">
                                @csrf
                                @method('PUT')

                                <!-- Code -->
                                <div class="form-group">
                                    <label class="form-label" for="kode_matkul">Course Code</label>
                                    <input class="form-input" id="kode_matkul" type="text" name="kode_matkul" value="{{ old('kode_matkul', $course->kode_matkul) }}" required>
                                    @error('kode_matkul')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="form-group">
                                    <label class="form-label" for="nama_matkul">Course Name</label>
                                    <input class="form-input" id="nama_matkul" type="text" name="nama_matkul" value="{{ old('nama_matkul', $course->nama_matkul) }}" required>
                                    @error('nama_matkul')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="form-group">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-textarea" id="description" name="description">{{ old('description', $course->description) }}</textarea>
                                    @error('description')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Dosen -->
                                <div class="form-group">
                                    <label class="form-label" for="dosen_id">Assign Lecturer</label>
                                    <select class="form-select" id="dosen_id" name="dosen_id" required>
                                        <option value="" disabled>Select Lecturer</option>
                                        @foreach($dosens as $dosen)
                                            <option value="{{ $dosen->id }}" {{ old('dosen_id', $course->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                                {{ $dosen->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dosen_id')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Class (Optional) -->
                                <div class="form-group">
                                    <label class="form-label" for="student_class_id">Kelas (Opsional)</label>
                                    <select class="form-select" id="student_class_id" name="student_class_id">
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('student_class_id', $course->student_class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} ({{ $class->studyProgram->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_class_id')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Semester -->
                                <div class="form-group">
                                    <label class="form-label" for="semester_id">Semester</label>
                                    <select class="form-select" id="semester_id" name="semester_id" required>
                                        <option value="" disabled selected>Pilih Semester</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ old('semester_id', $course->semester_id) == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->name }} ({{ $semester->academicYear->year_start }}/{{ $semester->academicYear->year_end }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester_id')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary mr-3">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Details</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Enrollment Management -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium">Enrolled Students ({{ $course->students->count() }})</h3>
                                
                                <!-- Enroll Student Form -->
                                <form method="POST" action="{{ route('admin.courses.enroll', $course) }}" class="flex items-end gap-2">
                                    @csrf
                                    <div>
                                        <select name="student_id" class="form-select py-1 text-sm w-48" required>
                                            <option value="" selected disabled>Enroll Student...</option>
                                            @foreach($availableStudents as $student)
                                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success">Enroll</button>
                                </form>
                            </div>

                            <!-- Students List -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Enrolled At</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($course->students as $student)
                                            <tr>
                                                <td class="px-4 py-2 text-sm">{{ $student->name }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ $student->email }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ $student->pivot->created_at->format('M d, Y') }}</td>
                                                <td class="px-4 py-2 text-right">
                                                    <form action="{{ route('admin.courses.unenroll', [$course, $student]) }}" method="POST" onsubmit="return confirm('Remove {{ $student->name }} from this course?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium uppercase">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No students enrolled yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
