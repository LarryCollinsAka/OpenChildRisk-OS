<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'facility_type',
        'organization_id',
        'district_id',
        'latitude',
        'longitude',
        'address',
        'contact_phone',
        'contact_email',
        'capacity',
        'services_offered',
        'operational_status',
        'last_inspection_date',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'capacity' => 'integer',
        'services_offered' => 'array',
        'last_inspection_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Organization managing this facility
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * District this facility is in
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Field workers assigned to this facility
     */
    public function fieldWorkers()
    {
        return $this->hasMany(FieldWorker::class, 'primary_facility_id');
    }

    /**
     * Check if facility is operational
     */
    public function isOperational(): bool
    {
        return $this->operational_status === 'operational';
    }
}