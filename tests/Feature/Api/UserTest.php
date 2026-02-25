<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

// Return all users
it('returns a list of users', function () {
    $response = $this->getJson('/api/v1/users');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'given_name',
                'family_name',
                'email',
                'email_verified_at',
                'remember_token',
                'created_at',
                'updated_at',
            ],
        ],
    ]);
});

// Return a single user
it('returns a single user', function () {
    $response = $this->getJson('/api/v1/users/1');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'given_name',
            'family_name',
            'email',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
        ],
    ]);
});

// Create a new user
it('returns a newly created user', function () {
    $attributes = User::factory()->raw();
    $response = $this->postJson('/api/v1/users', $attributes);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'given_name',
            'family_name',
            'email',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
        ],
    ]);
    $this->assertDatabaseHas('users', [
        'given_name' => $attributes['given_name'],
        'family_name' => $attributes['family_name'],
        'email' => $attributes['email'],
    ]);
});

// Create user with missing required field: given_name
it('returns status 422 with validation error when creating user with missing given_name', function () {
    $attributes = User::factory()->raw(['given_name' => null]);
    $response = $this->postJson('/api/v1/users', $attributes);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('given_name');
});

// Create user with missing required fields: email
it('returns status 422 with validation error when creating user with missing email', function () {
    $attributes = User::factory()->raw(['email' => null]);
    $response = $this->postJson('/api/v1/users', $attributes);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

// Update a user
it('returns an updated user', function () {
    $user = User::factory()->create();  // Create existing user first

    $attributes = User::factory()->raw([
        'id' => $user->id,  // ← Use the new user's ID
    ]);

    $updateData = [
        'given_name' => $attributes['given_name'],
        'family_name' => $attributes['family_name'],
        // email/password NOT included - user shouldn't change via simple PUT
    ];

    $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'given_name',
            'family_name',
            'email',
            'email_verified_at',
            'remember_token',
            'created_at',
            'updated_at',
        ],
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'given_name' => $updateData['given_name'],
        'family_name' => $updateData['family_name'],
        'email' => $user->email,  // ← Unchanged
    ]);
});

// Delete a user
it('deletes a user and returns status 204', function () {
    $user = User::factory()->create();
    $response = $this->deleteJson("/api/v1/users/{$user->id}");
    $response->assertStatus(204);
    expect(User::find($user->id))->toBeNull();
});

// Show JSON
//it('debug', function () {
//    $response = $this->getJson('/api/v1/users/1');
//    dd($response->json());
//});
