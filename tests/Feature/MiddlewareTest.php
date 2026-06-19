<?php

// Uji middleware role: gerbang akses dashboard per role + redirect root sesuai role pengguna.
use App\Models\User;

// ----------------------------------------------------------------
// Akses tanpa login
// ----------------------------------------------------------------

test('unauthenticated user is redirected from admin dashboard', function () {
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('/login');
});

test('unauthenticated user is redirected from dosen dashboard', function () {
    $response = $this->get('/dosen/dashboard');
    $response->assertRedirect('/login');
});

test('unauthenticated user is redirected from mahasiswa dashboard', function () {
    $response = $this->get('/mahasiswa/dashboard');
    $response->assertRedirect('/login');
});

// ----------------------------------------------------------------
// Gerbang role Admin
// ----------------------------------------------------------------

test('mahasiswa cannot access admin dashboard', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

    $response = $this->actingAs($mahasiswa)->get('/admin/dashboard');
    $response->assertRedirect('/mahasiswa/dashboard');
});

test('dosen cannot access admin dashboard', function () {
    $dosen = User::factory()->create(['role' => 'dosen']);

    $response = $this->actingAs($dosen)->get('/admin/dashboard');
    $response->assertRedirect('/dosen/dashboard');
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertOk();
});

// ----------------------------------------------------------------
// Gerbang role Dosen
// ----------------------------------------------------------------

test('mahasiswa cannot access dosen dashboard', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

    $response = $this->actingAs($mahasiswa)->get('/dosen/dashboard');
    $response->assertRedirect('/mahasiswa/dashboard');
});

test('admin cannot access dosen dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/dosen/dashboard');
    $response->assertRedirect('/admin/dashboard');
});

test('dosen can access dosen dashboard', function () {
    $dosen = User::factory()->create(['role' => 'dosen']);

    $response = $this->actingAs($dosen)->get('/dosen/dashboard');
    $response->assertOk();
});

// ----------------------------------------------------------------
// Gerbang role Mahasiswa
// ----------------------------------------------------------------

test('dosen cannot access mahasiswa dashboard', function () {
    $dosen = User::factory()->create(['role' => 'dosen']);

    $response = $this->actingAs($dosen)->get('/mahasiswa/dashboard');
    $response->assertRedirect('/dosen/dashboard');
});

test('mahasiswa can access mahasiswa dashboard', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

    $response = $this->actingAs($mahasiswa)->get('/mahasiswa/dashboard');
    $response->assertOk();
});

// ----------------------------------------------------------------
// Redirect halaman root (/)
// ----------------------------------------------------------------

test('root redirects authenticated admin to admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin)->get('/');
    $response->assertRedirect('/admin/dashboard');
});

test('root redirects authenticated dosen to dosen dashboard', function () {
    $dosen = User::factory()->create(['role' => 'dosen']);
    $response = $this->actingAs($dosen)->get('/');
    $response->assertRedirect('/dosen/dashboard');
});

test('root redirects authenticated mahasiswa to mahasiswa dashboard', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    $response = $this->actingAs($mahasiswa)->get('/');
    $response->assertRedirect('/mahasiswa/dashboard');
});

test('root redirects unauthenticated user to login', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});
