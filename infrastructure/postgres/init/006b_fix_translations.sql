-- ================================================
-- OpenChildRisk OS — Fix Corrupted Translations
-- Reason: Notepad ANSI encoding corrupted
--         accented characters during initial seed
-- Fix: Delete and re-insert using UTF-8
-- ================================================

-- Delete corrupted French hazard translations
DELETE FROM hazard_type_translations
WHERE language_code = 'fr';

-- Delete corrupted Arabic hazard translations
DELETE FROM hazard_type_translations
WHERE language_code = 'ar';

-- Re-insert French hazard translations (UTF-8 correct)
INSERT INTO hazard_type_translations
    (hazard_type_id, language_code, label, translated_by)
VALUES
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
     'fr', 'Crise complexe', 'OpenChildRisk OS Team')

ON CONFLICT (hazard_type_id, language_code) DO UPDATE
    SET label = EXCLUDED.label,
        updated_at = NOW();


-- Re-insert Arabic hazard translations (UTF-8 correct)
INSERT INTO hazard_type_translations
    (hazard_type_id, language_code, label, translated_by)
VALUES
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

ON CONFLICT (hazard_type_id, language_code) DO UPDATE
    SET label = EXCLUDED.label,
        updated_at = NOW();


-- Verify fix
SELECT
    h.code,
    t.language_code,
    t.label,
    length(t.label) as char_length,
    octet_length(t.label) as byte_length
FROM hazard_type_translations t
JOIN hazard_types h ON h.id = t.hazard_type_id
WHERE t.language_code IN ('fr', 'ar')
ORDER BY t.language_code, h.code;