<x-app-layout>


    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Welcome Section -->
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Welcome back, Admin!</h3>
                <p class="text-gray-500 dark:text-gray-400">Here's what's happening in your academy today.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card stat-primary">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value">{{ $stats['total_users'] }}</div>
                    <div class="text-sm text-gray-500 mt-2">Active accounts</div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-label">Dosen</div>
                    <div class="stat-value">{{ $stats['total_dosen'] }}</div>
                    <div class="text-sm text-gray-500 mt-2">Teaching staff</div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-label">Mahasiswa</div>
                    <div class="stat-value">{{ $stats['total_mahasiswa'] }}</div>
                    <div class="text-sm text-gray-500 mt-2">Enrolled students</div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-label">Courses</div>
                    <div class="stat-value">{{ $stats['total_courses'] }}</div>
                    <div class="text-sm text-gray-500 mt-2">Active classes</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h3 class="card-title">Recent Users</h3>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline">Manage Users</a>
                    </div>
                    <div class="space-y-4">
                        @foreach($recent_users as $user)
                            <div class="list-item flex justify-between items-center">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                                @php
                                    $badgeClass = match($user->role) {
                                        'admin' => 'badge-danger',
                                        'dosen' => 'badge-info',
                                        'mahasiswa' => 'badge-success',
                                        default => 'badge-gray'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Courses -->
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h3 class="card-title">Recent Courses</h3>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline">Manage Courses</a>
                    </div>
                    <div class="space-y-4">
                        @foreach($recent_courses as $course)
                            <div class="list-item">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $course->nama_matkul }}</div>
                                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $course->kode_matkul }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>Lecturer: {{ $course->dosen ? $course->dosen->name : 'Unassigned' }}</span>
                                    <span>{{ $course->students_count ?? 0 }} Students</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
