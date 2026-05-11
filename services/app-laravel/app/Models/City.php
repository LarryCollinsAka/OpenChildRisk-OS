<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'state_id',
        'state_code',
        'country_id',
        'country_code',
        'latitude',
        'longitude',
        'flag',
        'wikiDataId',
    ];

    protected $casts = [
        'latitude' => 'decimal:10',
        'longitude' => 'decimal:10',
    ];

    /**
     * State this city belongs to
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Country this city belongs to
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Location aliases pointing to this city
     */
    public function locationAliases()
    {
        return $this->hasMany(LocationAlias::class, 'canonical_city_id');
    }
}