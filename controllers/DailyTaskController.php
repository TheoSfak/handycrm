<?php
/**
 * Daily Task Controller
 * Manages daily task operations
 */

require_once 'classes/BaseController.php';
require_once 'models/DailyTask.php';
require_once 'models/DailyTaskMaterial.php';
require_once 'models/DailyTaskTechnician.php';

// Load TCPDF library before using it
if (!class_exists('TCPDF')) {
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
}

/**
 * Custom TCPDF class with company header and footer for Daily Tasks
 */
class CustomDailyTaskPDF extends TCPDF {
    private $logoPath = null;
    
    public function setLogoPath($path) {
        $this->logoPath = $path;
    }
    
    public function Header() {
        // Logo (if exists)
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 10, 35, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Set font for header
        $this->SetFont('dejavusans', 'B', 16);
        $this->SetTextColor(0, 102, 204);
        
        // Company name - adjust position if logo exists
        $yPos = $this->logoPath && file_exists($this->logoPath) ? 10 : 15;
        $this->SetY($yPos);
        $this->SetX(55); // Offset to the right of logo
        $this->Cell(0, 10, 'ECOWATT Ενεργειακές Λύσεις', 0, 1, 'L');
        
        // Set font for subtitle
        $this->SetFont('dejavusans', '', 9);
        $this->SetTextColor(80, 80, 80);
        $this->SetX(55);
        $this->Cell(0, 5, 'ecowatt.gr | notifications@ecowatt.gr | Τηλ: +30 210 1234567', 0, 1, 'L');
        
        // Line
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(0, 102, 204);
        $this->Line(15, 35, $this->getPageWidth() - 15, 35);
        
        // Add some space
        $this->Ln(5);
    }
    
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Background color for footer
        $this->SetFillColor(240, 240, 240);
        $this->Rect(0, $this->GetY(), $this->getPageWidth(), 15, 'F');
        
        // Line above footer
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(0, 102, 204);
        $this->Line(10, $this->GetY(), $this->getPageWidth() - 10, $this->GetY());
        
        $this->SetY(-12);
        $this->SetFont('dejavusans', '', 8);
        $this->SetTextColor(60, 60, 60);
        
        // Company details
        $footerText = 'ECOWATT Ενεργειακές Λύσεις - Εργασία Ημέρας';
        $this->Cell(0, 5, $footerText, 0, 1, 'C');
        
        // Page number
        $this->SetY(-8);
        $this->SetFont('dejavusans', 'I', 7);
        $this->Cell(0, 3, 'Σελίδα ' . $this->getAliasNumPage() . ' από ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

class DailyTaskController extends BaseController {
    private $taskModel;
    
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $this->taskModel = new DailyTask();
    }
    
    /**
     * List all daily tasks
     */
    public function index() {
        // Get filters from GET
        $search = $_GET['search'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        $taskType = isset($_GET['task_type']) && $_GET['task_type'] !== '' ? $_GET['task_type'] : null;
        $technicianId = isset($_GET['technician_id']) && $_GET['technician_id'] !== '' ? $_GET['technician_id'] : null;
        $isInvoiced = isset($_GET['is_invoiced']) && $_GET['is_invoiced'] !== '' ? $_GET['is_invoiced'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        // Get tasks
        $tasks = $this->taskModel->getAll($page, $perPage, $search, $dateFrom, $dateTo, $taskType, $technicianId, $isInvoiced, $status);
        $totalCount = $this->taskModel->getTotalCount($search, $dateFrom, $dateTo, $taskType, $technicianId, $isInvoiced, $status);
        $totalPages = ceil($totalCount / $perPage);
        
        // Get all active users for filter dropdown
        require_once 'models/User.php';
        $userModel = new User();
        $technicians = $userModel->getAllActive();
        
        $this->view('daily-tasks/index', [
            'tasks' => $tasks,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'taskType' => $taskType,
            'technicianId' => $technicianId,
            'isInvoiced' => $isInvoiced,
            'status' => $status,
            'technicians' => $technicians,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ]);
    }
    
    /**
     * Show create form
     */
    public function create() {
        // Get all active users
        require_once 'models/User.php';
        $userModel = new User();
        $technicians = $userModel->getAllActive();
        
        $this->view('daily-tasks/create', [
            'technicians' => $technicians
        ]);
    }
    
    /**
     * Store new task
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        // Handle photo uploads
        $photos = [];
        if (!empty($_FILES['photos']['name'][0])) {
            $photos = $this->handlePhotoUploads($_FILES['photos']);
        }
        
        // Prepare data
        $data = [
            'date' => $_POST['date'],
            'customer_name' => $_POST['customer_name'],
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'task_type' => $_POST['task_type'],
            'description' => $_POST['description'],
            'hours_worked' => $_POST['hours_worked'] ?? null,
            'time_from' => $_POST['time_from'] ?? null,
            'time_to' => $_POST['time_to'] ?? null,
            'materials' => $_POST['materials'] ?? null,
            'is_invoiced' => isset($_POST['is_invoiced']) ? 1 : 0,
            'technician_id' => $_POST['technician_id'],
            'additional_technicians' => $_POST['additional_technicians'] ?? [],
            'notes' => $_POST['notes'] ?? null,
            'photos' => $photos,
            'status' => $_POST['status'] ?? 'completed',
            'created_by' => $_SESSION['user_id']
        ];
        
        $taskId = $this->taskModel->create($data);
        
        if ($taskId) {
            // Save technicians with hours
            $technicianModel = new DailyTaskTechnician();
            
            // Save primary technician
            if (!empty($_POST['technician_id'])) {
                $result = $technicianModel->create([
                    'daily_task_id' => $taskId,
                    'user_id' => $_POST['technician_id'],
                    'hours_worked' => floatval($_POST['primary_technician_hours'] ?? 8),
                    'is_primary' => 1
                ]);
                if (DEBUG_MODE) {
                    error_log("DailyTaskController - Primary technician save: " . ($result ? 'SUCCESS' : 'FAILED'));
                    error_log("DailyTaskController - Primary technician: taskId=$taskId, userId={$_POST['technician_id']}, hours=" . floatval($_POST['primary_technician_hours'] ?? 8));
                }
            }
            
            // Save additional technicians
            if (!empty($_POST['additional_technicians']) && is_array($_POST['additional_technicians'])) {
                if (DEBUG_MODE) {
                    error_log("DailyTaskController - Additional technicians: " . print_r($_POST['additional_technicians'], true));
                    error_log("DailyTaskController - Technician hours: " . print_r($_POST['technician_hours'] ?? [], true));
                }
                foreach ($_POST['additional_technicians'] as $techId) {
                    if (!empty($techId)) {
                        $hours = floatval($_POST['technician_hours'][$techId] ?? 8);
                        $result = $technicianModel->create([
                            'daily_task_id' => $taskId,
                            'user_id' => $techId,
                            'hours_worked' => $hours,
                            'is_primary' => 0
                        ]);
                        if (DEBUG_MODE) {
                            error_log("DailyTaskController - Additional technician save for techId=$techId, hours=$hours: " . ($result ? 'SUCCESS' : 'FAILED'));
                        }
                    }
                }
            }
            
            // Save materials from catalog
            if (!empty($_POST['materials']) && is_array($_POST['materials'])) {
                $materialModel = new DailyTaskMaterial();
                foreach ($_POST['materials'] as $material) {
                    if (!empty($material['name']) && !empty($material['quantity'])) {
                        $materialData = [
                            'daily_task_id' => $taskId,
                            'name' => $material['name'],
                            'unit' => $material['unit'] ?? '',
                            'unit_price' => floatval($material['unit_price'] ?? 0),
                            'quantity' => floatval($material['quantity']),
                            'catalog_material_id' => !empty($material['catalog_material_id']) ? intval($material['catalog_material_id']) : null
                        ];
                        $materialModel->create($materialData);
                    }
                }
            }
            
            $_SESSION['success'] = 'Η εργασία δημιουργήθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία της εργασίας';
        }
        
        header('Location: ' . BASE_URL . '/daily-tasks');
        exit;
    }
    
    /**
     * Show task details
     */
    public function show($id) {
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        // Get technicians with hours
        $technicianModel = new DailyTaskTechnician();
        $technicians = $technicianModel->getByDailyTask($id);
        
        // Separate primary and additional technicians
        $primaryTechnician = null;
        $additionalTechniciansDetails = [];
        
        foreach ($technicians as $tech) {
            if ($tech['is_primary'] == 1) {
                $primaryTechnician = $tech;
            } else {
                $additionalTechniciansDetails[] = $tech;
            }
        }
        
        // Get materials
        $materialModel = new DailyTaskMaterial();
        $materials = $materialModel->getByDailyTask($id);
        
        $this->view('daily-tasks/show', [
            'task' => $task,
            'primaryTechnician' => $primaryTechnician,
            'additionalTechnicians' => $additionalTechniciansDetails,
            'materials' => $materials
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        // Get all active users
        require_once 'models/User.php';
        $userModel = new User();
        $technicians = $userModel->getAllActive();
        
        // Get task technicians with hours
        $technicianModel = new DailyTaskTechnician();
        $taskTechnicians = $technicianModel->getByDailyTask($id);
        
        // Get materials
        $materialModel = new DailyTaskMaterial();
        $materials = $materialModel->getByDailyTask($id);
        
        $this->view('daily-tasks/edit', [
            'task' => $task,
            'technicians' => $technicians,
            'taskTechnicians' => $taskTechnicians,
            'materials' => $materials
        ]);
    }
    
    /**
     * Update task
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        // Handle new photo uploads
        $existingPhotos = $task['photos'] ?? [];
        $newPhotos = [];
        
        if (!empty($_FILES['photos']['name'][0])) {
            $newPhotos = $this->handlePhotoUploads($_FILES['photos']);
        }
        
        // Merge existing and new photos
        $allPhotos = array_merge($existingPhotos, $newPhotos);
        
        // Prepare data
        $data = [
            'date' => $_POST['date'],
            'customer_name' => $_POST['customer_name'],
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'task_type' => $_POST['task_type'],
            'description' => $_POST['description'],
            'hours_worked' => $_POST['hours_worked'] ?? null,
            'time_from' => $_POST['time_from'] ?? null,
            'time_to' => $_POST['time_to'] ?? null,
            'materials' => $_POST['materials'] ?? null,
            'technician_id' => $_POST['technician_id'],
            'additional_technicians' => $_POST['additional_technicians'] ?? [],
            'notes' => $_POST['notes'] ?? null,
            'photos' => $allPhotos,
            'status' => $_POST['status'] ?? 'completed'
        ];
        
        if ($this->taskModel->update($id, $data)) {
            // Update technicians - delete old and insert new
            $technicianModel = new DailyTaskTechnician();
            $technicianModel->deleteByDailyTask($id);
            
            // Save primary technician
            if (!empty($_POST['technician_id'])) {
                $technicianModel->create([
                    'daily_task_id' => $id,
                    'user_id' => $_POST['technician_id'],
                    'hours_worked' => floatval($_POST['primary_technician_hours'] ?? 8),
                    'is_primary' => 1
                ]);
            }
            
            // Save additional technicians
            if (!empty($_POST['additional_technicians']) && is_array($_POST['additional_technicians'])) {
                foreach ($_POST['additional_technicians'] as $techId) {
                    if (!empty($techId)) {
                        $hours = floatval($_POST['technician_hours'][$techId] ?? 8);
                        $technicianModel->create([
                            'daily_task_id' => $id,
                            'user_id' => $techId,
                            'hours_worked' => $hours,
                            'is_primary' => 0
                        ]);
                    }
                }
            }
            
            // Update materials - delete old and insert new
            $materialModel = new DailyTaskMaterial();
            $materialModel->deleteByDailyTask($id);
            
            if (!empty($_POST['materials']) && is_array($_POST['materials'])) {
                foreach ($_POST['materials'] as $material) {
                    if (!empty($material['name']) && !empty($material['quantity'])) {
                        $materialData = [
                            'daily_task_id' => $id,
                            'name' => $material['name'],
                            'unit' => $material['unit'] ?? '',
                            'unit_price' => floatval($material['unit_price'] ?? 0),
                            'quantity' => floatval($material['quantity']),
                            'catalog_material_id' => !empty($material['catalog_material_id']) ? intval($material['catalog_material_id']) : null
                        ];
                        $materialModel->create($materialData);
                    }
                }
            }
            
            $_SESSION['success'] = 'Η εργασία ενημερώθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση της εργασίας';
        }
        
        header('Location: ' . BASE_URL . '/daily-tasks/view/' . $id);
        exit;
    }
    
    /**
     * Delete task
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        // Get task info for logging
        $task = $this->taskModel->find($id);
        if (!$task) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/daily-tasks');
            exit;
        }
        
        // Soft delete the task
        $userId = $_SESSION['user_id'];
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "UPDATE daily_tasks SET deleted_at = ?, deleted_by = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([date('Y-m-d H:i:s'), $userId, $id]);
        
        if ($success) {
            $_SESSION['success'] = 'Η εργασία μεταφέρθηκε στον κάδο απορριμμάτων';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή της εργασίας';
        }
        
        header('Location: ' . BASE_URL . '/daily-tasks');
        exit;
    }
    
    /**
     * Delete photo from task
     */
    public function deletePhoto($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $photoPath = $input['photo'] ?? null;
        
        if (!$photoPath) {
            echo json_encode(['success' => false, 'message' => 'Photo path required']);
            exit;
        }
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Task not found']);
            exit;
        }
        
        // Remove photo from array
        $photos = $task['photos'] ?? [];
        $key = array_search($photoPath, $photos);
        
        if ($key !== false) {
            unset($photos[$key]);
            $photos = array_values($photos); // Re-index array
            
            // Update task
            $data = [
                'date' => $task['date'],
                'customer_name' => $task['customer_name'],
                'address' => $task['address'],
                'phone' => $task['phone'],
                'task_type' => $task['task_type'],
                'description' => $task['description'],
                'hours_worked' => $task['hours_worked'],
                'time_from' => $task['time_from'],
                'time_to' => $task['time_to'],
                'materials' => $task['materials'],
                'technician_id' => $task['technician_id'],
                'additional_technicians' => $task['additional_technicians'],
                'notes' => $task['notes'],
                'photos' => $photos,
                'status' => $task['status']
            ];
            
            if ($this->taskModel->update($id, $data)) {
                // Delete physical file
                $fullPath = __DIR__ . '/../' . $photoPath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Photo not found']);
        }
        
        exit;
    }
    
    /**
     * Toggle invoiced status (AJAX)
     */
    public function toggleInvoiced($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? null;
        
        if ($status === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Status required']);
            exit;
        }
        
        try {
            if ($this->taskModel->updateInvoicedStatus($id, $status)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } catch (Exception $e) {
            error_log('Update invoiced status failed in DailyTaskController::updateInvoicedStatus: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Resize and optimize image
     * Max dimensions: 1920x1080, JPEG quality: 85%
     */
    private function resizeImage($sourcePath, $destinationPath, $maxWidth = 1920, $maxHeight = 1080, $quality = 85) {
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($origWidth, $origHeight, $imageType) = $imageInfo;
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
        
        // If image is already smaller, still process for optimization
        if ($ratio >= 1) {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        } else {
            $newWidth = round($origWidth * $ratio);
            $newHeight = round($origHeight * $ratio);
        }
        
        // Create image resource from source
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Always save as JPEG for smaller file size
        $result = imagejpeg($newImage, $destinationPath, $quality);
        
        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $result;
    }

    /**
     * Handle multiple photo uploads
     */
    private function handlePhotoUploads($files) {
        $uploadDir = 'uploads/daily-tasks/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadedPhotos = [];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$i];
                $name = $files['name'][$i];
                $size = $files['size'][$i];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                $fileType = mime_content_type($tmpName);
                
                if (!in_array($fileType, $allowedTypes)) {
                    continue;
                }
                
                // Validate file size (max 10MB, will be resized)
                if ($size > 10 * 1024 * 1024) {
                    continue;
                }
                
                // Generate unique filename (always save as JPG)
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid('task_' . time() . '_') . '.jpg';
                $destination = $uploadDir . $filename;
                
                // Resize and optimize the image
                if ($this->resizeImage($tmpName, $destination, 1920, 1080, 85)) {
                    $uploadedPhotos[] = $destination;
                }
            }
        }
        
        return $uploadedPhotos;
    }
    
    /**
     * Send task via email with PDF
     */
    public function sendEmail($id) {
        // Handle GET request - show email form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $task = $this->taskModel->find($id);
            
            if (!$task) {
                $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
                header('Location: ' . BASE_URL . '/daily-tasks');
                exit;
            }
            
            $this->view('daily-tasks/email', [
                'task' => $task
            ]);
            return;
        }
        
        // Handle POST request - send email
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $recipientEmail = $_POST['email'] ?? '';
                $subject = $_POST['subject'] ?? '';
                $message = $_POST['message'] ?? '';
                $sendCopy = isset($_POST['send_copy']);
                
                if (empty($recipientEmail) || empty($subject)) {
                    throw new Exception('Το email και το θέμα είναι υποχρεωτικά');
                }
                
                // Get task details
                $task = $this->taskModel->find($id);
                
                if (!$task) {
                    throw new Exception('Η εργασία δεν βρέθηκε');
                }
                
                // Get materials for PDF
                $materialModel = new DailyTaskMaterial();
                $task['materials_list'] = $materialModel->getByDailyTask($id);
                
                // Get technicians with hours for PDF
                $technicianModel = new DailyTaskTechnician();
                $task['technicians_list'] = $technicianModel->getByDailyTask($id);
                
                // Load database and email service
                require_once __DIR__ . '/../classes/Database.php';
                $database = new Database();
                $pdo = $database->connect();
                
                require_once __DIR__ . '/../classes/EmailService.php';
                $emailService = new EmailService($pdo);
                
                // Clear any debug messages
                if (isset($_SESSION['smtp_debug'])) {
                    unset($_SESSION['smtp_debug']);
                }
                
                // DEBUG: Log SMTP settings
                if (DEBUG_MODE) {
                    error_log("=== EMAIL DEBUG START ===");
                    error_log("EmailService created");
                    error_log("isConfigured: " . ($emailService->isConfigured() ? 'YES' : 'NO'));
                }
                
                // Check if email is configured
                if (!$emailService->isConfigured()) {
                    if (DEBUG_MODE) error_log("SMTP NOT CONFIGURED - Stopping");
                    throw new Exception('Οι ρυθμίσεις SMTP δεν έχουν ρυθμιστεί. Παρακαλώ ρυθμίστε τα στοιχεία email από τις Ρυθμίσεις Συστήματος.');
                }
                
                // Get company logo from settings (try multiple possible table structures)
                $logoPath = null;
                try {
                    
                    // Temporarily disable exceptions to test queries
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    
                    // Try different possible query formats
                    $possibleQueries = [
                        // Format 1: setting_key, setting_value
                        ["SELECT setting_value FROM settings WHERE setting_key = 'company_logo' LIMIT 1", 'setting_value'],
                        // Format 2: key, value
                        ["SELECT `value` FROM settings WHERE `key` = 'company_logo' LIMIT 1", 'value'],
                        // Format 3: name, value
                        ["SELECT `value` FROM settings WHERE `name` = 'company_logo' LIMIT 1", 'value'],
                    ];
                    
                    foreach ($possibleQueries as list($query, $column)) {
                        $stmt = $pdo->query($query);
                        if ($stmt) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($result && !empty($result[$column])) {
                                $logoPath = __DIR__ . '/..' . $result[$column];
                                break; // Found it, stop trying
                            }
                        }
                    }
                    
                    // Restore exception mode
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                } catch (Exception $e) {
                    // Logo is optional, continue without it
                }
                
                // Generate PDF with custom header/footer
                $pdf = new CustomDailyTaskPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                
                // Set logo if available
                if ($logoPath && file_exists($logoPath)) {
                    $pdf->setLogoPath($logoPath);
                }
                
                // Set document information
                $pdf->SetCreator('HandyCRM');
                $pdf->SetAuthor('ECOWATT');
                $pdf->SetTitle('Εργασία Ημέρας - ' . $task['task_number']);
                $pdf->SetSubject('Εργασία Ημέρας');
                
                // Set margins (increased top margin for header)
                $pdf->SetMargins(15, 40, 15);
                $pdf->SetHeaderMargin(5);
                $pdf->SetFooterMargin(15);
                $pdf->SetAutoPageBreak(TRUE, 20);
                
                // Add page
                $pdf->AddPage();
                
                // Set font
                $pdf->SetFont('dejavusans', '', 10);
                
                // Generate HTML content
                $html = $this->generateTaskPDFHTML($task);
                
                // Write HTML
                $pdf->writeHTML($html, true, false, true, false, '');
                
                // Save to temp file
                $tempPdfPath = sys_get_temp_dir() . '/daily_task_' . $task['task_number'] . '.pdf';
                $filename = 'Εργασία_' . $task['task_number'] . '.pdf';
                $pdf->Output($tempPdfPath, 'F');
                
                // Send email with PHPMailer
                if (DEBUG_MODE) {
                    error_log("DailyTaskController - Creating mailer and preparing email");
                    error_log("DailyTaskController - Recipient: " . $recipientEmail);
                    error_log("DailyTaskController - Subject: " . $subject);
                }
                
                $mail = $emailService->createMailer();
                $mail->addAddress($recipientEmail, $task['customer_name']);
                
                // Add CC only if it's a valid email (not .local domain)
                if ($sendCopy && isset($_SESSION['email']) && filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
                    $userEmail = $_SESSION['email'];
                    // Skip .local domains (they're not real emails)
                    if (!str_ends_with($userEmail, '.local')) {
                        $mail->addCC($userEmail);
                        if (DEBUG_MODE) error_log("DailyTaskController - Added CC: " . $userEmail);
                    }
                }
                
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = nl2br(htmlspecialchars($message));
                $mail->addAttachment($tempPdfPath, $filename);
                
                if (DEBUG_MODE) error_log("DailyTaskController - Attempting to send email...");
                
                if ($mail->send()) {
                    if (DEBUG_MODE) error_log("DailyTaskController - Email sent successfully!");
                    // Delete temp file
                    unlink($tempPdfPath);
                    
                    $_SESSION['success'] = 'Το email στάλθηκε επιτυχώς';
                } else {
                    if (DEBUG_MODE) error_log("DailyTaskController - Email send returned false");
                    throw new Exception('Email sending failed');
                }
                
            } catch (Exception $e) {
                error_log("DailyTaskController::emailTask - EMAIL ERROR: " . $e->getMessage());
                if (DEBUG_MODE) error_log("=== EMAIL DEBUG END ===");
                $_SESSION['error'] = 'Σφάλμα κατά την αποστολή email: ' . $e->getMessage();
            }
            
            header('Location: ' . BASE_URL . '/daily-tasks/view/' . $id);
            exit;
        }
    }
    
    /**
     * Export daily task as PDF
     */
    public function exportPdf($id) {
        try {
            // Get task details
            $task = $this->taskModel->find($id);
            
            if (!$task) {
                $_SESSION['error'] = 'Η εργασία δεν βρέθηκε';
                header('Location: ' . BASE_URL . '/daily-tasks');
                exit;
            }
            
            // Get materials
            $materialsModel = new DailyTaskMaterial();
            $task['materials_list'] = $materialsModel->getByDailyTask($id);
            
            // Get technicians with hours for PDF
            $technicianModel = new DailyTaskTechnician();
            $task['technicians_list'] = $technicianModel->getByDailyTask($id);
            
            // Create PDF
            $pdf = new CustomDailyTaskPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set logo path
            $logoPath = __DIR__ . '/../assets/images/logo.png';
            if (file_exists($logoPath)) {
                $pdf->setLogoPath($logoPath);
            }
            
            // Set document information
            $pdf->SetCreator('HandyCRM');
            $pdf->SetAuthor('ECOWATT');
            $pdf->SetTitle('Εργασία Ημέρας ' . $task['task_number']);
            $pdf->SetSubject('Εργασία Ημέρας');
            
            // Set margins
            $pdf->SetMargins(15, 45, 15);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 20);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font for Greek characters
            $pdf->SetFont('dejavusans', '', 10);
            
            // Generate and write HTML content
            $html = $this->generateTaskPDFHTML($task);
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Output PDF as download
            $filename = 'ergasia_' . $task['task_number'] . '_' . date('Ymd') . '.pdf';
            $pdf->Output($filename, 'D'); // 'D' = force download
            
        } catch (Exception $e) {
            error_log("PDF Export ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία PDF: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/daily-tasks/view/' . $id);
            exit;
        }
    }
    
    /**
     * Generate HTML for task PDF
     */
    private function generateTaskPDFHTML($task) {
        $customerName = htmlspecialchars($task['customer_name']);
        $address = htmlspecialchars($task['address'] ?? '-');
        $phone = htmlspecialchars($task['phone'] ?? '-');
        $description = nl2br(htmlspecialchars($task['description']));
        $notes = nl2br(htmlspecialchars($task['notes'] ?? '-'));
        
        // Build materials HTML from materials list
        $materialsHtml = '';
        if (!empty($task['materials_list']) && is_array($task['materials_list'])) {
            $materialsHtml = '<table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Υλικό</th>
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Μονάδα</th>
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Τιμή</th>
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Ποσότητα</th>
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Σύνολο</th>
                    </tr>
                </thead>
                <tbody>';
            
            $totalCost = 0;
            foreach ($task['materials_list'] as $material) {
                $totalCost += $material['subtotal'];
                $materialsHtml .= '<tr>
                    <td style="border: 1px solid #ddd; padding: 6px;">' . htmlspecialchars($material['name']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 6px;">' . htmlspecialchars($material['unit'] ?? '-') . '</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">' . number_format($material['unit_price'], 2) . ' €</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">' . number_format($material['quantity'], 2) . '</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: right;"><strong>' . number_format($material['subtotal'], 2) . ' €</strong></td>
                </tr>';
            }
            
            $materialsHtml .= '<tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="4" style="border: 1px solid #ddd; padding: 6px; text-align: right;">Σύνολο Υλικών:</td>
                    <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">' . number_format($totalCost, 2) . ' €</td>
                </tr>
                </tbody>
            </table>';
        } else {
            $materialsHtml = '<div class="info-box">Δεν καταχωρήθηκαν υλικά</div>';
        }
        
        $taskTypeLabels = [
            'electrical' => 'Ηλεκτρολογικές Εργασίες',
            'inspection' => 'Επίσκεψη/Έλεγχος',
            'other' => 'Διάφορα'
        ];
        $taskType = $taskTypeLabels[$task['task_type']] ?? '';
        
        $statusLabels = [
            'completed' => 'Ολοκληρώθηκε',
            'in_progress' => 'Σε Εξέλιξη',
            'cancelled' => 'Ακυρώθηκε'
        ];
        $status = $statusLabels[$task['status']] ?? '';
        
        $invoiced = $task['is_invoiced'] ? 'Ναι' : 'Όχι';
        $hours = !empty($task['hours_worked']) ? number_format($task['hours_worked'], 2) . ' ώρες' : '-';
        
        // Check for valid time range
        $hasValidTimeFrom = !empty($task['time_from']) && $task['time_from'] !== '00:00:00';
        $hasValidTimeTo = !empty($task['time_to']) && $task['time_to'] !== '00:00:00';
        if ($hasValidTimeFrom && $hasValidTimeTo) {
            $hours .= ' (' . substr($task['time_from'], 0, 5) . ' - ' . substr($task['time_to'], 0, 5) . ')';
        }
        
        $html = '
        <style>
            h1 { color: #0066cc; font-size: 18pt; text-align: center; margin-bottom: 20px; }
            h2 { 
                color: #0066cc; 
                font-size: 14pt; 
                margin-top: 15px; 
                margin-bottom: 10px;
                font-weight: bold;
            }
            table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
            th { background-color: #f0f0f0; padding: 8px; text-align: left; font-weight: bold; border: 1px solid #ddd; }
            td { padding: 8px; border: 1px solid #ddd; }
            .info-box { background-color: #f8f9fa; padding: 10px; margin-bottom: 10px; border-left: 4px solid #0066cc; }
        </style>
        
        <h1>ΕΡΓΑΣΙΑ ΗΜΕΡΑΣ</h1>
        
        <div class="info-box">
            <strong>Αριθμός Εργασίας:</strong> ' . htmlspecialchars($task['task_number']) . '<br>
            <strong>Ημερομηνία:</strong> ' . date('d/m/Y', strtotime($task['date'])) . '
        </div>
        
        <h2>Βασικά Στοιχεία</h2>
        <table>
            <tr>
                <th width="30%">Τύπος Εργασίας</th>
                <td>' . $taskType . '</td>
            </tr>
            <tr>
                <th>Κατάσταση</th>
                <td>' . $status . '</td>
            </tr>
            <tr>
                <th>Ώρες Εργασίας</th>
                <td>' . $hours . '</td>
            </tr>
            <tr>
                <th>Τιμολογήθηκε</th>
                <td>' . $invoiced . '</td>
            </tr>
        </table>
        
        <h2>Στοιχεία Πελάτη</h2>
        <table>
            <tr>
                <th width="30%">Όνομα</th>
                <td>' . $customerName . '</td>
            </tr>
            <tr>
                <th>Διεύθυνση</th>
                <td>' . $address . '</td>
            </tr>
            <tr>
                <th>Τηλέφωνο</th>
                <td>' . $phone . '</td>
            </tr>
        </table>
        
        <h2>Περιγραφή Εργασίας</h2>
        <div class="info-box">
            ' . $description . '
        </div>
        
        <h2>Υλικά που Χρησιμοποιήθηκαν</h2>
        ' . $materialsHtml . '
        ';
        
        if (!empty($task['notes'])) {
            $html .= '
        <h2>Σημειώσεις</h2>
        <div class="info-box">
            ' . $notes . '
        </div>';
        }
        
        $html .= '
        <h2>Τεχνικοί</h2>
        <table style="width: 100%; border-collapse: collapse;">';
        
        // Display technicians with hours
        if (!empty($task['technicians_list'])) {
            $totalHours = 0;
            foreach ($task['technicians_list'] as $tech) {
                $isPrimary = $tech['is_primary'] == 1;
                $label = $isPrimary ? 'Κύριος Τεχνικός' : 'Επιπλέον Τεχνικός';
                $hours = number_format($tech['hours_worked'], 1);
                $totalHours += $tech['hours_worked'];
                
                $html .= '<tr>
                    <th style="width: 30%; padding: 8px; background-color: #f0f0f0; border: 1px solid #ddd;">' . $label . '</th>
                    <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($tech['full_name']) . ' <strong>(' . $hours . ' ώρες)</strong></td>
                </tr>';
            }
            
            // Total hours
            $html .= '<tr>
                <th style="padding: 8px; background-color: #e8f4f8; border: 1px solid #ddd;">Σύνολο Ώρών</th>
                <td style="padding: 8px; background-color: #e8f4f8; border: 1px solid #ddd; font-weight: bold;">' . number_format($totalHours, 1) . ' ώρες</td>
            </tr>';
        } else {
            // Fallback to old format if no technicians_list
            $html .= '<tr>
                <th style="width: 30%; padding: 8px; background-color: #f0f0f0; border: 1px solid #ddd;">Κύριος Τεχνικός</th>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($task['technician_name']) . '</td>
            </tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }
}
