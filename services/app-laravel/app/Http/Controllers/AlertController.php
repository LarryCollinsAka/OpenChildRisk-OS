<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\District;
use App\Models\RiskScore;
use App\Services\RiskEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    public function __construct(protected RiskEngineService $riskEngine) {}

    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'district_id'         => 'required|uuid|exists:districts,id',
            'rainfall_mm'         => 'required|numeric|min:0',
            'temperature'         => 'required|numeric',
            'sanitation_coverage' => 'required|numeric|between:0,1',
            'under5_population'   => 'required|integer|min:0',
        ]);

        $district = District::findOrFail($data['district_id']);

        // Resolve hazard_type_id from hazard_types table
        $hazardTypeId = DB::table('hazard_types')
            ->where('name', 'like', '%Cholera%')
            ->value('id');

        // Call Python risk engine
        $result = $this->riskEngine->evaluate($data);

        // Map risk_level → severity (varchar) and priority (int 1-5)
        $severity = match ($result['risk_level']) {
            'HIGH'   => 'HIGH',
            'MEDIUM' => 'MEDIUM',
            default  => 'LOW',
        };
        $priority = match ($result['risk_level']) {
            'HIGH'   => 3,
            'MEDIUM' => 2,
            default  => 1,
        };

        [$riskScore, $alert] = DB::transaction(function () use (
            $data, $district, $result, $hazardTypeId, $severity, $priority
        ) {
            $riskScore = RiskScore::create([
                'district_id'        => $district->id,
                'hazard_type_id'     => $hazardTypeId,
                'score'              => $result['score'],
                'risk_level'         => $result['risk_level'],
                'reason'             => $result['reason'],
                'children_at_risk'   => $result['children_at_risk'],
                'time_window_days'   => $result['time_window_days'],
                'input_data'         => $data,
                'risk_engine'        => 'cholera_v1',
                'calculation_source' => 'api',
                'calculated_at'      => now(),
                'metadata'           => $result,
            ]);

            $alert = Alert::create([
                'district_id'        => $district->id,
                'hazard_type_id'     => $hazardTypeId,
                'risk_score_id'      => $riskScore->id,
                'title'              => "{$result['risk_level']} cholera risk in {$district->name}",
                'message'            => $result['message'],
                'recommended_action' => $result['action'],
                'severity'           => $severity,
                'priority'           => $priority,
                'status'             => 'pending',
                'metadata'           => $result,
            ]);

            return [$riskScore, $alert];
        });

        return response()->json([
            'alert_id'          => $alert->id,
            'district'          => $district->name,
            'risk_level'        => $result['risk_level'],
            'score'             => $result['score'],
            'children_affected' => $result['children_at_risk'],
            'message'           => $result['message'],
            'priority_score'    => $result['score'],
            'access_level'      => 'unknown',
            'capacity_status'   => 'unknown',
        ], 201);
    }
}
