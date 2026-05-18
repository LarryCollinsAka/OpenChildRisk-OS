"""
OpenChildRisk OS - Risk Engine
===============================
FastAPI service responsible for calculating
child-specific climate risk scores.

This service is called by the Laravel application
and returns risk assessments used to generate alerts.

Versioning Strategy:
    URL-based versioning (/api/v1/, /api/v2/)
    Current stable version: v1
    All endpoints prefixed with /api/v1/

Author: OpenChildRisk OS Team
Version: 1.0.0
UNICEF Alignment: CCRI 2021, SOWC 2025
"""

from fastapi import FastAPI
from fastapi.routing import APIRouter
from pydantic import BaseModel
from engines.cholera import CholeraRiskEngine
import os

app = FastAPI(
    title="OpenChildRisk - Risk Engine",
    description=(
        "Child-specific climate risk scoring service. "
        "Converts climate signals into actionable "
        "child protection intelligence.\n\n"
        "**Current Version:** v1\n"
        "**Base URL:** /api/v1/"
    ),
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    root_path=os.getenv("ROOT_PATH", ""),
    root_path_from_headers=True,
)


# --- API ROUTER VERSION 1 ----------------------------------------------------
# All endpoints are prefixed with /api/v1/
# When v2 is needed, create a new router and mount at /api/v2/
# v1 remains active for backward compatibility

v1 = APIRouter(prefix="/api/v1")


# --- REQUEST MODEL -----------------------------------------------------------
# Defines the expected input payload for risk evaluation.
# All fields map to real data sources we will connect later:
#   - rainfall_mm         -> CHIRPS / NASA POWER API
#   - temperature         -> NASA POWER API
#   - sanitation_coverage -> WHO JMP / UNICEF MICS
#   - under5_population   -> WorldPop

class RiskRequest(BaseModel):
    """
    Input payload for risk evaluation endpoint.
    Represents climate and vulnerability data for one district.
    Version: v1
    """

    # UUID of the district being evaluated
    # Must exist in the districts table
    district_id: str

    # Total rainfall in millimeters over past 7 days
    # Source: CHIRPS / NASA POWER (future live connection)
    # Mock value acceptable for MVP testing
    rainfall_mm: float

    # Maximum daily temperature in Celsius
    # Source: NASA POWER API (future live connection)
    # Threshold: >35C dangerous for under-5 children
    temperature: float

    # Proportion of population with safe sanitation (0.0 to 1.0)
    # Source: WHO JMP / UNICEF MICS 2019
    # Example: 0.11 = 11% coverage (Far North Cameroon baseline)
    sanitation_coverage: float

    # Number of children under 5 years in the district
    # Source: WorldPop 2020 / UNICEF MICS
    # Used to calculate children_at_risk estimate
    under5_population: int


# --- RESPONSE MODEL ----------------------------------------------------------
# Defines the exact structure returned after risk evaluation.
# This response is consumed by Laravel to create alerts in the database.

class RiskResponse(BaseModel):
    """
    Output payload from risk evaluation.
    Contains risk score, level, explanation, and recommended action.
    Version: v1
    """

    # API version - helps clients detect version mismatches
    api_version: str = "v1"

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
    # Calculated as: under5_population x exposure_fraction
    children_at_risk: int

    # Specific action recommended for CHWs or health officers
    # Example: "Pre-position ORS. Alert CHWs immediately."
    action: str

    # Complete human-readable alert message
    # This is what gets sent via SMS / WhatsApp to field workers
    message: str


# --- HEALTH CHECK RESPONSE MODEL ---------------------------------------------

class HealthResponse(BaseModel):
    """
    Health check response payload.
    Version: v1
    """
    status: str
    service: str
    version: str
    api_version: str


# --- V1 ENDPOINTS ------------------------------------------------------------

@v1.get(
    "/health",
    response_model=HealthResponse,
    summary="Health check",
    description="Confirms the risk engine service is running and responsive.",
    tags=["System"],
)
def health():
    """
    Health check endpoint.

    Used by Docker, Laravel, and monitoring tools
    to confirm this service is alive and responsive.

    Returns:
        HealthResponse: Service status and version information
    """
    return {
        "status":      "ok",
        "service":     "risk-engine",
        "version":     "1.0.0",
        "api_version": "v1",
    }


@v1.post(
    "/risk/evaluate",
    response_model=RiskResponse,
    summary="Evaluate child climate risk",
    description=(
        "Accepts climate and vulnerability data for a district "
        "and returns a child-specific risk score with recommended action.\n\n"
        "**Currently supports:** Cholera risk (WASH vulnerability methodology)\n\n"
        "**Coming in v1.1:** Heat stress, flood, drought, malnutrition\n\n"
        "**UNICEF Alignment:** CCRI 2023 water vulnerability framework"
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
    # Future versions will select engine dynamically based on
    # hazard type, season, geography, and available data
    engine = CholeraRiskEngine()

    # Run the risk calculation
    result = engine.score(request)

    # Inject API version into response for client traceability
    result["api_version"] = "v1"

    return result


# --- MOUNT VERSIONED ROUTER --------------------------------------------------
# Mount v1 router on the main app
# Future: mount v2 router alongside v1 for backward compatibility

app.include_router(v1)


# --- ROOT ENDPOINT -----------------------------------------------------------
# Returns API discovery information

@app.get(
    "/",
    summary="API Discovery",
    tags=["System"],
    include_in_schema=False,
)
def root():
    """
    Root endpoint.
    Returns available API versions and documentation links.
    """
    return {
        "service":     "OpenChildRisk Risk Engine",
        "description": "Child climate risk scoring API",
        "versions": {
            "v1": {
                "status":   "stable",
                "base_url": "/api/v1",
                "docs":     "/docs",
            }
        },
        "health": "/api/v1/health",
    }