-- HandyCRM Test Data Cleanup
-- Removes all test records created by test-agent.js

-- Delete test customers (name contains TestBot or AutoTest)
DELETE FROM customers 
WHERE 
    (first_name LIKE '%TestBot%' OR last_name LIKE '%AutoTest%')
    OR email LIKE 'aitest%@test.com'
    OR notes LIKE '%AI automated test%';

-- Delete test projects (title contains AI Test)
DELETE FROM projects 
WHERE title LIKE '%AI Test Project%' 
   OR description LIKE '%AI automated test%';

-- Delete test quotes (title contains AI Test)
DELETE FROM quotes 
WHERE title LIKE '%AI Test Quote%';

-- Delete test materials (name contains AI Test)
DELETE FROM materials_catalog 
WHERE name LIKE '%AI Test Material%';

-- Delete test transformer maintenance records
DELETE FROM transformer_maintenance 
WHERE notes LIKE '%AI automated test%';

-- Show what remains
SELECT 'Remaining Customers' as Type, COUNT(*) as Count FROM customers WHERE deleted_at IS NULL
UNION ALL
SELECT 'Remaining Projects', COUNT(*) FROM projects WHERE deleted_at IS NULL
UNION ALL
SELECT 'Remaining Quotes', COUNT(*) FROM quotes WHERE deleted_at IS NULL;
