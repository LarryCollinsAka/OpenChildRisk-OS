# 🤝 Contributing to OpenChildRisk OS

> Thank you for your interest in contributing.
> Every contribution — code, data, documentation, or feedback —
> directly supports better outcomes for children in climate-vulnerable regions.

---

## 📋 Table of Contents

1. [Who Should Contribute](#-who-should-contribute)
2. [Our Principles](#-our-principles)
3. [Getting Started](#-getting-started)
4. [Project Structure](#-project-structure)
5. [Development Workflow](#-development-workflow)
6. [How to Contribute](#-how-to-contribute)
7. [Code Standards](#-code-standards)
8. [Commit Message Format](#-commit-message-format)
9. [Opening Issues](#-opening-issues)
10. [Pull Request Process](#-pull-request-process)
11. [Data Contribution](#-data-contribution)
12. [Domain Expert Contributions](#-domain-expert-contributions)
13. [Code of Conduct](#-code-of-conduct)
14. [Getting Help](#-getting-help)

---

## 👥 Who Should Contribute

We welcome contributions from:

| Profile | How You Can Help |
|---------|-----------------|
| **Software Developers** | Backend, frontend, DevOps, ML |
| **Public Health Experts** | Risk model validation, indicator review |
| **GIS / Data Specialists** | Geospatial data, country datasets |
| **Humanitarian Workers** | Field feedback, use case validation |
| **UNICEF / UN Staff** | Framework alignment, programme data |
| **Governments / Ministries** | Country-specific data and requirements |
| **Researchers** | Climate-health literature, methodology |
| **Translators** | French, Arabic, Swahili, Hausa |

---

## 🧭 Our Principles

Before contributing, understand what we are building:

```
We are NOT building a generic climate app.
We are NOT building a data warehouse.

We ARE building decision infrastructure that
converts climate signals into child protection
actions — before shocks become crises.
```

Every contribution should serve this mission.

**Ask yourself before contributing:**

> Does this help protect children from climate shocks?
> Does this make the system more usable in low-resource settings?
> Does this make the system more trustworthy and accountable?

---

## 🚀 Getting Started

### Prerequisites

Make sure you have these installed:

| Tool | Version | Purpose |
|------|---------|---------|
| Git | Latest | Version control |
| Docker Desktop | Latest | Run all services |
| Docker Compose | v2+ | Service orchestration |
| Python | 3.11+ | Risk engine development |
| PHP | 8.2+ | Laravel development |
| Node.js | 18+ | Frontend development |
| VS Code | Latest | Recommended editor |

---

### Step 1 — Fork the Repository

Click **Fork** on GitHub:

```
https://github.com/LarryCollinsAka/OpenChildRisk-OS
```

---

### Step 2 — Clone Your Fork

```bash
git clone https://github.com/YOUR_USERNAME/OpenChildRisk-OS.git
cd OpenChildRisk-OS
```

---

### Step 3 — Configure Environment

```bash
cp .env.example .env
```

Open `.env` and review the default values.
For local development the defaults work out of the box.

---

### Step 4 — Start All Services

```bash
docker compose up -d
```

This starts:
- PostgreSQL + PostGIS (port 5432)
- Redis (port 6379)
- MinIO (port 9000/9001)
- Risk Engine — Python FastAPI (port 8001)

---

### Step 5 — Verify Everything Is Running

```bash
docker compose ps
```

All services should show **healthy** or **running**.

---

### Step 6 — Test the Risk Engine

```bash
curl http://localhost:8001/health
```

Expected response:

```json
{
  "status": "ok",
  "service": "risk-engine",
  "version": "1.0.0"
}
```

---

### Step 7 — Set Up Upstream Remote

```bash
git remote add upstream https://github.com/LarryCollinsAka/OpenChildRisk-OS.git
```

This lets you pull latest changes from the main repo.

---

## 📁 Project Structure

```
openchildrisk-os/
│
├── services/
│   ├── app-laravel/          # Main API — orchestration, auth, alerts
│   ├── risk-engine-python/   # Risk scoring — cholera, heat, flood
│   ├── ingestion-python/     # Climate data ingestion pipelines
│   ├── notification-worker/  # SMS, WhatsApp alert delivery
│   └── dashboard-frontend/   # React dashboard for ministries
│
├── infrastructure/
│   ├── nginx/                # Reverse proxy config
│   ├── postgres/             # DB init scripts and migrations
│   └── redis/                # Redis config
│
├── config/
│   ├── countries/            # Country-specific configuration
│   ├── rules/                # Alert trigger rules (YAML)
│   └── ccri_weights.yaml     # UNICEF CCRI scoring weights
│
├── docs/                     # Full documentation
├── .env.example              # Environment template
├── docker-compose.yml        # All services
├── README.md                 # Project overview
└── CONTRIBUTING.md           # This file
```

---

## 🔄 Development Workflow

### Always work on a branch

```bash
# Sync with upstream first
git fetch upstream
git checkout main
git merge upstream/main

# Create your feature branch
git checkout -b feature/your-feature-name
```

### Branch naming convention

| Type | Format | Example |
|------|--------|---------|
| Feature | `feature/description` | `feature/flood-risk-engine` |
| Bug fix | `fix/description` | `fix/cholera-score-calculation` |
| Documentation | `docs/description` | `docs/cameroon-pilot` |
| Data | `data/description` | `data/chad-districts` |
| Refactor | `refactor/description` | `refactor/alert-dispatcher` |

---

## 🛠️ How to Contribute

### Option A — Pick an Open Issue

Browse open issues on GitHub:

```
https://github.com/LarryCollinsAka/OpenChildRisk-OS/issues
```

Look for issues tagged:

- `good first issue` — ideal for new contributors
- `help wanted` — any skill level welcome
- `domain-expertise` — needs public health knowledge
- `data` — needs country or climate datasets

Comment on the issue to claim it before starting.

---

### Option B — Suggest a New Feature

Open a new issue using the **Feature Request** template.

Describe:
- What problem it solves
- Which children or contexts it helps
- How it aligns with UNICEF priorities

---

### Option C — Improve Documentation

Documentation is as important as code.

You can improve:
- README.md
- docs/ folder
- Code comments
- API documentation

---

### Option D — Add Country Data

Help us expand beyond Cameroon.

See [Data Contribution](#-data-contribution) section below.

---

## 📝 Code Standards

### Python (Risk Engine / Ingestion)

```python
# Every function must have a docstring
def score_rainfall(rainfall_mm: float) -> float:
    """
    Score rainfall signal on 0-10 scale.

    Args:
        rainfall_mm: Total rainfall in mm over 7 days

    Returns:
        float: Rainfall risk score (0-10)
    """
    pass

# Type hints are required on all functions
# Constants in UPPER_CASE with explanation comment

# Threshold above which flooding risk begins
RAINFALL_DANGER_THRESHOLD_MM = 100
```

### PHP / Laravel

```php
/**
 * Every method must have a docblock.
 * Explain what it does, not how it does it.
 *
 * @param RiskRequest $request
 * @return JsonResponse
 */
public function evaluate(RiskRequest $request): JsonResponse
{
    // Inline comments explain WHY, not WHAT
    // Good: // Normalize to UNICEF 0-10 scale
    // Bad:  // Divide by 20
}
```

### SQL

```sql
-- Every table and column must have a comment
-- explaining its purpose and data source

-- children_under5: Count of children aged 0-4 in district
-- Source: WorldPop 2020 / UNICEF MICS 2019
children_under5 INTEGER DEFAULT 0
```

### General Rules

- No magic numbers — use named constants with comments
- No silent failures — always log errors with context
- No hardcoded credentials — always use `.env`
- Comments must explain **why**, not **what**

---

## 📦 Commit Message Format

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): short description

Longer explanation if needed.
What problem does this solve?
Why was this approach chosen?
```

### Types

| Type | When to Use |
|------|-------------|
| `feat` | New feature or capability |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `data` | Dataset additions or updates |
| `refactor` | Code restructure, no behavior change |
| `test` | Adding or updating tests |
| `chore` | Build, config, dependency updates |

### Examples

```bash
feat(risk-engine): add heat stress scorer for under-5

fix(cholera): correct sanitation gap weight calculation

docs(cameroon): add Far North district vulnerability data

data(chad): add 15 districts with WASH coverage from WHO JMP 2022

feat(alerts): add priority score calculation based on population exposure
```

---

## 🐛 Opening Issues

When opening a bug report, include:

```markdown
## What happened
Clear description of the problem.

## Expected behavior
What should have happened.

## Steps to reproduce
1. Step one
2. Step two
3. Step three

## Environment
- OS:
- Docker version:
- Service affected:

## Logs
Paste relevant logs here.
```

---

## 🔁 Pull Request Process

### Before opening a PR

```bash
# Make sure your branch is up to date
git fetch upstream
git rebase upstream/main

# Test your changes
docker compose up -d
curl http://localhost:8001/health
```

### PR checklist

Before submitting, confirm:

- [ ] Code is commented explaining **why**
- [ ] No hardcoded credentials or secrets
- [ ] `.env.example` updated if new variables added
- [ ] Documentation updated if behavior changed
- [ ] Tested locally with Docker
- [ ] Commit messages follow convention

### PR title format

```
type(scope): what this PR does
```

Examples:
```
feat(risk-engine): add flood risk scorer
fix(seed): correct Cameroon district coordinates
docs(contributing): add data contribution guide
```

---

## 🗃️ Data Contribution

Expanding to new countries requires:

### 1. District boundaries and centroids

```yaml
# config/countries/nga.yaml (example: Nigeria)
country:
  iso: NGA
  name: Nigeria
  priority_regions:
    - Northeast  # Borno, Adamawa — high child poverty
    - Northwest  # Sokoto, Kebbi — drought affected
```

### 2. Required data per district

| Field | Source | Format |
|-------|--------|--------|
| District name | OCHA HDX | Text |
| Coordinates | OCHA HDX | Decimal degrees |
| Children under-5 | WorldPop | Integer |
| WASH coverage | WHO JMP | Float 0-1 |
| Sanitation coverage | WHO JMP | Float 0-1 |
| Conflict affected | ACLED | Boolean |

### 3. Data sources we accept

- UNICEF MICS surveys
- WHO JMP (WASH)
- WorldPop population grids
- OCHA HDX humanitarian data
- National statistics offices
- Peer-reviewed research

### 4. Submit data as a seed SQL file

```sql
-- nigeria_northeast_seed.sql
-- Source: UNICEF MICS 2021, WorldPop 2020
-- Contributor: Your Name
-- Date: 2026-01-01

INSERT INTO districts (...) VALUES (...);
```

---

## 🏥 Domain Expert Contributions

If you are a public health expert, humanitarian worker,
or UNICEF staff member — your knowledge is as valuable
as code.

You can contribute by:

- **Reviewing risk models** — are our weights clinically valid?
- **Validating thresholds** — is 100mm/week the right flood signal?
- **Adding hazard types** — what disease outbreaks are we missing?
- **Field feedback** — does the alert language work for CHWs?
- **Programme data** — which UNICEF programmes operate where?

Open an issue tagged `domain-expertise` to start a conversation.

---

## 🤝 Code of Conduct

This project exists to protect children.

We expect all contributors to:

- Treat all contributors with respect
- Welcome contributors from all backgrounds
- Accept constructive feedback gracefully
- Prioritize the mission over personal preferences
- Never use this system to harm or surveil communities

---

## 💬 Getting Help

| Channel | Purpose |
|---------|---------|
| GitHub Issues | Bug reports, feature requests |
| GitHub Discussions | Questions, ideas, collaboration |
| Pull Request comments | Code review feedback |

---

## 📄 License

By contributing to OpenChildRisk OS, you agree that
your contributions will be licensed under the
**Apache License 2.0**.

This protects:
- Contributors via patent grant
- Governments deploying the system
- Children whose protection depends on it

<div align="center">

**Every line of code is a line of defence for a child.**

*OpenChildRisk OS — Built for those who cannot wait.*

</div>