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
    
    // Quiz Routes
    Route::resource('quizzes', Dosen\QuizController::class)->except(['index', 'create', 'store']);
    Route::get('/courses/{course}/quizzes', [Dosen\QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/courses/{course}/quizzes/create', [Dosen\QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/courses/{course}/quizzes', [Dosen\QuizController::class, 'store'])->name('quizzes.store');
    
    // Quiz Questions Routes
    Route::get('/quizzes/{quiz}/questions', [Dosen\QuizController::class, 'questions'])->name('quizzes.questions.index');
    Route::get('/quizzes/{quiz}/questions/create', [Dosen\QuizController::class, 'createQuestion'])->name('quizzes.questions.create');
    Route::post('/quizzes/{quiz}/questions', [Dosen\QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::get('/questions/{question}/edit', [Dosen\QuizController::class, 'editQuestion'])->name('quizzes.questions.edit');
    Route::put('/questions/{question}', [Dosen\QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::delete('/questions/{question}', [Dosen\QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

    // Quiz Attempts Routes
    Route::get('/quizzes/{quiz}/attempts', [Dosen\QuizController::class, 'attempts'])->name('quizzes.attempts.index');
    Route::get('/quizzes/{quiz}/attempts/{attempt}', [Dosen\QuizController::class, 'showAttempt'])->name('quizzes.attempts.show');

    // Code Exercise Routes
    Route::get('/courses/{course}/exercises/create', [Dosen\ExerciseController::class, 'create'])->name('exercises.create');
    Route::post('/courses/{course}/exercises', [Dosen\ExerciseController::class, 'store'])->name('exercises.store');
    Route::get('/exercises/{assignment}/edit', [Dosen\ExerciseController::class, 'edit'])->name('exercises.edit');
    Route::put('/exercises/{assignment}', [Dosen\ExerciseController::class, 'update'])->name('exercises.update');
});

// Mahasiswa Routes
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses', [Mahasiswa\CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [Mahasiswa\CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [Mahasiswa\CourseController::class, 'enroll'])->name('courses.enroll');
    Route::get('/courses/{course}/materials/{material}', [Mahasiswa\CourseController::class, 'showMaterial'])->name('materials.show');

    Route::resource('submissions', Mahasiswa\SubmissionController::class)->except(['index', 'show'])->middleware('check.assignment.unlocked');
    
    // Quiz Routes
    Route::get('/quizzes/{quiz}', [Mahasiswa\QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}/start', [Mahasiswa\QuizController::class, 'start'])->name('quizzes.start');
    Route::get('/quizzes/{quiz}/take', [Mahasiswa\QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [Mahasiswa\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/result', [Mahasiswa\QuizController::class, 'result'])->name('quizzes.result');

    // Code Exercise Routes
    Route::get('/exercises/{assignment}/solve', [Mahasiswa\ExerciseController::class, 'solve'])->name('exercises.solve')->middleware('check.assignment.unlocked');
    Route::post('/exercises/submit', [Mahasiswa\ExerciseController::class, 'submit'])->name('exercises.submit');
});

require __DIR__.'/auth.php';
