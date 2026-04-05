<x-app-layout>
 <x-slot name="header">
 <div class="flex justify-between items-center">
 <h2 class="font-extrabold text-2xl font-headline text-on-surface leading-tight">
 {{ __('Edit Course & Enrollments') }}
 </h2>
 <a href="{{ route('admin.courses.index') }}" class="text-sm text-on-surface-variant hover:text-on-surface ">
 &larr; Back to Courses
 </a>
 </div>
 </x-slot>

 <div class="space-y-6">
 <div class="">
 
 @if(session('success'))
 <div class="px-5 py-4 bg-emerald-50 border-l-4 border-secondary text-secondary rounded-xl text-sm font-medium mb-6">
 {{ session('success') }}
 </div>
 @endif

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 
 <!-- Left Column: Course Details Form -->
 <div class="lg:col-span-1">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
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
 <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-highest transition-colors mr-3">Cancel</a>
 <button type="submit" class="px-5 py-2.5 architectural-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-[0.98] transition-all">Update Details</button>
 </div>
 </form>
 </div>
 </div>
 </div>

 <!-- Right Column: Enrollment Management -->
 <div class="lg:col-span-2">
 <div class="bg-surface-container-lowest overflow-hidden shadow-sm rounded-2xl">
 <div class="p-6 text-on-surface">
 
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
 <table class="min-w-full divide-y divide-surface-container-low">
 <thead class="bg-surface-container-low/50">
 <tr>
 <th class="px-4 py-2 text-left text-xs font-medium text-on-surface-variant uppercase">Name</th>
 <th class="px-4 py-2 text-left text-xs font-medium text-on-surface-variant uppercase">Email</th>
 <th class="px-4 py-2 text-left text-xs font-medium text-on-surface-variant uppercase">Enrolled At</th>
 <th class="px-4 py-2 text-right text-xs font-medium text-on-surface-variant uppercase">Action</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-surface-container-low">
 @forelse($course->students as $student)
 <tr>
 <td class="px-4 py-2 text-sm">{{ $student->name }}</td>
 <td class="px-4 py-2 text-sm text-on-surface-variant">{{ $student->email }}</td>
 <td class="px-4 py-2 text-sm text-on-surface-variant">{{ $student->pivot->created_at->format('M d, Y') }}</td>
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
 <td colspan="4" class="px-4 py-4 text-center text-sm text-on-surface-variant">No students enrolled yet.</td>
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
