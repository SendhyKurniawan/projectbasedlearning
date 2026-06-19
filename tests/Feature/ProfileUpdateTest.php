<?php

// Uji pembaruan field profil spesifik role: nim (mahasiswa), nip (dosen), sso_id, & keunikan nim.
use App\Models\User;

test('mahasiswa can update nim', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test Mahasiswa',
            'email' => 'mahasiswa@example.com',
            'nim' => '1234567890',
            'sso_id' => 'sso-123',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('1234567890', $user->nim);
    $this->assertSame('sso-123', $user->sso_id);
});

test('dosen can update nip', function () {
    $user = User::factory()->create(['role' => 'dosen']);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test Dosen',
            'email' => 'dosen@example.com',
            'nip' => '198001012020011001',
            'sso_id' => 'sso-456',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('198001012020011001', $user->nip);
    $this->assertSame('sso-456', $user->sso_id);
});

test('nim must be unique', function () {
    $user1 = User::factory()->create(['role' => 'mahasiswa', 'nim' => '11111']);
    $user2 = User::factory()->create(['role' => 'mahasiswa']);

    $response = $this
        ->actingAs($user2)
        ->patch('/profile', [
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'nim' => '11111', // Duplicate
        ]);

    $response->assertSessionHasErrors('nim');
});
