<?php
/**
 * Customer Controller
 * Handles customer management operations
 */

class CustomerController extends BaseController {
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->customerModel = new Customer();
    }
    
    /**
     * Display customers list with pagination and search
     */
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        
        // Get paginated customers
        $customers = $this->customerModel->getPaginated($page, ITEMS_PER_PAGE, $search, $type);
        
        $data = [
            'title' => __('customers.title') . ' - ' . APP_NAME,
            'customers' => $customers,
            'search' => $search,
            'type' => $type,
            'pagination' => $this->buildPagination($customers, '/customers', ['search' => $search, 'type' => $type])
        ];
        
        $this->view('customers/index', $data);
    }
    
    /**
     * Show create customer form
     */
    public function create() {
        $data = [
            'title' => __('customers.new_customer') . ' - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken(),
            'customer' => null
        ];
        
        $this->view('customers/create', $data);
    }
    
    /**
     * Store new customer
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customers/create');
        }
        
        try {
            $this->validateCsrfToken();
            
            // Sanitize input
            $data = $this->sanitize($_POST);
            
            // Validate required fields
            $required = ['first_name', 'last_name', 'phone', 'address'];
            $errors = $this->validateRequired($data, $required);
            
            // Additional validations
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Παρακαλώ εισάγετε έγκυρο email';
            }
            
            if (!empty($errors)) {
                $_SESSION['form_data'] = $data;
                $_SESSION['form_errors'] = $errors;
                $this->flash('error', 'Παρακαλώ διορθώστε τα σφάλματα στη φόρμα');
                $this->redirect('/customers/create');
            }
            
            // Prepare data for insertion
            $customerData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'company_name' => $data['company_name'] ?? null,
                'customer_type' => $data['customer_type'] ?? 'individual',
                'phone' => $data['phone'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $_SESSION['user_id']
            ];
            
            $customerId = $this->customerModel->create($customerData);
            
            if ($customerId) {
                // Generate slug
                $slug = $this->customerModel->generateSlug(
                    $customerId,
                    $customerData['first_name'],
                    $customerData['last_name'],
                    $customerData['company_name'] ?? null,
                    $customerData['customer_type']
                );
                
                $this->flash('success', 'Ο πελάτης δημιουργήθηκε επιτυχώς');
                $this->redirect('/customers/' . $slug);
            } else {
                throw new Exception('Αποτυχία δημιουργίας πελάτη');
            }
            
        } catch (Exception $e) {
            $this->flash('error', 'Σφάλμα: ' . $e->getMessage());
            $this->redirect('/customers/create');
        }
    }
    
    /**
     * Show customer details
     */
    public function show($id) {
        // Support both slug and ID
        $slug = $_GET['slug'] ?? '';
        
        if ($slug) {
            $customer = $this->customerModel->getBySlug($slug);
        } else {
            $customer = $this->customerModel->getWithHistory($id);
        }
        
        if (!$customer) {
            $this->flash('error', 'Ο πελάτης δεν βρέθηκε');
            $this->redirect('/customers');
        }
        
        $data = [
            'title' => __('customers.title') . ': ' . $this->customerModel->getFullName($customer) . ' - ' . APP_NAME,
            'customer' => $customer
        ];
        
        $this->view('customers/show', $data);
    }
    
    /**
     * Show edit customer form
     */
    public function edit($id) {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            $this->flash('error', 'Ο πελάτης δεν βρέθηκε');
            $this->redirect('/customers');
        }
        
        $data = [
            'title' => __('customers.edit') . ' ' . __('customers.title') . ' - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken(),
            'customer' => $customer
        ];
        
        $this->view('customers/edit', $data);
    }
    
    /**
     * Update customer
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customers/' . $id . '/edit');
        }
        
        try {
            $this->validateCsrfToken();
            
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                $this->flash('error', 'Ο πελάτης δεν βρέθηκε');
                $this->redirect('/customers');
            }
            
            // Sanitize input
            $data = $this->sanitize($_POST);
            
            // Validate required fields
            $required = ['first_name', 'last_name', 'phone', 'address'];
            $errors = $this->validateRequired($data, $required);
            
            // Additional validations
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Παρακαλώ εισάγετε έγκυρο email';
            }
            
            if (!empty($errors)) {
                $_SESSION['form_data'] = $data;
                $_SESSION['form_errors'] = $errors;
                $this->flash('error', 'Παρακαλώ διορθώστε τα σφάλματα στη φόρμα');
                $this->redirect('/customers/' . $id . '/edit');
            }
            
            // Prepare data for update
            $customerData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'company_name' => $data['company_name'] ?? null,
                'customer_type' => $data['customer_type'] ?? 'individual',
                'phone' => $data['phone'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'notes' => $data['notes'] ?? null
            ];
            
            $updated = $this->customerModel->update($id, $customerData);
            
            if ($updated) {
                $this->flash('success', 'Ο πελάτης ενημερώθηκε επιτυχώς');
                $this->redirect('/customers/' . $id);
            } else {
                throw new Exception('Αποτυχία ενημέρωσης πελάτη');
            }
            
        } catch (Exception $e) {
            $this->flash('error', 'Σφάλμα: ' . $e->getMessage());
            $this->redirect('/customers/' . $id . '/edit');
        }
    }
    
    /**
     * Delete customer
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customers');
        }
        
        try {
            $this->validateCsrfToken();
            
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                $this->flash('error', 'Ο πελάτης δεν βρέθηκε');
                $this->redirect('/customers');
            }
            
            // Check if customer has associated projects
            $projectCount = $this->customerModel->query(
                "SELECT COUNT(*) as count FROM projects WHERE customer_id = ?", 
                [$id]
            )[0]['count'];
            
            if ($projectCount > 0) {
                $this->flash('error', 'Δεν μπορείτε να διαγράψετε πελάτη που έχει συνδεδεμένα έργα');
                $this->redirect('/customers/' . $id);
            }
            
            // Soft delete - set is_active to 0
            $deleted = $this->customerModel->update($id, ['is_active' => 0]);
            
            if ($deleted) {
                $this->flash('success', 'Ο πελάτης διαγράφηκε επιτυχώς');
            } else {
                throw new Exception('Αποτυχία διαγραφής πελάτη');
            }
            
        } catch (Exception $e) {
            $this->flash('error', 'Σφάλμα: ' . $e->getMessage());
        }
        
        $this->redirect('/customers');
    }
    
    /**
     * Add communication record
     */
    public function addCommunication($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/customers/' . $id);
        }
        
        try {
            $this->validateCsrfToken();
            
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                $this->flash('error', 'Ο πελάτης δεν βρέθηκε');
                $this->redirect('/customers');
            }
            
            $data = $this->sanitize($_POST);
            
            $required = ['communication_type', 'description'];
            $errors = $this->validateRequired($data, $required);
            
            if (!empty($errors)) {
                $this->flash('error', 'Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία');
                $this->redirect('/customers/' . $id);
            }
            
            $this->customerModel->addCommunication(
                $id,
                $_SESSION['user_id'],
                $data['communication_type'],
                $data['subject'] ?? '',
                $data['description'],
                $data['communication_date'] ?? null
            );
            
            $this->flash('success', 'Η επικοινωνία καταγράφηκε επιτυχώς');
            
        } catch (Exception $e) {
            $this->flash('error', 'Σφάλμα: ' . $e->getMessage());
        }
        
        $this->redirect('/customers/' . $id);
    }
    
    /**
     * Search customers (AJAX)
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['error' => 'Invalid request method'], 405);
        }
        
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            $this->json(['customers' => []]);
        }
        
        $customers = $this->customerModel->search($query, 10);
        
        // Format results for autocomplete
        $results = array_map(function($customer) {
            return [
                'id' => $customer['id'],
                'name' => $this->customerModel->getFullName($customer),
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'type' => $customer['customer_type']
            ];
        }, $customers);
        
        $this->json(['customers' => $results]);
    }
    
    /**
     * Build pagination HTML
     */
    private function buildPagination($paginationData, $baseUrl, $params = []) {
        $currentPage = $paginationData['current_page'];
        $totalPages = $paginationData['total_pages'];
        $hasPrev = $paginationData['has_prev'];
        $hasNext = $paginationData['has_next'];
        
        if ($totalPages <= 1) {
            return '';
        }
        
        $queryString = http_build_query($params);
        $separator = $queryString ? '&' : '?';
        
        $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
        
        // Previous page
        if ($hasPrev) {
            $prevPage = $currentPage - 1;
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $baseUrl . '?' . $queryString . $separator . 'page=' . $prevPage . '">Προηγούμενη</a>';
            $html .= '</li>';
        }
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $currentPage ? ' active' : '';
            $html .= '<li class="page-item' . $active . '">';
            $html .= '<a class="page-link" href="' . $baseUrl . '?' . $queryString . $separator . 'page=' . $i . '">' . $i . '</a>';
            $html .= '</li>';
        }
        
        // Next page
        if ($hasNext) {
            $nextPage = $currentPage + 1;
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $baseUrl . '?' . $queryString . $separator . 'page=' . $nextPage . '">Επόμενη</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
}