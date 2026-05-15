<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConflictProviderSource;

class ConflictProviderSourcesSeeder extends Seeder
{
    /**
     * Seed conflict provider sources
     * 
     * Based on CONFLICT_ONTOLOGY.md v1.0
     */
    public function run(): void
    {
        $providers = [
            [
                'code' => 'ACLED',
                'name' => 'Armed Conflict Location & Event Data Project',
                'description' => 'Authoritative conflict event data with high spatial precision. Best for operational humanitarian action.',
                'provider_type' => 'premium',
                'reliability_score' => 0.95,
                'update_frequency' => 'weekly',
                'license_type' => 'Academic/Non-commercial (Institutional access required for commercial)',
                'historical_depth_years' => 30,
                'api_enabled' => true,
                'api_base_url' => 'https://api.acleddata.com',
                'api_auth_type' => 'oauth',
                'requires_institutional_access' => true,
                'geographic_coverage' => ['Africa', 'Middle East', 'Asia', 'Latin America', 'Europe'],
                'event_types_covered' => [
                    'Battles',
                    'Explosions/Remote violence',
                    'Violence against civilians',
                    'Protests',
                    'Riots',
                    'Strategic developments'
                ],
                'is_active' => true,
                'notes' => 'Premium provider. Requires institutional email for API access. Highest confidence for operational decisions.',
            ],
            [
                'code' => 'ICEWS',
                'name' => 'Integrated Crisis Early Warning System',
                'description' => 'Predictive conflict intelligence from media sources. Best for trend analysis and escalation detection.',
                'provider_type' => 'government',
                'reliability_score' => 0.75,
                'update_frequency' => 'daily',
                'license_type' => 'Public domain (US Government)',
                'historical_depth_years' => 40,
                'api_enabled' => true,
                'api_base_url' => 'https://dataverse.harvard.edu/api/access/datafile',
                'api_auth_type' => 'none',
                'requires_institutional_access' => false,
                'geographic_coverage' => ['Global'],
                'event_types_covered' => [
                    'Assault',
                    'Fight',
                    'Protest',
                    'Threaten',
                    'Coerce',
                    'Engage in material cooperation'
                ],
                'is_active' => true,
                'notes' => 'Open access. Media-derived. Best for historical patterns and ML training. Not for immediate operational action.',
            ],
            [
                'code' => 'GDELT',
                'name' => 'Global Database of Events, Language, and Tone',
                'description' => 'Realtime media monitoring. Best for weak signal detection and media amplification tracking.',
                'provider_type' => 'open',
                'reliability_score' => 0.60,
                'update_frequency' => 'realtime',
                'license_type' => 'Open access',
                'historical_depth_years' => 10,
                'api_enabled' => true,
                'api_base_url' => 'https://api.gdeltproject.org',
                'api_auth_type' => 'none',
                'requires_institutional_access' => false,
                'geographic_coverage' => ['Global'],
                'event_types_covered' => [
                    'Protest',
                    'Assault',
                    'Fight',
                    'Appeal',
                    'Demand',
                    'Threaten'
                ],
                'is_active' => true,
                'notes' => 'Open realtime access. High volume, lower precision. Best for media intensity and weak signals.',
            ],
        ];

        foreach ($providers as $provider) {
            ConflictProviderSource::updateOrCreate(
                ['code' => $provider['code']],
                $provider
            );
        }

        $this->command->info('✅ Seeded ' . count($providers) . ' conflict provider sources');
    }
}