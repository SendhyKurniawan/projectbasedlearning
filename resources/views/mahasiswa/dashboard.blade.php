<x-app-layout>


    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Enrolled Courses</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['enrolled_courses'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Assignments</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_assignments'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Submitted</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['submitted_assignments'] }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Enrolled Courses -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">My Courses</h3>
                        <div class="space-y-3">
                            @forelse($enrolled_courses as $course)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $course->nama_matkul }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $course->kode_matkul }}</p>
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $course->materials_count }} Materials | {{ $course->assignments_count }} Assignments
                                    </div>
                                    <a href="{{ route('mahasiswa.courses.show', $course) }}" 
                                       class="mt-3 inline-block text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                        View Course
                                    </a>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400">No courses enrolled yet.</p>
                                <a href="{{ route('mahasiswa.courses.index') }}" 
                                   class="text-blue-600 dark:text-blue-400 hover:underline">Browse available courses</a>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Upcoming Assignments -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Upcoming Assignments</h3>
                        <div class="space-y-3">
                            @forelse($upcoming_assignments as $assignment)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $assignment->title }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $assignment->course->nama_matkul }}</p>
                                    <div class="mt-2 text-sm">
                                        <span class="text-red-600 dark:text-red-400">
                                            Due: {{ $assignment->deadline->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400">No upcoming assignments.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
