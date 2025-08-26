<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class ScholarshipControllerTest extends TestCase
{
    public function test_index_returns_200_and_envelope(): void
    {
        $res = $this->getJson('/api/v1/scholarships');
        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_assigned_missing_params_returns_422(): void
    {
        // syid provided but neither student_id nor student_number
        $res = $this->getJson('/api/v1/scholarships/assigned?syid=1');
        $res->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_enrolled_missing_syid_returns_422(): void
    {
        $res = $this->getJson('/api/v1/scholarships/enrolled');
        $res->assertStatus(422)
            ->assertJsonStructure([
                'message', // default Laravel validation envelope
            ]);
    }

    public function test_upsert_returns_501(): void
    {
        $res = $this->postJson('/api/v1/scholarships/upsert', []);
        $res->assertStatus(501)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_delete_returns_501(): void
    {
        $res = $this->deleteJson('/api/v1/scholarships/123');
        $res->assertStatus(501)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }
}
