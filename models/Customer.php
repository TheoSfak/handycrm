<?php
/**
 * Customer Model
 * Handles customer data and related operations
 */

class Customer extends BaseModel {
    protected $table = 'customers';
    
    /**
     * Get customer with communication history
     */
    public function getWithHistory($id) {
        $customer = $this->find($id);
        if (!$customer) {
            return null;
        }
        
        // Get communication history
        $sql = "SELECT cc.*, u.first_name, u.last_name 
                FROM customer_communications cc 
                JOIN users u ON cc.user_id = u.id 
                WHERE cc.customer_id = ? 
                ORDER BY cc.communication_date DESC";
        $customer['communications'] = $this->db->fetchAll($sql, [$id]);
        
        // Get projects
        $sql = "SELECT p.*, u.first_name as tech_first_name, u.last_name as tech_last_name 
                FROM projects p 
                JOIN users u ON p.assigned_technician = u.id 
                WHERE p.customer_id = ? 
                ORDER BY p.created_at DESC";
        $customer['projects'] = $this->db->fetchAll($sql, [$id]);
        
        // Get appointments
        $sql = "SELECT a.*, u.first_name as technician_first_name, u.last_name as technician_last_name,
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM appointments a 
                LEFT JOIN users u ON a.technician_id = u.id 
                WHERE a.customer_id = ? 
                ORDER BY a.appointment_date DESC";
        $customer['appointments'] = $this->db->fetchAll($sql, [$id]);
        
        // Get quotes
        $sql = "SELECT * FROM quotes WHERE customer_id = ? ORDER BY created_at DESC";
        $customer['quotes'] = $this->db->fetchAll($sql, [$id]);
        
        // Get invoices
        $sql = "SELECT * FROM invoices WHERE customer_id = ? ORDER BY created_at DESC";
        $customer['invoices'] = $this->db->fetchAll($sql, [$id]);
        
        return $customer;
    }
    
    /**
     * Search customers by name, phone, or email
     */
    public function search($term, $limit = 10) {
        $searchTerm = "%{$term}%";
        $sql = "SELECT id, first_name, last_name, company_name, phone, email, customer_type 
                FROM {$this->table} 
                WHERE is_active = 1 
                AND (first_name LIKE ? 
                     OR last_name LIKE ? 
                     OR company_name LIKE ? 
                     OR phone LIKE ? 
                     OR email LIKE ?) 
                ORDER BY first_name, last_name 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    /**
     * Get customers with pagination and search
     */
    public function getPaginated($page = 1, $perPage = ITEMS_PER_PAGE, $search = '', $type = '') {
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['c.is_active = 1'];
        
        if (!empty($search)) {
            $whereConditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($type)) {
            $whereConditions[] = "c.customer_type = ?";
            $params[] = $type;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} c WHERE {$whereClause}";
        $totalResult = $this->db->fetchOne($countSql, $params);
        $totalRecords = $totalResult['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        // Get records
        $sql = "SELECT c.*, u.first_name as created_by_name, u.last_name as created_by_lastname 
                FROM {$this->table} c 
                JOIN users u ON c.created_by = u.id 
                WHERE {$whereClause} 
                ORDER BY c.first_name, c.last_name 
                LIMIT {$perPage} OFFSET {$offset}";
        
        $records = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $records,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
    
    /**
     * Add communication record
     */
    public function addCommunication($customerId, $userId, $type, $subject, $description, $date = null) {
        $data = [
            'customer_id' => $customerId,
            'user_id' => $userId,
            'communication_type' => $type,
            'subject' => $subject,
            'description' => $description,
            'communication_date' => $date ?: date('Y-m-d H:i:s')
        ];
        
        $sql = "INSERT INTO customer_communications (customer_id, user_id, communication_type, subject, description, communication_date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, array_values($data));
    }
    
    /**
     * Get customer statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total customers
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1");
        $stats['total_customers'] = $result['total'];
        
        // New customers this month
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE is_active = 1 
                AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->fetchOne($sql);
        $stats['new_customers_month'] = $result['total'];
        
        // Individual vs Company breakdown
        $sql = "SELECT customer_type, COUNT(*) as count FROM {$this->table} 
                WHERE is_active = 1 GROUP BY customer_type";
        $breakdown = $this->db->fetchAll($sql);
        $stats['breakdown'] = [];
        foreach ($breakdown as $item) {
            $stats['breakdown'][$item['customer_type']] = $item['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get recent customers
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT id, first_name, last_name, company_name, customer_type, phone, created_at 
                FROM {$this->table} 
                WHERE is_active = 1 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Get full customer name
     */
    public function getFullName($customer) {
        if ($customer['customer_type'] === 'company' && !empty($customer['company_name'])) {
            return $customer['company_name'];
        }
        return trim($customer['first_name'] . ' ' . $customer['last_name']);
    }
    
    /**
     * Get customer by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
        $customer = $this->db->fetchOne($sql, [$slug]);
        
        if ($customer) {
            // Get communications
            $sql = "SELECT c.*, u.first_name, u.last_name 
                    FROM customer_communications c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.customer_id = ? 
                    ORDER BY c.created_at DESC";
            $customer['communications'] = $this->db->fetchAll($sql, [$customer['id']]);
            
            // Get projects
            $sql = "SELECT p.*, u.first_name as tech_first_name, u.last_name as tech_last_name 
                    FROM projects p 
                    JOIN users u ON p.assigned_technician = u.id 
                    WHERE p.customer_id = ? 
                    ORDER BY p.created_at DESC";
            $customer['projects'] = $this->db->fetchAll($sql, [$customer['id']]);
            
            // Get appointments
            $sql = "SELECT a.*, u.first_name as technician_first_name, u.last_name as technician_last_name,
                    CONCAT(u.first_name, ' ', u.last_name) as technician_name
                    FROM appointments a 
                    LEFT JOIN users u ON a.technician_id = u.id 
                    WHERE a.customer_id = ? 
                    ORDER BY a.appointment_date DESC";
            $customer['appointments'] = $this->db->fetchAll($sql, [$customer['id']]);
            
            // Get quotes
            $sql = "SELECT * FROM quotes WHERE customer_id = ? ORDER BY created_at DESC";
            $customer['quotes'] = $this->db->fetchAll($sql, [$customer['id']]);
            
            // Get invoices
            $sql = "SELECT * FROM invoices WHERE customer_id = ? ORDER BY created_at DESC";
            $customer['invoices'] = $this->db->fetchAll($sql, [$customer['id']]);
        }
        
        return $customer;
    }
    
    /**
     * Generate and save slug for customer
     */
    public function generateSlug($id, $firstName, $lastName, $companyName = null, $customerType = 'individual') {
        require_once __DIR__ . '/../helpers/SlugHelper.php';
        
        // Use company name for business customers, full name for individuals
        if ($customerType === 'business' && !empty($companyName)) {
            $name = $companyName;
        } else {
            $name = trim($firstName . ' ' . $lastName);
        }
        
        $slug = SlugHelper::createUniqueSlug($name, $this->table, $id);
        
        $sql = "UPDATE {$this->table} SET slug = ? WHERE id = ?";
        $this->db->execute($sql, [$slug, $id]);
        
        return $slug;
    }
    
    /**
     * Check for duplicate customers by email or mobile
     * Returns array of potential duplicates
     */
    public function findDuplicates($email = null, $mobile = null, $excludeId = null) {
        $conditions = [];
        $params = [];
        
        if (!empty($email)) {
            $conditions[] = "email = ?";
            $params[] = $email;
        }
        
        if (!empty($mobile)) {
            $conditions[] = "mobile = ?";
            $params[] = $mobile;
        }
        
        // If no email or mobile provided, return empty
        if (empty($conditions)) {
            return [];
        }
        
        // Build query with OR conditions
        $whereClause = '(' . implode(' OR ', $conditions) . ') AND is_active = 1';
        
        // Exclude specific customer ID if provided (for updates)
        if ($excludeId) {
            $whereClause .= ' AND id != ?';
            $params[] = $excludeId;
        }
        
        $sql = "SELECT id, first_name, last_name, company_name, customer_type, 
                       phone, mobile, email, city, created_at
                FROM {$this->table} 
                WHERE {$whereClause}
                ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}