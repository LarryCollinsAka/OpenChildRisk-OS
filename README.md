# 🌍 OpenChildRisk OS

> **Open-source child risk intelligence infrastructure
> for climate-vulnerable countries.**

## What OpenChildRisk OS Does

Transforms climate and health data into **child-specific operational intelligence** for humanitarian responders.


Built on UNICEF's Children's Climate Risk Index (CCRI) methodology
and aligned with the State of the World's Children policy framework.

[![License: Apache 2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](docker-compose.yml)
[![UNICEF Aligned](https://img.shields.io/badge/UNICEF-Aligned-00AEEF)](docs/UNICEF-ALIGNMENT.md)
[![Phase 2 In Progress](https://img.shields.io/badge/Status-Phase%202%20In%20Progress-yellow)]()
[![Database Tables: 44](https://img.shields.io/badge/Database%20Tables-44-green)]()
[![Geographic Coverage: 250 Countries](https://img.shields.io/badge/Countries-250-brightgreen)]()

**Current Version:** 0.2.0-alpha  
**Last Updated:** May 7, 2026  
**MVP Target:** Cameroon Far North Region

---

## 📋 Table of Contents

1. [The Problem](#-the-problem)
2. [Our Answer](#-our-answer)
3. [The Story Behind This](#-the-story-behind-this)
4. [How It Works](#-how-it-works)
5. [Architecture](#-architecture)
6. [MVP Flow](#-mvp-flow)
7. [Current Status](#-current-status)
8. [Quick Start](#-quick-start)
9. [Pilot: Cameroon Far North](#-pilot-cameroon-far-north)
10. [UNICEF Alignment](#-unicef-alignment)
11. [Technology Stack](#-technology-stack)
12. [Documentation](#-documentation)
13. [Contributing](#-contributing)
14. [License](#-license)

---

## Current State: PHASE 1 COMPLETE ✅

- ✅ 32 database tables (geography, organizations, hazards, users)
- ✅ 250 countries, 5,308 states, 154,223 cities
- ✅ Multi-organization architecture (Spatie permissions)
- ✅ Platform + organization user types
- ✅ PostgreSQL with PostGIS + pg_trgm

---

### The Problem

UNICEF field officers need to know:
- **Which children** are at risk (not just "people")
- **Why** they're vulnerable (compound factors)
- **Where** to intervene (priority districts)
- **Who** should respond (community health workers)

Generic climate alerts say: "Flood in Far North"
OpenChildRisk says: "Flood affecting 12,000 under-5 children + 3,200 children with disabilities in Mora. Cholera risk HIGH due to weak WASH. Alert sent to 15 CHWs."
```

The numbers behind this failure:

| Indicator | Scale |
|-----------|-------|
| Children at extremely high climate risk | **1 billion** |
| Children outside social assistance | **1.8 billion** |
| Climate finance reaching children | **only 2.4%** |
| Children losing schooling to climate shocks (2024) | **242 million** |
| Children in extreme monetary poverty | **412 million** |

> *Sources: UNICEF CCRI 2023, State of the World's Children 2025*

---

## 💡 Our Answer

**OpenChildRisk OS** is an open-source child risk intelligence
platform that converts climate signals into anticipatory
child protection actions — **before shocks become crises.**

It connects:

- 🌦️ **Climate data** (rainfall, temperature, flood, drought)
- 👶 **Child vulnerability** (under-5 density, malnutrition, poverty)
- 🏥 **Service layers** (clinics, schools, WASH coverage)
- 🏛️ **Accountability** (organizations, programs, deployments)

Into:

```

"HIGH cholera risk in Mora.
 38,000 children exposed.
 Pre-position ORS. Alert CHWs immediately."

```

**48–72 hours before the shock arrives.**

---

## 📖 The Story Behind This

In Cameroon's Far North region — where drought,
flood, conflict, and malnutrition converge —
children face compounding crises that existing
systems cannot see or respond to in time.

A child in Mora faces:
- Seasonal flooding that contaminates water sources
- Sanitation coverage of only 11%
- 38,000 children under 5 in the district
- Active conflict displacement from the Lake Chad basin
- No coordinated early warning system connecting these facts

**This is the gap OpenChildRisk OS was built to close.**

We are not building a dashboard.
We are not building a climate app.

We are building **decision infrastructure** —
the missing layer between climate intelligence
and child protection action.

---

# What Makes OpenChildRisk OS Different

## Not Another Dashboard

OpenChildRisk OS is not a monitoring dashboard.

It is **operational intelligence infrastructure** designed to:

- **Predict** climate-driven health risks before they escalate
- **Prioritize** which districts need intervention first
- **Route** alerts to frontline responders with context
- **Track** whether interventions actually worked
- **Explain** every risk score in human-readable terms

## Technical Differentiation

| Feature | Generic Climate Systems | OpenChildRisk OS |
|---------|------------------------|------------------|
| **Focus** | Hazard detection | Child vulnerability |
| **Output** | "Flood detected" | "12,000 under-5 + 3,200 disabled children at risk in Mora. Cholera HIGH (poor WASH). 15 CHWs alerted." |
| **Risk Model** | Single hazard | Compound (hazard × vulnerability × capacity) |
| **Users** | Analysts | Field responders (CHWs, nurses, coordinators) |
| **Delivery** | Web dashboard | SMS, WhatsApp, offline-first |
| **Geography** | National averages | District-level operational targeting |
| **Population** | Homogeneous | Differentiated (under-5, disabled, displaced) |
| **Loop** | One-way alerts | Full cycle: Alert → Intervention → Outcome |
| **Explainability** | Black box scores | Transparent reasoning + recommendations |
---

## ⚙️ How It Works

```

                    ┌─────────────────────┐
                    │   Climate Signals   │
                    │  Rainfall · Temp    │
                    │  Flood · Drought    │
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │   Risk Engine       │
                    │   (Python/FastAPI)  │
                    │                     │
                    │  score = rainfall   │
                    │    × sanitation gap │
                    │    × temperature    │
                    │    × population     │
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │  Alert Orchestrator │
                    │   (Laravel API)     │
                    │                     │
                    │  Store risk score   │
                    │  Generate alert     │
                    │  Assign to org      │
                    │  Set priority       │
                    └──────────┬──────────┘
                               │
               ┌───────────────┼───────────────┐
               │               │               │
    ┌──────────▼──────┐ ┌──────▼──────┐ ┌─────▼────────┐
    │   SMS Alert     │ │  Dashboard  │ │   Database   │
    │ CHW receives    │ │  Ministry   │ │  Full audit  │
    │ action message  │ │  UNICEF     │ │  trail       │
    └─────────────────┘ └─────────────┘ └──────────────┘

```

---

## 🏗️ Architecture

```

┌─────────────────────────────────────────────────────────┐
│                  PRESENTATION LAYER                      │
│  Dashboard (React)  │  SMS/WhatsApp  │  REST API        │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│              ORCHESTRATION LAYER (Laravel)               │
│  Alert Generation  │  Auth/RBAC  │  Queue Management    │
└──────┬─────────────────────────────────────┬────────────┘
       │                                     │
┌──────▼──────────┐              ┌───────────▼────────────┐
│  RISK ENGINE    │              │   NOTIFICATION         │
│  (Python)       │              │   SERVICE              │
│                 │              │                        │
│  Cholera risk   │              │  SMS (Africa's         │
│  Heat stress    │              │  Talking)              │
│  Flood risk     │              │  WhatsApp              │
│  Malnutrition   │              │  Email                 │
└──────┬──────────┘              └────────────────────────┘
       │
┌──────▼──────────────────────────────────────────────────┐
│                    DATA LAYER                            │
│  PostgreSQL/PostGIS  │  Redis  │  MinIO                 │
│                                                         │
│  countries → districts → risk_scores → alerts           │
│  organizations → programs → program_deployments         │
│  hazard_types │ facilities │ alert_actions               │
└─────────────────────────────────────────────────────────┘

```

> 📄 Full architecture details: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

---

## 🔄 MVP Flow

The first working loop we are proving:

```

Step 1: INPUT
POST /alerts/generate
{
  "district_id": "<mora-uuid>",
  "rainfall_mm": 150,
  "temperature": 38,
  "sanitation_coverage": 0.11,
  "under5_population": 38000
}

Step 2: PROCESS
Laravel → calls Python risk engine
Python  → calculates cholera risk score
Score   → 8.2 / 10 (HIGH)

Step 3: STORE
risk_scores table ← risk calculation
alerts table      ← alert generated
alert_actions     ← accountability log

Step 4: OUTPUT
{
  "alert_id": "uuid",
  "district": "Mora",
  "risk_level": "HIGH",
  "score": 8.2,
  "children_affected": 4180,
  "message": "HIGH cholera risk in Mora.
              4,180 children at risk.
              Pre-position ORS. Alert CHWs immediately.",
  "priority_score": 7.9,
  "access_level": "unknown",
  "capacity_status": "unknown"
}

```

---

## 📊 Current Status

| Component | Status | Notes |
|-----------|--------|-------|
| PostgreSQL + PostGIS | ✅ Running | Docker |
| Redis | ✅ Running | Docker |
| MinIO | ✅ Running | Docker |
| Database Schema | ✅ Complete | 10 tables |
| Cameroon Seed Data | ✅ Loaded | 10 districts, Far North |
| Organizations | ✅ Seeded | UNICEF, WHO, WFP, Gov |
| Programs | ✅ Seeded | 5 programs |
| Risk Engine (Python) | 🔨 In Progress | Cholera scorer |
| Laravel API | ⏳ Pending | Alert orchestration |
| SMS Alerts | ⏳ Pending | Africa's Talking |
| Dashboard | ⏳ Pending | React + Mapbox |

---

## 🚀 Quick Start

### Prerequisites

- Docker Desktop installed
- Docker Compose v2+
- Git

### Launch

```bash
# Clone the repository
git clone https://github.com/openchildrisk/openchildrisk-os
cd openchildrisk-os

# Configure environment
cp .env.example .env

# Start infrastructure
docker compose up -d

# Verify services
docker compose ps
```

### Verify

```bash
# Check risk engine health
curl http://localhost:8001/health

# Expected response:
# {"status": "ok", "service": "risk-engine", "version": "1.0.0"}
```

> 📄 Full deployment guide: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

---

## 🇨🇲 Pilot: Cameroon Far North

Our first pilot targets **Cameroon's Far North Region** —
the intersection of all three crises identified in SOWC 2025:

| District | Children U5 | Conflict | WASH Coverage | Priority |
|----------|-------------|----------|---------------|----------|
| Maroua | 45,000 | No | 41% | HIGH |
| Mora | 38,000 | **Yes** | 28% | CRITICAL |
| Kousseri | 41,000 | **Yes** | 32% | CRITICAL |
| Makary | 29,000 | **Yes** | 21% | CRITICAL |
| Yagoua | 33,000 | No | 35% | HIGH |
| Kaele | 27,000 | No | 29% | HIGH |
| Meri | 22,000 | No | 19% | HIGH |
| Mindif | 19,000 | No | 23% | MEDIUM |
| Tokombere | 24,000 | No | 17% | HIGH |
| Waza | 16,000 | **Yes** | 15% | CRITICAL |

> 📄 Full pilot documentation: [docs/PILOT-CAMEROON.md](docs/PILOT-CAMEROON.md)

---

## 🤝 UNICEF Alignment

OpenChildRisk OS is built directly on UNICEF's own frameworks:

| UNICEF Framework | How We Implement It |
|-----------------|---------------------|
| CCRI 2021/2023 | Core risk scoring methodology |
| SOWC 2025 Five Policy Pillars | System design principles |
| Anticipatory Action Evidence | Cash transfer trigger logic |
| WASH Vulnerability Framework | Cholera risk engine |
| Child-Responsive EWS | Alert architecture |

> 📄 Full alignment document: [docs/UNICEF-ALIGNMENT.md](docs/UNICEF-ALIGNMENT.md)

---

## 🛠️ Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Orchestration | Laravel (PHP) | API, auth, workflows, queues |
| Risk Engine | FastAPI (Python) | Risk scoring, ML models |
| Database | PostgreSQL + PostGIS | Geospatial child vulnerability data |
| Cache/Queue | Redis | Job queues, caching |
| Storage | MinIO | Raw data lake |
| SMS Alerts | Africa's Talking | Field worker notifications |
| Containers | Docker + Compose | Deployment |
| Metadata | OpenMetadata | Data lineage + governance |

---

## 📚 Documentation

| Document | Description |
|----------|-------------|
| [Architecture](docs/ARCHITECTURE.md) | System design and service map |
| [Roadmap](docs/ROADMAP.md) | Development phases and milestones |
| [Data Sources](docs/DATA-SOURCES.md) | Climate, health, and population data |
| [UNICEF Alignment](docs/UNICEF-ALIGNMENT.md) | Framework mapping |
| [Pilot: Cameroon](docs/PILOT-CAMEROON.md) | Far North deployment guide |

---

## 🤲 Contributing

OpenChildRisk OS is open to contributions from:
- Developers
- Public health experts
- Humanitarian data specialists
- UNICEF and UN agency staff
- Governments and NGOs

Coming soon: `CONTRIBUTING.md`

---

## 📄 License

Licensed under the **Apache License 2.0**.

This means:
- ✔ Free to use, modify and distribute
- ✔ Commercial use permitted
- ✔ Patent protection for contributors
- ✔ Governments and humanitarian organizations
  may deploy without licensing fees
- ✔ Countries may adapt for local contexts

---

# Frequently Asked Questions

## General

**Q: Is this a monitoring dashboard?**  
A: No. OpenChildRisk OS is **operational intelligence infrastructure**. Dashboards are a delivery mechanism, but the core value is predictive risk modeling, operational prioritization, and intervention tracking.

**Q: Can this replace existing systems?**  
A: No. OpenChildRisk OS is designed to **augment** existing systems (DHIS2, WHO surveillance, GIS platforms) by providing a unified child vulnerability intelligence layer.

**Q: Why focus on children specifically?**  
A: Children are disproportionately affected by climate shocks but often invisible in generic population statistics. Age-specific, disability-aware, and vulnerability-differentiated modeling enables more effective interventions.

## Technical

**Q: Can this run offline?**  
A: Partially. The system is designed for **intermittent connectivity**. Field workers receive SMS/WhatsApp alerts that work offline. Data syncs when connectivity resumes.

**Q: What's the minimum hardware requirement?**  
A: Development: 8GB RAM. Production (single country): 16GB RAM, 500GB storage.

**Q: Can I deploy this for a different country?**  
A: Yes. The system includes 250 countries by default. You add districts, indicators, and field workers for your target region.

**Q: How do I get indicator data?**  
A: Phase 2 includes connectors for DHIS2, CSV imports, and manual entry. Phase 3 adds automated API sync with UNICEF, WHO, and climate data sources.

## Humanitarian

**Q: Does this work in conflict zones?**  
A: Yes. The system models conflict as a hazard type and includes displacement tracking. SMS/WhatsApp delivery works in low-infrastructure environments.

**Q: Can multiple organizations use this together?**  
A: Yes. Multi-tenancy is built-in. UNICEF, WHO, WFP, and NGOs can operate on the same platform with data isolation and coordination capabilities.

**Q: How much does it cost to operate?**  
A: Cloud hosting: ~$300-500/month. SMS costs: ~$0.024/message (Africa). Open-source means no licensing fees.

---

See [LICENSE](LICENSE) for full terms.

<div align="center">

**Built for the children who cannot wait.**

*Aligned with UNICEF CCRI 2023 · SOWC 2025 · SDG 1 · SDG 3 · SDG 13*

</div>