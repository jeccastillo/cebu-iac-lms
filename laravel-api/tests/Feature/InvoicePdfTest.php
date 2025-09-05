<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\InvoiceService;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class InvoicePdfTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Disable role middleware to focus test on controller behavior
        $this->withoutMiddleware(\App\Http\Middleware\RequireRole::class);
    }

    protected function mockInvoice(array $invoice): void
    {
        $this->mock(InvoiceService::class, function ($mock) use ($invoice) {
            $mock->shouldReceive('get')
                ->once()
                ->andReturn($invoice);
        });
    }

    protected function mockInvoiceNull(): void
    {
        $this->mock(InvoiceService::class, function ($mock) {
            $mock->shouldReceive('get')
                ->once()
                ->andReturn(null);
        });
    }

    public function test_invoice_pdf_streams_inline_pdf_with_expected_headers(): void
    {
        // Deterministic invoice returned by the service
        $invoice = [
            'id' => 123,
            'invoice_number' => '110001',
            'type' => 'other',
            'status' => 'posted',
            'remarks' => 'Test invoice',
            'amount_total' => 150.75,
            'items' => [
                ['title' => 'Item A', 'amount' => 100.50],
                ['title' => 'Item B', 'amount' => 50.25],
            ],
            'posted_at' => now()->toDateTimeString(),
        ];
        $this->mockInvoice($invoice);

        // Fake the Snappy PDF wrapper to avoid invoking wkhtmltopdf binary
        PDF::shouldReceive('loadView')
            ->once()
            ->andReturn(new class {
                public function setPaper($paper)
                {
                    return $this;
                }
                public function setOption($key, $value)
                {
                    return $this;
                }
                public function inline($filename)
                {
                    $content = '%PDF-TEST-INVOICE%';
                    return response($content, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    ]);
                }
            });

        $res = $this->get('/api/v1/finance/invoices/123/pdf');

        $res->assertOk();
        $res->assertHeader('Content-Type', 'application/pdf');
        $res->assertHeader('Content-Disposition', 'inline; filename="invoice-110001.pdf"');
        $this->assertStringContainsString('%PDF-TEST-INVOICE%', $res->getContent());
    }

    public function test_invoice_pdf_returns_404_when_invoice_not_found(): void
    {
        $this->mockInvoiceNull();

        $res = $this->get('/api/v1/finance/invoices/999/pdf');

        $res->assertStatus(404);
        $json = $res->json();
        $this->assertFalse($json['success'] ?? true);
        $this->assertEquals('Invoice id 999 not found', $json['message'] ?? '');
    }
}
