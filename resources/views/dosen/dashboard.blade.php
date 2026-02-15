<x-app-layout>


    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">My Courses</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_courses'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Students</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_students'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Assignments</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_assignments'] }}</div>
                    </div>
                </div>
            </div>

            <!-- My Courses -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">My Courses</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($courses as $course)
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $course->nama_matkul }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Code: {{ $course->kode_matkul }}</p>
                                <div class="mt-3 flex gap-2 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ $course->materials_count }} Materials
                                    </span>
                                    <span class="text-gray-600 dark:text-gray-400">&bull;</span>
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ $course->assignments_count }} Assignments
                                    </span>
                                    <span class="text-gray-600 dark:text-gray-400">&bull;</span>
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ $course->students_count }} Students
                                    </span>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <a href="{{ route('dosen.materials.index', $course) }}" 
                                       class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                        Materials
                                    </a>
                                    <a href="{{ route('dosen.assignments.index', $course) }}" 
                                       class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">
                                        Assignments
                                    </a>

                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No courses assigned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
