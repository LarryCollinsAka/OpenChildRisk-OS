-- ================================================
-- OpenChildRisk OS — Multilingual Data Layer
-- Version: 1.0.0 FINAL
-- ================================================
-- Design Principles:
--   1. Canonical data stored once in core tables
--   2. Translations stored in entity-specific tables
--   3. Adding a language = one INSERT in languages
--   4. English is always the fallback language
--   5. No code changes required to add a language
--   6. System messages use JSON files (not DB)
--   7. DB translations = domain data only
--      (district names, facility names, etc.)
-- ================================================


-- ================================================
-- TABLE: languages
-- Single source of truth for all supported languages.
-- Adding a new language requires only one INSERT here.
-- System message JSON file is optional (falls back to EN)
-- ================================================
CREATE TABLE IF NOT EXISTS languages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    -- ISO 639-1 language code (2 letters)
    -- This code links DB translations to JSON files
    -- Example: en, fr, ar, es, pt, nl, de
    code VARCHAR(10) NOT NULL UNIQUE,

    -- Language name in its own script
    -- Example: "Français", "العربية", "Deutsch"
    name VARCHAR(100) NOT NULL,

    -- Language name in English for admin interfaces
    -- Example: "French", "Arabic", "German"
    name_en VARCHAR(100) NOT NULL,

    -- Text direction for UI rendering
    -- ltr = left-to-right (most languages)
    -- rtl = right-to-left (Arabic, etc.)
    direction VARCHAR(3) NOT NULL DEFAULT 'ltr'
        CHECK (direction IN ('ltr', 'rtl')),

    -- Translation completeness status
    -- complete = fully translated
    -- partial  = some translations exist, falls back to EN
    -- pending  = no translations yet, always falls back to EN
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('complete', 'partial', 'pending')),

    -- Whether system message JSON file exists
    -- TRUE  = lang/[code]/alerts.json exists
    -- FALSE = system messages fall back to English
    has_message_file BOOLEAN DEFAULT false,

    -- Whether this language is active in the system
    -- Inactive languages are hidden from users
    active BOOLEAN DEFAULT true,

    -- Who added this language and when
    added_by VARCHAR(200),

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TRIGGER trg_languages_updated
    BEFORE UPDATE ON languages
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- Index for fast active language queries
CREATE INDEX IF NOT EXISTS idx_languages_active
    ON languages(active, status);

-- ================================================
-- SEED: Core Languages
-- Add new languages with a simple INSERT below
-- ================================================
INSERT INTO languages
    (code, name, name_en, direction, status, has_message_file, added_by)
VALUES
    -- English: default fallback, always required
    ('en', 'English',    'English',    'ltr', 'complete', true,  'OpenChildRisk OS Team'),

    -- French: primary for Cameroon, DRC, West Africa, UNICEF HQ
    ('fr', 'Français',   'French',     'ltr', 'complete', true,  'OpenChildRisk OS Team'),

    -- Arabic: Sudan, Chad, Middle East, North Africa
    ('ar', 'العربية',    'Arabic',     'rtl', 'complete', true,  'OpenChildRisk OS Team'),

    -- Spanish: Latin America UNICEF/WFP operations
    ('es', 'Español',    'Spanish',    'ltr', 'pending',  false, 'OpenChildRisk OS Team'),

    -- Portuguese: Angola, Mozambique, Brazil
    ('pt', 'Português',  'Portuguese', 'ltr', 'pending',  false, 'OpenChildRisk OS Team'),

    -- Dutch: Netherlands donor community
    ('nl', 'Nederlands', 'Dutch',      'ltr', 'pending',  false, 'OpenChildRisk OS Team'),

    -- German: Germany donor community
    ('de', 'Deutsch',    'German',     'ltr', 'pending',  false, 'OpenChildRisk OS Team')

ON CONFLICT (code) DO NOTHING;


-- ================================================
-- TABLE: district_translations
-- Translated names and descriptions for districts
-- ================================================
CREATE TABLE IF NOT EXISTS district_translations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    -- Reference to the canonical district record
    district_id UUID NOT NULL
        REFERENCES districts(id) ON DELETE CASCADE,

    -- Language code — must exist in languages table
    -- Adding a new language = insert in languages first
    language_code VARCHAR(10) NOT NULL
        REFERENCES languages(code) ON DELETE RESTRICT,

    -- Translated district name
    -- Example: "Maroua" (same in FR/EN), "مارواء" (AR)
    name TEXT NOT NULL,

    -- Optional translated description
    -- Context about the district for field workers
    description TEXT,

    -- Who provided this translation
    translated_by VARCHAR(200),

    -- When this translation was verified accurate
    verified_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    -- One translation per district per language
    UNIQUE (district_id, language_code)
);

CREATE INDEX IF NOT EXISTS idx_district_translations_district
    ON district_translations(district_id);

CREATE INDEX IF NOT EXISTS idx_district_translations_lang
    ON district_translations(language_code);

CREATE TRIGGER trg_district_translations_updated
    BEFORE UPDATE ON district_translations
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();


-- ================================================
-- TABLE: facility_translations
-- Translated names for clinics, schools, WASH points
-- ================================================
CREATE TABLE IF NOT EXISTS facility_translations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    facility_id UUID NOT NULL
        REFERENCES facilities(id) ON DELETE CASCADE,

    language_code VARCHAR(10) NOT NULL
        REFERENCES languages(code) ON DELETE RESTRICT,

    -- Translated facility name
    name TEXT NOT NULL,

    translated_by VARCHAR(200),
    verified_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE (facility_id, language_code)
);

CREATE INDEX IF NOT EXISTS idx_facility_translations_facility
    ON facility_translations(facility_id);

CREATE INDEX IF NOT EXISTS idx_facility_translations_lang
    ON facility_translations(language_code);

CREATE TRIGGER trg_facility_translations_updated
    BEFORE UPDATE ON facility_translations
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();


-- ================================================
-- TABLE: hazard_type_translations
-- Translated names for hazard types
-- ================================================
CREATE TABLE IF NOT EXISTS hazard_type_translations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    hazard_type_id UUID NOT NULL
        REFERENCES hazard_types(id) ON DELETE CASCADE,

    language_code VARCHAR(10) NOT NULL
        REFERENCES languages(code) ON DELETE RESTRICT,

    -- Translated hazard name
    -- Example EN: "Cholera" FR: "Choléra" AR: "الكوليرا"
    label TEXT NOT NULL,

    -- Optional translated description
    description TEXT,

    translated_by VARCHAR(200),
    verified_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE (hazard_type_id, language_code)
);

CREATE INDEX IF NOT EXISTS idx_hazard_translations_hazard
    ON hazard_type_translations(hazard_type_id);

CREATE INDEX IF NOT EXISTS idx_hazard_translations_lang
    ON hazard_type_translations(language_code);

CREATE TRIGGER trg_hazard_translations_updated
    BEFORE UPDATE ON hazard_type_translations
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();


-- ================================================
-- TABLE: program_translations
-- Translated names and descriptions for programs
-- ================================================
CREATE TABLE IF NOT EXISTS program_translations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    program_id UUID NOT NULL
        REFERENCES programs(id) ON DELETE CASCADE,

    language_code VARCHAR(10) NOT NULL
        REFERENCES languages(code) ON DELETE RESTRICT,

    name TEXT NOT NULL,
    description TEXT,

    translated_by VARCHAR(200),
    verified_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE (program_id, language_code)
);

CREATE INDEX IF NOT EXISTS idx_program_translations_program
    ON program_translations(program_id);

CREATE TRIGGER trg_program_translations_updated
    BEFORE UPDATE ON program_translations
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();


-- ================================================
-- TABLE: organization_translations
-- Translated names for organizations
-- ================================================
CREATE TABLE IF NOT EXISTS organization_translations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    organization_id UUID NOT NULL
        REFERENCES organizations(id) ON DELETE CASCADE,

    language_code VARCHAR(10) NOT NULL
        REFERENCES languages(code) ON DELETE RESTRICT,

    name TEXT NOT NULL,
    description TEXT,

    translated_by VARCHAR(200),
    verified_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE (organization_id, language_code)
);

CREATE INDEX IF NOT EXISTS idx_org_translations_org
    ON organization_translations(organization_id);

CREATE TRIGGER trg_org_translations_updated
    BEFORE UPDATE ON organization_translations
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();


-- ================================================
-- FUNCTION: get_translation
-- Retrieves translation with automatic English fallback.
-- Used by application layer to fetch translated content.
--
-- Usage:
--   SELECT get_district_name('<uuid>', 'fr');
--   Returns French name or English if FR missing.
-- ================================================
CREATE OR REPLACE FUNCTION get_district_name(
    p_district_id UUID,
    p_lang        VARCHAR DEFAULT 'en'
)
RETURNS TEXT AS $$
DECLARE
    v_value TEXT;
BEGIN
    -- Try requested language first
    SELECT name INTO v_value
    FROM district_translations
    WHERE district_id = p_district_id
      AND language_code = p_lang;

    -- Fall back to English if translation missing
    IF v_value IS NULL AND p_lang != 'en' THEN
        SELECT name INTO v_value
        FROM district_translations
        WHERE district_id = p_district_id
          AND language_code = 'en';
    END IF;

    RETURN v_value;
END;
$$ LANGUAGE plpgsql;


-- ================================================
-- FUNCTION: get_hazard_label
-- Returns translated hazard type label with fallback
-- ================================================
CREATE OR REPLACE FUNCTION get_hazard_label(
    p_hazard_code VARCHAR,
    p_lang        VARCHAR DEFAULT 'en'
)
RETURNS TEXT AS $$
DECLARE
    v_value TEXT;
BEGIN
    -- Try requested language
    SELECT t.label INTO v_value
    FROM hazard_type_translations t
    JOIN hazard_types h ON h.id = t.hazard_type_id
    WHERE h.code = p_hazard_code
      AND t.language_code = p_lang;

    -- Fall back to English
    IF v_value IS NULL AND p_lang != 'en' THEN
        SELECT t.label INTO v_value
        FROM hazard_type_translations t
        JOIN hazard_types h ON h.id = t.hazard_type_id
        WHERE h.code = p_hazard_code
          AND t.language_code = 'en';
    END IF;

    -- Final fallback: return the code itself
    IF v_value IS NULL THEN
        RETURN p_hazard_code;
    END IF;

    RETURN v_value;
END;
$$ LANGUAGE plpgsql;


-- ================================================
-- SEED: District Translations (EN + FR)
-- Cameroon Far North — official languages
-- ================================================
INSERT INTO district_translations
    (district_id, language_code, name,
     description, translated_by)
SELECT
    d.id, 'en', d.admin2_name,
    'Far North Region district, Cameroon.',
    'OpenChildRisk OS Team'
FROM districts d
WHERE d.country_iso = 'CMR'
ON CONFLICT (district_id, language_code) DO NOTHING;

-- French translations for conflict-affected districts
INSERT INTO district_translations
    (district_id, language_code, name,
     description, translated_by)
SELECT
    d.id, 'fr', d.admin2_name,
    'District de la région Extrême-Nord, Cameroun.',
    'OpenChildRisk OS Team'
FROM districts d
WHERE d.country_iso = 'CMR'
ON CONFLICT (district_id, language_code) DO NOTHING;


-- ================================================
-- SEED: Hazard Type Translations (EN + FR + AR)
-- ================================================
INSERT INTO hazard_type_translations
    (hazard_type_id, language_code, label, translated_by)
VALUES
    -- English
    ((SELECT id FROM hazard_types WHERE code='cholera'),
     'en', 'Cholera', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='heat'),
     'en', 'Heatwave', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='flood'),
     'en', 'Flooding', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='drought'),
     'en', 'Drought', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='air_pollution'),
     'en', 'Air Pollution', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='malnutrition'),
     'en', 'Acute Malnutrition', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='measles'),
     'en', 'Measles Outbreak', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='conflict'),
     'en', 'Armed Conflict', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='displacement'),
     'en', 'Mass Displacement', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='compound'),
     'en', 'Compound Crisis', 'OpenChildRisk OS Team'),

    -- French
    ((SELECT id FROM hazard_types WHERE code='cholera'),
     'fr', 'Choléra', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='heat'),
     'fr', 'Vague de chaleur', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='flood'),
     'fr', 'Inondation', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='drought'),
     'fr', 'Sécheresse', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='air_pollution'),
     'fr', 'Pollution de l''air', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='malnutrition'),
     'fr', 'Malnutrition aiguë', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='measles'),
     'fr', 'Épidémie de rougeole', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='conflict'),
     'fr', 'Conflit armé', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='displacement'),
     'fr', 'Déplacement massif', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='compound'),
     'fr', 'Crise complexe', 'OpenChildRisk OS Team'),

    -- Arabic
    ((SELECT id FROM hazard_types WHERE code='cholera'),
     'ar', 'الكوليرا', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='heat'),
     'ar', 'موجة حر', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='flood'),
     'ar', 'فيضان', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='drought'),
     'ar', 'جفاف', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='malnutrition'),
     'ar', 'سوء التغذية الحاد', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='conflict'),
     'ar', 'نزاع مسلح', 'OpenChildRisk OS Team'),
    ((SELECT id FROM hazard_types WHERE code='displacement'),
     'ar', 'نزوح جماعي', 'OpenChildRisk OS Team')

ON CONFLICT (hazard_type_id, language_code) DO NOTHING;


-- ================================================
-- SEED: Program Translations (FR)
-- ================================================
INSERT INTO program_translations
    (program_id, language_code, name, translated_by)
VALUES
    ((SELECT id FROM programs
      WHERE name = 'Child Nutrition Emergency Response'),
     'fr',
     'Réponse d''urgence nutritionnelle pour l''enfant',
     'OpenChildRisk OS Team'),

    ((SELECT id FROM programs
      WHERE name = 'WASH Emergency Response'),
     'fr',
     'Réponse d''urgence WASH',
     'OpenChildRisk OS Team'),

    ((SELECT id FROM programs
      WHERE name = 'Disease Surveillance and Response'),
     'fr',
     'Surveillance et réponse aux maladies',
     'OpenChildRisk OS Team'),

    ((SELECT id FROM programs
      WHERE name = 'Education in Emergencies'),
     'fr',
     'Éducation en situations d''urgence',
     'OpenChildRisk OS Team'),

    ((SELECT id FROM programs
      WHERE name = 'Anticipatory Cash Transfer'),
     'fr',
     'Transfert monétaire anticipatoire',
     'OpenChildRisk OS Team')

ON CONFLICT (program_id, language_code) DO NOTHING;