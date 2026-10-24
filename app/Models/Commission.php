<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Komisi agency untuk satu termin dari satu akad jual beli.
 *
 *   pending ──(syarat termin terpenuhi)──▶ due ──(invoice)──▶ invoiced ──(developer bayar)──▶ paid ──(payout)──▶ settled
 *      └──(akad batal sebelum termin jatuh tempo)──▶ void
 *
 * Tidak ada clawback: termin yang sudah dibayarkan adalah hak agency (ju'alah).
 * Lewat jatuh tempo hanya memicu pengingat dan blokir listing baru, tanpa denda.
 */
class Commission extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DUE = 'due';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_PAID = 'paid';
    public const STATUS_SETTLED = 'settled';
    public const STATUS_VOID = 'void';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Syarat',
        self::STATUS_DUE => 'Jatuh Tempo',
        self::STATUS_INVOICED => 'Ditagihkan',
        self::STATUS_PAID => 'Dana Diterima',
        self::STATUS_SETTLED => 'Selesai',
        self::STATUS_VOID => 'Gugur',
    ];

    /**
     * Transisi yang diizinkan dari setiap status.
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_DUE, self::STATUS_VOID],
        self::STATUS_DUE => [self::STATUS_INVOICED],
        self::STATUS_INVOICED => [self::STATUS_PAID],
        self::STATUS_PAID => [self::STATUS_SETTLED],
    ];

    protected $fillable = [
        'digital_akad_id',
        'agency_partner_id',
        'termin',
        'termin_percentage',
        'base_transaction_amount',
        'commission_rate',
        'total_commission',
        'platform_fee_rate',
        'platform_fee',
        'net_commission',
        'status',
        'invoice_number',
        'invoiced_at',
        'due_at',
        'paid_at',
        'payment_proof_path',
        'settled_at',
        'payout_proof_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'termin' => 'integer',
            'termin_percentage' => 'decimal:2',
            'base_transaction_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'platform_fee_rate' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_commission' => 'decimal:2',
            'invoiced_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'settled_at' => 'datetime',
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

    public function statusLogs(): HasMany
    {
        return $this->hasMany(CommissionStatusLog::class)->latest('id');
    }

    // ---------- State machine ----------

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Pindahkan status sekaligus mencatat jejak audit dalam satu transaksi database.
     *
     * @throws LogicException bila transisi tidak diizinkan
     */
    public function transitionTo(string $status, ?User $actor, ?string $note = null, array $attributes = []): void
    {
        if (! $this->canTransitionTo($status)) {
            throw new LogicException("Komisi berstatus '{$this->statusLabel()}' tidak dapat diubah menjadi '" . (self::STATUSES[$status] ?? $status) . "'.");
        }

        DB::transaction(function () use ($status, $actor, $note, $attributes) {
            $from = $this->status;

            $this->fill([...$attributes, 'status' => $status])->save();

            $this->statusLogs()->create([
                'user_id' => $actor?->id,
                'from_status' => $from,
                'to_status' => $status,
                'note' => $note,
            ]);
        });
    }

    /**
     * Catat terbitnya komisi (status awal) di jejak audit.
     */
    public function logCreated(?User $actor, string $note): void
    {
        $this->statusLogs()->create([
            'user_id' => $actor?->id,
            'from_status' => null,
            'to_status' => $this->status,
            'note' => $note,
        ]);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function terminLabel(): string
    {
        return config("commission.termins.{$this->termin}.label", "Termin {$this->termin}");
    }

    public function terminCondition(): ?string
    {
        return config("commission.termins.{$this->termin}.condition");
    }

    // ---------- Pemantauan jatuh tempo (tanpa denda) ----------

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_INVOICED && $this->due_at?->isPast();
    }

    public function payoutDueAt(): ?Carbon
    {
        return $this->paid_at?->copy()->addWeekdays((int) config('commission.payout_due_business_days'));
    }

    public function isPayoutLate(): bool
    {
        return $this->status === self::STATUS_PAID && $this->payoutDueAt()?->isPast();
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INVOICED)->where('due_at', '<', now());
    }

    /**
     * Tagihan lewat jatuh tempo milik developer tertentu (developer dicatat sebagai teks di projek).
     */
    public function scopeOverdueForDeveloper(Builder $query, string $developerName): Builder
    {
        return $query->overdue()->whereHas('digitalAkad.propertyProject', fn (Builder $project) => $project
            ->whereRaw('LOWER(TRIM(developer_name)) = ?', [mb_strtolower(trim($developerName))]));
    }

    /**
     * Nomor invoice berformat INV/YYYYMM/XXXX, berurutan per bulan.
     */
    public static function nextInvoiceNumber(): string
    {
        $prefix = 'INV/' . now()->format('Ym') . '/';
        $sequence = static::where('invoice_number', 'like', "{$prefix}%")->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
