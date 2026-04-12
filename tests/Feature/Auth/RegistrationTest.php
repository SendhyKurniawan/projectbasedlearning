<?php

use App\Models\StudentClass;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});

test('new mahasiswa can register and is redirected to dashboard', function () {
    $studentClass = StudentClass::factory()->create();

    $response = $this->post('/register', [
        'name'                  => 'Test Mahasiswa',
        'email'                 => 'test@example.com',
        'role'                  => 'mahasiswa',
        'nim'                   => '123456789',
        'student_class_id'      => $studentClass->id,
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
});

test('new dosen registration requires admin approval', function () {
    $response = $this->post('/register', [
        'name'                  => 'Test Dosen',
        'email'                 => 'dosen@example.com',
        'role'                  => 'dosen',
        'nip'                   => '987654321',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    // Dosen is not logged in -- must wait for admin activation
    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');
});
