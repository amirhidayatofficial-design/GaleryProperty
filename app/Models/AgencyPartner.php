<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class AgencyPartner extends Model
{
    use HasFactory;

    public const VERIFICATION_STATUSES = [
        'pending' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
    ];

    public const BANKS = [
        'Bank Syariah Indonesia (BSI)',
        'Bank Muamalat',
        'BCA Syariah',
    ];

    protected $fillable = [
        'user_id',
        'agency_name',
        'license_number',
        'company_address',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'verification_status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function propertyProjects(): HasMany
    {
        return $this->hasMany(PropertyProject::class);
    }

    public function buyerLeads(): HasMany
    {
        return $this->hasMany(BuyerLead::class);
    }

    public function digitalAkads(): HasMany
    {
        return $this->hasMany(DigitalAkad::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Aturan kolom profil agency yang bisa diisi admin maupun agency sendiri.
     */
    public static function profileRules(): array
    {
        return [
            'agency_name' => ['required', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'company_address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', Rule::in(self::BANKS)],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
        ];
    }
}
