<?php

namespace App\Services\Conflict;

use App\Models\ConflictEvent;
use App\Models\ConflictCategory;
use App\Models\ConflictProviderSource;
use App\Models\Country;
use App\Models\District;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Conflict Event Normalizer
 * 
 * Provider-agnostic event normalization service.
 * Transforms provider-specific data into canonical format.
 * 
 * Philosophy: Single normalized schema, explainable transformations.
 */
class ConflictEventNormalizer
{
    /**
     * Canonical category mapping
     * Based on CONFLICT_ONTOLOGY.md v1.0
     */
    const CATEGORY_MAPPING = [
        // ACLED mappings
        'ACLED' => [
            'Battles' => 'ARMED_CONFLICT',
            'Explosions/Remote violence' => 'EXPLOSIVE_VIOLENCE',
            'Violence against civilians' => 'CIVILIAN_TARGETING',
            'Protests' => 'CIVIL_UNREST',
            'Riots' => 'CIVIL_UNREST',
            'Strategic developments' => 'STRATEGIC_DEVELOPMENT',
        ],
        // ICEWS mappings (future)
        'ICEWS' => [
            'Assault' => 'ARMED_CONFLICT',
            'Fight' => 'ARMED_CONFLICT',
            'Protest' => 'CIVIL_UNREST',
            'Threaten' => 'STRATEGIC_DEVELOPMENT',
            'Coerce' => 'CIVILIAN_TARGETING',
        ],
        // GDELT mappings (future)
        'GDELT' => [
            '18*' => 'ARMED_CONFLICT',  // Assault
            '19*' => 'ARMED_CONFLICT',  // Fight
            '14*' => 'CIVIL_UNREST',    // Protest
        ],
    ];

    /**
     * Base severity scores by category
     * From CONFLICT_ONTOLOGY.md
     */
    const BASE_SEVERITY = [
        'CIVILIAN_TARGETING' => 9.0,
        'EXPLOSIVE_VIOLENCE' => 8.0,
        'ARMED_CONFLICT' => 7.0,
        'DISPLACEMENT_EVENT' => 6.0,
        'CIVIL_UNREST' => 4.0,
        'STRATEGIC_DEVELOPMENT' => 2.0,
    ];

    /**
     * Signal type by provider
     */
    const SIGNAL_TYPES = [
        'ACLED' => 'operational',
        'ICEWS' => 'predictive',
        'GDELT' => 'weak_signal',
    ];

    protected ConflictProviderSource $providerSource;

    public function __construct(string $providerCode)
    {
        $this->providerSource = ConflictProviderSource::where('code', $providerCode)->firstOrFail();
    }

    /**
     * Normalize event from provider-specific format
     */
    public function normalize(array $rawEvent, Country $country): array
    {
        $provider = $this->providerSource->code;

        // Extract common fields
        $normalized = [
            'source_provider' => $provider,
            'provider_raw_data' => $rawEvent,
            'country_id' => $country->id,
            'status' => 'active',
        ];

        // Provider-specific extraction
        switch ($provider) {
            case 'ACLED':
                $normalized = array_merge($normalized, $this->normalizeAcled($rawEvent));
                break;
            case 'ICEWS':
                $normalized = array_merge($normalized, $this->normalizeIcews($rawEvent));
                break;
            case 'GDELT':
                $normalized = array_merge($normalized, $this->normalizeGdelt($rawEvent));
                break;
            default:
                throw new \Exception("Unknown provider: {$provider}");
        }

        // Add confidence scores
        $normalized['source_confidence'] = $this->providerSource->getCompositeConfidence();

        // Add signal type
        $normalized['metadata'] = array_merge(
            $normalized['metadata'] ?? [],
            ['signal_type' => self::SIGNAL_TYPES[$provider] ?? 'contextual']
        );

        // Generate canonical hash for deduplication
        $normalized['canonical_event_hash'] = ConflictEvent::generateCanonicalHash(
            $normalized['event_date'],
            $normalized['latitude'],
            $normalized['longitude'],
            $normalized['canonical_category'],
            $normalized['fatalities'] ?? 0
        );

        // Set deduplication parameters
        $normalized['temporal_window_hours'] = 48;
        $normalized['spatial_radius_km'] = 50.0;

        return $normalized;
    }

    /**
     * Normalize ACLED event
     */
    protected function normalizeAcled(array $event): array
    {
        // Map to canonical category
        $eventType = $event['event_type'] ?? '';
        $canonicalCategory = self::CATEGORY_MAPPING['ACLED'][$eventType] ?? null;

        if (!$canonicalCategory) {
            throw new \Exception("Unknown ACLED event type: {$eventType}");
        }

        $category = ConflictCategory::where('code', $canonicalCategory)->first();
        if (!$category) {
            throw new \Exception("Canonical category not found: {$canonicalCategory}");
        }

        // Find district by coordinates
        $district = $this->findDistrictByCoordinates(
            (float) $event['latitude'],
            (float) $event['longitude']
        );

        // Calculate severity
        $severity = $this->calculateSeverity(
            $canonicalCategory,
            (int) ($event['fatalities'] ?? 0),
            null // No displacement data in ACLED
        );

        return [
            'external_id' => $event['event_id_cnty'],
            'conflict_category_id' => $category->id,
            'canonical_category' => $canonicalCategory,
            'district_id' => $district?->id,
            'event_date' => Carbon::parse($event['event_date']),
            'sub_event_type' => $event['sub_event_type'] ?? null,
            'notes' => $event['notes'] ?? null,
            'actors' => [
                'actor1' => $event['actor1'] ?? null,
                'actor2' => $event['actor2'] ?? null,
                'inter1' => $event['inter1'] ?? null,
                'inter2' => $event['inter2'] ?? null,
            ],
            'fatalities' => (int) ($event['fatalities'] ?? 0),
            'latitude' => (float) $event['latitude'],
            'longitude' => (float) $event['longitude'],
            'location_name' => $event['location'] ?? null,
            'severity_score' => $severity,
            'metadata' => [
                'admin1' => $event['admin1'] ?? null,
                'admin2' => $event['admin2'] ?? null,
                'admin3' => $event['admin3'] ?? null,
                'source' => $event['source'] ?? null,
                'source_scale' => $event['source_scale'] ?? null,
            ],
        ];
    }

    /**
     * Normalize ICEWS event (future)
     */
    protected function normalizeIcews(array $event): array
    {
        // TODO: Implement ICEWS normalization
        throw new \Exception("ICEWS normalization not yet implemented");
    }

    /**
     * Normalize GDELT event (future)
     */
    protected function normalizeGdelt(array $event): array
    {
        // TODO: Implement GDELT normalization
        throw new \Exception("GDELT normalization not yet implemented");
    }

    /**
     * Calculate severity score
     */
    protected function calculateSeverity(
        string $canonicalCategory,
        int $fatalities,
        ?int $displaced
    ): float {
        $baseSeverity = self::BASE_SEVERITY[$canonicalCategory] ?? 5.0;

        // Fatality modifier (max +5 points)
        $fatalityScore = min($fatalities / 2, 5.0);

        // Displacement modifier (max +3 points)
        $displacementScore = $displaced ? min($displaced / 1000, 3.0) : 0;

        $severity = $baseSeverity + $fatalityScore + $displacementScore;

        return min(round($severity, 2), 10.0);
    }

    /**
     * Find district by coordinates (Haversine)
     */
    protected function findDistrictByCoordinates(float $lat, float $lng): ?District
    {
        return District::selectRaw('
                *,
                (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(centroid_lat)) *
                        cos(radians(centroid_lng) - radians(?)) +
                        sin(radians(?)) * sin(radians(centroid_lat))
                    )
                ) AS distance
            ', [$lat, $lng, $lat])
            ->having('distance', '<', 50) // Within 50km
            ->orderBy('distance')
            ->first();
    }
}