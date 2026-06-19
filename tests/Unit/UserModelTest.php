<?php

use App\Models\User;

// Uji helper role (isAdmin/isDosen/isMahasiswa/hasRole) — instansiasi model murni, tanpa DB/facade.
test('isAdmin returns true for admin role', function () {
    $user = new User(['role' => 'admin']);
    expect($user->isAdmin())->toBeTrue();
});

test('isAdmin returns false for non-admin role', function () {
    $user = new User(['role' => 'dosen']);
    expect($user->isAdmin())->toBeFalse();
});

test('isDosen returns true for dosen role', function () {
    $user = new User(['role' => 'dosen']);
    expect($user->isDosen())->toBeTrue();
});

test('isMahasiswa returns true for mahasiswa role', function () {
    $user = new User(['role' => 'mahasiswa']);
    expect($user->isMahasiswa())->toBeTrue();
});

test('hasRole returns true when role matches', function () {
    $user = new User(['role' => 'dosen']);
    expect($user->hasRole('dosen'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
});

test('role helpers are mutually exclusive', function () {
    $admin = new User(['role' => 'admin']);
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isDosen())->toBeFalse();
    expect($admin->isMahasiswa())->toBeFalse();
});
