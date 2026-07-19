<?php

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Group;
use App\Models\StepSubmission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Uji fitur progresi tugas multi-step: CRUD step oleh dosen (+ penguncian & penyalinan),
// pengerjaan step berurutan oleh mahasiswa (individu & kelompok), gate submission final,
// dan akumulasi nilai mode per_step.

// Helper: buat dosen + course + mahasiswa ter-enroll + tugas ber-step.
function setupSteppedAssignment(array $assignmentAttrs = [], int $stepCount = 2): array
{
    $dosen = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student = User::factory()->create(['role' => 'mahasiswa']);
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $assignment = Assignment::factory()->tugas()->create(array_merge([
        'course_id' => $course->id,
        'submission_format' => 'pdf',
    ], $assignmentAttrs));

    $steps = collect();
    foreach (range(1, $stepCount) as $i) {
        $steps->push($assignment->steps()->create([
            'step_number' => $i,
            'title' => "Step {$i}",
            'max_score' => 50,
        ]));
    }

    return [$dosen, $course, $student, $assignment, $steps];
}

// ----------------------------------------------------------------
// Dosen: kelola step
// ----------------------------------------------------------------

test('dosen can add steps to a tugas assignment', function () {
    $dosen = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $this->actingAs($dosen)->post(route('dosen.assignments.steps.store', $assignment), [
        'title' => 'Analisis Kebutuhan',
    ])->assertRedirect(route('dosen.assignments.steps.index', $assignment));

    $this->actingAs($dosen)->post(route('dosen.assignments.steps.store', $assignment), [
        'title' => 'Perancangan',
    ]);

    expect($assignment->steps()->pluck('step_number')->all())->toBe([1, 2]);
});

test('steps are locked once a step submission exists', function () {
    [$dosen, , $student, $assignment, $steps] = setupSteppedAssignment();

    StepSubmission::create([
        'assignment_step_id' => $steps[0]->id,
        'mahasiswa_id' => $student->id,
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($dosen)->post(route('dosen.assignments.steps.store', $assignment), [
        'title' => 'Step Baru',
    ]);

    $response->assertSessionHas('error');
    expect($assignment->steps()->count())->toBe(2);
});

test('copying an assignment also copies its steps', function () {
    [$dosen, $course, , $assignment] = setupSteppedAssignment();

    $sibling = Course::factory()->create([
        'dosen_id' => $dosen->id,
        'kode_matkul' => $course->kode_matkul,
        'semester_id' => $course->semester_id,
    ]);

    $this->actingAs($dosen)->post(route('dosen.assignments.copy', $assignment), [
        'sibling_ids' => [$sibling->id],
    ])->assertSessionHas('success');

    $copied = Assignment::where('course_id', $sibling->id)->where('title', $assignment->title)->first();
    expect($copied)->not->toBeNull()
        ->and($copied->steps()->count())->toBe(2);
});

// ----------------------------------------------------------------
// Mahasiswa: pengerjaan step berurutan (individu)
// ----------------------------------------------------------------

test('mahasiswa cannot submit a later step before earlier ones', function () {
    Storage::fake('public');
    [, , $student, $assignment, $steps] = setupSteppedAssignment();

    $response = $this->actingAs($student)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[1]]),
        ['file' => UploadedFile::fake()->create('step2.pdf', 100, 'application/pdf')]
    );

    $response->assertSessionHas('error');
    expect(StepSubmission::count())->toBe(0);
});

test('mahasiswa can submit steps sequentially', function () {
    Storage::fake('public');
    [, , $student, $assignment, $steps] = setupSteppedAssignment();

    $this->actingAs($student)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[0]]),
        ['file' => UploadedFile::fake()->create('step1.pdf', 100, 'application/pdf')]
    )->assertRedirect(route('mahasiswa.assignments.steps.show', $assignment));

    $this->actingAs($student)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[1]]),
        ['file' => UploadedFile::fake()->create('step2.pdf', 100, 'application/pdf')]
    );

    expect(StepSubmission::where('mahasiswa_id', $student->id)->count())->toBe(2)
        ->and($assignment->allStepsCompletedBy($student->id))->toBeTrue();
});

test('step submitted after its deadline is marked late', function () {
    Storage::fake('public');
    [, , $student, $assignment, $steps] = setupSteppedAssignment();

    $steps[0]->update(['deadline' => now()->subDay()]);

    $this->actingAs($student)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[0]]),
        ['file' => UploadedFile::fake()->create('late.pdf', 100, 'application/pdf')]
    );

    expect(StepSubmission::first()->status)->toBe('late');
});

test('final submission is blocked until all steps are done', function () {
    [, , $student, $assignment] = setupSteppedAssignment(['step_grading_mode' => 'final']);

    $response = $this->actingAs($student)->get(route('mahasiswa.submissions.create', [
        'assignment_id' => $assignment->id,
    ]));

    $response->assertRedirect(route('mahasiswa.assignments.steps.show', $assignment));
    $response->assertSessionHas('error');
});

// ----------------------------------------------------------------
// Kelompok: kelompok terbentuk di step pertama & progres berbagi
// ----------------------------------------------------------------

test('submitting the first step forms the group and fans out to members', function () {
    Storage::fake('public');
    [, $course, $student, $assignment, $steps] = setupSteppedAssignment(['is_group' => true, 'max_group_size' => 5]);

    $friend = User::factory()->create(['role' => 'mahasiswa']);
    $course->students()->attach($friend->id, ['enrolled_at' => now()]);

    $this->actingAs($student)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[0]]),
        [
            'file' => UploadedFile::fake()->create('step1.pdf', 100, 'application/pdf'),
            'member_ids' => [$friend->id],
            'group_name' => 'Tim Hebat',
        ]
    );

    $group = Group::where('assignment_id', $assignment->id)->first();
    expect($group)->not->toBeNull()
        ->and($group->members()->count())->toBe(2)
        ->and(StepSubmission::where('assignment_step_id', $steps[0]->id)->count())->toBe(2);
});

test('another group member can submit the next step', function () {
    Storage::fake('public');
    [, $course, $student, $assignment, $steps] = setupSteppedAssignment(['is_group' => true, 'max_group_size' => 5]);

    $friend = User::factory()->create(['role' => 'mahasiswa']);
    $course->students()->attach($friend->id, ['enrolled_at' => now()]);

    $this->actingAs($student)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[0]]),
        [
            'file' => UploadedFile::fake()->create('step1.pdf', 100, 'application/pdf'),
            'member_ids' => [$friend->id],
        ]
    );

    // Anggota lain (bukan pembentuk kelompok) melanjutkan step 2 tanpa memilih anggota lagi.
    $this->actingAs($friend)->post(
        route('mahasiswa.assignments.steps.submit', [$assignment, $steps[1]]),
        ['file' => UploadedFile::fake()->create('step2.pdf', 100, 'application/pdf')]
    );

    expect(StepSubmission::where('assignment_step_id', $steps[1]->id)->count())->toBe(2)
        ->and($assignment->allStepsCompletedBy($student->id))->toBeTrue();
});

// ----------------------------------------------------------------
// Mode per_step: penilaian per step & akumulasi nilai akhir
// ----------------------------------------------------------------

test('grading all steps aggregates into a final submission', function () {
    Storage::fake('public');
    [$dosen, , $student, $assignment, $steps] = setupSteppedAssignment(['step_grading_mode' => 'per_step']);
    $steps[0]->update(['max_score' => 40]);
    $steps[1]->update(['max_score' => 60]);

    foreach ($steps as $step) {
        $this->actingAs($student)->post(
            route('mahasiswa.assignments.steps.submit', [$assignment, $step]),
            ['file' => UploadedFile::fake()->create("step{$step->step_number}.pdf", 100, 'application/pdf')]
        );
    }

    $first = StepSubmission::where('assignment_step_id', $steps[0]->id)->first();
    $second = StepSubmission::where('assignment_step_id', $steps[1]->id)->first();

    $this->actingAs($dosen)->post(route('dosen.step-submissions.grade', $first), ['score' => 35]);

    // Belum semua step dinilai → belum ada nilai agregat.
    $this->assertDatabaseMissing('submissions', ['assignment_id' => $assignment->id]);

    $this->actingAs($dosen)->post(route('dosen.step-submissions.grade', $second), ['score' => 55]);

    $this->assertDatabaseHas('submissions', [
        'assignment_id' => $assignment->id,
        'mahasiswa_id' => $student->id,
        'score' => 90,
        'status' => 'graded',
    ]);
});
