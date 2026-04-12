<?php

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ----------------------------------------------------------------
// Material Index
// ----------------------------------------------------------------

test('dosen can view materials for their own course', function () {
    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);

    $response = $this->actingAs($dosen)->get(route('dosen.materials.index', $course));
    $response->assertOk();
});

test('dosen cannot view materials for another dosen course', function () {
    $dosen1 = User::factory()->create(['role' => 'dosen']);
    $dosen2 = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen1->id]);

    $response = $this->actingAs($dosen2)->get(route('dosen.materials.index', $course));
    $response->assertForbidden();
});

// ----------------------------------------------------------------
// Material Store
// ----------------------------------------------------------------

test('dosen can create a material with a file', function () {
    Storage::fake('public');

    $dosen  = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen->id]);
    $file   = UploadedFile::fake()->create('lecture.pdf', 500, 'application/pdf');

    $response = $this->actingAs($dosen)->post(route('dosen.materials.store', $course), [
        'title'   => 'Pertemuan 1',
        'content' => 'Deskripsi materi pertama.',
        'file'    => $file,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('materials', [
        'course_id' => $course->id,
        'title'     => 'Pertemuan 1',
    ]);
});

test('dosen cannot create material for another dosen course', function () {
    Storage::fake('public');

    $dosen1 = User::factory()->create(['role' => 'dosen']);
    $dosen2 = User::factory()->create(['role' => 'dosen']);
    $course = Course::factory()->create(['dosen_id' => $dosen1->id]);

    $response = $this->actingAs($dosen2)->post(route('dosen.materials.store', $course), [
        'title'   => 'Hacked Material',
        'content' => 'This should not be created.',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseEmpty('materials');
});

// ----------------------------------------------------------------
// Material Update
// ----------------------------------------------------------------

test('dosen can update their own material', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $material = Material::factory()->create(['course_id' => $course->id]);

    $response = $this->actingAs($dosen)->put(route('dosen.materials.update', $material), [
        'title'   => 'Updated Title',
        'content' => 'Updated content.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('materials', [
        'id'    => $material->id,
        'title' => 'Updated Title',
    ]);
});

// ----------------------------------------------------------------
// Material Delete
// ----------------------------------------------------------------

test('dosen can delete their own material', function () {
    $dosen    = User::factory()->create(['role' => 'dosen']);
    $course   = Course::factory()->create(['dosen_id' => $dosen->id]);
    $material = Material::factory()->create(['course_id' => $course->id]);

    $response = $this->actingAs($dosen)->delete(route('dosen.materials.destroy', $material));

    $response->assertRedirect();
    $this->assertDatabaseMissing('materials', ['id' => $material->id]);
});
