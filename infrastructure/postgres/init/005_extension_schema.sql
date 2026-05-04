-- ================================================
-- OpenChildRisk OS — Extension Schema
-- Version: 1.0 FINAL
-- Multi-agency, accountability, traceability
-- ================================================

-- ================================================
-- TABLE: organizations
-- UN agencies, governments, NGOs, donors
-- ================================================
CREATE TABLE IF NOT EXISTS organizations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(200) NOT NULL,
    short_name VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL
        CHECK (type IN (
            'UN',
            'Government',
            'NGO',
            'Donor',
            'Implementing Partner'
        )),
    country_id UUID
        REFERENCES countries(id) ON DELETE SET NULL,
    active BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_organizations_type
    ON organizations(type);

CREATE INDEX IF NOT EXISTS idx_organizations_country
    ON organizations(country_id);

CREATE TRIGGER trg_organizations_updated
    BEFORE UPDATE ON organizations
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- TABLE: hazard_types
-- Dynamic lookup — replaces hardcoded CHECK
-- ================================================
CREATE TABLE IF NOT EXISTS hazard_types (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL
        CHECK (category IN (
            'climate',
            'disease',
            'conflict',
            'compound'
        )),
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TRIGGER trg_hazard_types_updated
    BEFORE UPDATE ON hazard_types
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- Seed core hazard types
INSERT INTO hazard_types (code, name, category) VALUES
    ('heat',          'Heatwave',              'climate'),
    ('flood',         'Flooding',              'climate'),
    ('drought',       'Drought',               'climate'),
    ('air_pollution', 'Air Pollution',          'climate'),
    ('cholera',       'Cholera Outbreak',       'disease'),
    ('malnutrition',  'Acute Malnutrition',     'disease'),
    ('measles',       'Measles Outbreak',       'disease'),
    ('conflict',      'Armed Conflict',         'conflict'),
    ('displacement',  'Mass Displacement',      'conflict'),
    ('compound',      'Compound Crisis',        'compound')
ON CONFLICT (code) DO NOTHING;

-- ================================================
-- TABLE: programs
-- Program definitions (reusable across countries)
-- ================================================
CREATE TABLE IF NOT EXISTS programs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(200) NOT NULL,
    description TEXT,
    domain VARCHAR(50) NOT NULL
        CHECK (domain IN (
            'nutrition',
            'wash',
            'health',
            'education',
            'protection',
            'cash_transfer',
            'multi_sector'
        )),
    created_by_org_id UUID NOT NULL
        REFERENCES organizations(id) ON DELETE RESTRICT,
    active BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TRIGGER trg_programs_updated
    BEFORE UPDATE ON programs
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- TABLE: program_deployments
-- Actual operational deployments in the field
-- Organization + Program + Geography + Time
-- ================================================
CREATE TABLE IF NOT EXISTS program_deployments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    program_id UUID NOT NULL
        REFERENCES programs(id) ON DELETE RESTRICT,
    organization_id UUID NOT NULL
        REFERENCES organizations(id) ON DELETE RESTRICT,
    country_id UUID NOT NULL
        REFERENCES countries(id) ON DELETE RESTRICT,
    district_id UUID
        REFERENCES districts(id) ON DELETE SET NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active'
        CHECK (status IN (
            'planned',
            'active',
            'suspended',
            'completed'
        )),
    start_date DATE NOT NULL,
    end_date DATE,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    -- Deployment must end after it starts
    CHECK (end_date IS NULL OR end_date >= start_date)
);

CREATE INDEX IF NOT EXISTS idx_deployments_organization
    ON program_deployments(organization_id);

CREATE INDEX IF NOT EXISTS idx_deployments_district
    ON program_deployments(district_id);

CREATE INDEX IF NOT EXISTS idx_deployments_status
    ON program_deployments(status);

CREATE TRIGGER trg_deployments_updated
    BEFORE UPDATE ON program_deployments
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- ================================================
-- MODIFY: risk_scores
-- Replace hardcoded hazard_type with FK
-- ================================================
ALTER TABLE risk_scores
    ADD COLUMN IF NOT EXISTS hazard_type_id UUID
        REFERENCES hazard_types(id) ON DELETE RESTRICT;

CREATE INDEX IF NOT EXISTS idx_risk_scores_hazard_type
    ON risk_scores(hazard_type_id);

-- ================================================
-- MODIFY: alerts
-- Add ownership + program + assignment
-- ================================================
ALTER TABLE alerts
    ADD COLUMN IF NOT EXISTS organization_id UUID NOT NULL
        REFERENCES organizations(id) ON DELETE RESTRICT
        DEFAULT (
            SELECT id FROM organizations
            WHERE short_name = 'UNICEF'
            LIMIT 1
        );

ALTER TABLE alerts
    ADD COLUMN IF NOT EXISTS program_deployment_id UUID
        REFERENCES program_deployments(id) ON DELETE SET NULL;

ALTER TABLE alerts
    ADD COLUMN IF NOT EXISTS assigned_to_org_id UUID
        REFERENCES organizations(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_alerts_organization
    ON alerts(organization_id);

CREATE INDEX IF NOT EXISTS idx_alerts_deployment
    ON alerts(program_deployment_id);

-- ================================================
-- TABLE: alert_actions
-- WHO did WHAT and WHEN
-- Full accountability log
-- ================================================
CREATE TABLE IF NOT EXISTS alert_actions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    alert_id UUID NOT NULL
        REFERENCES alerts(id) ON DELETE CASCADE,
    organization_id UUID NOT NULL
        REFERENCES organizations(id) ON DELETE RESTRICT,
    action_type VARCHAR(50) NOT NULL
        CHECK (action_type IN (
            'notified',
            'acknowledged',
            'dispatched',
            'treated',
            'resolved',
            'escalated',
            'failed'
        )),
    notes TEXT,
    response_time_minutes INTEGER
        CHECK (response_time_minutes >= 0),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_alert_actions_alert
    ON alert_actions(alert_id);

CREATE INDEX IF NOT EXISTS idx_alert_actions_org
    ON alert_actions(organization_id);

CREATE TRIGGER trg_alert_actions_updated
    BEFORE UPDATE ON alert_actions
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();