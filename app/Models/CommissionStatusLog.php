<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jejak audit setiap perubahan status komisi: siapa, kapan, dari apa ke apa, dan catatannya.
 */
class CommissionStatusLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'commission_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
    ];

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
