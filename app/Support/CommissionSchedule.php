<?php

namespace App\Support;

use App\Models\Commission;
use App\Models\DigitalAkad;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menerbitkan dan menggugurkan termin komisi mengikuti siklus akad jual beli.
 */
class CommissionSchedule
{
    /**
     * Terbitkan semua termin komisi (status pending) untuk akad yang baru sah.
     * Besaran komisi dikunci saat ini agar tidak berubah walau listing diubah kemudian (menghindari gharar).
     *
     * @return Collection<int, Commission>
     */
    public static function createForAkad(DigitalAkad $akad, ?User $actor): Collection
    {
        if (! $akad->generatesCommission()) {
            return collect();
        }

        return DB::transaction(function () use ($akad, $actor) {
            $base = (float) $akad->transaction_amount;
            $rate = (float) $akad->propertyProject->commission_rate;
            $feeRate = (float) config('commission.platform_fee_rate');
            $termins = config('commission.termins');

            $totalCommission = round($base * $rate / 100, 2);
            $allocated = 0.0;
            $commissions = collect();

            foreach ($termins as $termin => $setting) {
                // Termin terakhir menerima sisa pembulatan agar total tepat sama
                $amount = $termin === array_key_last($termins)
                    ? round($totalCommission - $allocated, 2)
                    : round($totalCommission * $setting['percentage'] / 100, 2);
                $allocated += $amount;

                $platformFee = round($amount * $feeRate / 100, 2);

                $commission = $akad->commissions()->create([
                    'agency_partner_id' => $akad->agency_partner_id,
                    'termin' => $termin,
                    'termin_percentage' => $setting['percentage'],
                    'base_transaction_amount' => $base,
                    'commission_rate' => $rate,
                    'total_commission' => $amount,
                    'platform_fee_rate' => $feeRate,
                    'platform_fee' => $platformFee,
                    'net_commission' => round($amount - $platformFee, 2),
                    'status' => Commission::STATUS_PENDING,
                ]);

                $commission->logCreated($actor, "Diterbitkan otomatis dari akad {$akad->akad_number}. Syarat: {$setting['condition']}.");
                $commissions->push($commission);
            }

            return $commissions;
        });
    }

    /**
     * Akad batal: termin yang syaratnya belum terpenuhi gugur.
     * Termin yang sudah jatuh tempo tetap menjadi hak agency karena hasil kerjanya sudah tercapai.
     */
    public static function voidPendingForAkad(DigitalAkad $akad, ?User $actor): int
    {
        $pending = $akad->commissions()->where('status', Commission::STATUS_PENDING)->get();

        foreach ($pending as $commission) {
            $commission->transitionTo(Commission::STATUS_VOID, $actor, "Akad {$akad->akad_number} dibatalkan sebelum syarat termin terpenuhi.");
        }

        return $pending->count();
    }
}
