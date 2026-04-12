<?php

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;

test('isGraded accessor returns false when score is null', function () {
    $course = Course::factory()->create();
    $assignment = Assignment::factory()->create(['course_id' => $course->id]);
    $student = User::factory()->create(['role' => 'mahasiswa']);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
        'score'         => null,
    ]);

    expect($submission->is_graded)->toBeFalse();
});

test('isGraded accessor returns true when score is set', function () {
    $course = Course::factory()->create();
    $assignment = Assignment::factory()->create(['course_id' => $course->id]);
    $student = User::factory()->create(['role' => 'mahasiswa']);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
        'score'         => 85,
    ]);

    expect($submission->is_graded)->toBeTrue();
});

test('submission belongs to assignment', function () {
    $course = Course::factory()->create();
    $assignment = Assignment::factory()->create(['course_id' => $course->id]);
    $student = User::factory()->create(['role' => 'mahasiswa']);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
    ]);

    expect($submission->assignment->id)->toBe($assignment->id);
});

test('submission belongs to mahasiswa', function () {
    $course = Course::factory()->create();
    $assignment = Assignment::factory()->create(['course_id' => $course->id]);
    $student = User::factory()->create(['role' => 'mahasiswa']);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'mahasiswa_id'  => $student->id,
    ]);

    expect($submission->mahasiswa->id)->toBe($student->id);
});
