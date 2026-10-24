<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DigitalAkad extends Model
{
    use HasFactory;

    public const AKAD_TYPES = [
        'akad_istishna' => 'Akad Istishna',
        'akad_murabahah' => 'Akad Murabahah',
        'mou_peminatan' => 'MOU Peminatan',
        'mou_keagenan' => 'MOU Keagenan',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'pending_signature' => 'Menunggu Tanda Tangan',
        'signed' => 'Ditandatangani',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'akad_number',
        'property_project_id',
        'buyer_lead_id',
        'agency_partner_id',
        'akad_type',
        'transaction_amount',
        'dp_amount',
        'tenor_months',
        'terms_content',
        'document_pdf_path',
        'buyer_signed_at',
        'agency_signed_at',
        'developer_signed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'transaction_amount' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'tenor_months' => 'integer',
            'buyer_signed_at' => 'datetime',
            'agency_signed_at' => 'datetime',
            'developer_signed_at' => 'datetime',
        ];
    }

    public function propertyProject(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class);
    }

    public function buyerLead(): BelongsTo
    {
        return $this->belongsTo(BuyerLead::class);
    }

    public function agencyPartner(): BelongsTo
    {
        return $this->belongsTo(AgencyPartner::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function isFullySigned(): bool
    {
        return !is_null($this->buyer_signed_at) &&
               !is_null($this->agency_signed_at) &&
               !is_null($this->developer_signed_at);
    }
}
