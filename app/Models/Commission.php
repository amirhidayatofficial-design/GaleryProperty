<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Disetujui',
        'paid' => 'Dibayar',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'digital_akad_id',
        'agency_partner_id',
        'base_transaction_amount',
        'commission_rate',
        'total_commission',
        'platform_fee',
        'net_commission',
        'status',
        'paid_at',
        'payment_proof_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_transaction_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_commission' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function digitalAkad(): BelongsTo
    {
        return $this->belongsTo(DigitalAkad::class);
    }

    public function agencyPartner(): BelongsTo
    {
        return $this->belongsTo(AgencyPartner::class);
    }
}
