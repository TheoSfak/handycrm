<?php
/**
 * ProjectTasksController
 * 
 * Handles project tasks management (single day and date range)
 * with materials and labor tracking
 * 
 * @package HandyCRM
 * @version 1.1.0
 */

require_once 'classes/BaseController.php';
require_once 'models/ProjectTask.php';
require_once 'models/TaskMaterial.php';
require_once 'models/TaskLabor.php';
require_once 'models/User.php';

class ProjectTasksController extends BaseController {
    private $taskModel;
    private $materialModel;
    private $laborModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->taskModel = new ProjectTask();
        $this->materialModel = new TaskMaterial();
        $this->laborModel = new TaskLabor();
        $this->userModel = new User();
    }
    
    /**
     * List all tasks for a project
     * GET /projects/{project_id}/tasks
     */
    public function index($projectId) {
        $this->checkAuth();
        
        // Get filters from query params
        $filters = [
            'task_type' => $_GET['task_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Get tasks with filters
        $tasks = $this->taskModel->getByProject($projectId, $filters);
        
        // Get summary statistics
        $summary = $this->taskModel->getSummary($projectId, $filters);
        
        // Get project info
        require_once 'models/Project.php';
        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        
        if (!$project) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε';
            $this->redirect('/projects');
            return;
        }
        
        parent::view('projects/tasks/index', [
            'title' => 'Εργασίες - ' . ($project['title'] ?? $project['name'] ?? 'Project'),
            'project' => $project,
            'tasks' => $tasks,
            'summary' => $summary,
            'filters' => $filters
        ]);
    }
    
    /**
     * Show add task form
     * GET /projects/{project_id}/tasks/add
     */
    public function add($projectId) {
        $this->checkAuth();
        
        // Get project
        require_once 'models/Project.php';
        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        
        if (!$project) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε';
            $this->redirect('/projects');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store($projectId);
        }
        
        // Get all active technicians (users with role technician or admin)
        $technicians = $this->userModel->getByRole(['admin', 'technician', 'assistant']);
        
        parent::view('projects/tasks/add', [
            'title' => 'Νέα Εργασία - ' . ($project['title'] ?? $project['name'] ?? 'Project'),
            'project' => $project,
            'technicians' => $technicians
        ]);
    }
    
    /**
     * Store new task
     * POST /projects/{project_id}/tasks/add
     */
    private function store($projectId) {
        // Validate CSRF token would go here
        
        // Helper function to convert date formats (DD/MM/YYYY or YYYY-MM-DD to YYYY-MM-DD)
        $convertDate = function($dateStr) {
            if (empty($dateStr)) return null;
            
            // If already in YYYY-MM-DD format, return as-is
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                return $dateStr;
            }
            
            // If in DD/MM/YYYY format, convert to YYYY-MM-DD
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
            
            return null;
        };
        
        // Collect task data
        $taskData = [
            'project_id' => $projectId,
            'task_type' => $_POST['task_type'] ?? 'single_day',
            'task_date' => $convertDate($_POST['task_date'] ?? null),
            'date_from' => $convertDate($_POST['date_from'] ?? null),
            'date_to' => $convertDate($_POST['date_to'] ?? null),
            'description' => trim($_POST['description'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validate task data
        $errors = $this->validateTaskData($taskData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirectBack();
            return;
        }
        
        // Collect materials
        $materials = $this->collectMaterials();
        
        // Collect labor
        $labor = $this->collectLabor();
        
        // Create task with all details
        $taskId = $this->taskModel->createWithDetails($taskData, $materials, $labor);
        
        if ($taskId) {
            $_SESSION['success'] = 'Η εργασία δημιουργήθηκε επιτυχώς!';
            $this->redirect('/projects/' . $projectId . '/tasks');
        } else {
            $_SESSION['error'] = 'Αποτυχία δημιουργίας εργασίας';
            $this->redirectBack();
        }
    }
    
    /**
     * Collect materials from POST data
     */
    private function collectMaterials() {
        $materials = [];
        
        if (!empty($_POST['materials'])) {
            foreach ($_POST['materials'] as $index => $material) {
                // Check for 'name' field (new form) or 'description' field (old form)
                $materialName = trim($material['name'] ?? $material['description'] ?? '');
                
                if (!empty($materialName)) {
                    $materials[] = [
                        'name' => $materialName,
                        'catalog_material_id' => !empty($material['catalog_material_id']) ? intval($material['catalog_material_id']) : null,
                        'unit' => trim($material['unit'] ?? ''),
                        'unit_type' => trim($material['unit'] ?? $material['unit_type'] ?? 'other'),
                        'unit_price' => floatval($material['unit_price'] ?? 0),
                        'quantity' => floatval($material['quantity'] ?? 0)
                    ];
                }
            }
        }
        
        return $materials;
    }
    
    /**
     * Collect labor from POST data
     */
    private function collectLabor() {
        $labor = [];
        
        if (!empty($_POST['labor'])) {
            foreach ($_POST['labor'] as $index => $laborItem) {
                // Check if this labor entry has meaningful data (hours or rate)
                $hasHours = !empty($laborItem['hours_worked']) && floatval($laborItem['hours_worked']) > 0;
                $hasRate = !empty($laborItem['hourly_rate']) && floatval($laborItem['hourly_rate']) > 0;
                
                // Only include labor if it has hours or rate
                if ($hasHours || $hasRate) {
                    $isTemporary = !empty($laborItem['is_temporary']) || empty($laborItem['technician_id']);
                    
                    // Calculate hours if time_from and time_to provided (and not 00:00)
                    $hoursWorked = floatval($laborItem['hours_worked'] ?? 0);
                    
                    if (!empty($laborItem['time_from']) && !empty($laborItem['time_to']) 
                        && $laborItem['time_from'] !== '00:00' && $laborItem['time_to'] !== '00:00') {
                        $hoursWorked = $this->laborModel->calculateHoursFromTime(
                            $laborItem['time_from'], 
                            $laborItem['time_to']
                        );
                    }
                    
                    $labor[] = [
                        'technician_id' => !$isTemporary ? intval($laborItem['technician_id']) : null,
                        'technician_name' => trim($laborItem['technician_name']),
                        'technician_role' => $laborItem['technician_role'] ?? null,
                        'is_temporary' => $isTemporary ? 1 : 0,
                        'hours_worked' => $hoursWorked,
                        'time_from' => $laborItem['time_from'] ?? null,
                        'time_to' => $laborItem['time_to'] ?? null,
                        'hourly_rate' => floatval($laborItem['hourly_rate'] ?? 0),
                        'notes' => trim($laborItem['notes'] ?? '')
                    ];
                }
            }
        }
        
        return $labor;
    }
    
    /**
     * Show edit task form
     * GET /projects/{project_id}/tasks/edit/{id}
     */
    public function edit($projectId, $taskId) {
        $this->checkAuth();
        
        $task = $this->taskModel->getById($taskId);
        
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($projectId, $taskId);
        }
        
        // Get all active technicians (users with role technician or admin)
        $technicians = $this->userModel->getByRole(['admin', 'technician', 'assistant']);
        
        // Get project
        require_once 'models/Project.php';
        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        
        parent::view('projects/tasks/edit', [
            'title' => 'Επεξεργασία Εργασίας - ' . ($project['title'] ?? $project['name'] ?? 'Project'),
            'project' => $project,
            'task' => $task,
            'materials' => $task['materials'] ?? [],
            'labor' => $task['labor'] ?? [],
            'technicians' => $technicians
        ]);
    }
    
    /**
     * Update task
     * POST /projects/{project_id}/tasks/edit/{id}
     */
    private function update($projectId, $taskId) {
        // Helper function to convert date formats (DD/MM/YYYY or YYYY-MM-DD to YYYY-MM-DD)
        $convertDate = function($dateStr) {
            if (empty($dateStr)) return null;
            
            // If already in YYYY-MM-DD format, return as-is
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                return $dateStr;
            }
            
            // If in DD/MM/YYYY format, convert to YYYY-MM-DD
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
            
            return null;
        };
        
        // Collect task data
        $taskData = [
            'task_type' => $_POST['task_type'] ?? 'single_day',
            'task_date' => $convertDate($_POST['task_date'] ?? null),
            'date_from' => $convertDate($_POST['date_from'] ?? null),
            'date_to' => $convertDate($_POST['date_to'] ?? null),
            'description' => trim($_POST['description'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validate
        $errors = $this->validateTaskData($taskData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirectBack();
            return;
        }
        
        // Collect materials and labor
        $materials = $this->collectMaterials();
        $labor = $this->collectLabor();
        
        // Update task
        if ($this->taskModel->updateWithDetails($taskId, $taskData, $materials, $labor)) {
            $_SESSION['success'] = 'Η εργασία ενημερώθηκε επιτυχώς!';
            $this->redirect('/projects/' . $projectId . '/tasks');
        } else {
            $_SESSION['error'] = 'Αποτυχία ενημέρωσης εργασίας';
            $this->redirectBack();
        }
    }
    
    /**
     * View task details
     * GET /projects/{project_id}/tasks/view/{id}
     */
    public function viewTask($projectId, $taskId) {
        $this->checkAuth();
        
        $task = $this->taskModel->getById($taskId);
        
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        // Get project
        require_once 'models/Project.php';
        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        
        // Calculate additional info
        $task['total_days'] = $this->taskModel->getTotalDays($task);
        $task['daily_average'] = $this->taskModel->getDailyAverage($task);
        
        // Get photos for preview (limit 6 most recent)
        require_once 'models/TaskPhoto.php';
        $photoModel = new TaskPhoto();
        $recentPhotos = $photoModel->query(
            "SELECT tp.*, u.username, u.first_name, u.last_name 
             FROM task_photos tp
             LEFT JOIN users u ON tp.uploaded_by = u.id
             WHERE tp.task_id = ?
             ORDER BY tp.created_at DESC
             LIMIT 6",
            [$taskId]
        );
        $photoCount = $photoModel->getCountByType($taskId);
        $totalPhotos = $photoCount['total'];
        
        parent::view('projects/tasks/view', [
            'title' => 'Προβολή Εργασίας - ' . ($project['title'] ?? $project['name'] ?? 'Project'),
            'project' => $project,
            'task' => $task,
            'materials' => $task['materials'] ?? [],
            'labor' => $task['labor'] ?? [],
            'recentPhotos' => $recentPhotos,
            'totalPhotos' => $totalPhotos
        ]);
    }
    
    /**
     * Show daily breakdown for date range tasks
     * GET /projects/{project_id}/tasks/{id}/breakdown
     */
    public function breakdown($projectId, $taskId) {
        $this->checkAuth();
        
        $task = $this->taskModel->getById($taskId);
        
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        if ($task['task_type'] !== 'date_range') {
            $_SESSION['error'] = 'Το αναλυτικό ημέρας είναι διαθέσιμο μόνο για εργασίες εύρους ημερομηνιών';
            $this->redirect('/projects/' . $projectId . '/tasks/view/' . $taskId);
            return;
        }
        
        // Get daily breakdown
        $breakdown = $this->taskModel->getDailyBreakdown($taskId);
        
        // Get project
        require_once 'models/Project.php';
        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        
        parent::view('projects/tasks/breakdown', [
            'title' => 'Αναλυτικό Ημερών - ' . ($project['title'] ?? $project['name'] ?? 'Project'),
            'project' => $project,
            'task' => $task,
            'breakdown' => $breakdown
        ]);
    }
    
    /**
     * Copy task
     * POST /projects/{project_id}/tasks/copy/{id}
     */
    public function copy($projectId, $taskId) {
        $this->checkAuth();
        
        $task = $this->taskModel->getById($taskId);
        
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        // Copy with new date if provided
        $overrides = [];
        if (!empty($_POST['new_date'])) {
            if ($task['task_type'] === 'single_day') {
                $overrides['task_date'] = $_POST['new_date'];
            } else {
                // Calculate date range offset
                $originalFrom = new DateTime($task['date_from']);
                $newFrom = new DateTime($_POST['new_date']);
                $diff = $originalFrom->diff($newFrom);
                
                $newTo = new DateTime($task['date_to']);
                $newTo->add($diff);
                
                $overrides['date_from'] = $newFrom->format('Y-m-d');
                $overrides['date_to'] = $newTo->format('Y-m-d');
            }
        }
        
        $newTaskId = $this->taskModel->copyTask($taskId, $overrides);
        
        if ($newTaskId) {
            $_SESSION['success'] = 'Η εργασία αντιγράφηκε επιτυχώς!';
            $this->redirect('/projects/' . $projectId . '/tasks/edit/' . $newTaskId);
        } else {
            $_SESSION['error'] = 'Αποτυχία αντιγραφής εργασίας';
            $this->redirectBack();
        }
    }
    
    /**
     * Delete task
     * POST /projects/{project_id}/tasks/delete
     */
    public function delete($projectId) {
        $this->checkAuth();
        
        $taskId = intval($_POST['task_id'] ?? 0);
        
        if (!$taskId) {
            $_SESSION['error'] = 'Μη έγκυρο ID εργασίας';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        // Verify task belongs to project
        $task = $this->taskModel->getById($taskId);
        
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        if ($this->taskModel->delete($taskId)) {
            $_SESSION['success'] = 'Η εργασία διαγράφηκε επιτυχώς!';
        } else {
            $_SESSION['error'] = 'Αποτυχία διαγραφής εργασίας';
        }
        
        $this->redirect('/projects/' . $projectId . '/tasks');
    }
    
    /**
     * API: Check for overlapping tasks
     * POST /api/tasks/check-overlap
     */
    public function apiCheckOverlap() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $projectId = intval($_POST['project_id'] ?? 0);
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        $excludeId = intval($_POST['exclude_id'] ?? 0);
        
        if (!$projectId || !$dateFrom || !$dateTo) {
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }
        
        $overlapping = $this->taskModel->checkOverlap($projectId, $dateFrom, $dateTo, $excludeId ?: null);
        
        echo json_encode([
            'success' => true,
            'has_overlap' => !empty($overlapping),
            'count' => count($overlapping),
            'tasks' => $overlapping
        ]);
    }
    
    /**
     * Check if a technician has overlapping tasks
     * POST /api/tasks/check-technician-overlap
     */
    public function apiCheckTechnicianOverlap() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $technicianId = intval($_POST['technician_id'] ?? 0);
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        $taskDate = $_POST['task_date'] ?? '';
        $excludeTaskId = intval($_POST['exclude_task_id'] ?? 0);
        
        if (!$technicianId) {
            echo json_encode(['error' => 'Technician ID required']);
            return;
        }
        
        // For single day tasks, use task_date for both from and to
        if ($taskDate && !$dateFrom) {
            $dateFrom = $dateTo = $taskDate;
        }
        
        if (!$dateFrom || !$dateTo) {
            echo json_encode(['error' => 'Date range required']);
            return;
        }
        
        $overlapping = $this->taskModel->checkTechnicianOverlap(
            $technicianId, 
            $dateFrom, 
            $dateTo, 
            $excludeTaskId ?: null
        );
        
        echo json_encode([
            'success' => true,
            'has_overlap' => !empty($overlapping),
            'count' => count($overlapping),
            'tasks' => $overlapping
        ]);
    }
    
    /**
     * Validate task data
     */
    private function validateTaskData($data) {
        $errors = [];
        
        if (empty($data['description'])) {
            $errors[] = 'Η περιγραφή εργασίας είναι υποχρεωτική';
        }
        
        if ($data['task_type'] === 'single_day') {
            if (empty($data['task_date'])) {
                $errors[] = 'Η ημερομηνία εργασίας είναι υποχρεωτική';
            }
        } else {
            if (empty($data['date_from']) || empty($data['date_to'])) {
                $errors[] = 'Το εύρος ημερομηνιών είναι υποχρεωτικό';
            } elseif (strtotime($data['date_from']) > strtotime($data['date_to'])) {
                $errors[] = 'Η ημερομηνία "Από" πρέπει να είναι πριν την ημερομηνία "Έως"';
            }
        }
        
        return $errors;
    }
    
    /**
     * Helper to render view (temporary until we fix BaseController)
     */
    private function render($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include view file
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once __DIR__ . '/../views/includes/header.php';
            require $viewFile;
            require_once __DIR__ . '/../views/includes/footer.php';
        } else {
            die("View not found: $view");
        }
    }
    
    /**
     * Export tasks to CSV
     * GET /projects/{project_id}/tasks/export-csv
     */
    public function exportCsv($projectId) {
        $this->checkAuth();
        
        // Get project
        require_once 'models/Project.php';
        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        
        if (!$project) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε';
            $this->redirect('/projects');
            return;
        }
        
        // Get all tasks with filters
        $filters = [
            'type' => $_GET['type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest'
        ];
        
        $tasks = $this->taskModel->getByProject($projectId, $filters);
        
        // Load materials and labor for each task
        foreach ($tasks as &$task) {
            $task['materials'] = $this->taskModel->getMaterials($task['id']);
            $task['labor'] = $this->taskModel->getLabor($task['id']);
        }
        
        // Set headers for CSV download
        $filename = $this->sanitizeFilename($project['title']) . '_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header row
        fputcsv($output, [
            'Ημερομηνία',
            'Τύπος',
            'Περιγραφή',
            'Ημέρες',
            'Υλικά (€)',
            'Εργατικά (€)',
            'Σύνολο (€)',
            'Τεχνικοί',
            'Σύνολο Ωρών',
            'Υλικά - Λεπτομέρειες',
            'Εργατικά - Λεπτομέρειες'
        ]);
        
        // Write data rows
        foreach ($tasks as $task) {
            // Calculate task info
            $taskType = $task['task_type'] === 'single_day' ? 'Μονοήμερη' : 'Πολυήμερη';
            $taskDate = $task['task_type'] === 'single_day' 
                ? date('d/m/Y', strtotime($task['task_date']))
                : date('d/m/Y', strtotime($task['date_from'])) . ' - ' . date('d/m/Y', strtotime($task['date_to']));
            
            $totalDays = $this->taskModel->getTotalDays($task);
            
            // Get technicians list
            $technicians = [];
            $totalHours = 0;
            foreach ($task['labor'] as $labor) {
                if ($labor['technician_name']) {
                    $technicians[] = $labor['technician_name'];
                }
                $totalHours += $labor['hours_worked'];
            }
            $techniciansStr = implode(', ', array_unique($technicians));
            
            // Get materials details
            $materialsDetails = [];
            foreach ($task['materials'] as $material) {
                $materialsDetails[] = $material['description'] . ' (' . 
                    number_format($material['quantity'], 2) . ' ' . 
                    $this->getUnitLabel($material['unit_type']) . ' x ' . 
                    number_format($material['unit_price'], 2) . '€ = ' . 
                    number_format($material['subtotal'], 2) . '€)';
            }
            $materialsStr = implode('; ', $materialsDetails);
            
            // Get labor details
            $laborDetails = [];
            foreach ($task['labor'] as $labor) {
                $laborDetails[] = ($labor['technician_name'] ?: 'Άλλο') . ' - ' . 
                    number_format($labor['hours_worked'], 1) . 'h x ' . 
                    number_format($labor['hourly_rate'], 2) . '€ = ' . 
                    number_format($labor['subtotal'], 2) . '€';
            }
            $laborStr = implode('; ', $laborDetails);
            
            // Write row
            fputcsv($output, [
                $taskDate,
                $taskType,
                $task['description'],
                $totalDays,
                number_format($task['materials_total'], 2),
                number_format($task['labor_total'], 2),
                number_format($task['materials_total'] + $task['labor_total'], 2),
                $techniciansStr,
                number_format($totalHours, 1),
                $materialsStr,
                $laborStr
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get unit label in Greek
     */
    private function getUnitLabel($unit) {
        $units = [
            'meters' => 'μέτρα',
            'pieces' => 'τεμάχια',
            'kg' => 'κιλά',
            'liters' => 'λίτρα',
            'boxes' => 'κουτιά',
            'other' => 'άλλο'
        ];
        return $units[$unit] ?? $unit;
    }
    
    /**
     * Sanitize filename for safe download
     */
    private function sanitizeFilename($filename) {
        // Remove special characters and replace spaces
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        return $filename;
    }
    
    /**
     * Photo gallery for a task
     * GET /projects/{project_id}/tasks/{task_id}/photos
     */
    public function photos($projectId, $taskId) {
        $this->checkAuth();
        
        require_once 'models/TaskPhoto.php';
        require_once 'models/Project.php';
        $photoModel = new TaskPhoto();
        $projectModel = new Project();
        
        // Get project details
        $project = $projectModel->find($projectId);
        if (!$project) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε';
            $this->redirect('/projects');
            return;
        }
        
        // Get task details
        $task = $this->taskModel->getById($taskId);
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        // Get photos grouped by type
        $photos = $photoModel->getGroupedByType($taskId);
        $counts = $photoModel->getCountByType($taskId);
        
        $this->view('projects/tasks/photos', [
            'project' => $project,
            'project_id' => $projectId,
            'task' => $task,
            'photos' => $photos,
            'counts' => $counts,
            'pageTitle' => 'Φωτογραφίες Εργασίας'
        ]);
    }
    
    /**
     * Upload photo
     * POST /projects/{project_id}/tasks/{task_id}/photos/upload
     */
    public function uploadPhoto($projectId, $taskId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects/' . $projectId . '/tasks/' . $taskId . '/photos');
            return;
        }
        
        require_once 'models/TaskPhoto.php';
        $photoModel = new TaskPhoto();
        
        // Verify task exists
        $task = $this->taskModel->getById($taskId);
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        // Check if files were uploaded
        if (empty($_FILES['photos']['name'][0])) {
            $_SESSION['error'] = 'Δεν επιλέχθηκαν φωτογραφίες';
            $this->redirect('/projects/' . $projectId . '/tasks/' . $taskId . '/photos');
            return;
        }
        
        $photoType = $_POST['photo_type'] ?? 'other';
        $caption = $_POST['caption'] ?? '';
        $userId = $_SESSION['user_id'];
        
        $uploadedCount = 0;
        $errors = [];
        
        // Handle multiple file upload
        $fileCount = count($_FILES['photos']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['photos']['name'][$i],
                    'type' => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                    'error' => $_FILES['photos']['error'][$i],
                    'size' => $_FILES['photos']['size'][$i]
                ];
                
                $photoId = $photoModel->uploadPhoto($taskId, $file, $photoType, $caption, $userId);
                
                if ($photoId) {
                    $uploadedCount++;
                } else {
                    $errors[] = $file['name'];
                }
            }
        }
        
        if ($uploadedCount > 0) {
            $_SESSION['success'] = $uploadedCount . ' φωτογραφί' . ($uploadedCount == 1 ? 'α' : 'ες') . ' ανέβηκ' . ($uploadedCount == 1 ? 'ε' : 'αν') . ' επιτυχώς';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = 'Αποτυχία ανεβάσματος: ' . implode(', ', $errors);
        }
        
        $this->redirect('/projects/' . $projectId . '/tasks/' . $taskId . '/photos');
    }
    
    /**
     * Delete photo
     * POST /projects/{project_id}/tasks/{task_id}/photos/{photo_id}/delete
     */
    public function deletePhoto($projectId, $taskId, $photoId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects/' . $projectId . '/tasks/' . $taskId . '/photos');
            return;
        }
        
        require_once 'models/TaskPhoto.php';
        $photoModel = new TaskPhoto();
        
        // Verify task exists
        $task = $this->taskModel->getById($taskId);
        if (!$task || $task['project_id'] != $projectId) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            $this->redirect('/projects/' . $projectId . '/tasks');
            return;
        }
        
        if ($photoModel->deletePhoto($photoId)) {
            $_SESSION['success'] = 'Η φωτογραφία διαγράφηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Αποτυχία διαγραφής φωτογραφίας';
        }
        
        $this->redirect('/projects/' . $projectId . '/tasks/' . $taskId . '/photos');
    }
    
    /**
     * Update photo details
     * POST /projects/{project_id}/tasks/{task_id}/photos/{photo_id}/update
     */
    public function updatePhotoDetails($projectId, $taskId, $photoId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        require_once 'models/TaskPhoto.php';
        $photoModel = new TaskPhoto();
        
        $data = [
            'photo_type' => $_POST['photo_type'] ?? null,
            'caption' => $_POST['caption'] ?? null
        ];
        
        if ($photoModel->updatePhoto($photoId, $data)) {
            echo json_encode(['success' => true, 'message' => 'Ενημερώθηκε επιτυχώς']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Αποτυχία ενημέρωσης']);
        }
    }
    
    /**
     * Check for date overlap with existing tasks in the same project
     * Returns array with 'hasOverlap' and 'overlappingTasks' or false if no overlap
     */
    /**
     * Helper to redirect back
     */
    private function redirectBack() {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referrer);
        exit;
    }
}
