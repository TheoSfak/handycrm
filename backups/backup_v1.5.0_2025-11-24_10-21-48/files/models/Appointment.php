<?php
/**
 * Appointment Model
 * Handles appointment data and related operations
 */

class Appointment extends BaseModel {
    protected $table = 'appointments';
    
    /**
     * Get appointment with customer and technician details
     */
    public function getWithDetails($id) {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $sql = "SELECT a.*, 
                           CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                           c.first_name as customer_first_name, 
                           c.last_name as customer_last_name, 
                           c.company_name as customer_company_name,
                           c.customer_type,
                           c.phone as customer_phone,
                           c.email as customer_email,
                           c.address as customer_address,
                           t.first_name as tech_first_name, 
                           t.last_name as tech_last_name,
                           t.email as tech_email,
                           t.phone as tech_phone,
                           p.title as project_title,
                           p.description as project_description
                    FROM {$this->table} a 
                    LEFT JOIN customers c ON a.customer_id = c.id 
                    LEFT JOIN users t ON a.technician_id = t.id 
                    LEFT JOIN projects p ON a.project_id = p.id 
                    WHERE a.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Appointment getWithDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get appointments with pagination and filters
     */
    public function getPaginated($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $database = new Database();
        $db = $database->connect();
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['1=1'];
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['technician'])) {
            $whereConditions[] = "a.technician_id = ?";
            $params[] = $filters['technician'];
        }
        
        if (!empty($filters['customer'])) {
            $whereConditions[] = "a.customer_id = ?";
            $params[] = $filters['customer'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(a.appointment_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(a.appointment_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(a.title LIKE ? OR a.description LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM {$this->table} a 
                         JOIN customers c ON a.customer_id = c.id 
                         WHERE {$whereClause}";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get data
            $dataSql = "SELECT a.*, 
                               c.first_name as customer_first_name, 
                               c.last_name as customer_last_name, 
                               c.company_name as customer_company_name,
                               c.customer_type,
                               t.first_name as tech_first_name, 
                               t.last_name as tech_last_name,
                               p.title as project_title
                        FROM {$this->table} a 
                        JOIN customers c ON a.customer_id = c.id 
                        JOIN users t ON a.technician_id = t.id 
                        LEFT JOIN projects p ON a.project_id = p.id 
                        WHERE {$whereClause}
                        ORDER BY a.appointment_date DESC 
                        LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $db->prepare($dataSql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'data' => $data,
                'pagination' => [
                    'total_records' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $perPage),
                    'current_page' => $page,
                    'per_page' => $perPage
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Appointment getPaginated error: " . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'total_records' => 0,
                    'total_pages' => 0,
                    'current_page' => 1,
                    'per_page' => $perPage
                ]
            ];
        }
    }
    
    /**
     * Get appointments for calendar view
     */
    public function getForCalendar($startDate, $endDate, $technicianId = null) {
        $database = new Database();
        $db = $database->connect();
        
        $params = [$startDate, $endDate];
        $whereClause = "WHERE a.appointment_date BETWEEN ? AND ?";
        
        if ($technicianId) {
            $whereClause .= " AND a.technician_id = ?";
            $params[] = $technicianId;
        }
        
        try {
            $sql = "SELECT a.id, a.title, a.appointment_date, a.duration_minutes, a.status,
                           c.first_name as customer_first_name, 
                           c.last_name as customer_last_name, 
                           c.company_name as customer_company_name,
                           c.customer_type,
                           t.first_name as tech_first_name, 
                           t.last_name as tech_last_name
                    FROM {$this->table} a 
                    JOIN customers c ON a.customer_id = c.id 
                    JOIN users t ON a.technician_id = t.id 
                    {$whereClause}
                    AND a.status != 'cancelled'
                    ORDER BY a.appointment_date ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Appointment getForCalendar error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming appointments
     */
    public function getUpcoming($limit = 5, $technicianId = null) {
        $database = new Database();
        $db = $database->connect();
        
        $params = [];
        $whereClause = "WHERE a.appointment_date >= NOW() AND a.status IN ('scheduled', 'confirmed')";
        
        if ($technicianId) {
            $whereClause .= " AND a.technician_id = ?";
            $params[] = $technicianId;
        }
        
        try {
            $sql = "SELECT a.*, 
                           c.first_name as customer_first_name, 
                           c.last_name as customer_last_name, 
                           c.company_name as customer_company_name,
                           c.customer_type,
                           c.phone as customer_phone
                    FROM {$this->table} a 
                    JOIN customers c ON a.customer_id = c.id 
                    {$whereClause}
                    ORDER BY a.appointment_date ASC 
                    LIMIT ?";
            
            $params[] = $limit;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Appointment getUpcoming error: " . $e->getMessage());
            return [];
        }
    }
}
