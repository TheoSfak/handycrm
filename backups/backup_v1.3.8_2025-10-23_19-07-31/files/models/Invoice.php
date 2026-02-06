<?php
/**
 * Invoice Model
 * Manages invoice data and database operations
 */

class Invoice extends BaseModel {
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    
    /**
     * Get paginated invoices with filters
     */
    public function getPaginated($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $database = new Database();
        $db = $database->connect();
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['1=1'];
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['customer'])) {
            $whereConditions[] = "i.customer_id = ?";
            $params[] = $filters['customer'];
        }
        
        if (!empty($filters['payment_status'])) {
            $whereConditions[] = "i.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(i.issue_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(i.issue_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(i.invoice_number LIKE ? OR i.title LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM {$this->table} i 
                         LEFT JOIN customers c ON i.customer_id = c.id 
                         WHERE {$whereClause}";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get data
            $dataSql = "SELECT i.*, 
                               c.first_name as customer_first_name, 
                               c.last_name as customer_last_name, 
                               c.company_name as customer_company_name,
                               c.customer_type,
                               p.title as project_title
                        FROM {$this->table} i 
                        LEFT JOIN customers c ON i.customer_id = c.id 
                        LEFT JOIN projects p ON i.project_id = p.id 
                        WHERE {$whereClause}
                        ORDER BY i.issue_date DESC, i.id DESC 
                        LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $db->prepare($dataSql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'data' => $records,
                'pagination' => [
                    'total_records' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $perPage),
                    'current_page' => $page,
                    'per_page' => $perPage
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Invoice getPaginated error: " . $e->getMessage());
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
     * Get invoice with all related data
     */
    public function getWithDetails($id) {
        $database = new Database();
        $db = $database->connect();
        
        try {
            // Get invoice data
            $sql = "SELECT i.*, 
                           c.first_name as customer_first_name, 
                           c.last_name as customer_last_name, 
                           c.company_name as customer_company_name,
                           c.customer_type,
                           c.email as customer_email,
                           c.phone as customer_phone,
                           c.address as customer_address,
                           c.city as customer_city,
                           c.postal_code as customer_postal_code,
                           c.tax_id as customer_tax_id,
                           p.title as project_title,
                           p.project_address
                    FROM {$this->table} i 
                    LEFT JOIN customers c ON i.customer_id = c.id 
                    LEFT JOIN projects p ON i.project_id = p.id 
                    WHERE i.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                return null;
            }
            
            // Get invoice items
            $itemsSql = "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order";
            $stmt = $db->prepare($itemsSql);
            $stmt->execute([$id]);
            $invoice['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $invoice;
            
        } catch (PDOException $e) {
            error_log("Invoice getWithDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create invoice with items
     */
    public function createWithItems($invoiceData, $items) {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $db->beginTransaction();
            
            // Insert invoice
            $invoiceId = $this->create($invoiceData);
            
            if (!$invoiceId) {
                throw new Exception("Failed to create invoice");
            }
            
            // Insert invoice items
            $itemSql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price, sort_order) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($itemSql);
            
            foreach ($items as $index => $item) {
                $stmt->execute([
                    $invoiceId,
                    $item['description'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price'],
                    $index + 1
                ]);
            }
            
            $db->commit();
            return $invoiceId;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Invoice createWithItems error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update invoice with items
     */
    public function updateWithItems($id, $invoiceData, $items) {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $db->beginTransaction();
            
            // Update invoice
            $success = $this->update($id, $invoiceData);
            
            if (!$success) {
                throw new Exception("Failed to update invoice");
            }
            
            // Delete existing items
            $deleteSql = "DELETE FROM invoice_items WHERE invoice_id = ?";
            $stmt = $db->prepare($deleteSql);
            $stmt->execute([$id]);
            
            // Insert new items
            $itemSql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price, sort_order) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($itemSql);
            
            foreach ($items as $index => $item) {
                $stmt->execute([
                    $id,
                    $item['description'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price'],
                    $index + 1
                ]);
            }
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Invoice updateWithItems error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber() {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $year = date('Y');
            $prefix = "INV-{$year}-";
            
            // Get last invoice number for this year
            $sql = "SELECT invoice_number FROM {$this->table} 
                    WHERE invoice_number LIKE ? 
                    ORDER BY id DESC LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$prefix . '%']);
            $lastInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lastInvoice) {
                // Extract number and increment
                $lastNumber = (int) str_replace($prefix, '', $lastInvoice['invoice_number']);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
        } catch (PDOException $e) {
            error_log("Invoice generateInvoiceNumber error: " . $e->getMessage());
            return $prefix . '0001';
        }
    }
    
    /**
     * Get invoice statistics
     */
    public function getStatistics() {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $sql = "SELECT 
                        COUNT(*) as total_invoices,
                        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                        SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                        SUM(CASE WHEN payment_status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
                        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_paid,
                        SUM(CASE WHEN payment_status != 'paid' THEN total_amount ELSE 0 END) as total_unpaid
                    FROM {$this->table}
                    WHERE status != 'cancelled'";
            
            $stmt = $db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Invoice getStatistics error: " . $e->getMessage());
            return [
                'total_invoices' => 0,
                'paid_count' => 0,
                'unpaid_count' => 0,
                'overdue_count' => 0,
                'total_paid' => 0,
                'total_unpaid' => 0
            ];
        }
    }
    
    /**
     * Get invoice by slug (invoice number)
     */
    public function getBySlug($slug) {
        // Convert slug back to invoice number format
        $invoiceNumber = strtoupper($slug);
        
        $sql = "SELECT i.*, 
                       c.first_name as customer_first_name, 
                       c.last_name as customer_last_name,
                       c.company_name as customer_company_name,
                       c.customer_type,
                       c.email as customer_email,
                       c.phone as customer_phone,
                       c.address as customer_address,
                       p.title as project_title,
                       p.id as project_id
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                LEFT JOIN projects p ON i.project_id = p.id
                WHERE i.slug = ? OR i.invoice_number = ?";
        
        $invoice = $this->db->fetchOne($sql, [$slug, $invoiceNumber]);
        
        if ($invoice) {
            // Get invoice items
            $sql = "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id";
            $invoice['items'] = $this->db->fetchAll($sql, [$invoice['id']]);
        }
        
        return $invoice;
    }
    
    /**
     * Generate and save slug for invoice (uses invoice_number)
     */
    public function generateSlug($id, $invoiceNumber) {
        $slug = strtolower($invoiceNumber);
        
        $sql = "UPDATE {$this->table} SET slug = ? WHERE id = ?";
        $this->db->execute($sql, [$slug, $id]);
        
        return $slug;
    }
}
