<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function agencyPartner(): HasOne
    {
        return $this->hasOne(AgencyPartner::class);
    }

    public function buyerLeads(): HasMany
    {
        return $this->hasMany(BuyerLead::class);
    }

    /**
     * Aturan nomor WhatsApp: hanya angka, 10-15 digit, diawali 0 atau 62.
     */
    public static function phoneRules(): array
    {
        return ['digits_between:10,15', 'starts_with:0,62'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgencyPartner(): bool
    {
        return $this->role === 'agency_partner';
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }
}
