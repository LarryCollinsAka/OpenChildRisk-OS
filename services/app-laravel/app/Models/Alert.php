<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Alert Model
 *
 * Represents a child protection alert generated from
 * a risk score exceeding defined thresholds.
 *
 * Full lifecycle tracked:
 *   pending → sent → acknowledged → resolved
 *
 * Linked to:
 *   - district (where)
 *   - risk_score (why)
 *   - organization (who owns it)
 *   - program_deployment (which program)
 *
 * @property string $id
 * @property string $district_id
 * @property string $risk_score_id
 * @property string $type
 * @property string $risk_level
 * @property string $message
 * @property int    $children_affected
 * @property float  $priority_score
 * @property string $status
 * @property string $access_level
 * @property string $capacity_status
 */
class Alert extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'alerts';

    protected $fillable = [
        'district_id',
        'risk_score_id',
        'organization_id',
        'program_deployment_id',
        'assigned_to_org_id',
        'type',
        'risk_level',
        'message',
        'children_affected',
        'priority_score',
        'status',
        'access_level',
        'capacity_status',
        'triggered_at',
        'resolved_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'children_affected' => 'integer',
        'priority_score'    => 'float',
        'triggered_at'      => 'datetime',
        'resolved_at'       => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Alert belongs to a district.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Alert belongs to a risk score.
     * This is the traceability link —
     * every alert must be explainable by its risk score.
     */
    public function riskScore()
    {
        return $this->belongsTo(RiskScore::class, 'risk_score_id');
    }

    /**
     * Alert belongs to an organization.
     * The organization responsible for responding.
     */
    public function organization()
    {
        return $this->belongsTo(
            Organization::class,
            'organization_id'
        );
    }

    /**
     * Alert has many actions.
     * Tracks WHO did WHAT and WHEN in response.
     */
    public function actions()
    {
        return $this->hasMany(AlertAction::class, 'alert_id');
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /**
     * Scope: active alerts only (pending or sent).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'sent']);
    }

    /**
     * Scope: HIGH risk alerts only.
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'HIGH');
    }

    /**
     * Scope: filter by district.
     */
    public function scopeForDistrict($query, string $districtId)
    {
        return $query->where('district_id', $districtId);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Mark alert as sent and log the action.
     *
     * @return bool
     */
    public function markAsSent(): bool
    {
        $updated = $this->update(['status' => 'sent']);

        Log::info('Alert marked as sent', [
            'alert_id'   => $this->id,
            'district'   => $this->district_id,
            'risk_level' => $this->risk_level,
        ]);

        return $updated;
    }

    /**
     * Mark alert as resolved with timestamp.
     *
     * @return bool
     */
    public function markAsResolved(): bool
    {
        return $this->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Calculate response time in minutes.
     * Returns null if not yet resolved.
     *
     * @return int|null
     */
    public function responseTimeMinutes(): ?int
    {
        if (!$this->resolved_at) return null;

        return (int) $this->triggered_at
            ->diffInMinutes($this->resolved_at);
    }
}