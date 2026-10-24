<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'buyer_id',
        'agency_partner_id',
        'property_project_id',
        'buyer_last_read_message_id',
        'agency_last_read_message_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function agencyPartner(): BelongsTo
    {
        return $this->belongsTo(AgencyPartner::class);
    }

    public function propertyProject(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class)->withTrashed();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Percakapan yang melibatkan user: sebagai pembeli atau sebagai agency.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAgencyPartner()) {
            return $query->where('agency_partner_id', $user->agencyPartner?->id ?? 0);
        }

        return $query->where('buyer_id', $user->id);
    }

    public function isParticipant(User $user): bool
    {
        return $this->buyer_id === $user->id
            || ($user->isAgencyPartner() && $user->agencyPartner?->id === $this->agency_partner_id);
    }

    /**
     * Kolom penanda baca milik user ini (pembeli atau agency).
     */
    public function readColumnFor(User $user): string
    {
        return $this->buyer_id === $user->id ? 'buyer_last_read_message_id' : 'agency_last_read_message_id';
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('id', '>', $this->{$this->readColumnFor($user)} ?? 0)
            ->count();
    }

    public function markAsReadBy(User $user): void
    {
        $latestId = $this->messages()->max('id');

        if ($latestId) {
            $this->update([$this->readColumnFor($user) => $latestId]);
        }
    }

    /**
     * Jumlah percakapan yang memiliki pesan belum dibaca oleh user.
     */
    public static function unreadConversationCountFor(User $user): int
    {
        $readColumn = $user->isAgencyPartner() ? 'agency_last_read_message_id' : 'buyer_last_read_message_id';

        return static::forUser($user)
            ->whereHas('messages', fn (Builder $messages) => $messages
                ->where('sender_id', '!=', $user->id)
                ->whereRaw("messages.id > COALESCE(conversations.{$readColumn}, 0)"))
            ->count();
    }
}
