<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * DistrictTranslation Model
 *
 * Stores translated names and descriptions for districts.
 * One record per district per language.
 */
class DistrictTranslation extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'district_translations';

    protected $fillable = [
        'district_id',
        'language_id',
        'name',
        'description',
    ];

    /**
     * Translation belongs to a district.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Translation belongs to a language.
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}