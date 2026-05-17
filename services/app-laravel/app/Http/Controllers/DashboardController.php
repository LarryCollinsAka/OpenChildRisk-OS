<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\District;
use App\Models\Indicator;
use App\Models\PopulationGroup;
use App\Models\DataSource;
use App\Models\RiskScore;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Latest risk score per district (one query, no N+1)
        $latestScores = RiskScore::select('district_id', 'score', 'risk_level', 'reason')
            ->whereIn('id', function ($sub) {
                $sub->select('id')
                    ->from('risk_scores as rs2')
                    ->whereNull('deleted_at')
                    ->whereRaw('rs2.calculated_at = (
                        SELECT MAX(rs3.calculated_at)
                        FROM risk_scores rs3
                        WHERE rs3.district_id = rs2.district_id
                        AND rs3.deleted_at IS NULL
                    )');
            })
            ->get()
            ->keyBy('district_id');

        $districts = District::where('active', true)
            ->select('id', 'name', 'code', 'centroid_lat', 'centroid_lng', 'population')
            ->get()
            ->map(function ($district) use ($latestScores) {
                $rs    = $latestScores->get($district->id);
                $score = $rs ? (float) $rs->score : null;
                $level = $rs ? $rs->risk_level : 'Unknown';

                $status = match (true) {
                    $score >= 8  => 'Critical',
                    $score >= 6  => 'High',
                    $score >= 4  => 'Medium',
                    $score !== null => 'Low',
                    default      => 'No data',
                };

                return [
                    'name'       => $district->name,
                    'lat'        => (float) $district->centroid_lat,
                    'lng'        => (float) $district->centroid_lng,
                    'risk'       => $score,
                    'risk_level' => $level,
                    'status'     => $status,
                    'population' => $district->population
                        ? number_format($district->population / 1000, 1) . 'K'
                        : 'Unknown',
                    'factors'    => $rs?->reason ?? 'No assessment yet',
                ];
            });

        $stats = [
            'total_districts'        => District::where('active', true)->count(),
            'total_indicators'       => Indicator::where('active', true)->count(),
            'total_population_groups'=> PopulationGroup::where('active', true)->count(),
            'total_data_sources'     => DataSource::where('active', true)->count(),
            'total_alerts'           => Alert::where('status', 'pending')->count(),
        ];

        return Inertia::render('Dashboard', [
            'mapDistricts' => $districts,
            'stats'        => $stats,
        ]);
    }
}
