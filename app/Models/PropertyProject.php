<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyProject extends Model
{
    use HasFactory, SoftDeletes;

    public const PROPERTY_TYPES = [
        'rumah' => 'Rumah',
        'tanah_kavling' => 'Tanah Kavling',
        'ruko' => 'Ruko',
    ];

    public const SYARIAH_SCHEMES = [
        'istishna' => 'Istishna',
        'murabahah' => 'Murabahah',
        'cash_bertahap' => 'Cash Bertahap',
        'cash_keras' => 'Cash Keras',
    ];

    public const STATUSES = [
        'available' => 'Tersedia',
        'booked' => 'Dipesan',
        'sold_out' => 'Terjual Habis',
    ];

    protected $fillable = [
        'agency_partner_id',
        'title',
        'slug',
        'developer_name',
        'property_type',
        'syariah_scheme',
        'price',
        'commission_rate',
        'province',
        'city',
        'district',
        'address_detail',
        'bedroom',
        'bathroom',
        'building_area',
        'land_area',
        'description',
        'halal_guarantee_notes',
        'status',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'bedroom' => 'integer',
            'bathroom' => 'integer',
            'building_area' => 'integer',
            'land_area' => 'integer',
            'is_verified' => 'boolean',
        ];
    }

    public function agencyPartner(): BelongsTo
    {
        return $this->belongsTo(AgencyPartner::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PropertyFile::class);
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(PropertyFile::class)->where('is_primary', true);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyFile::class)->where('file_type', 'photo');
    }

    public function legalDocs(): HasMany
    {
        return $this->hasMany(PropertyFile::class)->where('file_type', 'legal_doc');
    }

    public function buyerLeads(): HasMany
    {
        return $this->hasMany(BuyerLead::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Listing yang sudah diverifikasi admin dan tayang di katalog.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Aturan validasi data listing, dipakai oleh admin dan agency partner.
     */
    public static function validationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'developer_name' => ['required', 'string', 'max:150'],
            'property_type' => ['required', Rule::in(array_keys(self::PROPERTY_TYPES))],
            'syariah_scheme' => ['required', Rule::in(array_keys(self::SYARIAH_SCHEMES))],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address_detail' => ['required', 'string'],
            'bedroom' => ['nullable', 'integer', 'min:0', 'max:255'],
            'bathroom' => ['nullable', 'integer', 'min:0', 'max:255'],
            'building_area' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'land_area' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'description' => ['required', 'string'],
            'halal_guarantee_notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ];
    }

    /**
     * Slug unik dari judul, termasuk terhadap projek yang sudah di-soft delete.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'projek';
        $slug = $base;
        $counter = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function digitalAkads(): HasMany
    {
        return $this->hasMany(DigitalAkad::class);
    }
}
