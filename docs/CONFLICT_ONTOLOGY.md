# OpenChildRisk Conflict Event Ontology v1.0

## Philosophy

Provider-agnostic humanitarian conflict intelligence.
Optimized for child safety risk assessment, not academic conflict studies.

## Design Principles

1. **Humanitarian-Centric**: Categories reflect child risk, not geopolitical taxonomy
2. **Provider-Agnostic**: All providers map to same canonical schema
3. **ML-Ready**: Features designed for temporal/spatial/severity modeling
4. **Explainable**: Every classification traceable to source reasoning

---

## Canonical Event Categories

### Primary Categories (Humanitarian Risk Focus)

| Code | Name | Child Risk Profile | Examples |
|------|------|-------------------|----------|
| `ARMED_CONFLICT` | Armed Conflict | Direct violence exposure, displacement, service disruption | Battles, combat, armed clashes |
| `EXPLOSIVE_VIOLENCE` | Explosive Violence | Civilian casualties, infrastructure damage, UXO contamination | Bombings, IEDs, airstrikes |
| `CIVILIAN_TARGETING` | Violence Against Civilians | Direct harm to non-combatants, terror, displacement | Massacres, kidnapping, assault |
| `CIVIL_UNREST` | Civil Unrest | Service disruption, indirect risk, instability | Protests, riots, demonstrations |
| `DISPLACEMENT_EVENT` | Forced Displacement | Population movement, family separation, exposure | Refugee flows, IDP camps |
| `STRATEGIC_DEVELOPMENT` | Strategic Developments | Changing threat landscape, future risk | Ceasefires, negotiations, territorial control |

### Signal Types (Confidence Classification)

| Type | Definition | Use Case | Typical Providers |
|------|------------|----------|-------------------|
| `OPERATIONAL` | High-confidence field reality | Immediate action, alerts | ACLED, UN reports |
| `PREDICTIVE` | Emerging escalation signals | Early warning, trend analysis | ICEWS |
| `WEAK_SIGNAL` | Media amplification, early indicators | Context monitoring | GDELT |
| `CONTEXTUAL` | Humanitarian situational reports | Background understanding | ReliefWeb |

---

## Provider Mapping

### ACLED → Canonical

| ACLED Event Type | Canonical Category | Notes |
|------------------|-------------------|-------|
| Battles | `ARMED_CONFLICT` | Direct violence |
| Explosions/Remote violence | `EXPLOSIVE_VIOLENCE` | IEDs, bombings |
| Violence against civilians | `CIVILIAN_TARGETING` | Direct harm |
| Protests | `CIVIL_UNREST` | Usually peaceful |
| Riots | `CIVIL_UNREST` | Violent unrest |
| Strategic developments | `STRATEGIC_DEVELOPMENT` | Context changes |

### ICEWS → Canonical (Future)

| ICEWS Event Type | Canonical Category | Notes |
|------------------|-------------------|-------|
| Assault | `ARMED_CONFLICT` | - |
| Fight | `ARMED_CONFLICT` | - |
| Protest | `CIVIL_UNREST` | - |
| Threaten | `STRATEGIC_DEVELOPMENT` | Escalation signal |

### GDELT → Canonical (Future)

| GDELT Event Code | Canonical Category | Notes |
|------------------|-------------------|-------|
| 18* (Assault) | `ARMED_CONFLICT` | - |
| 19* (Fight) | `ARMED_CONFLICT` | - |
| 14* (Protest) | `CIVIL_UNREST` | - |

---

## Multi-Dimensional Confidence

### Confidence Components

```json
{
  "source_reliability": 0.0-1.0,  // Provider base confidence
  "spatial_confidence": 0.0-1.0,   // Location precision
  "temporal_confidence": 0.0-1.0,  // Date certainty
  "classification_confidence": 0.0-1.0  // Category accuracy
}
```

### Provider Baseline Confidence

| Provider | Source Reliability | Spatial Typical | Temporal Typical | Classification Typical |
|----------|-------------------|-----------------|------------------|----------------------|
| ACLED | 0.95 | 0.90 | 0.95 | 0.90 |
| ICEWS | 0.75 | 0.70 | 0.80 | 0.70 |
| GDELT | 0.60 | 0.50 | 0.90 | 0.60 |

### Composite Confidence Calculation
composite_confidence = (
source_reliability * 0.40 +
spatial_confidence * 0.25 +
temporal_confidence * 0.20 +
classification_confidence * 0.15
)

---

## Severity Scoring

### Base Severity by Category

| Category | Base Score (0-10) | Rationale |
|----------|-------------------|-----------|
| `CIVILIAN_TARGETING` | 9.0 | Direct harm to non-combatants |
| `EXPLOSIVE_VIOLENCE` | 8.0 | High casualty potential, UXO |
| `ARMED_CONFLICT` | 7.0 | Direct violence zone |
| `DISPLACEMENT_EVENT` | 6.0 | Major humanitarian consequence |
| `CIVIL_UNREST` | 4.0 | Indirect service disruption |
| `STRATEGIC_DEVELOPMENT` | 2.0 | Future risk context |

### Severity Modifiers

```python
severity_score = base_severity + fatality_score + displacement_score

fatality_score = min(fatalities / 2, 5.0)
displacement_score = min(displaced / 1000, 3.0)

final_score = min(severity_score, 10.0)
```

---

## Event Deduplication

### Matching Criteria

**Temporal Window**: ±48 hours
**Spatial Radius**: 50km
**Category**: Must match
**Fatalities**: Within 20% or ±5 (whichever larger)

### Canonical Event Hash
hash = SHA256(
event_date.strftime('%Y-%m-%d') +
round(latitude, 2) +
round(longitude, 2) +
canonical_category +
round(fatalities, -1)  // Round to nearest 10
)

---

## Raw Payload Storage

**CRITICAL**: Always store original provider data

```json
{
  "provider": "ACLED",
  "provider_event_id": "CMR12345",
  "ingested_at": "2026-05-14T12:00:00Z",
  "raw_data": { ... }  // Complete original payload
}
```

**Purpose**: Reprocessing, ontology updates, ML feature extraction

---

## Version History

- v1.0 (2026-05-14): Initial ontology for ACLED/ICEWS/GDELT