<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'language_id',
        'phone',
        'phone_country_code',
        'phone_verified',
        'first_name',
        'last_name',
        'job_title',
        'primary_district_id',
        'primary_facility_id',
        'receive_sms_alerts',
        'receive_email_alerts',
        'receive_whatsapp_alerts',
        'active',
        'last_active_at',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified' => 'boolean',
            'receive_sms_alerts' => 'boolean',
            'receive_email_alerts' => 'boolean',
            'receive_whatsapp_alerts' => 'boolean',
            'active' => 'boolean',
            'last_active_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user's organizations through their roles.
     * 
     * Example: $user->organizations
     * Returns all orgs where user has any role.
     */
    public function organizations()
    {
        return Organization::whereIn('id', function ($query) {
            $query->select('organization_id')
                  ->from('model_has_roles')
                  ->where('model_type', self::class)
                  ->where('model_id', $this->id);
        })->get();
    }

    /**
     * Get user's roles in a specific organization.
     * 
     * Example: $user->rolesInOrganization($orgId)
     */
    public function rolesInOrganization($organizationId)
    {
        return $this->roles()->wherePivot('organization_id', $organizationId)->get();
    }

    /**
     * Check if user has permission in specific organization.
     * 
     * Example: $user->hasPermissionInOrg('create_alerts', $orgId)
     */
    public function hasPermissionInOrg(string $permission, $organizationId): bool
    {
        return $this->hasPermissionTo($permission, $organizationId);
    }

    /**
     * Check if user has role in specific organization.
     * 
     * Example: $user->hasRoleInOrg('Field Officer', $orgId)
     */
    public function hasRoleInOrg(string $role, $organizationId): bool
    {
        return $this->hasRole($role, $organizationId);
    }

    /**
     * Relationships
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function primaryDistrict()
    {
        return $this->belongsTo(District::class, 'primary_district_id');
    }

    public function primaryFacility()
    {
        return $this->belongsTo(Facility::class, 'primary_facility_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWithRoleInOrganization($query, $role, $organizationId)
    {
        return $query->whereHas('roles', function ($q) use ($role, $organizationId) {
            $q->where('name', $role)
              ->where('organization_id', $organizationId);
        });
    }
}