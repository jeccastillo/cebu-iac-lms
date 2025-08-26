<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class RegistrarControllerTest extends TestCase
{
    public function test_grading_meta_invalid_dept_returns_422(): void
    {
        $res = $this->getJson('/api/v1/registrar/grading/meta?dept=invalid');
        $res->assertStatus(422)
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_grading_meta_valid_dept_returns_success(): void
    {
        // 'college' is a valid dept; response should be success, even if lists are empty
        $res = $this->getJson('/api/v1/registrar/grading/meta?dept=college');
        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'terms',
                    'faculty',
                ],
            ]);
    }

    public function test_daily_enrollment_missing_fields_returns_422(): void
    {
        // Missing required fields should trigger 422
        $res = $this->postJson('/api/v1/registrar/daily-enrollment', []);
        $res->assertStatus(422)
            ->assertJsonStructure(['message']); // Laravel validation default
    }

    public function test_classlist_submitted_unknown_returns_404(): void
    {
        // Use a very large/non-existent classlist id
        $res = $this->getJson('/api/v1/registrar/classlist/999999999/submitted');
        $res->assertStatus(404)
            ->assertJsonStructure(['success', 'message']);
    }
}
