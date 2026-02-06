<?php

class Trash {
    private $conn;
    private $tables = [
        'project' => ['table' => 'projects', 'name_field' => 'title'],
        'project_task' => ['table' => 'project_tasks', 'name_field' => 'description'],
        'task_labor' => ['table' => 'task_labor', 'name_field' => 'description'],
        'daily_task' => ['table' => 'daily_tasks', 'name_field' => 'customer_name'],
        'maintenance' => ['table' => 'transformer_maintenances', 'name_field' => 'customer_name']
    ];
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Λήψη διαγραμμένων στοιχείων με φίλτρα
     */
    public function getDeletedItems($type, $search = '', $dateFrom = '', $dateTo = '', $page = 1, $perPage = 20) {
        if (!isset($this->tables[$type])) {
            return [];
        }
        
        $table = $this->tables[$type]['table'];
        $nameField = $this->tables[$type]['name_field'];
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT t.*, 
                       u.username as deleted_by_username,
                       CONCAT(u.first_name, ' ', u.last_name) as deleted_by_name
                FROM {$table} t
                LEFT JOIN users u ON t.deleted_by = u.id
                WHERE t.deleted_at IS NOT NULL";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND t.{$nameField} LIKE ?";
            $params[] = "%{$search}%";
        }
        
        if (!empty($dateFrom)) {
            $sql .= " AND DATE(t.deleted_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if (!empty($dateTo)) {
            $sql .= " AND DATE(t.deleted_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY t.deleted_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt && $stmt->execute($params)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [];
    }
    
    /**
     * Επαναφορά διαγραμμένου στοιχείου
     */
    public function restoreItem($type, $id, $userId, $userName) {
        if (!isset($this->tables[$type])) {
            return false;
        }
        
        $table = $this->tables[$type]['table'];
        $nameField = $this->tables[$type]['name_field'];
        
        // Λήψη πληροφοριών στοιχείου πριν την επαναφορά
        $stmt = $this->conn->prepare("SELECT {$nameField} as name FROM {$table} WHERE id = ? AND deleted_at IS NOT NULL");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            return false;
        }
        
        // Επαναφορά
        $sql = "UPDATE {$table} SET deleted_at = NULL, deleted_by = NULL WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([$id])) {
            // CASCADE RESTORE: Αν είναι project, επαναφέρουμε και τις εργασίες του
            if ($type === 'project') {
                // Επαναφορά project_tasks
                $tasksSql = "UPDATE project_tasks 
                            SET deleted_at = NULL, deleted_by = NULL 
                            WHERE project_id = ? AND deleted_at IS NOT NULL";
                $tasksStmt = $this->conn->prepare($tasksSql);
                $tasksStmt->execute([$id]);
                
                // Επαναφορά task_labor για όλες τις εργασίες του project
                $laborSql = "UPDATE task_labor tl
                            INNER JOIN project_tasks pt ON tl.task_id = pt.id
                            SET tl.deleted_at = NULL, tl.deleted_by = NULL
                            WHERE pt.project_id = ? AND tl.deleted_at IS NOT NULL";
                $laborStmt = $this->conn->prepare($laborSql);
                $laborStmt->execute([$id]);
            }
            
            // Καταγραφή στο log
            $this->logAction($type, $id, $item['name'], 'restored', $userId, $userName);
            return true;
        }
        
        return false;
    }
    
    /**
     * Οριστική διαγραφή στοιχείου
     */
    public function permanentDeleteItem($type, $id, $userId, $userName) {
        if (!isset($this->tables[$type])) {
            return false;
        }
        
        $table = $this->tables[$type]['table'];
        $nameField = $this->tables[$type]['name_field'];
        
        // Λήψη πληροφοριών στοιχείου
        $stmt = $this->conn->prepare("SELECT {$nameField} as name FROM {$table} WHERE id = ? AND deleted_at IS NOT NULL");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            return false;
        }
        
        // CASCADE PERMANENT DELETE: Αν είναι project, διαγράφουμε πρώτα τις εργασίες και τα labor
        if ($type === 'project') {
            // Διαγραφή task_labor για όλες τις εργασίες του project
            $laborSql = "DELETE tl FROM task_labor tl
                        INNER JOIN project_tasks pt ON tl.task_id = pt.id
                        WHERE pt.project_id = ?";
            $laborStmt = $this->conn->prepare($laborSql);
            $laborStmt->execute([$id]);
            
            // Διαγραφή project_tasks
            $tasksSql = "DELETE FROM project_tasks WHERE project_id = ?";
            $tasksStmt = $this->conn->prepare($tasksSql);
            $tasksStmt->execute([$id]);
        }
        
        // Οριστική διαγραφή του κύριου στοιχείου
        $sql = "DELETE FROM {$table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([$id])) {
            // Καταγραφή στο log
            $this->logAction($type, $id, $item['name'], 'permanent', $userId, $userName);
            return true;
        }
        
        return false;
    }
    
    /**
     * Λήψη αριθμού διαγραμμένων στοιχείων ανά τύπο
     */
    public function getDeletedCount($type) {
        if (!isset($this->tables[$type])) {
            return 0;
        }
        
        $table = $this->tables[$type]['table'];
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE deleted_at IS NOT NULL";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Λήψη αριθμού διαγραμμένων για όλους τους τύπους
     */
    public function getDeletedCountByType() {
        $counts = [];
        foreach (array_keys($this->tables) as $type) {
            $counts[$type] = $this->getDeletedCount($type);
        }
        return $counts;
    }
    
    /**
     * Άδειασμα κάδου για συγκεκριμένο τύπο
     */
    public function emptyTrash($type, $userId, $userName) {
        if (!isset($this->tables[$type])) {
            return false;
        }
        
        $table = $this->tables[$type]['table'];
        $nameField = $this->tables[$type]['name_field'];
        
        // Λήψη όλων των διαγραμμένων
        $stmt = $this->conn->prepare("SELECT id, {$nameField} as name FROM {$table} WHERE deleted_at IS NOT NULL");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($items)) {
            return true;
        }
        
        // Οριστική διαγραφή όλων
        $sql = "DELETE FROM {$table} WHERE deleted_at IS NOT NULL";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute()) {
            // Καταγραφή στο log για κάθε στοιχείο
            foreach ($items as $item) {
                $this->logAction($type, $item['id'], $item['name'], 'permanent', $userId, $userName);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Λήψη ιστορικού διαγραφών
     */
    public function getDeletionLog($page = 1, $perPage = 50, $type = '', $action = '') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM deletion_log WHERE 1=1";
        $params = [];
        
        if (!empty($type)) {
            $sql .= " AND item_type = ?";
            $params[] = $type;
        }
        
        if (!empty($action)) {
            $sql .= " AND action = ?";
            $params[] = $action;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt && $stmt->execute($params)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [];
    }
    
    /**
     * Καταγραφή ενέργειας στο deletion_log
     */
    private function logAction($itemType, $itemId, $itemName, $action, $userId, $userName, $itemDetails = null) {
        $sql = "INSERT INTO deletion_log (item_type, item_id, item_name, action, user_id, user_name, item_details) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $itemType,
            $itemId,
            $itemName,
            $action,
            $userId,
            $userName,
            $itemDetails ? json_encode($itemDetails) : null
        ]);
    }
    
    /**
     * Λήψη ελληνικού ονόματος τύπου
     */
    public static function getTypeLabel($type) {
        $labels = [
            'project' => 'Έργο',
            'project_task' => 'Εργασία Έργου',
            'task_labor' => 'Ημερομίσθιο',
            'daily_task' => 'Ημερήσια Εργασία',
            'maintenance' => 'Συντήρηση Μ/Σ',
            'material' => 'Υλικό'
        ];
        
        return $labels[$type] ?? $type;
    }
    
    /**
     * Λήψη ελληνικού ονόματος ενέργειας
     */
    public static function getActionLabel($action) {
        $labels = [
            'deleted' => 'Διαγράφηκε',
            'restored' => 'Επαναφέρθηκε',
            'permanent' => 'Οριστική Διαγραφή'
        ];
        
        return $labels[$action] ?? $action;
    }
}
