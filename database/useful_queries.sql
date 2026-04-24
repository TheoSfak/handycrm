-- ============================================
-- HandyCRM - Useful SQL Queries
-- Quick reference queries for database analysis
-- ============================================

-- ============================================
-- 📊 CUSTOMER ANALYTICS
-- ============================================

-- Top 10 customers by total revenue
SELECT 
    c.id,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    c.customer_type,
    COUNT(DISTINCT p.id) as total_projects,
    COUNT(DISTINCT pay.id) as total_payments,
    COALESCE(SUM(pay.amount), 0) as total_revenue,
    c.phone,
    c.email
FROM customers c
LEFT JOIN projects p ON c.id = p.customer_id
LEFT JOIN payments pay ON c.id = pay.customer_id
WHERE c.deleted_at IS NULL
GROUP BY c.id
ORDER BY total_revenue DESC
LIMIT 10;

-- Customers with pending projects
SELECT 
    c.id,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    COUNT(*) as pending_projects,
    c.phone,
    c.email
FROM customers c
JOIN projects p ON c.id = p.customer_id
WHERE p.status IN ('pending', 'in_progress')
  AND c.deleted_at IS NULL
  AND p.deleted_at IS NULL
GROUP BY c.id
ORDER BY pending_projects DESC;

-- Customers with no recent activity (90+ days)
SELECT 
    c.id,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    c.phone,
    c.email,
    MAX(p.created_at) as last_project_date,
    DATEDIFF(CURRENT_DATE, MAX(p.created_at)) as days_since_last_project
FROM customers c
LEFT JOIN projects p ON c.id = p.customer_id
WHERE c.deleted_at IS NULL
GROUP BY c.id
HAVING last_project_date IS NULL OR days_since_last_project > 90
ORDER BY days_since_last_project DESC;


-- ============================================
-- 💼 PROJECT ANALYTICS
-- ============================================

-- Projects by status summary
SELECT 
    p.status,
    COUNT(*) as count,
    SUM(p.total_cost) as total_value,
    AVG(p.total_cost) as avg_value,
    SUM(p.material_cost) as total_materials,
    SUM(p.labor_cost) as total_labor
FROM projects p
WHERE p.deleted_at IS NULL
GROUP BY p.status
ORDER BY FIELD(p.status, 'pending', 'in_progress', 'completed', 'cancelled');

-- Projects with highest material costs
SELECT 
    p.id,
    p.title,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    p.material_cost,
    p.labor_cost,
    p.total_cost,
    p.status,
    CONCAT(u.first_name, ' ', u.last_name) as technician
FROM projects p
JOIN customers c ON p.customer_id = c.id
LEFT JOIN users u ON p.assigned_to = u.id
WHERE p.deleted_at IS NULL
ORDER BY p.material_cost DESC
LIMIT 20;

-- Overdue projects (completed late or still in progress past deadline)
SELECT 
    p.id,
    p.title,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    p.deadline,
    p.completed_at,
    DATEDIFF(COALESCE(p.completed_at, CURRENT_DATE), p.deadline) as days_overdue,
    p.status,
    p.total_cost
FROM projects p
JOIN customers c ON p.customer_id = c.id
WHERE p.deleted_at IS NULL
  AND p.deadline < CURRENT_DATE
  AND (p.status != 'completed' OR p.completed_at > p.deadline)
ORDER BY days_overdue DESC;

-- Projects profit margin (if you track costs)
SELECT 
    p.id,
    p.title,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    p.material_cost + p.labor_cost as total_cost,
    p.total_cost as project_value,
    p.total_cost - (p.material_cost + p.labor_cost) as profit,
    ROUND(((p.total_cost - (p.material_cost + p.labor_cost)) / p.total_cost) * 100, 2) as profit_margin_percent,
    p.status
FROM projects p
JOIN customers c ON p.customer_id = c.id
WHERE p.deleted_at IS NULL
  AND p.total_cost > 0
ORDER BY profit_margin_percent DESC
LIMIT 20;


-- ============================================
-- 💰 PAYMENT ANALYTICS
-- ============================================

-- Monthly revenue report (current year)
SELECT 
    DATE_FORMAT(pay.payment_date, '%Y-%m') as month,
    COUNT(*) as payment_count,
    SUM(pay.amount) as total_revenue,
    AVG(pay.amount) as avg_payment
FROM payments pay
WHERE YEAR(pay.payment_date) = YEAR(CURRENT_DATE)
GROUP BY month
ORDER BY month DESC;

-- Unpaid invoices/projects
SELECT 
    p.id,
    p.title,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    p.total_cost as amount_due,
    p.deadline,
    DATEDIFF(CURRENT_DATE, p.deadline) as days_overdue,
    c.phone,
    c.email
FROM projects p
JOIN customers c ON p.customer_id = c.id
WHERE p.payment_status != 'paid'
  AND p.deleted_at IS NULL
  AND p.status = 'completed'
ORDER BY days_overdue DESC;

-- Payment methods breakdown
SELECT 
    pay.payment_method,
    COUNT(*) as transaction_count,
    SUM(pay.amount) as total_amount,
    AVG(pay.amount) as avg_amount
FROM payments pay
WHERE pay.payment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
GROUP BY pay.payment_method
ORDER BY total_amount DESC;

-- Customers with outstanding balances
SELECT 
    c.id,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    SUM(p.total_cost) as total_invoiced,
    COALESCE(SUM(pay.amount), 0) as total_paid,
    SUM(p.total_cost) - COALESCE(SUM(pay.amount), 0) as balance_due,
    c.phone,
    c.email
FROM customers c
JOIN projects p ON c.id = p.customer_id
LEFT JOIN payments pay ON c.id = pay.customer_id
WHERE c.deleted_at IS NULL
  AND p.deleted_at IS NULL
GROUP BY c.id
HAVING balance_due > 0
ORDER BY balance_due DESC;


-- ============================================
-- 👨‍🔧 TECHNICIAN PERFORMANCE
-- ============================================

-- Technician workload summary
SELECT 
    u.id,
    CONCAT(u.first_name, ' ', u.last_name) as technician_name,
    r.display_name as role,
    COUNT(DISTINCT CASE WHEN p.status = 'in_progress' THEN p.id END) as active_projects,
    COUNT(DISTINCT CASE WHEN p.status = 'completed' THEN p.id END) as completed_projects,
    COUNT(DISTINCT a.id) as upcoming_appointments,
    u.hourly_rate,
    u.phone
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN projects p ON u.id = p.assigned_to AND p.deleted_at IS NULL
LEFT JOIN appointments a ON u.id = a.technician_id AND a.appointment_date >= CURRENT_DATE
WHERE u.is_active = 1
  AND r.name IN ('technician', 'supervisor')
GROUP BY u.id
ORDER BY active_projects DESC;

-- Technician revenue generated (last 6 months)
SELECT 
    u.id,
    CONCAT(u.first_name, ' ', u.last_name) as technician_name,
    COUNT(DISTINCT p.id) as projects_completed,
    SUM(p.total_cost) as total_revenue_generated,
    AVG(p.total_cost) as avg_project_value,
    SUM(p.labor_cost) as total_labor_charged
FROM users u
JOIN projects p ON u.id = p.assigned_to
WHERE p.status = 'completed'
  AND p.completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
  AND p.deleted_at IS NULL
GROUP BY u.id
ORDER BY total_revenue_generated DESC;

-- Appointments this week by technician
SELECT 
    u.id,
    CONCAT(u.first_name, ' ', u.last_name) as technician_name,
    DATE_FORMAT(a.appointment_date, '%Y-%m-%d %H:%i') as appointment_time,
    a.title,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    p.title as project_title,
    a.status
FROM appointments a
JOIN users u ON a.technician_id = u.id
LEFT JOIN customers c ON a.customer_id = c.id
LEFT JOIN projects p ON a.project_id = p.id
WHERE a.appointment_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
ORDER BY a.appointment_date, u.last_name;


-- ============================================
-- 📦 MATERIALS & INVENTORY
-- ============================================

-- Most used materials (from project tasks)
SELECT 
    tm.material_name,
    COUNT(DISTINCT tm.task_id) as times_used,
    COUNT(DISTINCT pt.project_id) as projects_used_in,
    SUM(tm.quantity) as total_quantity,
    tm.unit_type
FROM task_materials tm
JOIN project_tasks pt ON tm.task_id = pt.id
GROUP BY tm.material_name, tm.unit_type
ORDER BY times_used DESC
LIMIT 30;

-- Materials catalog - items needing price updates
SELECT 
    mc.id,
    mc.name,
    mc.sku,
    mc.price,
    mc.updated_at,
    DATEDIFF(CURRENT_DATE, mc.updated_at) as days_since_update,
    cat.name as category
FROM materials_catalog mc
LEFT JOIN material_categories cat ON mc.category_id = cat.id
WHERE mc.price IS NULL 
   OR mc.updated_at < DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
ORDER BY days_since_update DESC
LIMIT 50;


-- ============================================
-- 📝 QUOTES & CONVERSIONS
-- ============================================

-- Quote conversion rate
SELECT 
    COUNT(*) as total_quotes,
    SUM(CASE WHEN q.status = 'accepted' THEN 1 ELSE 0 END) as accepted_quotes,
    SUM(CASE WHEN q.status = 'rejected' THEN 1 ELSE 0 END) as rejected_quotes,
    SUM(CASE WHEN q.status = 'pending' THEN 1 ELSE 0 END) as pending_quotes,
    ROUND((SUM(CASE WHEN q.status = 'accepted' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as conversion_rate_percent,
    SUM(q.total_amount) as total_quoted_value,
    SUM(CASE WHEN q.status = 'accepted' THEN q.total_amount ELSE 0 END) as accepted_value
FROM quotes q
WHERE q.issue_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH);

-- Quotes pending response (older than 7 days)
SELECT 
    q.id,
    q.quote_number,
    q.title,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    q.total_amount,
    q.issue_date,
    DATEDIFF(CURRENT_DATE, q.issue_date) as days_pending,
    c.phone,
    c.email
FROM quotes q
JOIN customers c ON q.customer_id = c.id
WHERE q.status = 'pending'
  AND q.issue_date < DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
ORDER BY days_pending DESC;


-- ============================================
-- 🔧 MAINTENANCE TRACKING
-- ============================================

-- Upcoming transformer maintenance
SELECT 
    tm.id,
    tm.transformer_name,
    tm.location,
    tm.maintenance_date as last_maintenance,
    tm.next_maintenance_date,
    DATEDIFF(tm.next_maintenance_date, CURRENT_DATE) as days_until_due,
    CASE 
        WHEN c.customer_type = 'company' THEN c.company_name
        ELSE CONCAT(c.first_name, ' ', c.last_name)
    END as customer_name,
    tm.status
FROM transformer_maintenance tm
LEFT JOIN customers c ON tm.customer_id = c.id
WHERE tm.next_maintenance_date >= CURRENT_DATE
  AND tm.next_maintenance_date <= DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY)
  AND tm.status != 'completed'
ORDER BY tm.next_maintenance_date;


-- ============================================
-- 📊 DASHBOARD SUMMARY QUERIES
-- ============================================

-- Today's overview
SELECT 
    (SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL AND status = 'in_progress') as active_projects,
    (SELECT COUNT(*) FROM appointments WHERE appointment_date = CURRENT_DATE) as today_appointments,
    (SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL AND payment_status != 'paid' AND status = 'completed') as unpaid_invoices,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_date = CURRENT_DATE) as today_revenue,
    (SELECT COUNT(*) FROM customers WHERE deleted_at IS NULL) as total_customers,
    (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users;

-- This month vs last month
SELECT 
    'This Month' as period,
    COUNT(DISTINCT p.id) as projects_completed,
    COALESCE(SUM(pay.amount), 0) as revenue,
    COUNT(DISTINCT pay.id) as payments_received,
    COUNT(DISTINCT c.id) as new_customers
FROM projects p
LEFT JOIN payments pay ON MONTH(pay.payment_date) = MONTH(CURRENT_DATE) AND YEAR(pay.payment_date) = YEAR(CURRENT_DATE)
LEFT JOIN customers c ON MONTH(c.created_at) = MONTH(CURRENT_DATE) AND YEAR(c.created_at) = YEAR(CURRENT_DATE)
WHERE p.status = 'completed'
  AND MONTH(p.completed_at) = MONTH(CURRENT_DATE)
  AND YEAR(p.completed_at) = YEAR(CURRENT_DATE)

UNION ALL

SELECT 
    'Last Month' as period,
    COUNT(DISTINCT p.id) as projects_completed,
    COALESCE(SUM(pay.amount), 0) as revenue,
    COUNT(DISTINCT pay.id) as payments_received,
    COUNT(DISTINCT c.id) as new_customers
FROM projects p
LEFT JOIN payments pay ON MONTH(pay.payment_date) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) 
    AND YEAR(pay.payment_date) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
LEFT JOIN customers c ON MONTH(c.created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
    AND YEAR(c.created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
WHERE p.status = 'completed'
  AND MONTH(p.completed_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
  AND YEAR(p.completed_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH));


-- ============================================
-- 🔍 DATA QUALITY CHECKS
-- ============================================

-- Projects missing critical information
SELECT 
    p.id,
    p.title,
    p.status,
    CASE WHEN p.customer_id IS NULL THEN 'Missing Customer' END as issue_1,
    CASE WHEN p.assigned_to IS NULL THEN 'Missing Technician' END as issue_2,
    CASE WHEN p.deadline IS NULL THEN 'Missing Deadline' END as issue_3,
    CASE WHEN p.total_cost = 0 OR p.total_cost IS NULL THEN 'Missing Cost' END as issue_4
FROM projects p
WHERE p.deleted_at IS NULL
  AND (p.customer_id IS NULL 
    OR p.assigned_to IS NULL 
    OR p.deadline IS NULL 
    OR p.total_cost = 0 
    OR p.total_cost IS NULL);

-- Duplicate customer detection (similar names/phone)
SELECT 
    c1.id as customer_1_id,
    CASE 
        WHEN c1.customer_type = 'company' THEN c1.company_name
        ELSE CONCAT(c1.first_name, ' ', c1.last_name)
    END as customer_1_name,
    c1.phone as customer_1_phone,
    c2.id as customer_2_id,
    CASE 
        WHEN c2.customer_type = 'company' THEN c2.company_name
        ELSE CONCAT(c2.first_name, ' ', c2.last_name)
    END as customer_2_name,
    c2.phone as customer_2_phone
FROM customers c1
JOIN customers c2 ON c1.id < c2.id
WHERE c1.deleted_at IS NULL 
  AND c2.deleted_at IS NULL
  AND (
    c1.phone = c2.phone 
    OR (c1.email = c2.email AND c1.email IS NOT NULL)
    OR (c1.company_name = c2.company_name AND c1.customer_type = 'company')
  )
ORDER BY c1.id;
