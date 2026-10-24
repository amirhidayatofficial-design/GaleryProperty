<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyFile extends Model
{
    use HasFactory;

    public const FILE_TYPES = [
        'photo' => 'Foto',
        'siteplan' => 'Siteplan / Denah',
        'brochure_pdf' => 'Brosur PDF',
        'legal_doc' => 'Dokumen Legalitas',
    ];

    protected $fillable = [
        'property_project_id',
        'file_path',
        'file_type',
        'caption',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function propertyProject(): BelongsTo
    {
        return $this->belongsTo(PropertyProject::class);
    }
}
