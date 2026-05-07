<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'specialization_area',
        'active',
        'metadata',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Field workers of this type
     */
    public function fieldWorkers()
    {
        return $this->hasMany(FieldWorker::class);
    }

    /**
     * Get active field workers of this type
     */
    public function activeFieldWorkers()
    {
        return $this->hasMany(FieldWorker::class)->where('status', 'active');
    }
}