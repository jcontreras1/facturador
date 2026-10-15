<?php

use App\Models\User;

test('non-admin users cannot access admin routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/config')->assertForbidden();
    $this->actingAs($user)->get('/usuarios')->assertForbidden();
});

test('admin users can access admin routes', function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $user = User::factory()->admin()->create();

    $this->actingAs($user)->get('/config')->assertOk();
    $this->actingAs($user)->get('/usuarios')->assertOk();
});
