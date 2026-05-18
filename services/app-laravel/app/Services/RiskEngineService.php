<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class RiskEngineService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.risk_engine.url'), '/');
    }

    /**
     * Send climate inputs to the Python risk engine and return the assessment.
     *
     * @throws RequestException
     */
    public function evaluate(array $payload): array
    {
        return Http::timeout(10)
            ->post("{$this->baseUrl}/api/v1/risk/evaluate", $payload)
            ->throw()
            ->json();
    }
}
