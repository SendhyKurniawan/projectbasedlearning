<?php

// Uji model Assignment: scope active/past (berdasarkan deadline) & gerbang prasyarat
// isUnlockedFor() (tugas terkunci sampai materi prasyarat dibuka mahasiswa).
use App\Models\Assignment;
use App\Models\MaterialView;
use App\Models\Material;
use App\Models\Course;
use App\Models\User;

test('scopeActive returns only assignments with future deadlines', function () {
    $course = Course::factory()->create();

    $active = Assignment::factory()->create([
        'course_id' => $course->id,
        'deadline'  => now()->addDays(5),
    ]);

    $past = Assignment::factory()->create([
        'course_id' => $course->id,
        'deadline'  => now()->subDays(1),
    ]);

    $results = Assignment::active()->pluck('id');

    expect($results)->toContain($active->id);
    expect($results)->not->toContain($past->id);
});

test('scopePast returns only assignments with past deadlines', function () {
    $course = Course::factory()->create();

    $active = Assignment::factory()->create([
        'course_id' => $course->id,
        'deadline'  => now()->addDays(5),
    ]);

    $past = Assignment::factory()->create([
        'course_id' => $course->id,
        'deadline'  => now()->subDays(1),
    ]);

    $results = Assignment::past()->pluck('id');

    expect($results)->not->toContain($active->id);
    expect($results)->toContain($past->id);
});

test('isUnlockedFor returns true when no prerequisite material is set', function () {
    $course = Course::factory()->create();
    $assignment = Assignment::factory()->create([
        'course_id'          => $course->id,
        'required_material_id' => null,
    ]);

    $student = User::factory()->create(['role' => 'mahasiswa']);

    expect($assignment->isUnlockedFor($student->id))->toBeTrue();
});

test('isUnlockedFor returns false when student has not viewed prerequisite material', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);
    $material = Material::factory()->create(['course_id' => $course->id]);

    $assignment = Assignment::factory()->create([
        'course_id'            => $course->id,
        'required_material_id' => $material->id,
    ]);

    $student = User::factory()->create(['role' => 'mahasiswa']);

    expect($assignment->isUnlockedFor($student->id))->toBeFalse();
});

test('isUnlockedFor returns true when student has viewed prerequisite material', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);
    $material = Material::factory()->create(['course_id' => $course->id]);

    $assignment = Assignment::factory()->create([
        'course_id'            => $course->id,
        'required_material_id' => $material->id,
    ]);

    $student = User::factory()->create(['role' => 'mahasiswa']);

    MaterialView::create([
        'material_id' => $material->id,
        'student_id'  => $student->id,
        'viewed_at'   => now(),
    ]);

    expect($assignment->isUnlockedFor($student->id))->toBeTrue();
});
