-- Add company_display_name setting
-- This field allows customizing the brand name displayed throughout the app

INSERT INTO settings (setting_key, setting_value, setting_type, description)
VALUES ('company_display_name', '', 'string', 'Διακριτικός Τίτλος Εταιρίας (αν είναι κενό χρησιμοποιείται το HandyCRM)')
ON DUPLICATE KEY UPDATE description = 'Διακριτικός Τίτλος Εταιρίας (αν είναι κενό χρησιμοποιείται το HandyCRM)';
