<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user dapat melihat daftar tugas miliknya', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $task1 = Task::create([
        'user_id' => $user1->id,
        'title' => 'Tugas 1 User 1',
        'deadline' => '2026-11-20',
        'status' => 'todo',
    ]);
    $task2 = Task::create([
        'user_id' => $user1->id,
        'title' => 'Tugas 2 User 1',
        'deadline' => '2026-11-25',
        'status' => 'done',
    ]);
    Task::create([
        'user_id' => $user2->id,
        'title' => 'Tugas User 2',
        'deadline' => '2026-11-30',
        'status' => 'todo',
    ]);

    $response = $this->actingAs($user1, 'sanctum')->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Daftar tugas berhasil diambil',
        ])
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $task1->id, 'title' => 'Tugas 1 User 1', 'status' => 'todo'])
        ->assertJsonFragment(['id' => $task2->id, 'title' => 'Tugas 2 User 1', 'status' => 'done'])
        ->assertJsonMissing(['title' => 'Tugas User 2']);
});

test('user dapat memfilter tugas berdasarkan query status', function () {
    $user = User::factory()->create();

    Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Todo',
        'deadline' => '2026-11-20',
        'status' => 'todo',
    ]);
    Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Done',
        'deadline' => '2026-11-25',
        'status' => 'done',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks?status=done');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['title' => 'Tugas Done'])
        ->assertJsonMissing(['title' => 'Tugas Todo']);
});

test('user dapat menambahkan tugas baru', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
        'title' => 'Mengerjakan tugas PKL',
        'deadline' => '2026-11-20',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Tugas berhasil ditambahkan',
            'data' => [
                'title' => 'Mengerjakan tugas PKL',
                'deadline' => '2026-11-20',
                'status' => 'todo',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'user_id' => $user->id,
        'title' => 'Mengerjakan tugas PKL',
        'status' => 'todo',
    ]);
});

test('tambah tugas mengembalikan 422 jika input tidak valid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
        'title' => '',
        'deadline' => 'bukan-tanggal',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => [
                'title' => ['Judul wajib diisi.'],
                'deadline' => ['Format tanggal harus YYYY-MM-DD.'],
            ],
        ]);
});

test('user dapat melihat detail tugas miliknya', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Detail Tugas',
        'deadline' => '2026-11-20',
        'status' => 'todo',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks/'.$task->id);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Detail tugas berhasil diambil',
            'data' => [
                'id' => $task->id,
                'title' => 'Detail Tugas',
                'deadline' => '2026-11-20',
                'status' => 'todo',
            ],
        ]);
});

test('user dilarang mengakses detail tugas milik pengguna lain (403)', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $taskUser2 = Task::create([
        'user_id' => $user2->id,
        'title' => 'Tugas Rahasia User 2',
        'deadline' => '2026-11-20',
        'status' => 'todo',
    ]);

    $response = $this->actingAs($user1, 'sanctum')->getJson('/api/tasks/'.$taskUser2->id);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Anda tidak berhak mengakses tugas ini',
        ]);
});

test('detail tugas mengembalikan 404 jika tugas tidak ditemukan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks/9999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Tugas tidak ditemukan',
        ]);
});

test('user dapat mengubah tugas miliknya', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Awal',
        'deadline' => '2026-11-20',
        'status' => 'todo',
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson('/api/tasks/'.$task->id, [
        'title' => 'Mengerjakan laporan PKL',
        'deadline' => '2026-11-25',
        'status' => 'todo',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Tugas berhasil diubah',
            'data' => [
                'id' => $task->id,
                'title' => 'Mengerjakan laporan PKL',
                'deadline' => '2026-11-25',
                'status' => 'todo',
            ],
        ]);
});

test('user dapat mengubah status tugas (PATCH)', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Mengerjakan laporan PKL',
        'deadline' => '2026-11-25',
        'status' => 'todo',
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/tasks/'.$task->id.'/status', [
        'status' => 'done',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Status tugas berhasil diubah',
            'data' => [
                'id' => $task->id,
                'status' => 'done',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'done',
    ]);
});

test('user dapat menghapus tugas miliknya', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Tugas Mau Dihapus',
        'deadline' => '2026-11-25',
        'status' => 'todo',
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/tasks/'.$task->id);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Tugas berhasil dihapus',
            'data' => null,
        ]);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});
