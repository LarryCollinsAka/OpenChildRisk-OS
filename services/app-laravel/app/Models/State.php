<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'country_id',
        'country_code',
        'fips_code',
        'iso2',
        'type',
        'latitude',
        'longitude',
        'flag',
    ];

    protected $casts = [
        'latitude' => 'decimal:10',
        'longitude' => 'decimal:10',
    ];

    /**
     * Country this state belongs to
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Cities in this state
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Districts in this state
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * Location aliases pointing to this state
     */
    public function locationAliases()
    {
        return $this->hasMany(LocationAlias::class, 'canonical_state_id');
    }
}