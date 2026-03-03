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
});

// Mahasiswa Routes
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
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
});

require __DIR__.'/auth.php';
