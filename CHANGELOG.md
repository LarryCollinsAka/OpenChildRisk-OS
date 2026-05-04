# Changelog

All notable changes to OpenChildRisk OS are documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### In Progress
- Python FastAPI risk engine (cholera scorer)
- Laravel alert orchestration API
- End-to-end alert generation loop

---

## [0.1.0] — 2026-05-04

### 🎉 First Release — Infrastructure + Schema + Seed Data

This release establishes the complete foundation of
OpenChildRisk OS — infrastructure, database schema,
seed data, and project documentation.

### Added

#### Infrastructure
- Docker Compose stack with PostgreSQL/PostGIS, Redis, MinIO
- PostGIS 3.4 geospatial extensions enabled
- UUID and spatial reference systems configured
- All services networked on `ocr-network`

#### Database Schema
- `countries` — country registry with ISO codes
- `districts` — geospatial child vulnerability profiles
- `facilities` — clinics, schools, WASH points with geometry
- `hazard_types` — dynamic hazard type registry (replaces hardcoded constraints)
- `organizations` — UN agencies, governments, NGOs, donors
- `programs` — humanitarian programme definitions
- `program_deployments` — operational field deployments
- `risk_scores` — climate risk calculations per district
- `alerts` — child protection alerts with full lifecycle
- `alert_actions` — accountability and response logging

#### Data Integrity
- `updated_at` auto-trigger on all tables
- `CHECK` constraints on all enumerated fields
- `NOT NULL` enforcement on critical foreign keys
- `ON DELETE CASCADE` / `ON DELETE SET NULL` where appropriate
- Duplicate prevention via `UNIQUE` constraints
- Temporal consistency (`resolved_at >= triggered_at`)

#### Seed Data — Cameroon Far North Pilot
- 1 country (Cameroon)
- 10 districts (Far North region)
- 5 organizations (UNICEF, WHO, WFP, MINSANTE, MINEDUB)
- 5 humanitarian programmes
- 8 program deployments (conflict-affected districts)
- 10 hazard types (climate, disease, conflict, compound)

#### Documentation
- `README.md` — full project overview with architecture diagrams
- `CONTRIBUTING.md` — contributor onboarding guide
- `docs/ARCHITECTURE.md` — system architecture (stub)
- `docs/ROADMAP.md` — development roadmap (stub)
- `docs/DATA-SOURCES.md` — data source registry (stub)
- `docs/UNICEF-ALIGNMENT.md` — UNICEF framework alignment (stub)
- `docs/PILOT-CAMEROON.md` — Cameroon pilot documentation (stub)

#### Risk Engine (Scaffold)
- Python FastAPI service structure
- Versioned API routing (`/api/v1/`)
- `CholeraRiskEngine` — WASH vulnerability methodology
- Health check endpoint
- Dockerfile for containerized deployment

### Technical Notes
- Database: PostgreSQL 16 + PostGIS 3.4
- Schema designed for multi-country, multi-agency operations
- All tables support soft delete via `deleted_at`
- Priority score, access level, capacity status fields
  added as placeholders for Phase 2 intelligence layer

### UNICEF Alignment
- Schema aligned with SOWC 2025 Five Policy Pillars
- Hazard types based on CCRI 2021/2023 methodology
- Organization types reflect real UN/NGO operational structure
- Programme domains map to UNICEF sectoral priorities

---

## Versioning Notes

| Version | Milestone |
|---------|-----------|
| 0.1.x | Infrastructure + Schema |
| 0.2.x | Working risk → alert loop |
| 0.3.x | SMS + WhatsApp alerts |
| 0.4.x | Laravel full API |
| 0.5.x | Real climate data ingestion |
| 0.6.x | Dashboard frontend |
| 0.7.x | Composite risk engine |
| 0.8.x | Multi-country support |
| 0.9.x | Access + capacity layers |
| 1.0.0 | Production ready |

---

[Unreleased]: https://github.com/LarryCollinsAka/OpenChildRisk-OS/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/LarryCollinsAka/OpenChildRisk-OS/releases/tag/v0.1.0