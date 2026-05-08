-- ============================================================
-- Migration: Normalize unit values across all material tables
-- Canonical values: τεμ, μ, μ², μ³, κιλά, γρ, τόνοι,
--                   λίτρα, ml, σετ, κουτί, σακί, παλέτα,
--                   ρολό, φύλλο, κιβώτιο, ώρες, ημέρες
-- ============================================================

-- ── materials_catalog ─────────────────────────────────────

-- τεμάχια variants → τεμ
UPDATE materials_catalog SET unit = 'τεμ'
WHERE unit IN ('τεμάχια','τεμ.','ΤΕΜ','TEM','Τεμ','pieces','τεμαχια','ΤΕΜΑΧΙΑ','τεμάχιο');

-- μέτρα variants → μ
UPDATE materials_catalog SET unit = 'μ'
WHERE unit IN ('μέτρα','μετρα','meters','metre','μέτρο','μετρο','Μέτρα','Μ');

-- τ.μ. variants → μ²
UPDATE materials_catalog SET unit = 'μ²'
WHERE unit IN ('τ.μ.','τμ','τ.μ','m2','sqm','τετρ.μ.','τ.μ.²');

-- κ.μ. variants → μ³
UPDATE materials_catalog SET unit = 'μ³'
WHERE unit IN ('κ.μ.','κμ','κ.μ','m3','cbm','κυβ.μ.');

-- kg variants → κιλά
UPDATE materials_catalog SET unit = 'κιλά'
WHERE unit IN ('kg','κιλό','κιλο','kilos','kilogram','Κιλά','κιλά.');

-- λίτρα variants → λίτρα
UPDATE materials_catalog SET unit = 'λίτρα'
WHERE unit IN ('λ','liters','litres','λιτρα','λίτρο','Λίτρα','l');

-- κουτί variants → κουτί
UPDATE materials_catalog SET unit = 'κουτί'
WHERE unit IN ('κουτιά','κουτια','boxes','box','Κουτί');

-- συσκευασίες → σετ
UPDATE materials_catalog SET unit = 'σετ'
WHERE unit IN ('συσκευασίες','συσκευασια','sets','Σετ');

-- ── task_materials ─────────────────────────────────────────

UPDATE task_materials SET unit = 'τεμ'
WHERE unit IN ('τεμάχια','τεμ.','ΤΕΜ','TEM','Τεμ','pieces','τεμαχια','ΤΕΜΑΧΙΑ','τεμάχιο');

UPDATE task_materials SET unit = 'μ'
WHERE unit IN ('μέτρα','μετρα','meters','metre','μέτρο','μετρο','Μέτρα','Μ');

UPDATE task_materials SET unit = 'μ²'
WHERE unit IN ('τ.μ.','τμ','τ.μ','m2','sqm','τετρ.μ.');

UPDATE task_materials SET unit = 'μ³'
WHERE unit IN ('κ.μ.','κμ','κ.μ','m3','cbm','κυβ.μ.');

UPDATE task_materials SET unit = 'κιλά'
WHERE unit IN ('kg','κιλό','κιλο','kilos','kilogram','Κιλά');

UPDATE task_materials SET unit = 'λίτρα'
WHERE unit IN ('λ','liters','litres','λιτρα','λίτρο','Λίτρα','l');

UPDATE task_materials SET unit = 'κουτί'
WHERE unit IN ('κουτιά','κουτια','boxes','box','Κουτί');

UPDATE task_materials SET unit = 'σετ'
WHERE unit IN ('συσκευασίες','συσκευασια','sets','Σετ');

-- ── materials (inventory) ──────────────────────────────────

UPDATE materials SET unit = 'τεμ'
WHERE unit IN ('τεμάχια','τεμ.','ΤΕΜ','TEM','Τεμ','pieces','τεμαχια','ΤΕΜΑΧΙΑ','τεμάχιο');

UPDATE materials SET unit = 'μ'
WHERE unit IN ('μέτρα','μετρα','meters','metre','μέτρο','μετρο','Μέτρα','Μ');

UPDATE materials SET unit = 'μ²'
WHERE unit IN ('τ.μ.','τμ','τ.μ','m2','sqm','τετρ.μ.');

UPDATE materials SET unit = 'μ³'
WHERE unit IN ('κ.μ.','κμ','κ.μ','m3','cbm','κυβ.μ.');

UPDATE materials SET unit = 'κιλά'
WHERE unit IN ('kg','κιλό','κιλο','kilos','kilogram','Κιλά');

UPDATE materials SET unit = 'λίτρα'
WHERE unit IN ('λ','liters','litres','λιτρα','λίτρο','Λίτρα','l');

UPDATE materials SET unit = 'κουτί'
WHERE unit IN ('κουτιά','κουτια','boxes','box','Κουτί');

UPDATE materials SET unit = 'σετ'
WHERE unit IN ('συσκευασίες','συσκευασια','sets','Σετ');
