<?php
/**
 * Dashboard Controller
 * Handles main dashboard and overview statistics
 */

class DashboardController extends BaseController {
    
    /**
     * Show main dashboard
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        // Ensure user is logged in and valid
        if (!$user || !is_array($user)) {
            $this->redirect('/login');
            return;
        }
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($user);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get upcoming appointments
        $upcomingAppointments = $this->getUpcomingAppointments($user);
        
        // Get notifications
        $notifications = $this->getNotifications($user['id']);
        
        $data = [
            'title' => __('dashboard.title') . ' - ' . APP_NAME,
            'user' => $user,
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'upcoming_appointments' => $upcomingAppointments,
            'notifications' => $notifications,
            'current_date' => date('d/m/Y'),
            'current_time' => date('H:i')
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Get dashboard statistics based on user role
     */
    private function getDashboardStats($user) {
        $database = new Database();
        $db = $database->connect();
        $stats = [];
        
        try {
            // Total customers
            $stmt = $db->query("SELECT COUNT(*) as total FROM customers WHERE is_active = 1");
            $stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // New customers this month
            $stmt = $db->query("SELECT COUNT(*) as total FROM customers 
                               WHERE is_active = 1 AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                               AND YEAR(created_at) = YEAR(CURRENT_DATE())");
            $stats['new_customers_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Active projects (new, in_progress, and invoiced - not completed or cancelled)
            $stmt = $db->query("SELECT COUNT(*) as total FROM projects 
                               WHERE status IN ('new', 'in_progress', 'invoiced')");
            $stats['active_projects'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Appointments today
            $stmt = $db->query("SELECT COUNT(*) as total FROM appointments 
                               WHERE DATE(appointment_date) = CURDATE() AND status != 'cancelled'");
            $stats['appointments_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Revenue this month - check if invoices table exists first
            $checkTable = $db->query("SHOW TABLES LIKE 'invoices'");
            error_log("Dashboard: Checking invoices table - rows: " . $checkTable->rowCount());
            if ($checkTable->rowCount() > 0) {
                $stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices 
                                   WHERE paid_date IS NOT NULL 
                                   AND MONTH(paid_date) = MONTH(CURRENT_DATE()) 
                                   AND YEAR(paid_date) = YEAR(CURRENT_DATE())");
                $stats['revenue_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Overdue invoices (sent or overdue status, and past due date)
                $stmt = $db->query("SELECT COUNT(*) as total FROM invoices 
                                   WHERE status IN ('sent', 'overdue') AND due_date < CURDATE()");
                $stats['overdue_invoices'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            } else {
                // If invoices table doesn't exist, calculate from invoiced/completed projects this month
                // Calculate correct total from materials + labor instead of stale total_cost
                $stmt = $db->query("
                    SELECT COALESCE(SUM(
                        COALESCE((SELECT SUM(tm.subtotal) 
                                  FROM task_materials tm 
                                  JOIN project_tasks pt ON tm.task_id = pt.id 
                                  WHERE pt.project_id = p.id), 0) +
                        COALESCE((SELECT SUM(tl.subtotal) 
                                  FROM task_labor tl 
                                  JOIN project_tasks pt ON tl.task_id = pt.id 
                                  WHERE pt.project_id = p.id), 0)
                    ), 0) as total 
                    FROM projects p
                    WHERE status IN ('completed', 'invoiced') 
                    AND (
                        (MONTH(updated_at) = MONTH(CURRENT_DATE()) AND YEAR(updated_at) = YEAR(CURRENT_DATE()))
                        OR (completion_date IS NOT NULL AND MONTH(completion_date) = MONTH(CURRENT_DATE()) AND YEAR(completion_date) = YEAR(CURRENT_DATE()))
                        OR (invoiced_at IS NOT NULL AND MONTH(invoiced_at) = MONTH(CURRENT_DATE()) AND YEAR(invoiced_at) = YEAR(CURRENT_DATE()))
                    )
                ");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats['revenue_month'] = $result ? $result['total'] : 0;
                error_log("Dashboard revenue calculation - Result: " . print_r($result, true) . " | revenue_month: " . $stats['revenue_month']);
                
                // Count completed/invoiced projects this month
                $stmt = $db->query("
                    SELECT COUNT(*) as count
                    FROM projects p
                    WHERE status IN ('completed', 'invoiced') 
                    AND (
                        (MONTH(updated_at) = MONTH(CURRENT_DATE()) AND YEAR(updated_at) = YEAR(CURRENT_DATE()))
                        OR (completion_date IS NOT NULL AND MONTH(completion_date) = MONTH(CURRENT_DATE()) AND YEAR(completion_date) = YEAR(CURRENT_DATE()))
                        OR (invoiced_at IS NOT NULL AND MONTH(invoiced_at) = MONTH(CURRENT_DATE()) AND YEAR(invoiced_at) = YEAR(CURRENT_DATE()))
                    )
                ");
                $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats['completed_projects_count'] = $countResult ? $countResult['count'] : 0;
                
                $stats['overdue_invoices'] = 0;
            }
            
            // Pending quotes (draft and sent) - check if table exists
            $checkQuotes = $db->query("SHOW TABLES LIKE 'quotes'");
            if ($checkQuotes->rowCount() > 0) {
                $stmt = $db->query("SELECT COUNT(*) as total FROM quotes WHERE status IN ('draft', 'sent')");
                $stats['pending_quotes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            } else {
                $stats['pending_quotes'] = 0;
            }
            
        } catch (PDOException $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            // Set defaults for any missing values
            $defaults = [
                'total_customers' => 0,
                'new_customers_month' => 0,
                'active_projects' => 0,
                'appointments_today' => 0,
                'revenue_month' => 0,
                'pending_quotes' => 0,
                'overdue_invoices' => 0
            ];
            // Merge with existing stats, keeping already retrieved values
            $stats = array_merge($defaults, $stats);
        }
        
        // Check if user is valid before accessing role
        if (!$user || !is_array($user) || !isset($user['role'])) {
            return $stats;
        }
        
        if ($user['role'] === 'admin') {
            // Admin sees overall statistics
            return $stats;
        } else {
            // Technicians see personal statistics
            $stats = $this->getTechnicianStats($user['id']);
        }
        
        return $stats;
    }
    
    /**
     * Get admin statistics
     */
    private function getAdminStats() {
        $stats = [];
        
        // Total customers
        $customerModel = new Customer();
        $customerStats = $customerModel->getStats();
        $stats['total_customers'] = $customerStats['total_customers'];
        $stats['new_customers_month'] = $customerStats['new_customers_month'];
        
        // Project statistics
        $projectModel = new Project();
        $projectStats = $projectModel->getStats();
        $stats['active_projects'] = $projectStats['active_projects'];
        $stats['revenue_month'] = $projectStats['revenue_month'];
        $stats['project_status'] = $projectStats['status_breakdown'];
        $stats['project_categories'] = $projectStats['category_breakdown'];
        
        // Appointment statistics
        $sql = "SELECT COUNT(*) as count FROM appointments 
                WHERE DATE(appointment_date) = CURDATE() 
                AND status IN ('scheduled', 'confirmed', 'in_progress')";
        $result = $this->db->fetchOne($sql);
        $stats['appointments_today'] = $result['count'];
        
        // This week's appointments
        $sql = "SELECT COUNT(*) as count FROM appointments 
                WHERE WEEK(appointment_date) = WEEK(CURDATE()) 
                AND YEAR(appointment_date) = YEAR(CURDATE())
                AND status IN ('scheduled', 'confirmed', 'in_progress')";
        $result = $this->db->fetchOne($sql);
        $stats['appointments_week'] = $result['count'];
        
        // Overdue invoices
        $sql = "SELECT COUNT(*) as count FROM invoices 
                WHERE due_date < CURDATE() 
                AND status NOT IN ('paid', 'cancelled')";
        $result = $this->db->fetchOne($sql);
        $stats['overdue_invoices'] = $result['count'];
        
        return $stats;
    }
    
    /**
     * Get technician personal statistics
     */
    private function getTechnicianStats($userId) {
        $userModel = new User();
        return $userModel->getUserStats($userId);
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities($user) {
        $database = new Database();
        $db = $database->connect();
        $activities = [];
        
        try {
            // Get recent customers
            $stmt = $db->query("SELECT id, first_name, last_name, company_name, customer_type, phone, created_at 
                               FROM customers 
                               ORDER BY created_at DESC 
                               LIMIT 3");
            $recentCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($recentCustomers as $customer) {
                $customerName = $customer['customer_type'] === 'company' && !empty($customer['company_name']) 
                    ? $customer['company_name'] 
                    : $customer['first_name'] . ' ' . $customer['last_name'];
                
                $activities[] = [
                    'type' => 'customer',
                    'icon' => 'fas fa-user-plus',
                    'title' => 'Νέος πελάτης: ' . $customerName,
                    'description' => 'Τηλέφωνο: ' . $customer['phone'],
                    'date' => $customer['created_at'],
                    'link' => '?route=/customers/show&id=' . $customer['id']
                ];
            }
            
            // Get recent projects
            $stmt = $db->query("SELECT p.id, p.title, p.status, p.created_at, 
                               c.first_name, c.last_name, c.company_name, c.customer_type 
                               FROM projects p 
                               JOIN customers c ON p.customer_id = c.id 
                               ORDER BY p.created_at DESC 
                               LIMIT 3");
            $recentProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($recentProjects as $project) {
                $customerName = $project['customer_type'] === 'company' && !empty($project['company_name']) 
                    ? $project['company_name'] 
                    : $project['first_name'] . ' ' . $project['last_name'];
                
                $activities[] = [
                    'type' => 'project',
                    'icon' => 'fas fa-project-diagram',
                    'title' => 'Νέο έργο: ' . $project['title'],
                    'description' => 'Πελάτης: ' . $customerName,
                    'date' => $project['created_at'],
                    'link' => '?route=/projects/show&id=' . $project['id']
                ];
            }
            
            // Sort by date
            usort($activities, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            // Return only 5 most recent
            return array_slice($activities, 0, 5);
            
        } catch (PDOException $e) {
            error_log("Recent activities error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Deprecated method - keeping for compatibility
     */
    private function getRecentActivitiesOld($user) {
        $activities = [];
        
        // Safety check
        if (!$user || !is_array($user) || !isset($user['role'])) {
            return $activities;
        }
        
        if ($user['role'] === 'admin') {
            // Technician's recent projects
            $sql = "SELECT p.*, c.first_name, c.last_name, c.company_name, c.customer_type 
                    FROM projects p 
                    JOIN customers c ON p.customer_id = c.id 
                    WHERE p.assigned_technician = ? 
                    ORDER BY p.updated_at DESC 
                    LIMIT 5";
            $recentProjects = $this->db->fetchAll($sql, [$user['id']]);
            
            foreach ($recentProjects as $project) {
                $customerName = $project['customer_type'] === 'company' && !empty($project['company_name']) 
                    ? $project['company_name'] 
                    : $project['first_name'] . ' ' . $project['last_name'];
                
                $activities[] = [
                    'type' => 'project',
                    'icon' => 'fas fa-tools',
                    'title' => $project['title'],
                    'description' => 'Πελάτης: ' . $customerName . ' - ' . ucfirst($project['status']),
                    'date' => $project['updated_at'],
                    'link' => '/projects/' . $project['id']
                ];
            }
        }
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return array_slice($activities, 0, 5);
    }
    
    /**
     * Get upcoming appointments
     */
    private function getUpcomingAppointments($user) {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $sql = "SELECT a.*, 
                           c.first_name, c.last_name, c.company_name, c.customer_type, c.phone,
                           u.first_name as tech_first_name, u.last_name as tech_last_name
                    FROM appointments a 
                    JOIN customers c ON a.customer_id = c.id 
                    JOIN users u ON a.technician_id = u.id 
                    WHERE a.appointment_date >= NOW() 
                    AND a.status IN ('scheduled', 'confirmed') 
                    ORDER BY a.appointment_date ASC 
                    LIMIT 5";
            
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Upcoming appointments error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user notifications
     */
    private function getNotifications($userId) {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ? 
                    AND is_read = 0 
                    ORDER BY created_at DESC 
                    LIMIT 5";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark notification as read (AJAX)
     */
    public function markNotificationRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $notificationId = $_POST['notification_id'] ?? null;
        
        if (!$notificationId) {
            $this->json(['success' => false, 'message' => 'Notification ID required'], 400);
        }
        
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $this->db->execute($sql, [$notificationId, $_SESSION['user_id']]);
        
        if ($this->db->rowCount($stmt) > 0) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
    }
}