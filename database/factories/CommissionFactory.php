<?php

namespace Database\Factories;

use App\Models\AgencyPartner;
use App\Models\Commission;
use App\Models\DigitalAkad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        $base = fake()->randomElement([600000000, 750000000, 900000000, 1200000000]);
        $rate = 2.50;
        $total = round($base * $rate / 100 * 0.5, 2);
        $feeRate = 20;
        $fee = round($total * $feeRate / 100, 2);

        // Termin 1 (50%) yang masih menunggu syarat
        return [
            'digital_akad_id' => DigitalAkad::factory(),
            'agency_partner_id' => AgencyPartner::factory(),
            'termin' => 1,
            'termin_percentage' => 50,
            'base_transaction_amount' => $base,
            'commission_rate' => $rate,
            'total_commission' => $total,
            'platform_fee_rate' => $feeRate,
            'platform_fee' => $fee,
            'net_commission' => $total - $fee,
            'status' => 'pending',
        ];
    }
}
