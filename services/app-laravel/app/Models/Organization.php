<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Organization Model
 *
 * Represents UN agencies, governments, NGOs, and donors
 * operating within the OpenChildRisk OS system.
 */
class Organization extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'short_name',
        'type',
        'country_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Organization has many alerts assigned to it.
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class, 'organization_id');
    }
}