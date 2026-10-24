<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerLead extends Model
{
    use HasFactory;

    public const STATUSES = [
        'new_lead' => 'Lead Baru',
        'survey_scheduled' => 'Jadwal Survei',
        'booking_prospect' => 'Prospek Booking',
        'akad_process' => 'Proses Akad',
        'closed' => 'Closing',
        'lost' => 'Batal',
    ];

    protected $fillable = [
        'user_id',
        'agency_partner_id',
        'property_project_id',
        'full_name',
        'phone',
        'email',
        'budget_min',
        'budget_max',
        'preferred_location',
        'preferred_scheme',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agencyPartner(): BelongsTo
    {
        return $this->belongsTo(AgencyPartner::class);
    }

    public function propertyProject(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class);
    }

    public function digitalAkads(): HasMany
    {
        return $this->hasMany(DigitalAkad::class);
    }
}
