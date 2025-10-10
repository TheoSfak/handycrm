<?php
/**
 * Invoice Controller
 * Handles invoice management operations
 */

class InvoiceController extends BaseController {
    
    /**
     * Show invoices list
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? '',
            'customer' => $_GET['customer'] ?? '',
            'payment_status' => $_GET['payment_status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Get current page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get invoices
        $invoiceModel = new Invoice();
        $result = $invoiceModel->getPaginated($page, ITEMS_PER_PAGE, $filters);
        
        // Get filter options
        $database = new Database();
        $db = $database->connect();
        
        // Get all customers
        $stmt = $db->query("SELECT id, first_name, last_name, company_name, customer_type FROM customers WHERE is_active = 1 ORDER BY first_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Define statuses
        $statuses = [
            'draft' => 'Πρόχειρο',
            'sent' => 'Απεσταλμένο',
            'viewed' => 'Αναγνωσμένο',
            'paid' => 'Πληρωμένο',
            'cancelled' => 'Ακυρωμένο'
        ];
        
        $paymentStatuses = [
            'unpaid' => 'Απλήρωτο',
            'partial' => 'Μερική Πληρωμή',
            'paid' => 'Πληρωμένο',
            'overdue' => 'Εκπρόθεσμο'
        ];
        
        $data = [
            'title' => __('invoices.title') . ' - ' . APP_NAME,
            'user' => $user,
            'invoices' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $filters,
            'customers' => $customers,
            'statuses' => $statuses,
            'paymentStatuses' => $paymentStatuses
        ];
        
        $this->view('invoices/index', $data);
    }
    
    /**
     * Show create invoice form
     */
    public function create() {
        $user = $this->getCurrentUser();
        
        // Get pre-selected customer ID from query string (if coming from customer page)
        $preselectedCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        
        $database = new Database();
        $db = $database->connect();
        
        // Get customers
        $stmt = $db->query("SELECT id, first_name, last_name, company_name, customer_type FROM customers WHERE is_active = 1 ORDER BY first_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get projects - if customer is preselected, filter by that customer
        if ($preselectedCustomerId) {
            $stmt = $db->prepare("SELECT id, title FROM projects WHERE customer_id = ? AND status NOT IN ('completed', 'cancelled') ORDER BY title");
            $stmt->execute([$preselectedCustomerId]);
        } else {
            $stmt = $db->query("SELECT id, title FROM projects WHERE status NOT IN ('completed', 'cancelled') ORDER BY title");
        }
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate invoice number
        $invoiceModel = new Invoice();
        $invoiceNumber = $invoiceModel->generateInvoiceNumber();
        
        $data = [
            'title' => __('invoices.new_invoice') . ' - ' . APP_NAME,
            'user' => $user,
            'customers' => $customers,
            'projects' => $projects,
            'invoiceNumber' => $invoiceNumber,
            'preselected_customer_id' => $preselectedCustomerId
        ];
        
        $this->view('invoices/create', $data);
    }
    
    /**
     * Store new invoice
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/invoices');
        }
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/invoices/create');
            }
        }
        
        $user = $this->getCurrentUser();
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['customer_id'])) {
            $errors[] = 'Παρακαλώ επιλέξτε πελάτη';
        }
        
        if (empty($_POST['issue_date'])) {
            $errors[] = 'Παρακαλώ εισάγετε ημερομηνία τιμολογίου';
        }
        
        if (empty($_POST['due_date'])) {
            $errors[] = 'Παρακαλώ εισάγετε ημερομηνία λήξης';
        }
        
        // Validate items
        if (empty($_POST['items']) || !is_array($_POST['items'])) {
            $errors[] = 'Παρακαλώ προσθέστε τουλάχιστον ένα προϊόν/υπηρεσία';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/invoices/create');
        }
        
        // Prepare invoice data
        $invoiceData = [
            'invoice_number' => trim($_POST['invoice_number']),
            'customer_id' => (int)$_POST['customer_id'],
            'project_id' => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'issue_date' => $_POST['issue_date'],
            'due_date' => $_POST['due_date'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'subtotal' => (float)$_POST['subtotal'],
            'vat_rate' => (float)$_POST['vat_rate'],
            'vat_amount' => (float)$_POST['vat_amount'],
            'total_amount' => (float)$_POST['total_amount'],
            'payment_status' => 'unpaid',
            'status' => $_POST['status'] ?? 'draft',
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => $user['id']
        ];
        
        // Prepare items
        $items = [];
        foreach ($_POST['items'] as $item) {
            if (!empty($item['description'])) {
                $items[] = [
                    'description' => trim($item['description']),
                    'quantity' => (float)$item['quantity'],
                    'unit_price' => (float)$item['unit_price'],
                    'total_price' => (float)$item['total_price']
                ];
            }
        }
        
        // Create invoice
        $invoiceModel = new Invoice();
        $invoiceId = $invoiceModel->createWithItems($invoiceData, $items);
        
        if ($invoiceId) {
            $_SESSION['success'] = 'Το τιμολόγιο δημιουργήθηκε με επιτυχία';
            $this->redirect('/invoices/view?id=' . $invoiceId);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία του τιμολογίου';
            $this->redirect('/invoices/create');
        }
    }
    
    /**
     * Show invoice details
     */
    public function details() {
        $user = $this->getCurrentUser();
        
        // Support both slug and ID
        $slug = $_GET['slug'] ?? '';
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$slug && !$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό τιμολογίου';
            $this->redirect('/invoices');
        }
        
        $invoiceModel = new Invoice();
        
        if ($slug) {
            $invoice = $invoiceModel->getBySlug($slug);
        } else {
            $invoice = $invoiceModel->getWithDetails($id);
        }
        
        if (!$invoice) {
            $_SESSION['error'] = 'Το τιμολόγιο δεν βρέθηκε';
            $this->redirect('/invoices');
        }
        
        $data = [
            'title' => __('invoices.view') . ' ' . __('invoices.title') . ' - ' . APP_NAME,
            'user' => $user,
            'invoice' => $invoice
        ];
        
        $this->view('invoices/view', $data);
    }
    
    /**
     * Show edit invoice form
     */
    public function edit() {
        $user = $this->getCurrentUser();
        
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό τιμολογίου';
            $this->redirect('/invoices');
        }
        
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->getWithDetails($id);
        
        if (!$invoice) {
            $_SESSION['error'] = 'Το τιμολόγιο δεν βρέθηκε';
            $this->redirect('/invoices');
        }
        
        // Cannot edit paid invoices
        if ($invoice['payment_status'] === 'paid') {
            $_SESSION['error'] = 'Δεν μπορείτε να επεξεργαστείτε πληρωμένο τιμολόγιο';
            $this->redirect('/invoices/view?id=' . $id);
        }
        
        $database = new Database();
        $db = $database->connect();
        
        // Get customers
        $stmt = $db->query("SELECT id, first_name, last_name, company_name, customer_type FROM customers WHERE is_active = 1 ORDER BY first_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get projects
        $stmt = $db->query("SELECT id, title FROM projects WHERE status NOT IN ('completed', 'cancelled') ORDER BY title");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => __('invoices.edit') . ' ' . __('invoices.title') . ' - ' . APP_NAME,
            'user' => $user,
            'invoice' => $invoice,
            'customers' => $customers,
            'projects' => $projects
        ];
        
        $this->view('invoices/edit', $data);
    }
    
    /**
     * Update invoice
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/invoices');
        }
        
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό τιμολογίου';
            $this->redirect('/invoices');
        }
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/invoices/edit?id=' . $id);
            }
        }
        
        $user = $this->getCurrentUser();
        
        // Check if invoice can be edited
        $invoiceModel = new Invoice();
        $existingInvoice = $invoiceModel->find($id);
        
        if (!$existingInvoice) {
            $_SESSION['error'] = 'Το τιμολόγιο δεν βρέθηκε';
            $this->redirect('/invoices');
        }
        
        if ($existingInvoice['payment_status'] === 'paid') {
            $_SESSION['error'] = 'Δεν μπορείτε να επεξεργαστείτε πληρωμένο τιμολόγιο';
            $this->redirect('/invoices/view?id=' . $id);
        }
        
        // Update invoice data
        $invoiceData = [
            'customer_id' => (int)$_POST['customer_id'],
            'project_id' => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'issue_date' => $_POST['issue_date'],
            'due_date' => $_POST['due_date'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'subtotal' => (float)$_POST['subtotal'],
            'vat_rate' => (float)$_POST['vat_rate'],
            'vat_amount' => (float)$_POST['vat_amount'],
            'total_amount' => (float)$_POST['total_amount'],
            'status' => $_POST['status'] ?? 'draft',
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Prepare items
        $items = [];
        foreach ($_POST['items'] as $item) {
            if (!empty($item['description'])) {
                $items[] = [
                    'description' => trim($item['description']),
                    'quantity' => (float)$item['quantity'],
                    'unit_price' => (float)$item['unit_price'],
                    'total_price' => (float)$item['total_price']
                ];
            }
        }
        
        // Update invoice
        $success = $invoiceModel->updateWithItems($id, $invoiceData, $items);
        
        if ($success) {
            $_SESSION['success'] = 'Το τιμολόγιο ενημερώθηκε με επιτυχία';
            $this->redirect('/invoices/view?id=' . $id);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του τιμολογίου';
            $this->redirect('/invoices/edit?id=' . $id);
        }
    }
    
    /**
     * Delete invoice
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/invoices');
        }
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/invoices');
            }
        }
        
        $user = $this->getCurrentUser();
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό τιμολογίου';
            $this->redirect('/invoices');
        }
        
        // Check if invoice can be deleted
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($id);
        
        if (!$invoice) {
            $_SESSION['error'] = 'Το τιμολόγιο δεν βρέθηκε';
            $this->redirect('/invoices');
        }
        
        if ($invoice['status'] === 'paid') {
            $_SESSION['error'] = 'Δεν μπορείτε να διαγράψετε πληρωμένο τιμολόγιο';
            $this->redirect('/invoices');
        }
        
        $success = $invoiceModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Το τιμολόγιο διαγράφηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του τιμολογίου';
        }
        
        $this->redirect('/invoices');
    }
    
    /**
     * Update invoice status
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/invoices');
        }
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/invoices');
            }
        }
        
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        
        if (!$invoiceId || !$newStatus) {
            $_SESSION['error'] = 'Μη έγκυρα δεδομένα';
            $this->redirect('/invoices');
        }
        
        // Validate status
        $validStatuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['error'] = 'Μη έγκυρο status';
            $this->redirect('/invoices/view?id=' . $invoiceId);
        }
        
        // Get invoice details
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($invoiceId);
        
        if (!$invoice) {
            $_SESSION['error'] = 'Το τιμολόγιο δεν βρέθηκε';
            $this->redirect('/invoices');
        }
        
        // Build update data
        $database = new Database();
        $db = $database->connect();
        
        // If status is 'paid', auto-set paid_date and paid_amount
        if ($newStatus === 'paid') {
            $stmt = $db->prepare("UPDATE invoices SET 
                status = ?, 
                paid_date = COALESCE(paid_date, CURDATE()), 
                paid_amount = COALESCE(NULLIF(paid_amount, 0), total_amount)
                WHERE id = ?");
            $stmt->execute([$newStatus, $invoiceId]);
        } else {
            $stmt = $db->prepare("UPDATE invoices SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $invoiceId]);
        }
        
        // Status labels in Greek
        $statusLabels = [
            'draft' => 'Πρόχειρο',
            'sent' => 'Απεσταλμένο',
            'paid' => 'Πληρωμένο',
            'overdue' => 'Ληξιπρόθεσμο',
            'cancelled' => 'Ακυρωμένο'
        ];
        
        $_SESSION['success'] = 'Το status του τιμολογίου άλλαξε σε "' . $statusLabels[$newStatus] . '"';
        $this->redirect('/invoices/view?id=' . $invoiceId);
    }
    
    /**
     * Update payment status (legacy method - kept for compatibility)
     */
    public function updatePaymentStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/invoices');
        }
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/invoices');
            }
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $paymentStatus = $_POST['payment_status'] ?? '';
        
        if (!$id || !$paymentStatus) {
            $_SESSION['error'] = 'Μη έγκυρα δεδομένα';
            $this->redirect('/invoices');
        }
        
        $invoiceModel = new Invoice();
        $data = [
            'payment_status' => $paymentStatus,
            'paid_date' => ($paymentStatus === 'paid') ? date('Y-m-d') : null,
            'payment_reference' => trim($_POST['payment_reference'] ?? '')
        ];
        
        $success = $invoiceModel->update($id, $data);
        
        if ($success) {
            $_SESSION['success'] = 'Η κατάσταση πληρωμής ενημερώθηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση της κατάστασης πληρωμής';
        }
        
        $this->redirect('/invoices/view?id=' . $id);
    }
}
