<?php
/**
 * Quote Controller
 * Handles quote/proposal management operations
 */

class QuoteController extends BaseController {
    
    /**
     * Show quotes list
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? '',
            'customer' => $_GET['customer'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Get current page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get quotes
        $quoteModel = new Quote();
        $result = $quoteModel->getPaginated($page, ITEMS_PER_PAGE, $filters);
        
        // Get quote statuses (matching database enum values)
        $statuses = [
            'draft' => 'Πρόχειρο',
            'sent' => 'Απεσταλμένο',
            'accepted' => 'Αποδεκτό',
            'rejected' => 'Απορριφθέν',
            'expired' => 'Ληγμένο'
        ];
        
        $data = [
            'title' => 'Προσφορές - ' . APP_NAME,
            'user' => $user,
            'quotes' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $filters,
            'statuses' => $statuses
        ];
        
        parent::view('quotes/index', $data);
    }
    
    /**
     * Show quote details
     */
    public function details() {
        $user = $this->getCurrentUser();
        
        // Support both slug and ID
        $slug = $_GET['slug'] ?? '';
        $id = $_GET['id'] ?? 0;
        
        if (!$slug && !$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό προσφοράς';
            $this->redirect('/quotes');
        }
        
        $quoteModel = new Quote();
        
        if ($slug) {
            $quote = $quoteModel->getBySlug($slug);
        } else {
            $quote = $quoteModel->getWithDetails($id);
        }
        
        if (!$quote) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε';
            $this->redirect('/quotes');
        }
        
        $data = [
            'title' => 'Προσφορά: ' . $quote['quote_number'] . ' - ' . APP_NAME,
            'user' => $user,
            'quote' => $quote
        ];
        
        parent::view('quotes/view', $data);
    }
    
    /**
     * Show create quote form
     */
    public function create() {
        $user = $this->getCurrentUser();
        
        // Get pre-selected customer ID from query string (if coming from customer page)
        $preselectedCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        
        // Get customers for dropdown
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM customers WHERE is_active = 1 ORDER BY first_name, last_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate quote number
        $quoteModel = new Quote();
        $quoteNumber = $quoteModel->generateQuoteNumber();
        
        $data = [
            'title' => 'Νέα Προσφορά - ' . APP_NAME,
            'user' => $user,
            'customers' => $customers,
            'quote_number' => $quoteNumber,
            'preselected_customer_id' => $preselectedCustomerId,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        parent::view('quotes/create', $data);
    }
    
    /**
     * Store new quote
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/quotes/create');
        }
        
        $user = $this->getCurrentUser();
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'Το πεδίο τίτλος είναι υποχρεωτικό';
        }
        
        if (empty($_POST['customer_id'])) {
            $errors[] = 'Το πεδίο πελάτης είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/quotes/create');
        }
        
        // Create quote
        $quoteData = [
            'quote_number' => $_POST['quote_number'],
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'customer_id' => (int)$_POST['customer_id'],
            'valid_until' => !empty($_POST['valid_until']) ? $_POST['valid_until'] : date('Y-m-d', strtotime('+30 days')),
            'subtotal' => (float)($_POST['subtotal'] ?? 0),
            'tax_rate' => (float)($_POST['tax_rate'] ?? 24),
            'tax_amount' => (float)($_POST['tax_amount'] ?? 0),
            'total_amount' => (float)($_POST['total_amount'] ?? 0),
            'status' => $_POST['status'] ?? 'draft',
            'notes' => trim($_POST['notes'] ?? ''),
            'terms' => trim($_POST['terms'] ?? ''),
            'created_by' => $user['id']
        ];
        
        $quoteModel = new Quote();
        $quoteId = $quoteModel->create($quoteData);
        
        if ($quoteId) {
            // Save quote items if provided
            if (!empty($_POST['items'])) {
                $database = new Database();
                $db = $database->connect();
                
                foreach ($_POST['items'] as $index => $item) {
                    if (!empty($item['description'])) {
                        $itemData = [
                            'quote_id' => $quoteId,
                            'item_type' => $item['item_type'] ?? 'service',
                            'description' => trim($item['description']),
                            'quantity' => (float)($item['quantity'] ?? 1),
                            'unit_price' => (float)($item['unit_price'] ?? 0),
                            'total_price' => (float)($item['total_price'] ?? 0),
                            'sort_order' => $index + 1
                        ];
                        
                        $sql = "INSERT INTO quote_items (quote_id, item_type, description, quantity, unit_price, total_price, sort_order) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            $itemData['quote_id'],
                            $itemData['item_type'],
                            $itemData['description'],
                            $itemData['quantity'],
                            $itemData['unit_price'],
                            $itemData['total_price'],
                            $itemData['sort_order']
                        ]);
                    }
                }
            }
            
            $_SESSION['success'] = 'Η προσφορά δημιουργήθηκε με επιτυχία';
            $this->redirect('/quotes/details?id=' . $quoteId);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία της προσφοράς';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/quotes/create');
        }
    }
    
    /**
     * Show edit quote form
     */
    public function edit() {
        $user = $this->getCurrentUser();
        
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό προσφοράς';
            $this->redirect('/quotes');
        }
        
        $quoteModel = new Quote();
        $quote = $quoteModel->getWithDetails($id);
        
        if (!$quote) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε';
            $this->redirect('/quotes');
        }
        
        // Get customers for dropdown
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM customers WHERE is_active = 1 ORDER BY first_name, last_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => 'Επεξεργασία Προσφοράς - ' . APP_NAME,
            'user' => $user,
            'quote' => $quote,
            'customers' => $customers,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        parent::view('quotes/edit', $data);
    }
    
    /**
     * Update existing quote
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/quotes');
        }
        
        $user = $this->getCurrentUser();
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό προσφοράς';
            $this->redirect('/quotes');
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'Το πεδίο τίτλος είναι υποχρεωτικό';
        }
        
        if (empty($_POST['customer_id'])) {
            $errors[] = 'Το πεδίο πελάτης είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/quotes/edit?id=' . $id);
        }
        
        // Update quote
        $quoteData = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'customer_id' => (int)$_POST['customer_id'],
            'valid_until' => !empty($_POST['valid_until']) ? $_POST['valid_until'] : date('Y-m-d', strtotime('+30 days')),
            'subtotal' => (float)($_POST['subtotal'] ?? 0),
            'tax_rate' => (float)($_POST['tax_rate'] ?? 24),
            'tax_amount' => (float)($_POST['tax_amount'] ?? 0),
            'total_amount' => (float)($_POST['total_amount'] ?? 0),
            'status' => $_POST['status'] ?? 'draft',
            'notes' => trim($_POST['notes'] ?? ''),
            'terms' => trim($_POST['terms'] ?? '')
        ];
        
        $quoteModel = new Quote();
        $success = $quoteModel->update($id, $quoteData);
        
        if ($success) {
            // Update quote items
            $database = new Database();
            $db = $database->connect();
            
            // Delete existing items
            $stmt = $db->prepare("DELETE FROM quote_items WHERE quote_id = ?");
            $stmt->execute([$id]);
            
            // Add new items
            if (!empty($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
                    if (!empty($item['description'])) {
                        $itemData = [
                            'quote_id' => $id,
                            'item_type' => $item['item_type'] ?? 'service',
                            'description' => trim($item['description']),
                            'quantity' => (float)($item['quantity'] ?? 1),
                            'unit_price' => (float)($item['unit_price'] ?? 0),
                            'total_price' => (float)($item['total_price'] ?? 0),
                            'sort_order' => $index + 1
                        ];
                        
                        $sql = "INSERT INTO quote_items (quote_id, item_type, description, quantity, unit_price, total_price, sort_order) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            $itemData['quote_id'],
                            $itemData['item_type'],
                            $itemData['description'],
                            $itemData['quantity'],
                            $itemData['unit_price'],
                            $itemData['total_price'],
                            $itemData['sort_order']
                        ]);
                    }
                }
            }
            
            $_SESSION['success'] = 'Η προσφορά ενημερώθηκε με επιτυχία';
            $this->redirect('/quotes/details?id=' . $id);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση της προσφοράς';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/quotes/edit?id=' . $id);
        }
    }
    
    /**
     * Delete quote
     */
    public function delete() {
        $user = $this->getCurrentUser();
        
        // Accept both GET and POST for testing
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        
        // Skip CSRF validation in debug mode or GET requests
        if ($isPost && !DEBUG_MODE) {
            $this->validateCsrfToken();
        }
        
        // Get ID from POST or GET
        $id = $isPost ? ($_POST['id'] ?? 0) : ($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό προσφοράς';
            $this->redirect('/quotes');
        }
        
        $quoteModel = new Quote();
        
        // Debug: Log the delete attempt
        error_log("Attempting to delete quote with ID: " . $id);
        
        $success = $quoteModel->delete($id);
        
        // Debug: Log the result
        error_log("Delete result: " . ($success ? 'SUCCESS' : 'FAILED'));
        
        if ($success) {
            $_SESSION['success'] = 'Η προσφορά διαγράφηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή της προσφοράς';
        }
        
        $this->redirect('/quotes');
    }
}
