<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HazardCategory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'color',
        'active',
        'metadata',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Hazard types in this category
     */
    public function hazardTypes()
    {
        return $this->hasMany(HazardType::class, 'category_id');
    }

    /**
     * Active hazard types
     */
    public function activeHazardTypes()
    {
        return $this->hasMany(HazardType::class, 'category_id')->where('active', true);
    }
}