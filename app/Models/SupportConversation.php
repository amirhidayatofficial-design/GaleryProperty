<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Percakapan customer support antara pembeli/agency dengan tim admin.
 * Semua admin berbagi satu inbox, jadi penanda baca sisi admin juga satu.
 */
class SupportConversation extends Model
{
    protected $fillable = [
        'user_id',
        'user_last_read_message_id',
        'admin_last_read_message_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }

    /**
     * Kolom penanda baca: sisi admin atau sisi pemilik percakapan.
     */
    public static function readColumnFor(User $viewer): string
    {
        return $viewer->isAdmin() ? 'admin_last_read_message_id' : 'user_last_read_message_id';
    }

    /**
     * Pesan dari "pihak lain": untuk admin berarti pesan pengguna, untuk pengguna berarti pesan admin.
     */
    private static function whereFromOtherSide(Builder $messages, User $viewer): Builder
    {
        return $messages->whereHas('sender', fn (Builder $sender) => $viewer->isAdmin()
            ? $sender->where('role', '!=', 'admin')
            : $sender->where('role', 'admin'));
    }

    public function unreadCountFor(User $viewer): int
    {
        return static::whereFromOtherSide($this->messages()->getQuery(), $viewer)
            ->where('id', '>', $this->{static::readColumnFor($viewer)} ?? 0)
            ->count();
    }

    public function markAsReadBy(User $viewer): void
    {
        $latestId = $this->messages()->max('id');

        if ($latestId) {
            $this->update([static::readColumnFor($viewer) => $latestId]);
        }
    }

    /**
     * Admin: jumlah percakapan dengan pesan pengguna yang belum dibalas/dibaca.
     * Pengguna: 1 jika ada balasan admin yang belum dibaca.
     */
    public static function unreadConversationCountFor(User $viewer): int
    {
        $readColumn = static::readColumnFor($viewer);

        return static::query()
            ->when(! $viewer->isAdmin(), fn (Builder $query) => $query->where('user_id', $viewer->id))
            ->whereHas('messages', fn (Builder $messages) => static::whereFromOtherSide($messages, $viewer)
                ->whereRaw("support_messages.id > COALESCE(support_conversations.{$readColumn}, 0)"))
            ->count();
    }
}
