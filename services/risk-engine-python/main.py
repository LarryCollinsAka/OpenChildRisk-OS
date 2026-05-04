"""
OpenChildRisk OS — Risk Engine
===============================
FastAPI service responsible for calculating
child-specific climate risk scores.

This service is called by the Laravel application
and returns risk assessments used to generate alerts.

Author: OpenChildRisk OS Team
Version: 1.0.0
UNICEF Alignment: CCRI 2021, SOWC 2025
"""

from fastapi import FastAPI
from pydantic import BaseModel
from engines.cholera import CholeraRiskEngine


# ─── APPLICATION SETUP ─────────────────────────────────────────────────────────
# Initialize FastAPI with metadata for auto-generated API docs
# Docs available at: http://localhost:8001/docs

app = FastAPI(
    title="OpenChildRisk — Risk Engine",
    description=(
        "Child-specific climate risk scoring service. "
        "Converts climate signals into actionable "
        "child protection intelligence."
    ),
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
)


# ─── REQUEST MODEL ─────────────────────────────────────────────────────────────
# Defines the expected input payload for risk evaluation.
# All fields map to real data sources we will connect later:
#   - rainfall_mm       → CHIRPS / NASA POWER API
#   - temperature       → NASA POWER API
#   - sanitation_coverage → WHO JMP / UNICEF MICS
#   - under5_population → WorldPop

class RiskRequest(BaseModel):
    """
    Input payload for risk evaluation endpoint.
    Represents climate and vulnerability data for one district.
    """

    # UUID of the district being evaluated
    # Must exist in the districts table
    district_id: str

    # Total rainfall in millimeters over past 7 days
    # Source: CHIRPS / NASA POWER (future)
    # Mock value acceptable for MVP
    rainfall_mm: float

    # Maximum daily temperature in Celsius
    # Source: NASA POWER API (future)
    # Threshold: >35°C dangerous for under-5
    temperature: float

    # Proportion of population with safe sanitation (0.0 to 1.0)
    # Source: WHO JMP / UNICEF MICS 2019
    # Example: 0.11 = 11% coverage (Far North Cameroon)
    sanitation_coverage: float

    # Number of children under 5 years in the district
    # Source: WorldPop 2020 / UNICEF MICS
    # Used to calculate children_at_risk estimate
    under5_population: int


# ─── RESPONSE MODEL ────────────────────────────────────────────────────────────
# Defines the exact structure returned after risk evaluation.
# This response is consumed by Laravel to create alerts in the database.

class RiskResponse(BaseModel):
    """
    Output payload from risk evaluation.
    Contains risk score, level, explanation, and recommended action.
    """

    # Echo back the district ID for traceability
    district_id: str

    # Type of risk calculated
    # Example: "cholera", "heat", "flood"
    risk_type: str

    # Composite risk score from 0.0 to 10.0
    # 0-3: LOW | 4-6: MEDIUM | 7-10: HIGH
    score: float

    # Human-readable risk level
    # Values: LOW | MEDIUM | HIGH
    risk_level: str

    # Plain-language explanation of what drove the score
    # Example: "heavy rainfall detected + low sanitation coverage"
    reason: str

    # Number of days the risk window applies
    # Default: 5 days for cholera
    time_window_days: int

    # Estimated number of under-5 children at risk
    # Calculated as: under5_population × exposure_fraction
    children_at_risk: int

    # Specific action recommended for CHWs or health officers
    # Example: "Pre-position ORS. Alert CHWs immediately."
    action: str

    # Complete human-readable alert message
    # This is what gets sent via SMS / WhatsApp
    message: str


# ─── ENDPOINTS ─────────────────────────────────────────────────────────────────

@app.get(
    "/health",
    summary="Health check",
    description="Confirms the risk engine service is running.",
    tags=["System"],
)
def health():
    """
    Health check endpoint.

    Used by Docker, Laravel, and monitoring tools
    to confirm this service is alive and responsive.

    Returns:
        dict: Simple status confirmation
    """
    return {"status": "ok", "service": "risk-engine", "version": "1.0.0"}


@app.post(
    "/risk/evaluate",
    response_model=RiskResponse,
    summary="Evaluate child climate risk",
    description=(
        "Accepts climate and vulnerability data for a district "
        "and returns a child-specific risk score with recommended action. "
        "Currently supports cholera risk calculation based on "
        "UNICEF WASH vulnerability methodology."
    ),
    tags=["Risk Engine"],
)
def evaluate_risk(request: RiskRequest):
    """
    Core risk evaluation endpoint.

    Accepts climate signals and district vulnerability data,
    runs them through the appropriate risk engine,
    and returns a structured risk assessment.

    This endpoint is called by the Laravel alerts/generate
    endpoint to produce risk scores before creating alerts.

    Args:
        request (RiskRequest): Climate and vulnerability input data

    Returns:
        RiskResponse: Risk score, level, reason, action, and message
    """

    # Initialize the cholera risk engine
    # Future: engine selection will be dynamic based on
    # hazard type, season, and geography
    engine = CholeraRiskEngine()

    # Run the risk calculation and return the result
    result = engine.score(request)

    return result