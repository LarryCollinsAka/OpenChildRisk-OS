<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Check if language is active
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}