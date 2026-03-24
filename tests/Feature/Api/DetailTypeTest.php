<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\DetailType;

uses(RefreshDatabase::class);

describe('Detail Type', function () {

    // Return all detail types
    it('returns a list of detail types', function () {
        DetailType::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/detail-types');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    });

    // Return a single detail type
    it('returns a single detail type', function () {
        $detailType = DetailType::factory()->create();

        $response = $this->getJson("/api/v1/detail-types/{$detailType->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
            ],
        ]);
    });

    // Create a new detail type
    it('returns a newly created detail type', function () {
        $attributes = DetailType::factory()->raw();

        $response = $this->postJson('/api/v1/detail-types', $attributes);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
            ],
        ]);
        $this->assertDatabaseHas('detail_types', [
            'name' => $attributes['name'],
        ]);
    });

    // Create detail type with missing required field
    it('returns status 422 with validation error when creating detail type with missing name', function () {
        $attributes = DetailType::factory()->raw(['name' => null]);

        $response = $this->postJson('/api/v1/detail-types', $attributes);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    });

    // Update a detail type
    it('returns an updated detail type', function () {
        $detailType = DetailType::factory()->create();
        $attributes = DetailType::factory()->raw();

        $updateData = [
            'name' => $attributes['name'],
        ];

        $response = $this->patchJson("/api/v1/detail-types/{$detailType->id}", $updateData);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
            ],
        ]);

        $this->assertDatabaseHas('detail_types', [
            'id' => $detailType->id,
            'name' => $updateData['name'],
        ]);
    });

    // Delete a detail type
    it('deletes a detail type and returns status 204', function () {
        $detailType = DetailType::factory()->create();

        $response = $this->deleteJson("/api/v1/detail-types/{$detailType->id}");
        $response->assertStatus(204);
        expect(DetailType::find($detailType->id))->toBeNull();
    });
});
