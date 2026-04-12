<?php

use App\Models\Assignment;
use App\Models\Conference;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;

test('course belongs to dosen', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    expect($course->dosen->id)->toBe($dosen->id);
});

test('course has many materials ordered by order column', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    Material::factory()->create(['course_id' => $course->id, 'order' => 2]);
    Material::factory()->create(['course_id' => $course->id, 'order' => 1]);
    Material::factory()->create(['course_id' => $course->id, 'order' => 3]);

    $orders = $course->materials->pluck('order')->toArray();
    expect($orders)->toBe([1, 2, 3]);
});

test('course has many assignments ordered by order column', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    Assignment::factory()->create(['course_id' => $course->id, 'order' => 3]);
    Assignment::factory()->create(['course_id' => $course->id, 'order' => 1]);
    Assignment::factory()->create(['course_id' => $course->id, 'order' => 2]);

    $orders = $course->assignments->pluck('order')->toArray();
    expect($orders)->toBe([1, 2, 3]);
});

test('course has many students through enrollments', function () {
    $dosen   = User::factory()->create(['role' => 'dosen']);
    $course  = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student = User::factory()->create(['role' => 'mahasiswa']);

    $course->students()->attach($student->id, [
        'enrolled_at' => now(),
        'final_grade' => null,
    ]);

    expect($course->students)->toHaveCount(1);
    expect($course->students->first()->id)->toBe($student->id);
});

test('course has many conferences', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    Conference::factory()->count(3)->create([
        'course_id' => $course->id,
        'dosen_id'  => $dosen->id,
    ]);

    expect($course->conferences)->toHaveCount(3);
});
