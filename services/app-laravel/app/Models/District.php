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
 */
class District extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'districts';

    protected $fillable = [
        'name',
        'code',
        'description',
        'country_id',
        'state_id',
        'centroid_lat',
        'centroid_lng',
        'population',
        'area_sq_km',
        'district_type',
    ];

    protected $casts = [
        'centroid_lat' => 'float',
        'centroid_lng' => 'float',
        'population' => 'integer',
        'area_sq_km' => 'decimal:2',
        'active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class, 'district_id');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'district_id');
    }

    public function translations()
    {
        return $this->hasMany(DistrictTranslation::class, 'district_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function hazardEvents()
    {
        return $this->hasMany(HazardEvent::class, 'district_id');
    }

    // =========================================================================
    // MULTILINGUAL SUPPORT
    // =========================================================================

    /**
     * Get translated district name with English fallback.
     *
     * @param string|null $lang ISO 639-1 language code
     * @return string Translated name or fallback
     */
    public function translate(?string $lang = null): string
    {
        $lang = $lang ?? app()->getLocale();

        // Get language by code
        $language = Language::where('code', $lang)->first();

        if (!$language) {
            return $this->name;
        }

        // Try requested language first
        $translation = $this->translations()
            ->where('language_id', $language->id)
            ->first();

        // Fall back to English if not found
        if (!$translation && $lang !== 'en') {
            $englishLang = Language::where('code', 'en')->first();
            if ($englishLang) {
                $translation = $this->translations()
                    ->where('language_id', $englishLang->id)
                    ->first();
            }
        }

        // Final fallback: use name from core table
        return $translation?->name ?? $this->name;
    }

    /**
     * Get translated name (magic attribute)
     */
    public function getTranslatedNameAttribute(): string
    {
        return $this->translate();
    }

    /**
     * Get translated description
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $lang = app()->getLocale();
        $language = Language::where('code', $lang)->first();

        if (!$language) {
            return $this->description;
        }

        $translation = $this->translations()
            ->where('language_id', $language->id)
            ->first();

        return $translation?->description ?? $this->description;
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    public function scopeForCountry($query, string $iso)
    {
        return $query->where('country_iso', $iso);
    }

    public function scopeConflictAffected($query)
    {
        return $query->where('conflict_affected', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function latestRiskScore(string $hazardType = 'cholera')
    {
        return $this->riskScores()
            ->where('hazard_type', $hazardType)
            ->latest('scored_at')
            ->first();
    }

    public function hasActiveHighAlert(): bool
    {
        return $this->alerts()
            ->whereIn('risk_level', ['HIGH', 'CRITICAL'])
            ->whereIn('status', ['pending', 'sent'])
            ->exists();
    }
}