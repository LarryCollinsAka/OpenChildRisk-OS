<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * DistrictTranslation Model
 *
 * Stores translated names and descriptions for districts.
 * One record per district per language.
 *
 * Supports fallback to English via District::translate()
 */
class DistrictTranslation extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'district_translations';

    protected $fillable = [
        'district_id',
        'language_code',
        'name',
        'description',
        'translated_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Translation belongs to a district.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}