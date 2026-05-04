-- ================================================
-- OpenChildRisk OS — Cameroon Seed Data
-- Far North Region Pilot
-- Sources: UNICEF MICS 2019, WorldPop 2020, WHO JMP 2022
-- ================================================

-- Step 1: Country
INSERT INTO countries (iso, name)
VALUES ('CMR', 'Cameroon')
ON CONFLICT (iso) DO NOTHING;

-- Step 2: Organizations
INSERT INTO organizations (name, short_name, type, country_id)
VALUES
(
    'United Nations Children''s Fund',
    'UNICEF',
    'UN',
    (SELECT id FROM countries WHERE iso = 'CMR')
),
(
    'World Health Organization',
    'WHO',
    'UN',
    (SELECT id FROM countries WHERE iso = 'CMR')
),
(
    'World Food Programme',
    'WFP',
    'UN',
    (SELECT id FROM countries WHERE iso = 'CMR')
),
(
    'Ministry of Public Health Cameroon',
    'MINSANTE',
    'Government',
    (SELECT id FROM countries WHERE iso = 'CMR')
),
(
    'Ministry of Basic Education Cameroon',
    'MINEDUB',
    'Government',
    (SELECT id FROM countries WHERE iso = 'CMR')
)
ON CONFLICT DO NOTHING;

-- Step 3: Districts
INSERT INTO districts (
    country_iso,
    country_id,
    admin1_name,
    admin2_code,
    admin2_name,
    centroid_lat,
    centroid_lon,
    children_under5,
    children_5_14,
    under5_population,
    wash_coverage,
    sanitation_coverage,
    health_facility_density,
    social_protection_coverage,
    conflict_affected
)
SELECT
    'CMR',
    (SELECT id FROM countries WHERE iso = 'CMR'),
    admin1_name,
    admin2_code,
    admin2_name,
    centroid_lat,
    centroid_lon,
    children_under5,
    children_5_14,
    children_under5,
    wash_coverage,
    sanitation_coverage,
    health_facility_density,
    social_protection_coverage,
    conflict_affected
FROM (VALUES
    ('Far North','FN-MAR','Maroua',
     10.5900, 14.3200, 45000, 98000,
     0.41, 0.18, 0.6, 0.12, false),
    ('Far North','FN-MOR','Mora',
     11.0456, 14.1419, 38000, 82000,
     0.28, 0.11, 0.3, 0.08, true),
    ('Far North','FN-KOU','Kousseri',
     12.0760, 15.0290, 41000, 89000,
     0.32, 0.09, 0.4, 0.10, true),
    ('Far North','FN-MAK','Makary',
     12.3500, 14.9800, 29000, 63000,
     0.21, 0.07, 0.2, 0.06, true),
    ('Far North','FN-YAG','Yagoua',
     10.3400, 15.2300, 33000, 71000,
     0.35, 0.13, 0.3, 0.09, false),
    ('Far North','FN-KAE','Kaele',
     10.1000, 14.4500, 27000, 58000,
     0.29, 0.10, 0.3, 0.07, false),
    ('Far North','FN-MER','Meri',
     10.7200, 14.1800, 22000, 47000,
     0.19, 0.06, 0.2, 0.05, false),
    ('Far North','FN-MIN','Mindif',
     10.4000, 14.4300, 19000, 41000,
     0.23, 0.08, 0.2, 0.05, false),
    ('Far North','FN-TOK','Tokombere',
     10.7500, 14.0200, 24000, 52000,
     0.17, 0.05, 0.2, 0.04, false),
    ('Far North','FN-WAZ','Waza',
     11.3900, 14.6400, 16000, 34000,
     0.15, 0.04, 0.1, 0.03, true)
) AS d(
    admin1_name, admin2_code, admin2_name,
    centroid_lat, centroid_lon,
    children_under5, children_5_14,
    wash_coverage, sanitation_coverage,
    health_facility_density,
    social_protection_coverage,
    conflict_affected
);

-- Step 4: Programs
INSERT INTO programs (
    name,
    description,
    domain,
    created_by_org_id
)
VALUES
(
    'Child Nutrition Emergency Response',
    'Acute malnutrition treatment and prevention for under-5',
    'nutrition',
    (SELECT id FROM organizations WHERE short_name = 'UNICEF')
),
(
    'WASH Emergency Response',
    'Safe water and sanitation in climate shock areas',
    'wash',
    (SELECT id FROM organizations WHERE short_name = 'UNICEF')
),
(
    'Disease Surveillance and Response',
    'Cholera, measles and climate-driven disease monitoring',
    'health',
    (SELECT id FROM organizations WHERE short_name = 'WHO')
),
(
    'Education in Emergencies',
    'School continuity during climat