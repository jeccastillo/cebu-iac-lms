<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FinanceService;

class FinanceServiceTest extends TestCase
{
    public function test_list_transactions_returns_array(): void
    {
        $svc = app(FinanceService::class);

        $out = $svc->listTransactions(null, null, null);

        $this->assertIsArray($out);
    }

    public function test_or_lookup_nonexistent_returns_null(): void
    {
        $svc = app(FinanceService::class);

        // Use an unlikely OR number to assert null behavior
        $out = $svc->orLookup('999999999');

        $this->assertNull($out);
    }
}
