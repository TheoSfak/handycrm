<?php
/**
 * Quote Model
 * Handles quote/proposal data and related operations
 */

class Quote extends BaseModel {
    protected $table = 'quotes';
    
    /**
     * Get quote with customer and items details
     */
    public function getWithDetails($id) {
        $db = $this->db->connect();
        
        try {
            $sql = "SELECT q.*, 
                           c.first_name as customer_first_name, 
                           c.last_name as customer_last_name, 
                           c.company_name as customer_company_name,
                           c.customer_type,
                           c.phone as customer_phone,
                           c.email as customer_email,
                           c.address as customer_address,
                           creator.first_name as creator_first_name,
                           creator.last_name as creator_last_name
                    FROM {$this->table} q 
                    JOIN customers c ON q.customer_id = c.id 
                    JOIN users creator ON q.created_by = creator.id 
                    WHERE q.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $quote = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($quote) {
                // Get quote items
                $sql = "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY sort_order";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id]);
                $quote['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $quote;
            
        } catch (PDOException $e) {
            error_log("Quote getWithDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get quotes with pagination and filters
     */
    public function getPaginated($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $db = $this->db->connect();
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['1=1'];
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "q.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['customer'])) {
            $whereConditions[] = "q.customer_id = ?";
            $params[] = $filters['customer'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(q.quote_number LIKE ? OR q.title LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM {$this->table} q 
                         JOIN customers c ON q.customer_id = c.id 
                         WHERE {$whereClause}";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get data
            $dataSql = "SELECT q.*, 
                               c.first_name as customer_first_name, 
                               c.last_name as customer_last_name, 
                               c.company_name as customer_company_name,
                               c.customer_type,
                               creator.first_name as creator_first_name,
                               creator.last_name as creator_last_name
                        FROM {$this->table} q 
                        JOIN customers c ON q.customer_id = c.id 
                        JOIN users creator ON q.created_by = creator.id 
                        WHERE {$whereClause}
                        ORDER BY q.created_at DESC 
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
            error_log("Quote getPaginated error: " . $e->getMessage());
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
     * Generate next quote number
     */
    public function generateQuoteNumber() {
        $db = $this->db->connect();
        
        try {
            $year = date('Y');
            $prefix = "PRO-{$year}-";
            
            $sql = "SELECT quote_number FROM {$this->table} 
                    WHERE quote_number LIKE ? 
                    ORDER BY quote_number DESC 
                    LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$prefix . '%']);
            $lastQuote = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lastQuote) {
                $lastNumber = (int)substr($lastQuote['quote_number'], -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            
        } catch (PDOException $e) {
            error_log("Quote generateQuoteNumber error: " . $e->getMessage());
            return $prefix . '0001';
        }
    }
    
    /**
     * Create new quote
     */
    public function create($data) {
        $db = $this->db->connect();
        
        try {
            // Map tax_rate/tax_amount to vat_rate/vat_amount (database column names)
            // Map terms to terms_conditions
            $sql = "INSERT INTO {$this->table} (
                        quote_number, title, description, customer_id, valid_until,
                        subtotal, vat_rate, vat_amount, total_amount, status, notes, terms_conditions, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['quote_number'],
                $data['title'],
                $data['description'],
                $data['customer_id'],
                $data['valid_until'],
                $data['subtotal'],
                $data['tax_rate'] ?? 24,
                $data['tax_amount'] ?? 0,
                $data['total_amount'],
                $data['status'],
                $data['notes'] ?? '',
                $data['terms'] ?? '',
                $data['created_by']
            ]);
            
            return $db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Quote create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update existing quote
     */
    public function update($id, $data) {
        $db = $this->db->connect();
        
        try {
            // Map tax_rate/tax_amount to vat_rate/vat_amount (database column names)
            // Map terms to terms_conditions
            $sql = "UPDATE {$this->table} SET 
                        title = ?, 
                        description = ?, 
                        customer_id = ?, 
                        valid_until = ?,
                        subtotal = ?, 
                        vat_rate = ?, 
                        vat_amount = ?, 
                        total_amount = ?, 
                        status = ?, 
                        notes = ?, 
                        terms_conditions = ?
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([
                $data['title'],
                $data['description'],
                $data['customer_id'],
                $data['valid_until'],
                $data['subtotal'],
                $data['tax_rate'] ?? 24,
                $data['tax_amount'] ?? 0,
                $data['total_amount'],
                $data['status'],
                $data['notes'] ?? '',
                $data['terms'] ?? '',
                $id
            ]);
            
            return $success;
            
        } catch (PDOException $e) {
            error_log("Quote update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete quote and its items
     */
    public function delete($id) {
        $db = $this->db->connect();
        
        try {
            // Delete quote items first
            $stmt = $db->prepare("DELETE FROM quote_items WHERE quote_id = ?");
            $stmt->execute([$id]);
            $itemsDeleted = $stmt->rowCount();
            error_log("Deleted $itemsDeleted quote items for quote ID: $id");
            
            // Delete quote
            $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $quoteDeleted = $stmt->rowCount();
            error_log("Deleted $quoteDeleted quote(s) with ID: $id");
            
            return $quoteDeleted > 0;
            
        } catch (PDOException $e) {
            error_log("Quote delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get quote by slug (quote number)
     */
    public function getBySlug($slug) {
        // Convert slug back to quote number format
        $quoteNumber = strtoupper($slug);
        
        $sql = "SELECT q.*, 
                       c.first_name as customer_first_name, 
                       c.last_name as customer_last_name,
                       c.company_name as customer_company_name,
                       c.customer_type,
                       c.email as customer_email,
                       c.phone as customer_phone,
                       c.address as customer_address,
                       u.first_name as created_by_first_name,
                       u.last_name as created_by_last_name
                FROM {$this->table} q
                LEFT JOIN customers c ON q.customer_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                WHERE q.slug = ? OR q.quote_number = ?";
        
        $quote = $this->db->fetchOne($sql, [$slug, $quoteNumber]);
        
        if ($quote) {
            // Get quote items
            $sql = "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY id";
            $quote['items'] = $this->db->fetchAll($sql, [$quote['id']]);
        }
        
        return $quote;
    }
    
    /**
     * Generate and save slug for quote (uses quote_number)
     */
    public function generateSlug($id, $quoteNumber) {
        $slug = strtolower($quoteNumber);
        
        $sql = "UPDATE {$this->table} SET slug = ? WHERE id = ?";
        $this->db->execute($sql, [$slug, $id]);
        
        return $slug;
    }
}
