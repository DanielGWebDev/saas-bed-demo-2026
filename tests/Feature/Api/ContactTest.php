<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Contact;
use App\Models\User;

uses(RefreshDatabase::class);

describe('Contact', function () {

    // Create and authenticate a user via Sanctum before each test
    beforeEach(function () {
        $this->authUser = User::factory()->create();
        $this->actingAs($this->authUser, 'sanctum');
    });

    // Return all contacts
    it('returns a list of contacts', function () {
        User::factory()->count(3)->create()->each(function ($user) {
            Contact::factory()->count(2)->create(['user_id' => $user->id]);
        });

        $response = $this->getJson('/api/v1/contacts');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'given_name',
                    'family_name',
                    'nick_name',
                    'title'
                ],
            ],
        ]);
    });

    // Return a single contact
    it('returns a single contact', function () {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'given_name',
                'family_name',
                'nick_name',
                'title'
            ],
        ]);
    });

    // Create a new contact
    it('returns a newly created contact', function () {
        User::factory()->create();
        $attributes = Contact::factory()->raw();

        $response = $this->postJson('/api/v1/contacts', $attributes);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'given_name',
                'family_name',
                'nick_name',
                'title'
            ],
        ]);
        $this->assertDatabaseHas('contacts', [
            'given_name' => $attributes['given_name'],
            'family_name' => $attributes['family_name'],
            'nick_name' => $attributes['nick_name'],
            'title' => $attributes['title'],
        ]);
    });

    // Create contact with missing required fields
    it('returns status 422 with validation error when creating contact with missing required field', function () {
        User::factory()->create();
        $attributes = Contact::factory()->raw(['given_name' => null]);

        $response = $this->postJson('/api/v1/contacts', $attributes);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('given_name');
    });

    // Update a contact
    it('returns an updated contact', function () {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);
        $attributes = Contact::factory()->raw();

        $updateData = [
            'given_name' => $attributes['given_name'],
            'family_name' => $attributes['family_name'],
            'nick_name' => $attributes['nick_name'],
            'title' => $attributes['title'],
        ];

        $response = $this->patchJson("/api/v1/contacts/{$contact->id}", $updateData);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'given_name',
                'family_name',
                'nick_name',
                'title'
            ],
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'given_name' => $updateData['given_name'],
            'family_name' => $updateData['family_name'],
            'nick_name' => $updateData['nick_name'],
            'title' => $updateData['title'],
        ]);
    });

    // Delete a contact
    it('deletes a contact and returns status 204', function () {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");
        $response->assertStatus(204);
        expect(Contact::find($contact->id))->toBeNull();
    });
});
