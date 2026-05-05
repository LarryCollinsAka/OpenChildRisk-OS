<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * District Model
 *
 * Represents a geographic district in the OpenChildRisk system.
 * Each district has child vulnerability indicators and
 * links to risk scores, alerts and program deployments.
 *
 * Supports multilingual names via district_translations table.
 * Default language: English. Fallback: English always.
 *
 * @property string $id
 * @property string $country_iso
 * @property string $admin2_code
 * @property string $admin2_name
 * @property int    $children_under5
 * @property float  $sanitation_coverage
 * @property float  $wash_coverage
 * @property bool   $conflict_affected
 */
class District extends Model
{
    use HasFactory, HasUuids;

    /**
     * UUID primary key — no auto-increment.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Table name matches our PostgreSQL schema.
     */
    protected $table = 'districts';

    /**
     * Mass assignable attributes.
     * Explicitly listed for security.
     */
    protected $fillable = [
        'country_iso',
        'country_id',
        'admin1_name',
        'admin2_code',
        'admin2_name',
        'centroid_lat',
        'centroid_lon',
        'children_under5',
        'children_5_14',
        'under5_population',
        'wash_coverage',
        'sanitation_coverage',
        'health_facility_density',
        'social_protection_coverage',
        'conflict_affected',
    ];

    /**
     * Attribute casting.
     * Ensures correct PHP types when accessing model attributes.
     */
    protected $casts = [
        'centroid_lat'               => 'float',
        'centroid_lon'               => 'float',
        'children_under5'            => 'integer',
        'children_5_14'              => 'integer',
        'under5_population'          => 'integer',
        'wash_coverage'              => 'float',
        'sanitation_coverage'        => 'float',
        'health_facility_density'    => 'float',
        'social_protection_coverage' => 'float',
        'conflict_affected'          => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * District has many risk scores.
     * Each score represents one hazard assessment at one point in time.
     */
    public function riskScores()
    {
        return $this->hasMany(RiskScore::class, 'district_id');
    }

    /**
     * District has many alerts.
     * Alerts are generated from risk scores exceeding thresholds.
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class, 'district_id');
    }

    /**
     * District has many translations.
     * One translation record per language.
     */
    public function translations()
    {
        return $this->hasMany(
            DistrictTranslation::class,
            'district_id'
        );
    }

    /**
     * District belongs to a country.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    // =========================================================================
    // MULTILINGUAL SUPPORT
    // =========================================================================

    /**
     * Get translated district name with English fallback.
     *
     * Usage:
     *   $district->translate('fr') → "Maroua"
     *   $district->translate('es') → "Maroua" (fallback to EN)
     *
     * @param string $lang ISO 639-1 language code
     * @return string Translated name or English fallback
     */
    public function translate(string $lang = 'en'): string
    {
        // Try requested language first
        $translation = $this->translations()
            ->where('language_code', $lang)
            ->first();

        // Fall back to English if not found
        if (!$translation && $lang !== 'en') {
            $translation = $this->translations()
                ->where('language_code', 'en')
                ->first();
        }

        // Final fallback: use admin2_name from core table
        return $translation?->name ?? $this->admin2_name;
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /**
     * Scope: filter by country ISO code.
     * Example: District::forCountry('CMR')->get()
     */
    public function scopeForCountry($query, string $iso)
    {
        return $query->where('country_iso', $iso);
    }

    /**
     * Scope: filter conflict-affected districts only.
     * Example: District::conflictAffected()->get()
     */
    public function scopeConflictAffected($query)
    {
        return $query->where('conflict_affected', true);
    }

    /**
     * Scope: find district by admin2 code.
     * Example: District::byCode('FN-MOR')->first()
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('admin2_code', $code);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Get the most recent risk score for a specific hazard type.
     *
     * @param string $hazardType e.g. 'cholera', 'heat', 'flood'
     * @return RiskScore|null
     */
    public function latestRiskScore(string $hazardType = 'cholera')
    {
        return $this->riskScores()
            ->where('hazard_type', $hazardType)
            ->latest('scored_at')
            ->first();
    }

    /**
     * Check if district has HIGH or CRITICAL active alert.
     *
     * @return bool
     */
    public function hasActiveHighAlert(): bool
    {
        return $this->alerts()
            ->whereIn('risk_level', ['HIGH', 'CRITICAL'])
            ->whereIn('status', ['pending', 'sent'])
            ->exists();
    }
}