<?php
/**
 * Payments Controller
 * Manages weekly technician payments
 */

class PaymentsController extends Controller {
    private $paymentModel;
    private $technicianModel;
    
    public function __construct() {
        parent::__construct();
        $this->paymentModel = new Payment();
        $this->technicianModel = new Technician();
    }
    
    /**
     * Display payments page with filters
     */
    public function index() {
        // Get all technicians for dropdown
        $technicians = $this->technicianModel->getAll();
        
        // Get filter parameters
        $selectedTechnician = $_GET['technician_id'] ?? null;
        $weekStart = $_GET['week_start'] ?? null;
        $weekEnd = $_GET['week_end'] ?? null;
        
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
        
        // Get labor entries for each technician
        foreach ($weeklyData as &$tech) {
            $tech['entries'] = $this->paymentModel->getLaborEntriesForWeek(
                $tech['technician_id'],
                $weekStart,
                $weekEnd
            );
        }
        
        $this->view('payments/index', [
            'technicians' => $technicians,
            'selectedTechnician' => $selectedTechnician,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weeklyData' => $weeklyData,
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
}
