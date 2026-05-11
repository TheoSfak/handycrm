-- Normalize material units to the canonical dropdown values.
-- Canonical values: τεμ, μ, μ², μ³, κιλά, γρ, τόνοι, λίτρα, ml,
-- σετ, κουτί, σακί, παλέτα, ρολό, φύλλο, κιβώτιο, ώρες, ημέρες.

ALTER TABLE task_materials MODIFY COLUMN unit_type VARCHAR(50) NOT NULL DEFAULT 'τεμ';
ALTER TABLE task_materials MODIFY COLUMN unit VARCHAR(50) DEFAULT 'τεμ';
ALTER TABLE materials_catalog MODIFY COLUMN unit VARCHAR(50) DEFAULT 'τεμ';
ALTER TABLE materials MODIFY COLUMN unit VARCHAR(20) DEFAULT 'τεμ';
ALTER TABLE daily_task_materials MODIFY COLUMN unit VARCHAR(50) DEFAULT 'τεμ';

UPDATE materials_catalog SET unit = 'τεμ'
WHERE unit IS NULL OR TRIM(unit) = '' OR LOWER(TRIM(unit)) IN ('τεμ','τεμ.','τεμάχιο','τεμαχιο','τεμάχια','τεμαχια','pieces','piece','pcs','pc','tem');
UPDATE materials_catalog SET unit = 'μ'
WHERE LOWER(TRIM(unit)) IN ('μ','μ.','μέτρο','μετρο','μέτρα','μετρα','m','meter','meters','metre','metres');
UPDATE materials_catalog SET unit = 'μ²'
WHERE LOWER(TRIM(unit)) IN ('μ²','m²','τ.μ.','τ.μ','τμ','m2','sqm','τετρ.μ.','τ.μ.²');
UPDATE materials_catalog SET unit = 'μ³'
WHERE LOWER(TRIM(unit)) IN ('μ³','m³','κ.μ.','κ.μ','κμ','m3','cbm','κυβ.μ.');
UPDATE materials_catalog SET unit = 'κιλά'
WHERE LOWER(TRIM(unit)) IN ('κιλά','κιλα','κιλό','κιλο','kg','kgr','kilo','kilos','kilogram','kilograms','κιλά.');
UPDATE materials_catalog SET unit = 'γρ'
WHERE LOWER(TRIM(unit)) IN ('γρ','γρ.','γραμμάρια','γραμμαρια','γραμμάριο','γραμμαριο','gr','g','gram','grams');
UPDATE materials_catalog SET unit = 'τόνοι'
WHERE LOWER(TRIM(unit)) IN ('τόνοι','τονοι','τόνος','τονος','tn','ton','tons');
UPDATE materials_catalog SET unit = 'λίτρα'
WHERE LOWER(TRIM(unit)) IN ('λίτρα','λιτρα','λίτρο','λιτρο','λ','l','lt','liter','liters','litre','litres');
UPDATE materials_catalog SET unit = 'ml'
WHERE LOWER(TRIM(unit)) IN ('ml','milliliter','milliliters');
UPDATE materials_catalog SET unit = 'σετ'
WHERE LOWER(TRIM(unit)) IN ('σετ','set','sets','συσκευασία','συσκευασια','συσκευασίες','συσκευασιες');
UPDATE materials_catalog SET unit = 'κουτί'
WHERE LOWER(TRIM(unit)) IN ('κουτί','κουτι','κουτιά','κουτια','box','boxes');
UPDATE materials_catalog SET unit = 'σακί'
WHERE LOWER(TRIM(unit)) IN ('σακί','σακι','σακιά','σακια','bag','bags');
UPDATE materials_catalog SET unit = 'παλέτα'
WHERE LOWER(TRIM(unit)) IN ('παλέτα','παλετα','παλέτες','παλετες','pallet','pallets');
UPDATE materials_catalog SET unit = 'ρολό'
WHERE LOWER(TRIM(unit)) IN ('ρολό','ρολο','ρολά','ρολα','roll','rolls');
UPDATE materials_catalog SET unit = 'φύλλο'
WHERE LOWER(TRIM(unit)) IN ('φύλλο','φυλλο','φύλλα','φυλλα','sheet','sheets');
UPDATE materials_catalog SET unit = 'κιβώτιο'
WHERE LOWER(TRIM(unit)) IN ('κιβώτιο','κιβωτιο','κιβώτια','κιβωτια','carton','cartons');
UPDATE materials_catalog SET unit = 'ώρες'
WHERE LOWER(TRIM(unit)) IN ('ώρες','ωρες','ώρα','ωρα','hour','hours');
UPDATE materials_catalog SET unit = 'ημέρες'
WHERE LOWER(TRIM(unit)) IN ('ημέρες','ημερες','ημέρα','ημερα','day','days');

UPDATE task_materials SET unit = unit_type
WHERE (unit IS NULL OR TRIM(unit) = '') AND unit_type IS NOT NULL AND TRIM(unit_type) <> '';

UPDATE task_materials SET unit = 'τεμ'
WHERE unit IS NULL OR TRIM(unit) = '' OR LOWER(TRIM(unit)) IN ('τεμ','τεμ.','τεμάχιο','τεμαχιο','τεμάχια','τεμαχια','pieces','piece','pcs','pc','tem');
UPDATE task_materials SET unit = 'μ'
WHERE LOWER(TRIM(unit)) IN ('μ','μ.','μέτρο','μετρο','μέτρα','μετρα','m','meter','meters','metre','metres');
UPDATE task_materials SET unit = 'μ²'
WHERE LOWER(TRIM(unit)) IN ('μ²','m²','τ.μ.','τ.μ','τμ','m2','sqm','τετρ.μ.','τ.μ.²');
UPDATE task_materials SET unit = 'μ³'
WHERE LOWER(TRIM(unit)) IN ('μ³','m³','κ.μ.','κ.μ','κμ','m3','cbm','κυβ.μ.');
UPDATE task_materials SET unit = 'κιλά'
WHERE LOWER(TRIM(unit)) IN ('κιλά','κιλα','κιλό','κιλο','kg','kgr','kilo','kilos','kilogram','kilograms','κιλά.');
UPDATE task_materials SET unit = 'γρ'
WHERE LOWER(TRIM(unit)) IN ('γρ','γρ.','γραμμάρια','γραμμαρια','γραμμάριο','γραμμαριο','gr','g','gram','grams');
UPDATE task_materials SET unit = 'τόνοι'
WHERE LOWER(TRIM(unit)) IN ('τόνοι','τονοι','τόνος','τονος','tn','ton','tons');
UPDATE task_materials SET unit = 'λίτρα'
WHERE LOWER(TRIM(unit)) IN ('λίτρα','λιτρα','λίτρο','λιτρο','λ','l','lt','liter','liters','litre','litres');
UPDATE task_materials SET unit = 'ml'
WHERE LOWER(TRIM(unit)) IN ('ml','milliliter','milliliters');
UPDATE task_materials SET unit = 'σετ'
WHERE LOWER(TRIM(unit)) IN ('σετ','set','sets','συσκευασία','συσκευασια','συσκευασίες','συσκευασιες');
UPDATE task_materials SET unit = 'κουτί'
WHERE LOWER(TRIM(unit)) IN ('κουτί','κουτι','κουτιά','κουτια','box','boxes');
UPDATE task_materials SET unit = 'σακί'
WHERE LOWER(TRIM(unit)) IN ('σακί','σακι','σακιά','σακια','bag','bags');
UPDATE task_materials SET unit = 'παλέτα'
WHERE LOWER(TRIM(unit)) IN ('παλέτα','παλετα','παλέτες','παλετες','pallet','pallets');
UPDATE task_materials SET unit = 'ρολό'
WHERE LOWER(TRIM(unit)) IN ('ρολό','ρολο','ρολά','ρολα','roll','rolls');
UPDATE task_materials SET unit = 'φύλλο'
WHERE LOWER(TRIM(unit)) IN ('φύλλο','φυλλο','φύλλα','φυλλα','sheet','sheets');
UPDATE task_materials SET unit = 'κιβώτιο'
WHERE LOWER(TRIM(unit)) IN ('κιβώτιο','κιβωτιο','κιβώτια','κιβωτια','carton','cartons');
UPDATE task_materials SET unit = 'ώρες'
WHERE LOWER(TRIM(unit)) IN ('ώρες','ωρες','ώρα','ωρα','hour','hours');
UPDATE task_materials SET unit = 'ημέρες'
WHERE LOWER(TRIM(unit)) IN ('ημέρες','ημερες','ημέρα','ημερα','day','days');

UPDATE task_materials SET unit_type = unit
WHERE unit IS NOT NULL AND TRIM(unit) <> '';
UPDATE materials SET unit = 'τεμ'
WHERE unit IS NULL OR TRIM(unit) = '' OR LOWER(TRIM(unit)) IN ('τεμ','τεμ.','τεμάχιο','τεμαχιο','τεμάχια','τεμαχια','pieces','piece','pcs','pc','tem');
UPDATE materials SET unit = 'μ'
WHERE LOWER(TRIM(unit)) IN ('μ','μ.','μέτρο','μετρο','μέτρα','μετρα','m','meter','meters','metre','metres');
UPDATE materials SET unit = 'μ²'
WHERE LOWER(TRIM(unit)) IN ('μ²','m²','τ.μ.','τ.μ','τμ','m2','sqm','τετρ.μ.','τ.μ.²');
UPDATE materials SET unit = 'μ³'
WHERE LOWER(TRIM(unit)) IN ('μ³','m³','κ.μ.','κ.μ','κμ','m3','cbm','κυβ.μ.');
UPDATE materials SET unit = 'κιλά'
WHERE LOWER(TRIM(unit)) IN ('κιλά','κιλα','κιλό','κιλο','kg','kgr','kilo','kilos','kilogram','kilograms','κιλά.');
UPDATE materials SET unit = 'γρ'
WHERE LOWER(TRIM(unit)) IN ('γρ','γρ.','γραμμάρια','γραμμαρια','γραμμάριο','γραμμαριο','gr','g','gram','grams');
UPDATE materials SET unit = 'τόνοι'
WHERE LOWER(TRIM(unit)) IN ('τόνοι','τονοι','τόνος','τονος','tn','ton','tons');
UPDATE materials SET unit = 'λίτρα'
WHERE LOWER(TRIM(unit)) IN ('λίτρα','λιτρα','λίτρο','λιτρο','λ','l','lt','liter','liters','litre','litres');
UPDATE materials SET unit = 'ml'
WHERE LOWER(TRIM(unit)) IN ('ml','milliliter','milliliters');
UPDATE materials SET unit = 'σετ'
WHERE LOWER(TRIM(unit)) IN ('σετ','set','sets','συσκευασία','συσκευασια','συσκευασίες','συσκευασιες');
UPDATE materials SET unit = 'κουτί'
WHERE LOWER(TRIM(unit)) IN ('κουτί','κουτι','κουτιά','κουτια','box','boxes');
UPDATE materials SET unit = 'σακί'
WHERE LOWER(TRIM(unit)) IN ('σακί','σακι','σακιά','σακια','bag','bags');
UPDATE materials SET unit = 'παλέτα'
WHERE LOWER(TRIM(unit)) IN ('παλέτα','παλετα','παλέτες','παλετες','pallet','pallets');
UPDATE materials SET unit = 'ρολό'
WHERE LOWER(TRIM(unit)) IN ('ρολό','ρολο','ρολά','ρολα','roll','rolls');
UPDATE materials SET unit = 'φύλλο'
WHERE LOWER(TRIM(unit)) IN ('φύλλο','φυλλο','φύλλα','φυλλα','sheet','sheets');
UPDATE materials SET unit = 'κιβώτιο'
WHERE LOWER(TRIM(unit)) IN ('κιβώτιο','κιβωτιο','κιβώτια','κιβωτια','carton','cartons');
UPDATE materials SET unit = 'ώρες'
WHERE LOWER(TRIM(unit)) IN ('ώρες','ωρες','ώρα','ωρα','hour','hours');
UPDATE materials SET unit = 'ημέρες'
WHERE LOWER(TRIM(unit)) IN ('ημέρες','ημερες','ημέρα','ημερα','day','days');

UPDATE daily_task_materials SET unit = 'τεμ'
WHERE unit IS NULL OR TRIM(unit) = '' OR LOWER(TRIM(unit)) IN ('τεμ','τεμ.','τεμάχιο','τεμαχιο','τεμάχια','τεμαχια','pieces','piece','pcs','pc','tem');
UPDATE daily_task_materials SET unit = 'μ'
WHERE LOWER(TRIM(unit)) IN ('μ','μ.','μέτρο','μετρο','μέτρα','μετρα','m','meter','meters','metre','metres');
UPDATE daily_task_materials SET unit = 'μ²'
WHERE LOWER(TRIM(unit)) IN ('μ²','m²','τ.μ.','τ.μ','τμ','m2','sqm','τετρ.μ.','τ.μ.²');
UPDATE daily_task_materials SET unit = 'μ³'
WHERE LOWER(TRIM(unit)) IN ('μ³','m³','κ.μ.','κ.μ','κμ','m3','cbm','κυβ.μ.');
UPDATE daily_task_materials SET unit = 'κιλά'
WHERE LOWER(TRIM(unit)) IN ('κιλά','κιλα','κιλό','κιλο','kg','kgr','kilo','kilos','kilogram','kilograms','κιλά.');
UPDATE daily_task_materials SET unit = 'γρ'
WHERE LOWER(TRIM(unit)) IN ('γρ','γρ.','γραμμάρια','γραμμαρια','γραμμάριο','γραμμαριο','gr','g','gram','grams');
UPDATE daily_task_materials SET unit = 'τόνοι'
WHERE LOWER(TRIM(unit)) IN ('τόνοι','τονοι','τόνος','τονος','tn','ton','tons');
UPDATE daily_task_materials SET unit = 'λίτρα'
WHERE LOWER(TRIM(unit)) IN ('λίτρα','λιτρα','λίτρο','λιτρο','λ','l','lt','liter','liters','litre','litres');
UPDATE daily_task_materials SET unit = 'ml'
WHERE LOWER(TRIM(unit)) IN ('ml','milliliter','milliliters');
UPDATE daily_task_materials SET unit = 'σετ'
WHERE LOWER(TRIM(unit)) IN ('σετ','set','sets','συσκευασία','συσκευασια','συσκευασίες','συσκευασιες');
UPDATE daily_task_materials SET unit = 'κουτί'
WHERE LOWER(TRIM(unit)) IN ('κουτί','κουτι','κουτιά','κουτια','box','boxes');
UPDATE daily_task_materials SET unit = 'σακί'
WHERE LOWER(TRIM(unit)) IN ('σακί','σακι','σακιά','σακια','bag','bags');
UPDATE daily_task_materials SET unit = 'παλέτα'
WHERE LOWER(TRIM(unit)) IN ('παλέτα','παλετα','παλέτες','παλετες','pallet','pallets');
UPDATE daily_task_materials SET unit = 'ρολό'
WHERE LOWER(TRIM(unit)) IN ('ρολό','ρολο','ρολά','ρολα','roll','rolls');
UPDATE daily_task_materials SET unit = 'φύλλο'
WHERE LOWER(TRIM(unit)) IN ('φύλλο','φυλλο','φύλλα','φυλλα','sheet','sheets');
UPDATE daily_task_materials SET unit = 'κιβώτιο'
WHERE LOWER(TRIM(unit)) IN ('κιβώτιο','κιβωτιο','κιβώτια','κιβωτια','carton','cartons');
UPDATE daily_task_materials SET unit = 'ώρες'
WHERE LOWER(TRIM(unit)) IN ('ώρες','ωρες','ώρα','ωρα','hour','hours');
UPDATE daily_task_materials SET unit = 'ημέρες'
WHERE LOWER(TRIM(unit)) IN ('ημέρες','ημερες','ημέρα','ημερα','day','days');
