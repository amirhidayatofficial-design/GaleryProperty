<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Throwable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_SUSPENDED => 'Suspended',
    ];

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

    // ---------- State machine status akun ----------
    //
    //   registrasi ──▶ pending ──(klik tautan email)──▶ active
    //                     │                              │  ▲
    //                     └──────── admin ──────▶ suspended ┘ (admin)
    //
    // Aturan: active = email sudah terverifikasi, pending = belum.
    // Akun suspended tidak bisa login dan tidak berubah oleh verifikasi email.

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Tautan verifikasi diklik: pending → active (akun suspended tetap suspended).
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'status' => $this->isSuspended() ? self::STATUS_SUSPENDED : self::STATUS_ACTIVE,
        ])->save();
    }

    /**
     * Email diganti pengguna: active → pending sampai email baru diverifikasi. Belum disimpan.
     */
    public function markEmailAsUnverified(): void
    {
        $this->email_verified_at = null;

        if (! $this->isSuspended()) {
            $this->status = self::STATUS_PENDING;
        }
    }

    /**
     * Status diubah admin, dengan tetap menjaga aturan state machine. Belum disimpan.
     * Mengembalikan true bila akun baru masuk ke pending, sehingga email verifikasi perlu dikirim.
     */
    public function applyStatusFromAdmin(string $status): bool
    {
        $becomesPending = $status === self::STATUS_PENDING && $this->status !== self::STATUS_PENDING;

        $this->status = $status;

        if ($status === self::STATUS_PENDING) {
            $this->email_verified_at = null;
        } elseif ($status === self::STATUS_ACTIVE && ! $this->email_verified_at) {
            // Admin mengaktifkan akun = verifikasi manual
            $this->email_verified_at = $this->freshTimestamp();
        }

        return $becomesPending;
    }

    /**
     * Dipanggil otomatis saat registrasi. Kegagalan mengirim email tidak boleh membatalkan registrasi.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->trySendEmailVerification();
    }

    /**
     * Kirim email verifikasi; false bila layanan email sedang bermasalah (error tetap dicatat di log).
     */
    public function trySendEmailVerification(): bool
    {
        try {
            $this->notify(new VerifyEmail);

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
