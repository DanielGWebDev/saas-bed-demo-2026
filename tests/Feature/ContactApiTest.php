<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Contact;

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

it('returns a single contact', function () {
    $response = $this->getJson('/api/v1/contacts/1');
    $response->assertStatus(200);
    $response->assertJsonStructure(
        ['id', 'given_name', 'family_name', 'nick_name', 'title'],
    );
});

it('returns a newly created contact', function () {
    $attributes = Contact::factory()->raw();
    $response = $this->postJson('/api/v1/contacts', $attributes);
    $response->assertStatus(201)
             ->assertJsonStructure(
                 ['id', 'given_name', 'family_name', 'nick_name', 'title']
             );
    $this->assertDatabaseHas('contacts', $attributes);
});

it('returns create contact error when missing given name', function () {
    $attributes = Contact::factory()->raw(['given_name' => null]);
    $response = $this->postJson('/api/v1/contacts', $attributes);
    $response->assertStatus(422)
        ->assertJsonValidationErrors('given_name');
});

it('returns an updated contact', function () {
    $attributes = Contact::factory()->raw();
    $response = $this->putJson('/api/v1/contacts/1', $attributes);
    $response->assertStatus(200)
        ->assertJsonStructure(
            ['id', 'given_name', 'family_name', 'nick_name', 'title']
        );
    $this->assertDatabaseHas('contacts', $attributes);
});

it('returns an deleted contact', function () {
    $response = $this->getJson('/api/v1/contacts/1');
    $response->assertStatus(200);
});

it('returns status 204 when contact deleted', function () {
    $contact = Contact::factory()->create();  // Create a contact to delete
    $response = $this->delete("/api/v1/contacts/{$contact->id}");
    $response->assertStatus(204); // No content
});
