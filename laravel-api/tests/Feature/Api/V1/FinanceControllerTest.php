<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class FinanceControllerTest extends TestCase
{
    public function test_or_lookup_missing_param_returns_422(): void
    {
        $res = $this->getJson('/api/v1/finance/or-lookup');
        $res->assertStatus(422)
            ->assertJsonStructure(['message']); // validation error envelope
    }

    public function test_or_lookup_nonexistent_returns_404(): void
    {
        // Use a very unlikely OR number
        $res = $this->getJson('/api/v1/finance/or-lookup?or=999999999');
        $res->assertStatus(404)
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_transactions_no_params_returns_200_and_envelope(): void
    {
        $res = $this->getJson('/api/v1/finance/transactions');
        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_transactions_with_filters_returns_200(): void
    {
        // Filters are optional; this is a smoke to ensure filters are accepted
        $res = $this->getJson('/api/v1/finance/transactions?student_number=TEST123&syid=0');
        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
