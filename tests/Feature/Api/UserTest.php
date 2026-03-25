<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(RefreshDatabase::class);

describe('User', function () {

    // Create and authenticate a user via Sanctum before each test
    beforeEach(function () {
        $this->authUser = User::factory()->create();
        $this->actingAs($this->authUser, 'sanctum');
    });

    // Return all users
    it('returns a list of users', function () {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'given_name',
                    'family_name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    });

    // Return a single user
    it('returns a single user', function () {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'given_name',
                'family_name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);
    });

    // Create a new user
    it('returns a newly created user', function () {
        $attributes = User::factory()->raw();
        $attributes['password_confirmation'] = $attributes['password'];

        $response = $this->postJson('/api/v1/users', $attributes);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'given_name',
                'family_name',
                'email',
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
        $attributes['password_confirmation'] = $attributes['password'];

        $response = $this->postJson('/api/v1/users', $attributes);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('given_name');
    });

    // Create user with missing required fields: email
    it('returns status 422 with validation error when creating user with missing email', function () {
        $attributes = User::factory()->raw(['email' => null]);
        $attributes['password_confirmation'] = $attributes['password'];

        $response = $this->postJson('/api/v1/users', $attributes);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    });

    // Update a user
    it('returns an updated user', function () {
        $user = User::factory()->create();  // Create existing user first

        $attributes = User::factory()->raw(); // Create updated user details

        $updateData = [
            'given_name' => $attributes['given_name'],
            'family_name' => $attributes['family_name'],
        ];

        $response = $this->patchJson("/api/v1/users/{$user->id}", $updateData);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'given_name',
                'family_name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'given_name' => $updateData['given_name'],
            'family_name' => $updateData['family_name'],
            'email' => $user->email,  // Unchanged
        ]);
    });

    // Delete a user
    it('deletes a user and returns status 204', function () {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");
        $response->assertStatus(204);

        expect(User::find($user->id))->toBeNull();
    });
});
