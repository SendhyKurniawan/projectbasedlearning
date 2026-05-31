<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Dosen;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\CodeExecutionController;
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

    // Code execution proxy (server-side languages via Piston API)
    Route::post('/execute-code', [CodeExecutionController::class, 'execute'])->name('execute.code')->middleware('throttle:10,1');

    // Push Notifications Endpoint
    Route::post('/push-subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'store']);
    Route::post('/push-unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'destroy']);

    // Discussions & Announcements
    Route::resource('discussions', App\Http\Controllers\DiscussionController::class);
    Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);

    // Jitsi SSO: target for config.tokenAuthUrl. A tokenless Jitsi visitor is
    // sent here (?room=…); auth gates it through PBL login, then we mint a
    // per-user JWT and redirect back into the room.
    Route::get('/conferences/jitsi-auth', [App\Http\Controllers\ConferenceJoinController::class, 'jitsiAuth'])
        ->name('conferences.jitsi-auth');
});

// Admin Auth Routes
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [Admin\Auth\LoginController::class, 'store'])->name('login.store');
});

Route::get('/admin', function () {
    return redirect()->route('admin.login');
});

// Back-compat: redirect old hierarchy URLs to unified akademik page
Route::get('/admin/hierarchy/{any?}', fn() => redirect()->route('admin.akademik.index'))
    ->where('any', '.*')->middleware(['auth', 'role:admin']);

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

    // Unified Akademik Routes (replaces hierarchy drill-down)
    Route::get('/akademik', [Admin\AkademikController::class, 'index'])->name('akademik.index');

    Route::post('/akademik/academic-years', [Admin\AkademikController::class, 'storeAcademicYear'])->name('akademik.academic-years.store');
    Route::put('/akademik/academic-years/{academicYear}', [Admin\AkademikController::class, 'updateAcademicYear'])->name('akademik.academic-years.update');
    Route::delete('/akademik/academic-years/{academicYear}', [Admin\AkademikController::class, 'destroyAcademicYear'])->name('akademik.academic-years.destroy');
    Route::patch('/akademik/academic-years/{academicYear}/activate', [Admin\AkademikController::class, 'activateAcademicYear'])->name('akademik.academic-years.activate');

    Route::post('/akademik/semesters', [Admin\AkademikController::class, 'storeSemester'])->name('akademik.semesters.store');
    Route::put('/akademik/semesters/{semester}', [Admin\AkademikController::class, 'updateSemester'])->name('akademik.semesters.update');
    Route::delete('/akademik/semesters/{semester}', [Admin\AkademikController::class, 'destroySemester'])->name('akademik.semesters.destroy');
    Route::patch('/akademik/semesters/{semester}/activate', [Admin\AkademikController::class, 'activateSemester'])->name('akademik.semesters.activate');

    Route::post('/akademik/departments', [Admin\AkademikController::class, 'storeDepartment'])->name('akademik.departments.store');
    Route::put('/akademik/departments/{department}', [Admin\AkademikController::class, 'updateDepartment'])->name('akademik.departments.update');
    Route::delete('/akademik/departments/{department}', [Admin\AkademikController::class, 'destroyDepartment'])->name('akademik.departments.destroy');

    Route::post('/akademik/study-programs', [Admin\AkademikController::class, 'storeStudyProgram'])->name('akademik.study-programs.store');
    Route::put('/akademik/study-programs/{studyProgram}', [Admin\AkademikController::class, 'updateStudyProgram'])->name('akademik.study-programs.update');
    Route::delete('/akademik/study-programs/{studyProgram}', [Admin\AkademikController::class, 'destroyStudyProgram'])->name('akademik.study-programs.destroy');

    Route::post('/akademik/classes', [Admin\AkademikController::class, 'storeClass'])->name('akademik.classes.store');
    Route::put('/akademik/classes/{studentClass}', [Admin\AkademikController::class, 'updateClass'])->name('akademik.classes.update');
    Route::delete('/akademik/classes/{studentClass}', [Admin\AkademikController::class, 'destroyClass'])->name('akademik.classes.destroy');
    Route::post('/akademik/classes/{studentClass}/students', [Admin\AkademikController::class, 'assignStudents'])->name('akademik.classes.assign-students');
    Route::delete('/akademik/classes/{studentClass}/students/{user}', [Admin\AkademikController::class, 'unassignStudent'])->name('akademik.classes.unassign-student');

    Route::post('/akademik/courses', [Admin\AkademikController::class, 'storeCourse'])->name('akademik.courses.store');
    Route::delete('/akademik/courses/{course}', [Admin\AkademikController::class, 'destroyCourse'])->name('akademik.courses.destroy');

    // Push Debug Routes
    Route::get('/debug/push', [Admin\PushDebugController::class, 'index'])->name('debug.push.index');
    Route::post('/debug/push/send', [Admin\PushDebugController::class, 'send'])->name('debug.push.send');

    // Conference observer
    Route::get('/conferences', [Admin\ConferenceController::class, 'index'])->name('conferences.index');
    Route::get('/conferences/{conference}/room', [Admin\ConferenceController::class, 'room'])->name('conferences.room');
    Route::post('/conferences/{conference}/end', [Admin\ConferenceController::class, 'end'])->name('conferences.end');
});

// Dosen Routes
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [Dosen\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/grades', [Dosen\GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/{course}/export', [Dosen\GradeController::class, 'export'])->name('grades.export');
    Route::patch('/grades/{assignment}/{mahasiswa}/quick-grade', [Dosen\GradeController::class, 'quickGrade'])->name('grades.quickGrade');

    // Bare-URL fallbacks: redirect to first course or show "contact admin" page
    $sectionFallback = function (string $routeName, string $section) {
        $firstCourse = auth()->user()->courses()->where('dosen_id', auth()->id())->first();
        if ($firstCourse) {
            return redirect()->route($routeName, $firstCourse);
        }
        return response()->view('dosen.no-course', [
            'title'   => 'Belum Ada Mata Kuliah',
            'section' => $section,
        ]);
    };
    Route::get('/materials',   function () use ($sectionFallback) { return $sectionFallback('dosen.materials.index', 'Manajemen Materi'); })->name('materials.bare');
    Route::get('/assignments', function () use ($sectionFallback) { return $sectionFallback('dosen.assignments.index', 'Manajemen Tugas'); })->name('assignments.bare');
    Route::get('/exercises',   function () use ($sectionFallback) { return $sectionFallback('dosen.assignments.index', 'Latihan Kode'); })->name('exercises.bare');
    Route::get('/conferences', function () use ($sectionFallback) { return $sectionFallback('dosen.conferences.index', 'Kelas Virtual'); })->name('conferences.bare');

    Route::get('/courses/{course}/materials', [Dosen\MaterialController::class, 'index'])->name('materials.index');
    Route::get('/courses/{course}/materials/create', [Dosen\MaterialController::class, 'create'])->name('materials.create');
    Route::post('/courses/{course}/materials', [Dosen\MaterialController::class, 'store'])->name('materials.store');
    Route::post('/courses/{course}/materials/reorder', [Dosen\MaterialController::class, 'reorder'])->name('materials.reorder');
    Route::get('/materials/{material}/edit', [Dosen\MaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [Dosen\MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [Dosen\MaterialController::class, 'destroy'])->name('materials.destroy');
    Route::post('/materials/{material}/copy', [Dosen\MaterialController::class, 'copy'])->name('materials.copy');
    
    Route::get('/courses/{course}/assignments', [Dosen\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/courses/{course}/assignments/create', [Dosen\AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/courses/{course}/assignments', [Dosen\AssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/courses/{course}/assignments/reorder', [Dosen\AssignmentController::class, 'reorder'])->name('assignments.reorder');
    Route::get('/assignments/{assignment}/edit', [Dosen\AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [Dosen\AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [Dosen\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::post('/assignments/{assignment}/copy', [Dosen\AssignmentController::class, 'copy'])->name('assignments.copy');
    Route::get('/assignments/{assignment}/submissions', [Dosen\AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::post('/submissions/{submission}/grade', [Dosen\AssignmentController::class, 'grade'])->name('submissions.grade');
    Route::post('/groups/{group}/grade', [Dosen\AssignmentController::class, 'gradeGroup'])->name('groups.grade');
    
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
    Route::post('/conferences/{conference}/copy', [Dosen\ConferenceController::class, 'copy'])->name('conferences.copy');
    Route::post('/conferences/{conference}/start', [Dosen\ConferenceController::class, 'start'])->name('conferences.start');
    Route::post('/conferences/{conference}/end', [Dosen\ConferenceController::class, 'end'])->name('conferences.end');
    Route::get('/conferences/{conference}/room', [Dosen\ConferenceController::class, 'room'])->name('conferences.room');

    // Code Exercise CRUD (dedicated flow for exercise_config fields)
    Route::get('/courses/{course}/exercises/create', [Dosen\ExerciseController::class, 'create'])->name('exercises.create');
    Route::post('/courses/{course}/exercises', [Dosen\ExerciseController::class, 'store'])->name('exercises.store');
    Route::get('/exercises/{assignment}/edit', [Dosen\ExerciseController::class, 'edit'])->name('exercises.edit');
    Route::put('/exercises/{assignment}', [Dosen\ExerciseController::class, 'update'])->name('exercises.update');
});

// Mahasiswa Routes
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal', [Mahasiswa\ScheduleController::class, 'index'])->name('schedule.index');
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
});

require __DIR__.'/auth.php';
