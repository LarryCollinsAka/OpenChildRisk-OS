<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldWorker extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'worker_type_id',
        'external_id',
        'first_name',
        'last_name',
        'title',
        'specializations',
        'phone',
        'phone_country_code',
        'phone_verified',
        'email',
        'whatsapp_number',
        'primary_district_id',
        'primary_facility_id',
        'coverage_area_geom',
        'status',
        'hire_date',
        'training_completed_at',
        'receive_sms_alerts',
        'receive_whatsapp_alerts',
        'alert_priority_threshold',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'specializations' => 'array',
        'phone_verified' => 'boolean',
        'receive_sms_alerts' => 'boolean',
        'receive_whatsapp_alerts' => 'boolean',
        'hire_date' => 'date',
        'training_completed_at' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Organization this worker belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * User account (if field worker has login)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Worker type classification
     */
    public function workerType()
    {
        return $this->belongsTo(WorkerType::class);
    }

    /**
     * Primary district assignment
     */
    public function primaryDistrict()
    {
        return $this->belongsTo(District::class, 'primary_district_id');
    }

    /**
     * Primary facility assignment
     */
    public function primaryFacility()
    {
        return $this->belongsTo(Facility::class, 'primary_facility_id');
    }

    /**
     * Check if field worker has user login
     */
    public function hasLogin(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Check if field worker is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if field worker has completed training
     */
    public function isTrainingCompleted(): bool
    {
        return !is_null($this->training_completed_at);
    }

    /**
     * Check if worker has specific specialization
     */
    public function hasSpecialization(string $specialization): bool
    {
        return in_array($specialization, $this->specializations ?? []);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->title} {$this->first_name} {$this->last_name}");
    }

    /**
     * Get preferred contact method
     */
    public function getPreferredContactAttribute(): string
    {
        if ($this->receive_whatsapp_alerts && $this->whatsapp_number) {
            return 'whatsapp';
        }
        if ($this->receive_sms_alerts) {
            return 'sms';
        }
        if ($this->email) {
            return 'email';
        }
        return 'sms'; // Default
    }
}