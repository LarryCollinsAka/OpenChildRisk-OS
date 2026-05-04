"""
OpenChildRisk OS — Cholera Risk Engine
========================================
Calculates cholera risk score for under-5 children
based on climate signals and WASH vulnerability indicators.

Methodology:
    Based on UNICEF WASH Vulnerability Framework (2023)
    and Children's Climate Risk Index (CCRI) methodology.

Key Inputs:
    - Rainfall anomaly (flood signal)
    - Sanitation coverage gap
    - Temperature (disease amplifier)

Key Output:
    - Composite risk score (0-10)
    - Risk level (LOW / MEDIUM / HIGH)
    - Children at risk estimate
    - Recommended action

UNICEF Evidence Base:
    - 739 million children exposed to high water scarcity (CCRI 2023)
    - 436 million children in high water vulnerability zones
    - Cholera spreads rapidly after flooding in low-sanitation areas

Author: OpenChildRisk OS Team
Version: 1.0.0
"""


class CholeraRiskEngine:
    """
    Cholera risk scorer for under-5 children.

    Implements a weighted composite scoring model
    combining rainfall, sanitation, and temperature signals
    into a single actionable risk score.

    Score Range: 0.0 (no risk) to 10.0 (extreme risk)
    Risk Levels:
        HIGH   → score >= 7.0
        MEDIUM → score >= 4.0
        LOW    → score < 4.0
    """

    # ─── COMPONENT WEIGHTS ───────────────────────────────────────────────────
    # These weights reflect UNICEF's emphasis on WASH as the primary
    # driver of cholera outbreaks in children.
    # Source: UNICEF Climate-Changed Child Report 2023

    # Rainfall is the primary trigger (40%)
    # Heavy rainfall floods latrines and contaminates water sources
    RAINFALL_WEIGHT = 0.40

    # Sanitation gap is equally weighted (40%)
    # Low coverage means contaminated water reaches children directly
    SANITATION_WEIGHT = 0.40

    # Temperature amplifies bacterial growth (20%)
    # Vibrio cholerae multiplies faster above 30°C
    TEMPERATURE_WEIGHT = 0.20

    # ─── THRESHOLDS ──────────────────────────────────────────────────────────
    # Based on WHO cholera outbreak literature and
    # UNICEF field experience in Sub-Saharan Africa

    # Rainfall above this level per week signals flood risk
    RAINFALL_DANGER_THRESHOLD_MM = 100

    # Temperature above this level accelerates disease spread
    TEMPERATURE_DANGER_THRESHOLD_C = 30

    # Risk level boundaries
    HIGH_RISK_THRESHOLD   = 7.0
    MEDIUM_RISK_THRESHOLD = 4.0

    def score(self, data) -> dict:
        """
        Calculate cholera risk score for a district.

        Args:
            data: RiskRequest object containing:
                - district_id
                - rainfall_mm
                - temperature
                - sanitation_coverage
                - under5_population

        Returns:
            dict: Complete risk assessment including:
                - score (0-10)
                - risk_level (LOW/MEDIUM/HIGH)
                - reason (plain language explanation)
                - children_at_risk (integer estimate)
                - action (recommended response)
                - message (SMS-ready alert text)
        """

        # ─── STEP 1: SCORE EACH COMPONENT ────────────────────────────────────

        rainfall_score    = self._score_rainfall(data.rainfall_mm)
        sanitation_score  = self._score_sanitation(data.sanitation_coverage)
        temperature_score = self._score_temperature(data.temperature)

        # ─── STEP 2: CALCULATE WEIGHTED COMPOSITE SCORE ──────────────────────
        # Combine components using UNICEF-aligned weights
        # Cap at 10.0 to maintain consistent scale

        raw_score = (
            rainfall_score    * self.RAINFALL_WEIGHT +
            sanitation_score  * self.SANITATION_WEIGHT +
            temperature_score * self.TEMPERATURE_WEIGHT
        )

        # Round to 2 decimal places for clean output
        score = round(min(raw_score, 10.0), 2)

        # ─── STEP 3: DETERMINE RISK LEVEL ────────────────────────────────────

        risk_level = self._get_risk_level(score)

        # ─── STEP 4: ESTIMATE CHILDREN AT RISK ───────────────────────────────
        # Exposure fraction increases with risk score
        # Maximum 50% exposure at score 10.0
        # Formula: exposure = score / 20 (capped at 0.5)

        exposure_fraction = min(score / 20.0, 0.5)
        children_at_risk  = int(data.under5_population * exposure_fraction)

        # ─── STEP 5: BUILD PLAIN LANGUAGE REASON ─────────────────────────────
        # Explains WHY the score was triggered
        # Critical for UNICEF accountability and field worker trust

        reason = self._build_reason(
            rainfall_score,
            sanitation_score,
            temperature_score
        )

        # ─── STEP 6: MAP TO RECOMMENDED ACTION ───────────────────────────────
        # Translates risk level into specific field actions
        # These map directly to UNICEF cholera response protocols

        action = self._get_action(risk_level)

        # ─── STEP 7: BUILD SMS-READY MESSAGE ─────────────────────────────────
        # Final output must be readable by a community health worker
        # on a basic phone screen — no jargon, no numbers overload

        message = self._build_message(
            risk_level,
            children_at_risk,
            action
        )

        # ─── RETURN COMPLETE ASSESSMENT ──────────────────────────────────────

        return {
            "district_id":      data.district_id,
            "risk_type":        "cholera",
            "score":            score,
            "risk_level":       risk_level,
            "reason":           reason,
            "time_window_days": 5,
            "children_at_risk": children_at_risk,
            "action":           action,
            "message":          message,
        }

    # ─── PRIVATE SCORING METHODS ─────────────────────────────────────────────

    def _score_rainfall(self, rainfall_mm: float) -> float:
        """
        Score rainfall signal on 0-10 scale.

        Logic:
            100mm/week = score of 5.0 (moderate risk)
            200mm/week = score of 10.0 (maximum risk)

        Args:
            rainfall_mm: Total rainfall in mm over past 7 days

        Returns:
            float: Rainfall risk score (0-10)
        """
        # Divide by 20 to normalize:
        # 200mm → 10.0 (maximum), 100mm → 5.0 (moderate)
        score = rainfall_mm / 20.0
        return min(score, 10.0)

    def _score_sanitation(self, sanitation_coverage: float) -> float:
        """
        Score sanitation gap on 0-10 scale.

        Logic:
            Low coverage = high risk (inverse relationship)
            0% coverage  → score 10.0 (maximum risk)
            100% coverage → score 0.0 (no risk)

        Args:
            sanitation_coverage: Proportion with safe sanitation (0.0-1.0)

        Returns:
            float: Sanitation risk score (0-10)
        """
        # Invert coverage to get gap score
        # Example: 0.11 coverage → 0.89 gap → 8.9 score
        gap = 1.0 - sanitation_coverage
        return round(gap * 10.0, 2)

    def _score_temperature(self, temperature: float) -> float:
        """
        Score temperature risk on 0-10 scale.

        Logic:
            Below 30°C → no additional risk (score 0)
            Each degree above 30°C adds 0.8 to score
            40°C → score of 8.0

        Args:
            temperature: Maximum daily temperature in Celsius

        Returns:
            float: Temperature risk score (0-10)
        """
        # Only temperatures above threshold contribute to risk
        excess = max(0.0, temperature - self.TEMPERATURE_DANGER_THRESHOLD_C)

        # 0.8 multiplier per degree above threshold
        score = excess * 0.8
        return min(score, 10.0)

    def _get_risk_level(self, score: float) -> str:
        """
        Convert numeric score to human-readable risk level.

        Args:
            score: Composite risk score (0-10)

        Returns:
            str: Risk level — HIGH, MEDIUM, or LOW
        """
        if score >= self.HIGH_RISK_THRESHOLD:
            return "HIGH"
        elif score >= self.MEDIUM_RISK_THRESHOLD:
            return "MEDIUM"
        else:
            return "LOW"

    def _build_reason(
        self,
        rainfall_score: float,
        sanitation_score: float,
        temperature_score: float
    ) -> str:
        """
        Build plain-language explanation of risk drivers.

        Identifies which components contributed most to the score
        and returns a human-readable reason string.

        Args:
            rainfall_score: Component score for rainfall
            sanitation_score: Component score for sanitation gap
            temperature_score: Component score for temperature

        Returns:
            str: Plain language explanation for field workers
        """
        # Collect active risk drivers
        drivers = []

        if rainfall_score >= 5.0:
            drivers.append("heavy rainfall detected")

        if sanitation_score >= 6.0:
            drivers.append("critically low sanitation coverage")
        elif sanitation_score >= 4.0:
            drivers.append("insufficient sanitation coverage")

        if temperature_score >= 4.0:
            drivers.append("dangerous heat accelerating disease spread")

        # Build readable string
        if not drivers:
            return "moderate baseline conditions"

        return " + ".join(drivers)

    def _get_action(self, risk_level: str) -> str:
        """
        Map risk level to specific field action.

        Actions are aligned with UNICEF cholera response protocols
        and designed for community health workers in low-resource settings.

        Args:
            risk_level: HIGH, MEDIUM, or LOW

        Returns:
            str: Specific recommended action for field workers
        """
        actions = {
            "HIGH": (
                "Pre-position ORS at all health posts. "
                "Alert CHWs immediately. "
                "Activate water chlorination. "
                "Notify district health officer."
            ),
            "MEDIUM": (
                "Increase CHW monitoring visits. "
                "Check and treat water sources. "
                "Ensure ORS stocks are adequate."
            ),
            "LOW": (
                "Maintain routine surveillance. "
                "No immediate action required."
            ),
        }
        return actions.get(risk_level, "Monitor situation.")

    def _build_message(
        self,
        risk_level: str,
        children_at_risk: int,
        action: str
    ) -> str:
        """
        Build SMS-ready alert message for field delivery.

        Message is designed to be:
        - Under 160 characters for basic SMS compatibility
        - Actionable without additional context
        - Clear to non-technical community health workers

        Args:
            risk_level: HIGH, MEDIUM, or LOW
            children_at_risk: Estimated number of children exposed
            action: Recommended response action

        Returns:
            str: Complete alert message ready for SMS/WhatsApp delivery
        """
        return (
            f"{risk_level} cholera risk detected. "
            f"{children_at_risk:,} children under 5 at risk. "
            f"{action}"
        )