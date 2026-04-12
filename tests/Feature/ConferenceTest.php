<?php

use App\Models\Conference;
use App\Models\Course;
use App\Models\User;

// ----------------------------------------------------------------
// Conference Index
// ----------------------------------------------------------------

test('dosen can view their course conferences', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    $response = $this->actingAs($dosen)->get(route('dosen.conferences.index', $course));
    $response->assertOk();
});

test('dosen cannot view conferences for another dosen course', function () {
    $dosen1 = User::factory()->create(['role' => 'dosen']);
    $dosen2 = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen1->id]);

    $response = $this->actingAs($dosen2)->get(route('dosen.conferences.index', $course));
    $response->assertForbidden();
});

// ----------------------------------------------------------------
// Conference Store
// ----------------------------------------------------------------

test('dosen can schedule a conference', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    $response = $this->actingAs($dosen)->post(route('dosen.conferences.store', $course), [
        'title'        => 'Pertemuan Online 1',
        'description'  => 'Pengenalan materi.',
        'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
    ]);

    $response->assertRedirect(route('dosen.conferences.index', $course));
    $this->assertDatabaseHas('conferences', [
        'course_id' => $course->id,
        'title'     => 'Pertemuan Online 1',
        'status'    => 'scheduled',
    ]);
});

// ----------------------------------------------------------------
// Start / End
// ----------------------------------------------------------------

test('dosen can start a conference', function () {
    $dosen      = User::factory()->create(['role' => 'dosen']);
    $course     = Course::factory()->create(['dosen_id' => $dosen->id]);
    $conference = Conference::factory()->create([
        'course_id' => $course->id,
        'dosen_id'  => $dosen->id,
        'status'    => 'scheduled',
    ]);

    $response = $this->actingAs($dosen)->post(route('dosen.conferences.start', $conference));
    $response->assertRedirect(route('dosen.conferences.room', $conference));
    $this->assertDatabaseHas('conferences', [
        'id'     => $conference->id,
        'status' => 'live',
    ]);
});

test('dosen can end a live conference', function () {
    $dosen      = User::factory()->create(['role' => 'dosen']);
    $course     = Course::factory()->create(['dosen_id' => $dosen->id]);
    $conference = Conference::factory()->live()->create([
        'course_id' => $course->id,
        'dosen_id'  => $dosen->id,
    ]);

    $response = $this->actingAs($dosen)->post(route('dosen.conferences.end', $conference));
    $response->assertRedirect(route('dosen.conferences.index', $course));
    $this->assertDatabaseHas('conferences', [
        'id'     => $conference->id,
        'status' => 'ended',
    ]);
});

// ----------------------------------------------------------------
// Ownership Guard
// ----------------------------------------------------------------

test('dosen cannot start a conference they do not own', function () {
    $dosen1     = User::factory()->create(['role' => 'dosen']);
    $dosen2     = User::factory()->create(['role' => 'dosen']);
    $course     = Course::factory()->create(['dosen_id' => $dosen1->id]);
    $conference = Conference::factory()->create([
        'course_id' => $course->id,
        'dosen_id'  => $dosen1->id,
        'status'    => 'scheduled',
    ]);

    $response = $this->actingAs($dosen2)->post(route('dosen.conferences.start', $conference));
    $response->assertForbidden();
    $this->assertDatabaseHas('conferences', [
        'id'     => $conference->id,
        'status' => 'scheduled',
    ]);
});

test('mahasiswa can view their enrolled course conferences', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $student  = User::factory()->create(['role' => 'mahasiswa']);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    Conference::factory()->create([
        'course_id' => $course->id,
        'dosen_id'  => $dosen->id,
    ]);

    $response = $this->actingAs($student)->get(route('mahasiswa.conferences.index', $course));
    $response->assertOk();
});
