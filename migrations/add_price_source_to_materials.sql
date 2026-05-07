-- Add price_source and price_note columns to materials_catalog
ALTER TABLE materials_catalog 
    ADD COLUMN IF NOT EXISTS price_source ENUM('manual', 'web_search') NOT NULL DEFAULT 'manual' AFTER default_price,
    ADD COLUMN IF NOT EXISTS price_note VARCHAR(255) DEFAULT NULL AFTER price_source;
