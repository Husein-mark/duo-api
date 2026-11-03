<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user dapat melakukan registrasi dengan data valid', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'name' => 'Budi Santoso',
                    'email' => 'budi@example.com',
                ],
            ],
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'budi@example.com',
    ]);
});

test('registrasi gagal jika email sudah terdaftar', function () {
    User::factory()->create(['email' => 'budi@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => [
                'email' => ['Email sudah terdaftar.'],
            ],
        ]);
});

test('user dapat login dengan kredensial yang benar', function () {
    $user = User::factory()->create([
        'email' => 'budi@example.com',
        'password' => bcrypt('rahasia123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'budi@example.com',
                ],
            ],
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);
});

test('login gagal jika password salah', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => bcrypt('rahasia123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'budi@example.com',
        'password' => 'salah123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Email atau password salah',
        ]);
});

test('profil dapat diambil dengan bearer token', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profil berhasil diambil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

test('profil mengembalikan 401 unauthenticated tanpa token', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('user dapat logout dan menghapus token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logout berhasil',
            'data' => null,
        ]);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});
