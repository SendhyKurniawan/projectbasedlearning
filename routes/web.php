<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Dosen;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen' => redirect()->route('dosen.dashboard'),
            'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::get('/notifications/{id}/redirect', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');

    // Push Notifications Endpoint
    Route::post('/push-subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'store']);
    Route::post('/push-unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'destroy']);

    // Discussions & Announcements
    Route::resource('discussions', App\Http\Controllers\DiscussionController::class);
    Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);
});

// Admin Auth Routes
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [Admin\Auth\LoginController::class, 'store'])->name('login.store');
});

Route::get('/admin', function () {
    return redirect()->route('admin.login');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/grades', [Admin\GradeController::class, 'index'])->name('grades.index');
    Route::delete('/users/bulk-destroy', [Admin\UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    Route::resource('users', Admin\UserController::class);
    Route::patch('/users/{id}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::resource('courses', Admin\CourseController::class);
    Route::resource('academic-years', Admin\AcademicYearController::class);
    Route::resource('semesters', Admin\SemesterController::class);
    Route::resource('departments', Admin\DepartmentController::class);
    Route::resource('study-programs', Admin\StudyProgramController::class);
    Route::resource('student-classes', Admin\StudentClassController::class);
    Route::post('/courses/{course}/enroll', [Admin\CourseController::class, 'enroll'])->name('courses.enroll');
    Route::delete('/courses/{course}/enroll/{student}', [Admin\CourseController::class, 'unenroll'])->name('courses.unenroll');

    // Hierarchy Drill-down Routes
    Route::prefix('hierarchy')->name('hierarchy.')->group(function () {
        Route::get('/', [Admin\HierarchyController::class, 'index'])->name('index');
        Route::get('/departments', [Admin\HierarchyController::class, 'departments'])->name('departments.index');
        Route::get('/departments/{department}', [Admin\HierarchyController::class, 'studyPrograms'])->name('departments.show');
        Route::get('/study-programs/{studyProgram}/semesters', [Admin\HierarchyController::class, 'semesters'])->name('study-programs.show');
        Route::get('/study-programs/{studyProgram}/semesters/{semester}', [Admin\HierarchyController::class, 'classes'])->name('study-programs.semesters.show');
        Route::post('/study-programs/{studyProgram}/semesters/{semester}/courses', [Admin\HierarchyController::class, 'addSemesterCourse'])->name('study-programs.semesters.add-course');
        
        Route::get('/student-classes/{studentClass}', [Admin\HierarchyController::class, 'classDetails'])->name('student-classes.show');
        Route::post('/student-classes/{studentClass}/courses', [Admin\HierarchyController::class, 'addClassCourse'])->name('student-classes.add-course');
        
        Route::delete('/courses/{course}', [Admin\HierarchyController::class, 'removeCourse'])->name('courses.destroy');
    });

    // Push Debug Routes
    Route::get('/debug/push', [Admin\PushDebugController::class, 'index'])->name('debug.push.index');
    Route::post('/debug/push/send', [Admin\PushDebugController::class, 'send'])->name('debug.push.send');
});

// Dosen Routes
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [Dosen\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/grades', [Dosen\GradeController::class, 'index'])->name('grades.index');
    Route::get('/courses/{course}/materials', [Dosen\MaterialController::class, 'index'])->name('materials.index');
    Route::get('/courses/{course}/materials/create', [Dosen\MaterialController::class, 'create'])->name('materials.create');
    Route::post('/courses/{course}/materials', [Dosen\MaterialController::class, 'store'])->name('materials.store');
    Route::post('/courses/{course}/materials/reorder', [Dosen\MaterialController::class, 'reorder'])->name('materials.reorder');
    Route::get('/materials/{material}/edit', [Dosen\MaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [Dosen\MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [Dosen\MaterialController::class, 'destroy'])->name('materials.destroy');
    
    Route::get('/courses/{course}/assignments', [Dosen\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/courses/{course}/assignments/create', [Dosen\AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/courses/{course}/assignments', [Dosen\AssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/courses/{course}/assignments/reorder', [Dosen\AssignmentController::class, 'reorder'])->name('assignments.reorder');
    Route::get('/assignments/{assignment}/edit', [Dosen\AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [Dosen\AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [Dosen\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::get('/assignments/{assignment}/submissions', [Dosen\AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::post('/submissions/{submission}/grade', [Dosen\AssignmentController::class, 'grade'])->name('submissions.grade');
    
    // Unified Question Management
    Route::get('/assignments/{assignment}/questions', [Dosen\AssignmentController::class, 'questions'])->name('assignments.questions.index');
    Route::get('/assignments/{assignment}/questions/create', [Dosen\AssignmentController::class, 'createQuestion'])->name('assignments.questions.create');
    Route::post('/assignments/{assignment}/questions', [Dosen\AssignmentController::class, 'storeQuestion'])->name('assignments.questions.store');
    Route::get('/questions/{question}/edit', [Dosen\AssignmentController::class, 'editQuestion'])->name('assignments.questions.edit');
    Route::put('/questions/{question}', [Dosen\AssignmentController::class, 'updateQuestion'])->name('assignments.questions.update');
    Route::delete('/questions/{question}', [Dosen\AssignmentController::class, 'destroyQuestion'])->name('assignments.questions.destroy');

    // Quiz Attempt (Submission) Review
    Route::get('/assignments/{assignment}/submissions/{submission}', [Dosen\AssignmentController::class, 'showQuizAttempt'])->name('assignments.submissions.show');

    // Conference (Kelas Virtual)
    Route::get('/courses/{course}/conferences', [Dosen\ConferenceController::class, 'index'])->name('conferences.index');
    Route::get('/courses/{course}/conferences/create', [Dosen\ConferenceController::class, 'create'])->name('conferences.create');
    Route::post('/courses/{course}/conferences', [Dosen\ConferenceController::class, 'store'])->name('conferences.store');
    Route::get('/conferences/{conference}/edit', [Dosen\ConferenceController::class, 'edit'])->name('conferences.edit');
    Route::put('/conferences/{conference}', [Dosen\ConferenceController::class, 'update'])->name('conferences.update');
    Route::delete('/conferences/{conference}', [Dosen\ConferenceController::class, 'destroy'])->name('conferences.destroy');
    Route::post('/conferences/{conference}/start', [Dosen\ConferenceController::class, 'start'])->name('conferences.start');
    Route::post('/conferences/{conference}/end', [Dosen\ConferenceController::class, 'end'])->name('conferences.end');
    Route::get('/conferences/{conference}/room', [Dosen\ConferenceController::class, 'room'])->name('conferences.room');
    Route::get('/conferences/{conference}/token', [Dosen\ConferenceController::class, 'token'])->name('conferences.token');

    // Code Exercise CRUD (dedicated flow for exercise_config fields)
    Route::get('/courses/{course}/exercises/create', [Dosen\ExerciseController::class, 'create'])->name('exercises.create');
    Route::post('/courses/{course}/exercises', [Dosen\ExerciseController::class, 'store'])->name('exercises.store');
    Route::get('/exercises/{assignment}/edit', [Dosen\ExerciseController::class, 'edit'])->name('exercises.edit');
    Route::put('/exercises/{assignment}', [Dosen\ExerciseController::class, 'update'])->name('exercises.update');
});

// Mahasiswa Routes
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/grades', [Mahasiswa\GradeController::class, 'index'])->name('grades.index');
    Route::get('/courses', [Mahasiswa\CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [Mahasiswa\CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [Mahasiswa\CourseController::class, 'enroll'])->name('courses.enroll');
    Route::get('/courses/{course}/materials/{material}', [Mahasiswa\CourseController::class, 'showMaterial'])->name('materials.show');

    Route::resource('submissions', Mahasiswa\SubmissionController::class)->except(['index', 'show'])->middleware('check.assignment.unlocked');
    
    // Unified Quiz taking (now under Submission umbrella functionally)
    Route::get('/assignments/{assignment}/quiz', [Mahasiswa\QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/assignments/{assignment}/quiz/start', [Mahasiswa\QuizController::class, 'start'])->name('quizzes.start');
    Route::get('/assignments/{assignment}/quiz/take', [Mahasiswa\QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/assignments/{assignment}/quiz/submit', [Mahasiswa\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/assignments/{assignment}/quiz/result', [Mahasiswa\QuizController::class, 'result'])->name('quizzes.result');

    // Code Exercise Routes
    Route::get('/exercises/{assignment}/solve', [Mahasiswa\ExerciseController::class, 'solve'])->name('exercises.solve')->middleware('check.assignment.unlocked');
    Route::post('/exercises/submit', [Mahasiswa\ExerciseController::class, 'submit'])->name('exercises.submit');

    // Conference (Kelas Virtual)
    Route::get('/courses/{course}/conferences', [Mahasiswa\ConferenceController::class, 'index'])->name('conferences.index');
    Route::get('/conferences/{conference}/room', [Mahasiswa\ConferenceController::class, 'room'])->name('conferences.room');
    Route::get('/conferences/{conference}/token', [Mahasiswa\ConferenceController::class, 'token'])->name('conferences.token');
});

require __DIR__.'/auth.php';
