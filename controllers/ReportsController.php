<?php
/**
 * Reports Controller
 * Handles analytics and reporting
 */

require_once __DIR__ . '/../classes/AuthMiddleware.php';

class ReportsController extends BaseController {
    
    /**
     * Show reports dashboard
     */
    public function index() {
        // Check permission for viewing reports
        if (!$this->isAdmin() && !$this->isSupervisor() && !can('reports.view')) {
            $this->redirect('/dashboard?error=unauthorized');
        }
        
        $user = $this->getCurrentUser();
        
        // Get date range from query params (default: current month)
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $data = [
            'title' => __('reports.title') . ' - ' . APP_NAME,
            'user' => $user,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue_data' => $this->getRevenueData($startDate, $endDate),
            'customer_data' => $this->getCustomerData($startDate, $endDate),
            'project_data' => $this->getProjectData($startDate, $endDate),
            'technician_data' => $this->getTechnicianData($startDate, $endDate),
            'summary' => $this->getSummaryStats($startDate, $endDate)
        ];
        
        $this->view('reports/index', $data);
    }
    
    /**
     * Get revenue analytics (from invoiced projects)
     */
    private function getRevenueData($startDate, $endDate) {
        $sql = "SELECT 
                    DATE_FORMAT(invoiced_at, '%Y-%m') as month,
                    COUNT(*) as total_invoices,
                    SUM(total_cost) as total_revenue,
                    SUM(material_cost + labor_cost) as subtotal,
                    SUM(total_cost - (material_cost + labor_cost)) as vat_amount,
                    AVG(total_cost) as avg_revenue
                FROM projects 
                WHERE status = 'invoiced'
                AND invoiced_at IS NOT NULL
                AND invoiced_at BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(invoiced_at, '%Y-%m')
                ORDER BY month ASC";
        
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * Get customer analytics
     */
    private function getCustomerData($startDate, $endDate) {
        // Top 10 customers by revenue (from invoiced projects)
        $topCustomers = "SELECT 
                            c.id,
                            CASE 
                                WHEN c.customer_type = 'company' THEN c.company_name
                                ELSE CONCAT(c.first_name, ' ', c.last_name)
                            END as customer_name,
                            c.customer_type,
                            COUNT(DISTINCT p.id) as total_invoices,
                            COALESCE(SUM(p.total_cost), 0) as total_revenue
                        FROM customers c
                        LEFT JOIN projects p ON c.id = p.customer_id 
                            AND p.status = 'invoiced'
                            AND p.invoiced_at IS NOT NULL
                            AND p.invoiced_at BETWEEN ? AND ?
                        WHERE c.is_active = 1
                        GROUP BY c.id
                        ORDER BY total_revenue DESC
                        LIMIT 10";
        
        $top = $this->db->fetchAll($topCustomers, [$startDate, $endDate]);
        
        // Customer growth
        $growth = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as new_customers
                    FROM customers
                    WHERE created_at BETWEEN ? AND ?
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month ASC";
        
        $growthData = $this->db->fetchAll($growth, [$startDate, $endDate]);
        
        // Customer type breakdown
        $typeBreakdown = "SELECT 
                            customer_type,
                            COUNT(*) as count
                        FROM customers
                        WHERE is_active = 1
                        GROUP BY customer_type";
        
        $types = $this->db->fetchAll($typeBreakdown);
        
        return [
            'top_customers' => $top,
            'growth' => $growthData,
            'type_breakdown' => $types
        ];
    }
    
    /**
     * Get project analytics
     */
    private function getProjectData($startDate, $endDate) {
        // Projects by status
        $statusBreakdown = "SELECT 
                                status,
                                COUNT(*) as count
                            FROM projects
                            WHERE created_at BETWEEN ? AND ?
                            GROUP BY status";
        
        $statuses = $this->db->fetchAll($statusBreakdown, [$startDate, $endDate]);
        
        // Revenue by project category (from invoiced projects)
        $categoryBreakdown = "SELECT 
                                p.category,
                                COUNT(DISTINCT p.id) as count,
                                AVG(p.total_cost) as avg_cost,
                                SUM(p.total_cost) as total_revenue
                            FROM projects p
                            WHERE p.status = 'invoiced'
                            AND p.invoiced_at IS NOT NULL
                            AND p.invoiced_at BETWEEN ? AND ?
                            GROUP BY p.category
                            ORDER BY total_revenue DESC";
        
        $categories = $this->db->fetchAll($categoryBreakdown, [$startDate, $endDate]);
        
        // Average project duration
        $avgDuration = "SELECT 
                            AVG(DATEDIFF(completion_date, start_date)) as avg_days
                        FROM projects
                        WHERE status = 'completed'
                        AND completion_date IS NOT NULL
                        AND start_date IS NOT NULL
                        AND completion_date BETWEEN ? AND ?";
        
        $duration = $this->db->fetchOne($avgDuration, [$startDate, $endDate]);
        
        // Completion rate
        $completionRate = "SELECT 
                            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                            COUNT(*) as total,
                            ROUND((COUNT(CASE WHEN status = 'completed' THEN 1 END) / COUNT(*)) * 100, 1) as rate
                        FROM projects
                        WHERE created_at BETWEEN ? AND ?";
        
        $completion = $this->db->fetchOne($completionRate, [$startDate, $endDate]);
        
        return [
            'status_breakdown' => $statuses,
            'category_breakdown' => $categories,
            'avg_duration' => $duration['avg_days'] ?? 0,
            'completion_rate' => $completion
        ];
    }
    
    /**
     * Get technician performance (based on invoiced projects)
     */
    private function getTechnicianData($startDate, $endDate) {
        $sql = "SELECT 
                    u.id,
                    CONCAT(u.first_name, ' ', u.last_name) as technician_name,
                    COUNT(DISTINCT p.id) as total_projects,
                    COUNT(DISTINCT CASE WHEN p.status = 'completed' THEN p.id END) as completed_projects,
                    ROUND((COUNT(DISTINCT CASE WHEN p.status = 'completed' THEN p.id END) / COUNT(DISTINCT p.id)) * 100, 1) as completion_rate,
                    COALESCE(SUM(CASE WHEN p.status = 'invoiced' THEN p.total_cost END), 0) as total_revenue,
                    AVG(DATEDIFF(p.completion_date, p.start_date)) as avg_completion_days
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN projects p ON u.id = p.assigned_technician
                    AND p.created_at BETWEEN ? AND ?
                WHERE r.name = 'technician'
                GROUP BY u.id
                HAVING total_projects > 0
                ORDER BY total_revenue DESC";
        
        return $this->db->fetchAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * Get summary statistics
     */
    private function getSummaryStats($startDate, $endDate) {
        $stats = [];
        
        // Total revenue (from invoiced projects)
        $revenue = "SELECT COALESCE(SUM(total_cost), 0) as total 
                    FROM projects 
                    WHERE status = 'invoiced'
                    AND invoiced_at IS NOT NULL
                    AND invoiced_at BETWEEN ? AND ?";
        $stats['total_revenue'] = $this->db->fetchOne($revenue, [$startDate, $endDate])['total'];
        
        // Total projects
        $projects = "SELECT COUNT(*) as total 
                     FROM projects 
                     WHERE created_at BETWEEN ? AND ?";
        $stats['total_projects'] = $this->db->fetchOne($projects, [$startDate, $endDate])['total'];
        
        // Active customers (with projects in period)
        $customers = "SELECT COUNT(DISTINCT customer_id) as total 
                      FROM projects 
                      WHERE created_at BETWEEN ? AND ?";
        $stats['active_customers'] = $this->db->fetchOne($customers, [$startDate, $endDate])['total'];
        
        // Total appointments
        $appointments = "SELECT COUNT(*) as total 
                         FROM appointments 
                         WHERE appointment_date BETWEEN ? AND ?";
        $stats['total_appointments'] = $this->db->fetchOne($appointments, [$startDate, $endDate])['total'];
        
        // Quotes conversion rate
        $conversion = "SELECT 
                        COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted,
                        COUNT(*) as total,
                        ROUND((COUNT(CASE WHEN status = 'accepted' THEN 1 END) / COUNT(*)) * 100, 1) as rate
                       FROM quotes
                       WHERE created_at BETWEEN ? AND ?";
        $conversionData = $this->db->fetchOne($conversion, [$startDate, $endDate]);
        $stats['quote_conversion_rate'] = $conversionData['rate'] ?? 0;
        
        return $stats;
    }
}
