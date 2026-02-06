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
            
            // Validate required fields based on customer type
            $customerType = $data['customer_type'] ?? 'individual';
            
            if ($customerType === 'company') {
                $required = ['first_name', 'company_name', 'phone', 'address'];
            } else {
                $required = ['first_name', 'last_name', 'phone', 'address'];
            }
            
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
            
            // Check for duplicate customers
            $duplicates = $this->customerModel->findDuplicates(
                $data['email'] ?? null,
                $data['mobile'] ?? null
            );
            
            if (!empty($duplicates)) {
                $duplicateInfo = [];
                foreach ($duplicates as $dup) {
                    $name = $dup['customer_type'] === 'company' ? $dup['company_name'] : ($dup['first_name'] . ' ' . $dup['last_name']);
                    $matches = [];
                    if (!empty($data['email']) && $dup['email'] === $data['email']) {
                        $matches[] = 'email: ' . $dup['email'];
                    }
                    if (!empty($data['mobile']) && $dup['mobile'] === $data['mobile']) {
                        $matches[] = 'κινητό: ' . $dup['mobile'];
                    }
                    $duplicateInfo[] = $name . ' (' . implode(', ', $matches) . ')';
                }
                
                $_SESSION['form_data'] = $data;
                $message = 'Βρέθηκαν υπάρχοντες πελάτες με τα ίδια στοιχεία: ' . implode(' | ', $duplicateInfo);
                $this->flash('error', $message);
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
                $this->redirect('/customers/show?id=' . $customerId);
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
            $this->redirect('/customers/edit?id=' . $id);
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
                $this->redirect('/customers/edit?id=' . $id);
            }
            
            // Check for duplicate customers (excluding current customer)
            $duplicates = $this->customerModel->findDuplicates(
                $data['email'] ?? null,
                $data['mobile'] ?? null,
                $id  // Exclude current customer
            );
            
            if (!empty($duplicates)) {
                $duplicateInfo = [];
                foreach ($duplicates as $dup) {
                    $name = $dup['customer_type'] === 'company' ? $dup['company_name'] : ($dup['first_name'] . ' ' . $dup['last_name']);
                    $matches = [];
                    if (!empty($data['email']) && $dup['email'] === $data['email']) {
                        $matches[] = 'email: ' . $dup['email'];
                    }
                    if (!empty($data['mobile']) && $dup['mobile'] === $data['mobile']) {
                        $matches[] = 'κινητό: ' . $dup['mobile'];
                    }
                    $duplicateInfo[] = $name . ' (' . implode(', ', $matches) . ')';
                }
                
                $_SESSION['form_data'] = $data;
                $message = 'Βρέθηκαν υπάρχοντες πελάτες με τα ίδια στοιχεία: ' . implode(' | ', $duplicateInfo);
                $this->flash('error', $message);
                $this->redirect('/customers/edit?id=' . $id);
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
                $this->redirect('/customers/show?id=' . $id);
            } else {
                throw new Exception('Αποτυχία ενημέρωσης πελάτη');
            }
            
        } catch (Exception $e) {
            $this->flash('error', 'Σφάλμα: ' . $e->getMessage());
            $this->redirect('/customers/edit?id=' . $id);
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
                $this->redirect('/customers/show?id=' . $id);
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
            $this->redirect('/customers/show?id=' . $id);
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
                $this->redirect('/customers/show?id=' . $id);
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
        
        $this->redirect('/customers/show?id=' . $id);
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
    
    /**
     * Export all customers to CSV
     */
    public function exportCsv() {
        // Get all customers without pagination
        $customers = $this->customerModel->getAll();
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Type',
            'First Name',
            'Last Name',
            'Company Name',
            'Email',
            'Phone',
            'Mobile',
            'Address',
            'City',
            'Postal Code',
            'Country',
            'Tax ID',
            'Status',
            'Notes',
            'Created At'
        ]);
        
        // Add customer data
        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['id'],
                $customer['customer_type'] ?? '',
                $customer['first_name'] ?? '',
                $customer['last_name'] ?? '',
                $customer['company_name'] ?? '',
                $customer['email'] ?? '',
                $customer['phone'] ?? '',
                $customer['mobile'] ?? '',
                $customer['address'] ?? '',
                $customer['city'] ?? '',
                $customer['postal_code'] ?? '',
                $customer['country'] ?? '',
                $customer['tax_id'] ?? '',
                $customer['is_active'] == 1 ? 'active' : 'inactive',
                $customer['notes'] ?? '',
                $customer['created_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Download demo CSV file with sample data
     */
    public function downloadDemoCsv() {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="customers_demo.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add CSV headers
        fputcsv($output, [
            'Type',
            'First Name',
            'Last Name',
            'Company Name',
            'Email',
            'Phone',
            'Mobile',
            'Address',
            'City',
            'Postal Code',
            'Country',
            'Tax ID',
            'Status',
            'Notes'
        ]);
        
        // Add sample data
        $samples = [
            [
                'individual',
                'Γιάννης',
                'Παπαδόπουλος',
                '',
                'giannis@example.com',
                '2101234567',
                '6971234567',
                'Αθηνάς 10',
                'Αθήνα',
                '10552',
                'Ελλάδα',
                '',
                'active',
                'Σημειώσεις δοκιμής'
            ],
            [
                'company',
                'Μαρία',
                'Κωνσταντίνου',
                'Tech Solutions ΑΕ',
                'info@techsolutions.gr',
                '2109876543',
                '6979876543',
                'Συγγρού 50',
                'Αθήνα',
                '11742',
                'Ελλάδα',
                '123456789',
                'active',
                'Εταιρεία τεχνολογίας'
            ],
            [
                'individual',
                'Νίκος',
                'Ιωάννου',
                '',
                'nikos@example.com',
                '2310555666',
                '6945555666',
                'Τσιμισκή 100',
                'Θεσσαλονίκη',
                '54622',
                'Ελλάδα',
                '',
                'inactive',
                ''
            ]
        ];
        
        foreach ($samples as $sample) {
            fputcsv($output, $sample);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Import customers from CSV file
     */
    public function importCsv() {
        try {
            // Check if file was uploaded
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = __('customers.csv_file_required');
                header('Location: ?route=/customers');
                exit;
            }
            
            $file = $_FILES['csv_file']['tmp_name'];
            
            // Open CSV file
            $handle = fopen($file, 'r');
            if ($handle === false) {
                throw new Exception(__('customers.csv_invalid_format'));
            }
            
            // Read header row
            $headers = fgetcsv($handle);
            
            // Validate headers (basic check)
            $requiredHeaders = ['Type', 'First Name', 'Last Name', 'Email'];
            $headerMap = array_flip(array_map('strtolower', $headers));
            
            $importCount = 0;
            $errors = [];
            
            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                try {
                    // Map CSV columns to data array
                    $status = $this->getCsvValue($row, $headers, 'Status', 'active');
                    $data = [
                        'customer_type' => $this->getCsvValue($row, $headers, 'Type', 'individual'),
                        'first_name' => $this->getCsvValue($row, $headers, 'First Name'),
                        'last_name' => $this->getCsvValue($row, $headers, 'Last Name'),
                        'company_name' => $this->getCsvValue($row, $headers, 'Company Name'),
                        'email' => $this->getCsvValue($row, $headers, 'Email'),
                        'phone' => $this->getCsvValue($row, $headers, 'Phone'),
                        'mobile' => $this->getCsvValue($row, $headers, 'Mobile'),
                        'address' => $this->getCsvValue($row, $headers, 'Address'),
                        'city' => $this->getCsvValue($row, $headers, 'City'),
                        'postal_code' => $this->getCsvValue($row, $headers, 'Postal Code'),
                        'country' => $this->getCsvValue($row, $headers, 'Country', 'Ελλάδα'),
                        'tax_id' => $this->getCsvValue($row, $headers, 'Tax ID'),
                        'is_active' => ($status === 'active' || $status === '1') ? 1 : 0,
                        'notes' => $this->getCsvValue($row, $headers, 'Notes')
                    ];
                    
                    // Basic validation
                    if (empty($data['first_name']) || empty($data['last_name'])) {
                        continue;
                    }
                    
                    // Check for duplicates
                    if (!empty($data['email']) || !empty($data['mobile'])) {
                        $duplicates = $this->customerModel->findDuplicates($data['email'], $data['mobile'], null);
                        if (!empty($duplicates)) {
                            // Skip duplicate
                            continue;
                        }
                    }
                    
                    // Create customer
                    if ($this->customerModel->create($data)) {
                        $importCount++;
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            fclose($handle);
            
            // Set success message
            $_SESSION['success'] = str_replace('{count}', $importCount, __('customers.csv_import_success'));
            
            if (!empty($errors)) {
                $_SESSION['warning'] = implode(', ', array_slice($errors, 0, 5));
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = __('customers.csv_import_error') . ': ' . $e->getMessage();
        }
        
        header('Location: ?route=/customers');
        exit;
    }
    
    /**
     * Helper function to get CSV value by column name
     */
    private function getCsvValue($row, $headers, $columnName, $default = '') {
        $index = array_search($columnName, $headers);
        if ($index === false) {
            return $default;
        }
        return isset($row[$index]) && $row[$index] !== '' ? trim($row[$index]) : $default;
    }
}