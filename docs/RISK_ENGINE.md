# Risk Calculation Engine

## Compound Risk Formula
Composite Risk = f(Hazard, Vulnerability, Exposure, Capacity)
Where:
- Hazard: Climate/health threat intensity
- Vulnerability: Population sensitivity
- Exposure: How many affected
- Capacity: Response capability

---
## Architecture

┌──────────────────┐
│  Laravel API     │
└────────┬─────────┘
│
↓
┌──────────────────┐
│ Python FastAPI   │
│ Risk Engine      │
└────────┬─────────┘
│
↓
┌──────────────────┐
│  PostgreSQL      │
│  Indicator Data  │
└──────────────────┘

## API

[Document risk engine endpoints]

## Algorithms

[Explain calculation methods]