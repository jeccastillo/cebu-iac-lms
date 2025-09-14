<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMode;

class PaymentModeSeeder extends Seeder
{
    /**
     * Seed payment modes excluding Maya.
     */
    public function run(): void
    {
        $rows = [
            // Paynamics Online Bank Transfer
            [
                'name' => 'Paynamics Online Bank Transfer',
                'image_url' => null,
                'type' => 'percentage', // or 'fixed' depending on finance policy
                'charge' => 0,
                'is_active' => true,
                'pchannel' => 'ubp_online',
                'pmethod' => 'onlinebanktransfer',
                'is_nonbank' => false,
            ],
            // Paynamics Wallet (e.g., GCash)
            [
                'name' => 'Paynamics Wallet (GCash)',
                'image_url' => null,
                'type' => 'percentage',
                'charge' => 0,
                'is_active' => true,
                'pchannel' => 'gcash',
                'pmethod' => 'wallet',
                'is_nonbank' => false,
            ],
            // Paynamics Non-bank OTC (e.g., 7-Eleven)
            [
                'name' => 'Paynamics OTC (Non-bank)',
                'image_url' => null,
                'type' => 'fixed',
                'charge' => 0,
                'is_active' => true,
                'pchannel' => '711_ph',
                'pmethod' => 'nonbank_otc',
                'is_nonbank' => true,
            ],
            // BDO / CyberSource direct pay
            [
                'name' => 'BDO Pay (Credit/Debit)',
                'image_url' => null,
                'type' => 'fixed',
                'charge' => 0,
                'is_active' => true,
                'pchannel' => 'bdo_cybersource',
                'pmethod' => 'bdo_pay',
                'is_nonbank' => false,
            ],
            // MaxxPayment Installments
            [
                'name' => 'BDO Installment (MaxxPayment)',
                'image_url' => null,
                'type' => 'fixed',
                'charge' => 0,
                'is_active' => true,
                'pchannel' => 'maxx',
                'pmethod' => 'maxx_payment',
                'is_nonbank' => false,
            ],
        ];

        foreach ($rows as $row) {
            PaymentMode::updateOrCreate(
                ['pmethod' => $row['pmethod']],
                $row
            );
        }
    }
}
