<?php

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ----------------------------------------------------------------
// Submission Create (GET)
// ----------------------------------------------------------------

test('mahasiswa can view submission create form for enrolled course', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($student)->get(route('mahasiswa.submissions.create', [
        'assignment_id' => $assignment->id,
    ]));

    $response->assertOk();
});

test('mahasiswa cannot submit to course they are not enrolled in', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create([
        'course_id'         => $course->id,
        'submission_format' => 'pdf',
    ]);

    Storage::fake('public');
    $file = UploadedFile::fake()->create('assignment.pdf', 100, 'application/pdf');

    $response = $this->actingAs($student)->post(route('mahasiswa.submissions.store'), [
        'assignment_id' => $assignment->id,
        'file'          => $file,
    ]);

    $response->assertRedirect(route('mahasiswa.dashboard'));
    $response->assertSessionHas('error');
});

// ----------------------------------------------------------------
// Submission Store (POST)
// ----------------------------------------------------------------

test('mahasiswa can store a file submission', function () {
    Storage::fake('public');

    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create([
        'course_id'         => $course->id,
        'submission_format' => 'pdf',
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);
    $file = UploadedFile::fake()->create('assignment.pdf', 100, 'application/pdf');

    $response = $this->actingAs($student)->post(route('mahasiswa.submissions.store'), [
        'assignment_id' => $assignment->id,
        'file'          => $file,
    ]);

    $response->assertRedirect(route('mahasiswa.courses.show', $course));
    $this->assertDatabaseHas('submissions', [
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
    ]);
});

test('mahasiswa cannot submit to the same assignment twice', function () {
    Storage::fake('public');

    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create([
        'course_id'         => $course->id,
        'submission_format' => 'pdf',
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
    ]);

    $file = UploadedFile::fake()->create('assignment2.pdf', 100, 'application/pdf');

    $response = $this->actingAs($student)->post(route('mahasiswa.submissions.store'), [
        'assignment_id' => $assignment->id,
        'file'          => $file,
    ]);

    $response->assertSessionHas('error');
    $this->assertDatabaseCount('submissions', 1);
});

// ----------------------------------------------------------------
// Submission Delete
// ----------------------------------------------------------------

test('mahasiswa can delete their own ungraded submission', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
        'score'         => null,
    ]);

    $response = $this->actingAs($student)->delete(route('mahasiswa.submissions.destroy', $submission));
    $response->assertRedirect();
    $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
});

test('mahasiswa cannot delete a graded submission', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $submission = Submission::factory()->graded()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
    ]);

    $response = $this->actingAs($student)->delete(route('mahasiswa.submissions.destroy', $submission));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
});

test('mahasiswa cannot delete another student submission', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $owner    = User::factory()->create(['role' => 'mahasiswa']);
    $intruder = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $owner->id,
    ]);

    $response = $this->actingAs($intruder)->delete(route('mahasiswa.submissions.destroy', $submission));
    $response->assertForbidden();
});
