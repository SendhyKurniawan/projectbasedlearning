<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Dosen;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DiscussionController;
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

    // Discussion Routes
    Route::resource('discussions', DiscussionController::class);
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
    Route::resource('users', Admin\UserController::class);
    Route::patch('/users/{id}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::resource('courses', Admin\CourseController::class);
    Route::resource('academic-years', Admin\AcademicYearController::class);
    Route::resource('semesters', Admin\SemesterController::class);
    Route::post('/courses/{course}/enroll', [Admin\CourseController::class, 'enroll'])->name('courses.enroll');
    Route::delete('/courses/{course}/enroll/{student}', [Admin\CourseController::class, 'unenroll'])->name('courses.unenroll');
});

// Dosen Routes
Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [Dosen\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/grades', [Dosen\GradeController::class, 'index'])->name('grades.index');
    Route::get('/courses/{course}/materials', [Dosen\MaterialController::class, 'index'])->name('materials.index');
    Route::get('/courses/{course}/materials/create', [Dosen\MaterialController::class, 'create'])->name('materials.create');
    Route::post('/courses/{course}/materials', [Dosen\MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/edit', [Dosen\MaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [Dosen\MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [Dosen\MaterialController::class, 'destroy'])->name('materials.destroy');
    
    Route::get('/courses/{course}/assignments', [Dosen\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/courses/{course}/assignments/create', [Dosen\AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/courses/{course}/assignments', [Dosen\AssignmentController::class, 'store'])->name('assignments.store');
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

Route::get('/test-seed-dummy', function () {
    \Artisan::call('migrate', ['--force' => true]);
    
    $mhs = App\Models\User::where('email', 'fajar.mhs@pjbl.test')->first() ?? App\Models\User::where('role', 'mahasiswa')->first();
    $dosen = App\Models\User::where('role', 'dosen')->first();
    $course = App\Models\Course::first();

    // Add Material with PDF
    $mat = new App\Models\Material();
    $mat->course_id = $course->id;
    $mat->title = 'Test PDF Material Webview';
    // Let's create a dummy pdf file in storage
    Storage::disk('public')->put('materials/dummy.pdf', '%PDF-1.4... (dummy pdf content)');
    $mat->file_path = 'materials/dummy.pdf';
    $mat->save();

    // Add Assignment
    $assign = new App\Models\Assignment();
    $assign->course_id = $course->id;
    $assign->title = 'Test Code Assignment Webview';
    $assign->type = 'tugas';
    $assign->max_score = 100;
    $assign->deadline = now()->addDays(7);
    $assign->exercise_config = ['language' => 'htmlmixed'];
    $assign->save();

    // Add Submission with code
    $sub = new App\Models\Submission();
    $sub->assignment_id = $assign->id;
    $sub->mahasiswa_id = $mhs->id;
    $sub->code_answer = "<h1>Hello Webview</h1>\n<style>\n  h1 { color: purple; }\n</style>\n<script>\n  console.log('Sandbox executing!');\n  document.body.style.backgroundColor = '#f0f0f0';\n</script>";
    $sub->submitted_at = now();
    $sub->save();

    return 'Dummy data seeded!';
});
