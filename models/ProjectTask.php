<?php
/**
 * ProjectTask Model
 * 
 * Manages project tasks (single day or date range)
 * with materials and labor tracking
 * 
 * @package HandyCRM
 * @version 1.1.0
 */

require_once 'classes/BaseModel.php';

class ProjectTask extends BaseModel {
    protected $table = 'project_tasks';
    protected $primaryKey = 'id';
    
    /**
     * Get tasks by project ID with optional filters
     * 
     * @param int $projectId Project ID
     * @param array $filters Filters (task_type, date_from, date_to, search)
     * @return array
     */
    public function getByProject($projectId, $filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE project_id = ? AND deleted_at IS NULL";
        $params = [$projectId];
        
        // Filter by task type
        if (!empty($filters['task_type'])) {
            $sql .= " AND task_type = ?";
            $params[] = $filters['task_type'];
        }
        
        // Filter by date range
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $sql .= " AND (
                (task_type = 'single_day' AND task_date BETWEEN ? AND ?)
                OR
                (task_type = 'date_range' AND (
                    (date_from BETWEEN ? AND ?) OR 
                    (date_to BETWEEN ? AND ?) OR
                    (date_from <= ? AND date_to >= ?)
                ))
            )";
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
            $params[] = $filters['date_from'];
            $params[] = $filters['date_to'];
        }
        
        // Search in description
        if (!empty($filters['search'])) {
            $sql .= " AND (description LIKE ? OR notes LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Order by date (most recent first)
        $sql .= " ORDER BY 
                    CASE 
                        WHEN task_type = 'single_day' THEN task_date 
                        ELSE date_to 
                    END DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get task by ID with all related data
     * 
     * @param int $id Task ID
     * @return array|null
     */
    public function getById($id) {
        $sql = "SELECT pt.*, p.title as project_name 
                FROM {$this->table} pt
                LEFT JOIN projects p ON pt.project_id = p.id
                WHERE pt.id = ? AND pt.deleted_at IS NULL";
        
        $result = $this->query($sql, [$id]);
        
        if (!$result) {
            return null;
        }
        
        $task = $result[0];
        
        // Load materials
        $task['materials'] = $this->getMaterials($id);
        
        // Load labor
        $task['labor'] = $this->getLabor($id);
        
        return $task;
    }
    
    /**
     * Count tasks by project ID
     * 
     * @param int $projectId Project ID
     * @return int
     */
    public function countByProject($projectId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE project_id = ?";
        $result = $this->query($sql, [$projectId]);
        return $result ? (int)$result[0]['count'] : 0;
    }
    
    /**
     * Get materials for task
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getMaterials($taskId) {
        $sql = "SELECT * FROM task_materials WHERE task_id = ? ORDER BY id ASC";
        return $this->query($sql, [$taskId]);
    }
    
    /**
     * Get labor for task
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getLabor($taskId) {
        $sql = "SELECT * FROM task_labor WHERE task_id = ? ORDER BY id ASC";
        return $this->query($sql, [$taskId]);
    }
    
    /**
     * Create new task with materials and labor
     * 
     * @param array $data Task data
     * @param array $materials Materials array
     * @param array $labor Labor array
     * @return int|false Task ID or false on failure
     */
    public function createWithDetails($data, $materials = [], $labor = []) {
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Insert task
            $taskId = $this->createTask($data);
            
            if (!$taskId) {
                throw new Exception('Failed to create task');
            }
            
            // Insert materials
            if (!empty($materials)) {
                foreach ($materials as $material) {
                    $material['task_id'] = $taskId;
                    if (!$this->addMaterial($material)) {
                        throw new Exception('Failed to add material');
                    }
                }
            }
            
            // Insert labor
            if (!empty($labor)) {
                foreach ($labor as $laborItem) {
                    $laborItem['task_id'] = $taskId;
                    if (!$this->addLabor($laborItem)) {
                        throw new Exception('Failed to add labor');
                    }
                }
            }
            
            // Calculate totals
            $this->calculateTotals($taskId);
            
            $this->db->commit();
            return $taskId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Failed to create task with details: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create task (internal method)
     */
    private function createTask($data) {
        $sql = "INSERT INTO {$this->table} 
                (project_id, task_type, task_date, date_from, date_to, description, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->execute($sql, [
            $data['project_id'],
            $data['task_type'],
            $data['task_date'] ?? null,
            $data['date_from'] ?? null,
            $data['date_to'] ?? null,
            $data['description'],
            $data['notes'] ?? null
        ]);
        
        return $stmt ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Add material to task
     */
    private function addMaterial($data) {
        // Support both new fields (name, unit) and old fields (description, unit_type)
        $materialName = $data['name'] ?? $data['description'] ?? '';
        $unit = $data['unit'] ?? '';
        $unitType = $data['unit_type'] ?? 'other';
        
        $sql = "INSERT INTO task_materials 
                (task_id, catalog_material_id, name, description, unit, unit_price, quantity, unit_type, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $subtotal = $data['unit_price'] * $data['quantity'];
        
        $stmt = $this->db->execute($sql, [
            $data['task_id'],
            $data['catalog_material_id'] ?? null,
            $materialName,
            $materialName, // Keep description same as name for backward compatibility
            $unit,
            $data['unit_price'],
            $data['quantity'],
            $unitType,
            $subtotal
        ]);
        
        return $stmt !== false;
    }
    
    /**
     * Add labor to task
     */
    private function addLabor($data) {
        $sql = "INSERT INTO task_labor 
                (task_id, technician_id, technician_name, role_id, is_temporary, 
                 hours_worked, time_from, time_to, hourly_rate, subtotal, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $subtotal = $data['hours_worked'] * $data['hourly_rate'];
        
        $stmt = $this->db->execute($sql, [
            $data['task_id'],
            $data['technician_id'] ?? null,
            $data['technician_name'],
            $data['role_id'] ?? null,
            $data['is_temporary'] ?? 0,
            $data['hours_worked'],
            $data['time_from'] ?? null,
            $data['time_to'] ?? null,
            $data['hourly_rate'],
            $subtotal,
            $data['notes'] ?? null
        ]);
        
        return $stmt !== false;
    }
    
    /**
     * Update task with materials and labor
     * 
     * @param int $id Task ID
     * @param array $data Task data
     * @param array $materials Materials array
     * @param array $labor Labor array
     * @return bool
     */
    public function updateWithDetails($id, $data, $materials = [], $labor = []) {
        $this->db->beginTransaction();
        
        try {
            // Update task
            if (!$this->updateTask($id, $data)) {
                throw new Exception('Failed to update task');
            }
            
            // Delete existing materials and labor
            $this->deleteMaterials($id);
            $this->deleteLabor($id);
            
            // Insert new materials
            if (!empty($materials)) {
                foreach ($materials as $material) {
                    $material['task_id'] = $id;
                    if (!$this->addMaterial($material)) {
                        throw new Exception('Failed to add material');
                    }
                }
            }
            
            // Insert new labor
            if (!empty($labor)) {
                foreach ($labor as $laborItem) {
                    $laborItem['task_id'] = $id;
                    if (!$this->addLabor($laborItem)) {
                        throw new Exception('Failed to add labor');
                    }
                }
            }
            
            // Recalculate totals
            $this->calculateTotals($id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Failed to update task with details: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update task (internal method)
     */
    private function updateTask($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET task_type = ?, task_date = ?, date_from = ?, date_to = ?, 
                    description = ?, notes = ?
                WHERE id = ?";
        
        $stmt = $this->db->execute($sql, [
            $data['task_type'],
            $data['task_date'] ?? null,
            $data['date_from'] ?? null,
            $data['date_to'] ?? null,
            $data['description'],
            $data['notes'] ?? null,
            $id
        ]);
        
        return $stmt !== false;
    }
    
    /**
     * Delete materials for task
     */
    private function deleteMaterials($taskId) {
        $sql = "DELETE FROM task_materials WHERE task_id = ?";
        $this->db->execute($sql, [$taskId]);
    }
    
    /**
     * Delete labor for task
     */
    private function deleteLabor($taskId) {
        $sql = "DELETE FROM task_labor WHERE task_id = ?";
        $this->db->execute($sql, [$taskId]);
    }
    
    /**
     * Delete task (cascades to materials and labor)
     * 
     * @param int $id Task ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->execute($sql, [$id]);
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Calculate and update totals for task
     * 
     * @param int $taskId Task ID
     * @return bool
     */
    public function calculateTotals($taskId) {
        // Calculate materials total
        $sql = "SELECT SUM(subtotal) as total FROM task_materials WHERE task_id = ?";
        $result = $this->queryOne($sql, [$taskId]);
        $materialsTotal = $result['total'] ?? 0;
        
        // Calculate labor total
        $sql = "SELECT SUM(subtotal) as total FROM task_labor WHERE task_id = ?";
        $result = $this->queryOne($sql, [$taskId]);
        $laborTotal = $result['total'] ?? 0;
        
        // Update task totals
        $dailyTotal = $materialsTotal + $laborTotal;
        
        $sql = "UPDATE {$this->table} 
                SET materials_total = ?, labor_total = ?, daily_total = ? 
                WHERE id = ?";
        
        $stmt = $this->db->execute($sql, [$materialsTotal, $laborTotal, $dailyTotal, $taskId]);
        return $stmt !== false;
    }
    
    /**
     * Check for overlapping tasks
     * 
     * @param int $projectId Project ID
     * @param string $dateFrom Start date
     * @param string $dateTo End date
     * @param int|null $excludeId Exclude this task ID (for updates)
     * @return array Overlapping tasks
     */
    public function checkOverlap($projectId, $dateFrom, $dateTo, $excludeId = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE project_id = ?
                AND (
                    (task_type = 'single_day' AND task_date BETWEEN ? AND ?)
                    OR
                    (task_type = 'date_range' AND (
                        (date_from BETWEEN ? AND ?) OR 
                        (date_to BETWEEN ? AND ?) OR
                        (date_from <= ? AND date_to >= ?)
                    ))
                )";
        
        $params = [$projectId, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->query($sql, $params);
    }
    
    /**
     * Check if a technician has overlapping tasks
     * 
     * @param int $technicianId User ID of technician
     * @param string $dateFrom Start date (YYYY-MM-DD)
     * @param string $dateTo End date (YYYY-MM-DD)
     * @param int|null $excludeTaskId Task ID to exclude (for edit mode)
     * @return array Overlapping tasks with labor info
     */
    public function checkTechnicianOverlap($technicianId, $dateFrom, $dateTo, $excludeTaskId = null) {
        // First get all tasks in the date range
        $sql = "SELECT pt.*, p.title as project_name 
                FROM {$this->table} pt
                LEFT JOIN projects p ON pt.project_id = p.id
                WHERE (
                    (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                    OR
                    (pt.task_type = 'date_range' AND (
                        (pt.date_from BETWEEN ? AND ?) OR 
                        (pt.date_to BETWEEN ? AND ?) OR
                        (pt.date_from <= ? AND pt.date_to >= ?)
                    ))
                )";
        
        $params = [$dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo];
        
        if ($excludeTaskId) {
            $sql .= " AND pt.id != ?";
            $params[] = $excludeTaskId;
        }
        
        $tasks = $this->query($sql, $params);
        
        if (empty($tasks)) {
            return [];
        }
        
        // Now check which tasks have this technician
        $overlapping = [];
        foreach ($tasks as $task) {
            $labor = $this->getLabor($task['id']);
            foreach ($labor as $entry) {
                if ($entry['technician_id'] == $technicianId) {
                    $overlapping[] = array_merge($task, [
                        'labor_hours' => $entry['hours_worked'],
                        'labor_cost' => $entry['subtotal']
                    ]);
                    break; // Only add task once
                }
            }
        }
        
        return $overlapping;
    }
    
    /**
     * Get total days for task
     * 
     * @param array $task Task data
     * @return int Number of days
     */
    public function getTotalDays($task) {
        if ($task['task_type'] === 'single_day') {
            return 1;
        }
        
        $from = new DateTime($task['date_from']);
        $to = new DateTime($task['date_to']);
        $interval = $from->diff($to);
        
        return $interval->days + 1; // +1 to include both start and end
    }
    
    /**
     * Get daily average cost
     * 
     * @param array $task Task data
     * @return float
     */
    public function getDailyAverage($task) {
        $days = $this->getTotalDays($task);
        return $days > 0 ? ($task['daily_total'] / $days) : 0;
    }
    
    /**
     * Get daily breakdown for date range tasks
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getDailyBreakdown($taskId) {
        $task = $this->getById($taskId);
        
        if (!$task || $task['task_type'] !== 'date_range') {
            return [];
        }
        
        $days = $this->getTotalDays($task);
        $dailyMaterials = $task['materials_total'] / $days;
        
        $breakdown = [];
        $currentDate = new DateTime($task['date_from']);
        $endDate = new DateTime($task['date_to']);
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            $breakdown[] = [
                'date' => $dateStr,
                'date_formatted' => $currentDate->format('d/m/Y'),
                'day_name' => $currentDate->format('l'),
                'materials' => $dailyMaterials,
                'labor' => $task['labor_total'] / $days,
                'total' => $task['daily_total'] / $days
            ];
            
            $currentDate->modify('+1 day');
        }
        
        return $breakdown;
    }
    
    /**
     * Get summary/statistics for project tasks
     * 
     * @param int $projectId Project ID
     * @param array $filters Optional filters
     * @return array
     */
    public function getSummary($projectId, $filters = []) {
        $tasks = $this->getByProject($projectId, $filters);
        
        $summary = [
            'total_tasks' => count($tasks),
            'single_day_tasks' => 0,
            'date_range_tasks' => 0,
            'total_days' => 0,
            'materials_total' => 0.0,
            'labor_total' => 0.0,
            'grand_total' => 0.0,
            'average_daily_cost' => 0.0
        ];
        
        foreach ($tasks as $task) {
            if ($task['task_type'] === 'single_day') {
                $summary['single_day_tasks']++;
                $summary['total_days'] += 1;
            } else {
                $summary['date_range_tasks']++;
                $summary['total_days'] += $this->getTotalDays($task);
            }
            
            // Explicitly cast to float to prevent string concatenation issues
            $summary['materials_total'] += (float)($task['materials_total'] ?? 0);
            $summary['labor_total'] += (float)($task['labor_total'] ?? 0);
            $summary['grand_total'] += (float)($task['daily_total'] ?? 0);
        }
        
        if ($summary['total_days'] > 0) {
            $summary['average_daily_cost'] = $summary['grand_total'] / $summary['total_days'];
        }
        
        return $summary;
    }
    
    /**
     * Copy task to create a duplicate
     * 
     * @param int $taskId Task ID to copy
     * @param array $overrides Data to override in the copy
     * @return int|false New task ID or false
     */
    public function copyTask($taskId, $overrides = []) {
        $task = $this->getById($taskId);
        
        if (!$task) {
            return false;
        }
        
        // Prepare task data for copy
        $newTaskData = [
            'project_id' => $overrides['project_id'] ?? $task['project_id'],
            'task_type' => $overrides['task_type'] ?? $task['task_type'],
            'task_date' => $overrides['task_date'] ?? $task['task_date'],
            'date_from' => $overrides['date_from'] ?? $task['date_from'],
            'date_to' => $overrides['date_to'] ?? $task['date_to'],
            'description' => $overrides['description'] ?? $task['description'],
            'notes' => $overrides['notes'] ?? $task['notes']
        ];
        
        // Copy materials and labor arrays
        $materials = $task['materials'];
        $labor = $task['labor'];
        
        // Create new task with copied data
        return $this->createWithDetails($newTaskData, $materials, $labor);
    }
    
    /**
     * Get detailed statistics for a project
     * 
     * @param int $projectId Project ID
     * @return array Statistics data
     */
    public function getStatistics($projectId) {
        $tasks = $this->getByProject($projectId);
        
        $stats = [
            'total_tasks' => count($tasks),
            'total_cost' => 0,
            'materials_total' => 0,
            'labor_total' => 0,
            'total_hours' => 0,
            'total_days' => 0,
            'single_day_tasks' => 0,
            'date_range_tasks' => 0,
            'technicians' => [],
            'tasks_by_month' => [],
            'tasks_by_weekday' => [
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0,
                'Saturday' => 0,
                'Sunday' => 0
            ],
            'top_expensive_tasks' => [],
            'cost_timeline' => []
        ];
        
        if (empty($tasks)) {
            return $stats;
        }
        
        // Load labor data for each task
        foreach ($tasks as &$task) {
            $task['labor'] = $this->getLabor($task['id']);
        }
        unset($task); // Break reference
        
        foreach ($tasks as $task) {
            // Basic totals
            $taskTotal = ($task['materials_total'] ?? 0) + ($task['labor_total'] ?? 0);
            $stats['total_cost'] += $taskTotal;
            $stats['materials_total'] += $task['materials_total'] ?? 0;
            $stats['labor_total'] += $task['labor_total'] ?? 0;
            
            // Task type counting
            if ($task['task_type'] === 'single_day') {
                $stats['single_day_tasks']++;
                $stats['total_days'] += 1;
                $taskDate = $task['task_date'];
            } else {
                $stats['date_range_tasks']++;
                $days = $this->getTotalDays($task);
                $stats['total_days'] += $days;
                $taskDate = $task['date_from'];
            }
            
            // Tasks by month
            $monthKey = date('Y-m', strtotime($taskDate));
            if (!isset($stats['tasks_by_month'][$monthKey])) {
                $stats['tasks_by_month'][$monthKey] = [
                    'month' => date('F Y', strtotime($taskDate)),
                    'count' => 0,
                    'cost' => 0
                ];
            }
            $stats['tasks_by_month'][$monthKey]['count']++;
            $stats['tasks_by_month'][$monthKey]['cost'] += $taskTotal;
            
            // Tasks by weekday
            $weekday = date('l', strtotime($taskDate));
            $stats['tasks_by_weekday'][$weekday]++;
            
            // Top expensive tasks
            $stats['top_expensive_tasks'][] = [
                'id' => $task['id'],
                'description' => $task['description'],
                'date' => $taskDate,
                'cost' => $taskTotal
            ];
            
            // Get labor details for technician stats
            if (!empty($task['labor'])) {
                foreach ($task['labor'] as $labor) {
                    $techName = $labor['technician_name'];
                    
                    if (!isset($stats['technicians'][$techName])) {
                        $stats['technicians'][$techName] = [
                            'name' => $techName,
                            'role' => $labor['technician_role'] ?? 'other',
                            'total_hours' => 0,
                            'total_cost' => 0,
                            'tasks_count' => 0
                        ];
                    }
                    
                    $hours = $labor['hours_worked'] ?? 0;
                    $cost = $labor['subtotal'] ?? 0;
                    
                    $stats['technicians'][$techName]['total_hours'] += $hours;
                    $stats['technicians'][$techName]['total_cost'] += $cost;
                    $stats['technicians'][$techName]['tasks_count']++;
                    $stats['total_hours'] += $hours;
                }
            }
        }
        
        // Sort top expensive tasks
        usort($stats['top_expensive_tasks'], function($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        $stats['top_expensive_tasks'] = array_slice($stats['top_expensive_tasks'], 0, 5);
        
        // Sort technicians by hours worked
        uasort($stats['technicians'], function($a, $b) {
            return $b['total_hours'] <=> $a['total_hours'];
        });
        
        // Calculate averages
        if ($stats['total_tasks'] > 0) {
            $stats['avg_cost_per_task'] = $stats['total_cost'] / $stats['total_tasks'];
            $stats['avg_materials_per_task'] = $stats['materials_total'] / $stats['total_tasks'];
            $stats['avg_labor_per_task'] = $stats['labor_total'] / $stats['total_tasks'];
        } else {
            $stats['avg_cost_per_task'] = 0;
            $stats['avg_materials_per_task'] = 0;
            $stats['avg_labor_per_task'] = 0;
        }
        
        if ($stats['total_days'] > 0) {
            $stats['avg_cost_per_day'] = $stats['total_cost'] / $stats['total_days'];
        } else {
            $stats['avg_cost_per_day'] = 0;
        }
        
        // Percentages
        if ($stats['total_cost'] > 0) {
            $stats['materials_percentage'] = ($stats['materials_total'] / $stats['total_cost']) * 100;
            $stats['labor_percentage'] = ($stats['labor_total'] / $stats['total_cost']) * 100;
        } else {
            $stats['materials_percentage'] = 0;
            $stats['labor_percentage'] = 0;
        }
        
        return $stats;
    }
}
