-- ================================================
-- OpenChildRisk OS — Core Schema
-- Version: 1.0 (2024-06-01)
-- Description: Core tables for countries, districts, facilities, risk scores, and alerts.
-- ================================================

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS postgis;

-- ================================================
-- FUNCTION: auto-update updated_at on every table
-- ================================================
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ================================================
-- TABLE: countries
-- ================================================
CREATE TABLE IF NOT EXISTS countries (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    iso VARCHAR(3) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TRIGGER trg_countries_updated
    BEFORE UPDATE ON countries
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- TABLE: districts
-- ================================================
ALTER TABLE districts
    ADD COLUMN IF NOT EXISTS country_id UUID
        REFERENCES countries(id) ON DELETE SET NULL;

ALTER TABLE districts
    ADD COLUMN IF NOT EXISTS geom
        geometry(MULTIPOLYGON, 4326);

ALTER TABLE districts
    ADD COLUMN IF NOT EXISTS under5_population INTEGER
        DEFAULT 0 CHECK (under5_population >= 0);

ALTER TABLE districts
    ADD COLUMN IF NOT EXISTS updated_at
        TIMESTAMP DEFAULT NOW();

CREATE INDEX IF NOT EXISTS idx_districts_geom
    ON districts USING GIST(geom);

CREATE INDEX IF NOT EXISTS idx_districts_country_id
    ON districts(country_id);

CREATE TRIGGER trg_districts_updated
    BEFORE UPDATE ON districts
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- TABLE: facilities
-- ================================================
CREATE TABLE IF NOT EXISTS facilities (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    district_id UUID NOT NULL
        REFERENCES districts(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    type VARCHAR(50) NOT NULL
        CHECK (type IN (
            'clinic',
            'school',
            'wash_point',
            'other'
        )),
    location GEOMETRY(Point, 4326) NOT NULL,
    active BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_facilities_location
    ON facilities USING GIST(location);

CREATE INDEX IF NOT EXISTS idx_facilities_district
    ON facilities(district_id);

CREATE INDEX IF NOT EXISTS idx_facilities_type
    ON facilities(type);

CREATE TRIGGER trg_facilities_updated
    BEFORE UPDATE ON facilities
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- TABLE: risk_scores
-- ================================================
CREATE TABLE IF NOT EXISTS risk_scores (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    district_id UUID NOT NULL
        REFERENCES districts(id) ON DELETE CASCADE,
    hazard_type VARCHAR(50) NOT NULL
        CHECK (hazard_type IN (
            'heat',
            'flood',
            'drought',
            'air_pollution',
            'cholera',
            'conflict'
        )),
    score FLOAT NOT NULL
        CHECK (score >= 0 AND score <= 10),
    confidence FLOAT
        CHECK (confidence >= 0 AND confidence <= 1),
    metadata JSONB,
    scored_at TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_risk_scores_district
    ON risk_scores(district_id);

CREATE INDEX IF NOT EXISTS idx_risk_scores_latest
    ON risk_scores(district_id, hazard_type, scored_at DESC);

CREATE TRIGGER trg_risk_scores_updated
    BEFORE UPDATE ON risk_scores
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- TABLE: alerts
-- ================================================
CREATE TABLE IF NOT EXISTS alerts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    district_id UUID NOT NULL
        REFERENCES districts(id) ON DELETE CASCADE,
    risk_score_id UUID NOT NULL
        REFERENCES risk_scores(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    risk_level VARCHAR(20) NOT NULL
        CHECK (risk_level IN (
            'LOW',
            'MEDIUM',
            'HIGH',
            'CRITICAL'
        )),
    message TEXT NOT NULL,
    children_affected INTEGER DEFAULT 0
        CHECK (children_affected >= 0),
    status VARCHAR(20) DEFAULT 'pending'
        CHECK (status IN (
            'pending',
            'sent',
            'acknowledged',
            'resolved',
            'failed'
        )),
    triggered_at TIMESTAMP DEFAULT NOW(),
    resolved_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    -- No duplicate alerts
    UNIQUE (district_id, risk_score_id, type),

    -- resolved must be after triggered
    CHECK (
        resolved_at IS NULL
        OR resolved_at >= triggered_at
    )
);

CREATE INDEX IF NOT EXISTS idx_alerts_district
    ON alerts(district_id);

CREATE INDEX IF NOT EXISTS idx_alerts_risk_score_id
    ON alerts(risk_score_id);

CREATE INDEX IF NOT EXISTS idx_alerts_status
    ON alerts(status, triggered_at);

CREATE TRIGGER trg_alerts_updated
    BEFORE UPDATE ON alerts
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

ALTER TABLE alerts
    ADD CONSTRAINT chk_alerts_type
    CHECK (type IN (
        'heatwave',
        'flood',
        'drought',
        'air_pollution',
        'cholera',
        'conflict'
    ));