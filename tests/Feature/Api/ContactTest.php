<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Contact;

// Return all contacts
it('returns a list of contacts', function () {
    $response = $this->getJson('/api/v1/contacts');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        '*' => [
            'id',
            'given_name',
            'family_name',
            'nick_name',
            'title'
        ],
    ]);
});

// Return a single contact
it('returns a single contact', function () {
    $response = $this->getJson('/api/v1/contacts/1');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'id',
        'given_name',
        'family_name',
        'nick_name',
        'title'
    ]);
});

// Create a new contact
it('returns a newly created contact', function () {
    $attributes = Contact::factory()->raw();
    $response = $this->postJson('/api/v1/contacts', $attributes);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'id',
        'given_name',
        'family_name',
        'nick_name',
        'title'
    ]);
    $this->assertDatabaseHas('contacts', $attributes);
});

// Create contact with missing required fields
it('returns status 422 with validation error when creating contact with missing required field', function () {
    $attributes = Contact::factory()->raw(['given_name' => null]);
    $response = $this->postJson('/api/v1/contacts', $attributes);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('given_name');
});

// Update a contact
it('returns an updated contact', function () {
    $attributes = Contact::factory()->raw();
    $response = $this->putJson('/api/v1/contacts/1', $attributes);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'id',
        'given_name',
        'family_name',
        'nick_name',
        'title'
    ]);
    $this->assertDatabaseHas('contacts', $attributes);
});

// Delete a contact
it('deletes a contact and returns status 204', function () {
    $contact = Contact::factory()->create();
    $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");
    $response->assertStatus(204);
    expect(Contact::find($contact->id))->toBeNull();
});
