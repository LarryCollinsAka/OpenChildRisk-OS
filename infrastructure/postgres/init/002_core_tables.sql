-- Districts table
CREATE TABLE IF NOT EXISTS districts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    country_iso VARCHAR(3) NOT NULL,
    admin1_name VARCHAR(100),
    admin2_code VARCHAR(20) NOT NULL,
    admin2_name VARCHAR(100) NOT NULL,
    centroid_lat DECIMAL(10,7) NOT NULL,
    centroid_lon DECIMAL(10,7) NOT NULL,
    children_under5 INTEGER DEFAULT 0,
    children_5_14 INTEGER DEFAULT 0,
    wash_coverage FLOAT,
    sanitation_coverage FLOAT,
    health_facility_density FLOAT,
    social_protection_coverage FLOAT,
    conflict_affected BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_districts_country
    ON districts(country_iso);

CREATE INDEX IF NOT EXISTS idx_districts_admin2
    ON districts(country_iso, admin2_code);

