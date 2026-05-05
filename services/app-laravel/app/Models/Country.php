<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Country Model
 *
 * Registry of countries where OpenChildRisk OS is deployed.
 */
class Country extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'countries';

    protected $fillable = [
        'iso',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Country has many districts.
     */
    public function districts()
    {
        return $this->hasMany(District::class, 'country_id');
    }
}