<?php
/**
 * Payments Controller
 * Manages weekly technician payments
 */

require_once 'classes/BaseController.php';

class PaymentsController extends BaseController {
    private $paymentModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->paymentModel = new Payment();
        $this->userModel = new User();
    }
    
    /**
     * Display payments page with filters
     */
    public function index() {
        // Get all active technicians/assistants from users for dropdown
        $technicians = $this->userModel->getByRole(['technician', 'assistant']);
        
        // Get filter parameters
        $selectedTechnician = $_GET['technician_id'] ?? null;
        $weekStart = $_GET['week_start'] ?? null;
        $weekEnd = $_GET['week_end'] ?? null;
        $paidStatus = $_GET['paid_status'] ?? 'all'; // 'all', 'paid', 'unpaid'
        
        // Pagination parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $itemsPerPage = 10; // Show 10 technicians per page
        
        // If no dates provided, use last completed week
        if (!$weekStart || !$weekEnd) {
            $lastWeek = Payment::getLastCompletedWeek();
            $weekStart = $weekStart ?: $lastWeek['start'];
            $weekEnd = $weekEnd ?: $lastWeek['end'];
        }
        
        // Get technicians with labor for this week
        $weeklyData = $this->paymentModel->getTechniciansForWeek($weekStart, $weekEnd);
        
        // If specific technician selected, filter the results
        if ($selectedTechnician) {
            $weeklyData = array_filter($weeklyData, function($tech) use ($selectedTechnician) {
                return $tech['technician_id'] == $selectedTechnician;
            });
        }
        
        // Get labor entries for each technician and apply paid status filter
        foreach ($weeklyData as &$tech) {
            $entries = $this->paymentModel->getLaborEntriesForWeek(
                $tech['technician_id'],
                $weekStart,
                $weekEnd
            );
            
            // Filter by paid status
            if ($paidStatus === 'paid') {
                $entries = array_filter($entries, function($entry) {
                    return !empty($entry['paid_at']);
                });
            } elseif ($paidStatus === 'unpaid') {
                $entries = array_filter($entries, function($entry) {
                    return empty($entry['paid_at']);
                });
            }
            
            $tech['entries'] = array_values($entries); // Re-index array
            
            // Recalculate totals based on filtered entries
            $tech['filtered_total_hours'] = array_sum(array_column($entries, 'hours_worked'));
            $tech['filtered_total_amount'] = array_sum(array_column($entries, 'subtotal'));
            $tech['filtered_entry_count'] = count($entries);
        }
        
        // Remove technicians with no entries after filtering
        if ($paidStatus !== 'all') {
            $weeklyData = array_filter($weeklyData, function($tech) {
                return !empty($tech['entries']);
            });
        }
        
        // Re-index array
        $weeklyData = array_values($weeklyData);
        
        // Calculate pagination
        $totalTechnicians = count($weeklyData);
        $totalPages = ceil($totalTechnicians / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        
        // Slice data for current page
        $pagedWeeklyData = array_slice($weeklyData, $offset, $itemsPerPage);
        
        $this->view('payments/index', [
            'technicians' => $technicians,
            'selectedTechnician' => $selectedTechnician,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'paidStatus' => $paidStatus,
            'weeklyData' => $pagedWeeklyData,
            'totalTechnicians' => $totalTechnicians,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'pageTitle' => __('payments.page_title')
        ]);
    }
    
    /**
     * Mark a week as paid for a technician (AJAX)
     */
    public function markPaid() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $technicianId = $_POST['technician_id'] ?? null;
            $weekStart = $_POST['week_start'] ?? null;
            $weekEnd = $_POST['week_end'] ?? null;
            $totalHours = $_POST['total_hours'] ?? 0;
            $totalAmount = $_POST['total_amount'] ?? 0;
            $notes = $_POST['notes'] ?? null;
            
            if (!$technicianId || !$weekStart || !$weekEnd) {
                throw new Exception('Missing required parameters');
            }
            
            // Get current user ID
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                throw new Exception('User not authenticated');
            }
            
            // Mark as paid
            $paymentId = $this->paymentModel->markAsPaid(
                $technicianId,
                $weekStart,
                $weekEnd,
                $totalHours,
                $totalAmount,
                $userId,
                $notes
            );
            
            echo json_encode([
                'success' => true,
                'message' => __('payments.marked_as_paid'),
                'payment_id' => $paymentId,
                'paid_at' => date('d/m/Y H:i')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark a payment as unpaid (revert) - AJAX
     */
    public function markUnpaid() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $paymentId = $_POST['payment_id'] ?? null;
            
            if (!$paymentId) {
                throw new Exception('Missing payment ID');
            }
            
            $success = $this->paymentModel->markAsUnpaid($paymentId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? __('payments.marked_as_unpaid') : __('payments.error_updating')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get payment history for a technician (AJAX)
     */
    public function history() {
        header('Content-Type: application/json');
        
        $technicianId = $_GET['technician_id'] ?? null;
        
        if (!$technicianId) {
            echo json_encode(['success' => false, 'message' => 'Missing technician ID']);
            return;
        }
        
        $history = $this->paymentModel->getPaymentHistory($technicianId);
        
        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
    }
    
    /**
     * Mark individual labor entries as paid (AJAX)
     */
    public function markEntriesPaid() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $laborIds = $_POST['labor_ids'] ?? [];
            
            if (empty($laborIds) || !is_array($laborIds)) {
                throw new Exception('No labor entries selected');
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                throw new Exception('User not authenticated');
            }
            
            $success = $this->paymentModel->markLaborEntriesAsPaid($laborIds, $userId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? __('payments.entries_marked_paid') : __('payments.error_updating'),
                'paid_at' => date('d/m/Y H:i')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark individual labor entries as unpaid (AJAX)
     */
    public function markEntriesUnpaid() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $laborIds = $_POST['labor_ids'] ?? [];
            
            if (empty($laborIds) || !is_array($laborIds)) {
                throw new Exception('No labor entries selected');
            }
            
            $success = $this->paymentModel->markLaborEntriesAsUnpaid($laborIds);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? __('payments.entries_marked_unpaid') : __('payments.error_updating')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark entire week as paid for a technician (AJAX)
     */
    public function markWeekPaid() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $technicianId = $_POST['technician_id'] ?? null;
            $weekStart = $_POST['week_start'] ?? null;
            $weekEnd = $_POST['week_end'] ?? null;
            
            if (!$technicianId || !$weekStart || !$weekEnd) {
                throw new Exception('Missing required parameters');
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                throw new Exception('User not authenticated');
            }
            
            $paymentId = $this->paymentModel->markWeekAsPaid($technicianId, $weekStart, $weekEnd, $userId);
            
            echo json_encode([
                'success' => true,
                'message' => __('payments.week_marked_paid'),
                'payment_id' => $paymentId,
                'paid_at' => date('d/m/Y H:i')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark entire week as unpaid for a technician (AJAX)
     */
    public function markWeekUnpaid() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $technicianId = $_POST['technician_id'] ?? null;
            $weekStart = $_POST['week_start'] ?? null;
            $weekEnd = $_POST['week_end'] ?? null;
            
            if (!$technicianId || !$weekStart || !$weekEnd) {
                throw new Exception('Missing required parameters');
            }
            
            $success = $this->paymentModel->markWeekAsUnpaid($technicianId, $weekStart, $weekEnd);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? __('payments.week_marked_unpaid') : __('payments.error_updating')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}

