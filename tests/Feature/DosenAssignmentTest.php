<?php

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;

// Uji CRUD tugas + penilaian submission oleh dosen, dengan otorisasi lintas-dosen (403).

// ----------------------------------------------------------------
// Daftar tugas (index)
// ----------------------------------------------------------------

test('dosen can view assignments for their own course', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    $response = $this->actingAs($dosen)->get(route('dosen.assignments.index', $course));
    $response->assertOk();
});

test('dosen cannot view assignments for another dosen course', function () {
    $dosen1 = User::factory()->create(['role' => 'dosen']);
    $dosen2 = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen1->id]);

    $response = $this->actingAs($dosen2)->get(route('dosen.assignments.index', $course));
    $response->assertForbidden();
});

// ----------------------------------------------------------------
// Simpan tugas (store)
// ----------------------------------------------------------------

test('dosen can create a tugas assignment', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    $response = $this->actingAs($dosen)->post(route('dosen.assignments.store', $course), [
        'title'             => 'Tugas Pertama',
        'description'       => 'Deskripsi tugas.',
        'deadline'          => now()->addDays(7)->format('Y-m-d\TH:i'),
        'max_score'         => 100,
        'type'              => 'tugas',
        'submission_format' => 'pdf',
    ]);

    $response->assertRedirect(route('dosen.assignments.index', $course));
    $this->assertDatabaseHas('assignments', [
        'course_id' => $course->id,
        'title'     => 'Tugas Pertama',
        'type'      => 'tugas',
    ]);
});

test('dosen cannot create assignment for another dosen course', function () {
    $dosen1 = User::factory()->create(['role' => 'dosen']);
    $dosen2 = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen1->id]);

    $response = $this->actingAs($dosen2)->post(route('dosen.assignments.store', $course), [
        'title'             => 'Hacked Assignment',
        'description'       => 'Should not be created.',
        'deadline'          => now()->addDays(7)->format('Y-m-d\TH:i'),
        'max_score'         => 100,
        'type'              => 'tugas',
        'submission_format' => 'pdf',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseEmpty('assignments');
});

// ----------------------------------------------------------------
// Perbarui tugas (update)
// ----------------------------------------------------------------

test('dosen can update their own assignment', function () {
    $dosen      = User::factory()->create(['role' => 'dosen']);
    $course     = Course::factory()->create(['dosen_id' => $dosen->id]);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $response = $this->actingAs($dosen)->put(route('dosen.assignments.update', $assignment), [
        'title'             => 'Updated Title',
        'description'       => 'Updated description.',
        'deadline'          => now()->addDays(14)->format('Y-m-d\TH:i'),
        'max_score'         => 90,
        'type'              => 'tugas',
        'submission_format' => 'pdf',
    ]);

    $response->assertRedirect(route('dosen.assignments.index', $course));
    $this->assertDatabaseHas('assignments', [
        'id'    => $assignment->id,
        'title' => 'Updated Title',
    ]);
});

// ----------------------------------------------------------------
// Penilaian submission (grading)
// ----------------------------------------------------------------

test('dosen can grade a submission', function () {
    $dosen      = User::factory()->create(['role' => 'dosen']);
    $course     = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student    = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
        'score'         => null,
    ]);

    $response = $this->actingAs($dosen)->post(route('dosen.submissions.grade', $submission), [
        'score'    => 85,
        'feedback' => 'Good work!',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('submissions', [
        'id'     => $submission->id,
        'score'  => 85,
        'status' => 'graded',
    ]);
});

test('dosen cannot grade a submission from another dosen course', function () {
    $dosen1     = User::factory()->create(['role' => 'dosen']);
    $dosen2     = User::factory()->create(['role' => 'dosen']);
    $course     = Course::factory()->create(['dosen_id' => $dosen1->id]);
    $student    = User::factory()->create(['role' => 'mahasiswa']);
    $assignment = Assignment::factory()->tugas()->create(['course_id' => $course->id]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
    ]);

    $response = $this->actingAs($dosen2)->post(route('dosen.submissions.grade', $submission), [
        'score'    => 75,
        'feedback' => 'Attempted grade.',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('submissions', [
        'id'    => $submission->id,
        'score' => null,
    ]);
});
