<?php

require_once __DIR__ . '/../models/Trash.php';

class TrashController {
    private $trashModel;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->trashModel = new Trash($db);
        
        // Έλεγχος αν ο χρήστης είναι admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Δεν έχετε δικαίωμα πρόσβασης στον Κάδο Απορριμμάτων';
            header('Location: ' . BASE_URL);
            exit;
        }
    }
    
    /**
     * Κύρια σελίδα κάδου με tabs
     */
    public function index() {
        $type = $_GET['type'] ?? 'project';
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        
        // Λήψη διαγραμμένων στοιχείων
        $items = $this->trashModel->getDeletedItems($type, $search, $dateFrom, $dateTo, $page, $perPage);
        
        // Λήψη αριθμών για τα badges
        $counts = $this->trashModel->getDeletedCountByType();
        
        require_once __DIR__ . '/../views/trash/index.php';
    }
    
    /**
     * Επαναφορά μεμονωμένου στοιχείου
     */
    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?route=/trash');
            exit;
        }
        
        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? 0;
        
        if (empty($type) || empty($id)) {
            $_SESSION['error'] = 'Μη έγκυρα δεδομένα';
            header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['full_name'] ?? $_SESSION['username'];
        
        if ($this->trashModel->restoreItem($type, $id, $userId, $userName)) {
            $_SESSION['success'] = 'Το στοιχείο επαναφέρθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την επαναφορά του στοιχείου';
        }
        
        header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
        exit;
    }
    
    /**
     * Οριστική διαγραφή μεμονωμένου στοιχείου
     */
    public function permanentDelete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?route=/trash');
            exit;
        }
        
        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? 0;
        
        if (empty($type) || empty($id)) {
            $_SESSION['error'] = 'Μη έγκυρα δεδομένα';
            header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['full_name'] ?? $_SESSION['username'];
        
        if ($this->trashModel->permanentDeleteItem($type, $id, $userId, $userName)) {
            $_SESSION['success'] = 'Το στοιχείο διαγράφηκε οριστικά';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την οριστική διαγραφή';
        }
        
        header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
        exit;
    }
    
    /**
     * Μαζική επαναφορά
     */
    public function bulkRestore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?route=/trash');
            exit;
        }
        
        $type = $_POST['type'] ?? '';
        $ids = $_POST['ids'] ?? [];
        
        if (empty($type) || empty($ids) || !is_array($ids)) {
            $_SESSION['error'] = 'Δεν επιλέχθηκαν στοιχεία';
            header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['full_name'] ?? $_SESSION['username'];
        
        $successCount = 0;
        foreach ($ids as $id) {
            if ($this->trashModel->restoreItem($type, $id, $userId, $userName)) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            $_SESSION['success'] = "Επαναφέρθηκαν {$successCount} στοιχεία επιτυχώς";
        } else {
            $_SESSION['error'] = 'Δεν ήταν δυνατή η επαναφορά των στοιχείων';
        }
        
        header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
        exit;
    }
    
    /**
     * Μαζική οριστική διαγραφή
     */
    public function bulkDelete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?route=/trash');
            exit;
        }
        
        $type = $_POST['type'] ?? '';
        $ids = $_POST['ids'] ?? [];
        
        if (empty($type) || empty($ids) || !is_array($ids)) {
            $_SESSION['error'] = 'Δεν επιλέχθηκαν στοιχεία';
            header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['full_name'] ?? $_SESSION['username'];
        
        $successCount = 0;
        foreach ($ids as $id) {
            if ($this->trashModel->permanentDeleteItem($type, $id, $userId, $userName)) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            $_SESSION['success'] = "Διαγράφηκαν οριστικά {$successCount} στοιχεία";
        } else {
            $_SESSION['error'] = 'Δεν ήταν δυνατή η διαγραφή των στοιχείων';
        }
        
        header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
        exit;
    }
    
    /**
     * Άδειασμα ολόκληρου του κάδου για συγκεκριμένο τύπο
     */
    public function emptyTrash() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?route=/trash');
            exit;
        }
        
        $type = $_POST['type'] ?? '';
        
        if (empty($type)) {
            $_SESSION['error'] = 'Μη έγκυρος τύπος';
            header('Location: ' . BASE_URL . '/?route=/trash');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['full_name'] ?? $_SESSION['username'];
        
        if ($this->trashModel->emptyTrash($type, $userId, $userName)) {
            $_SESSION['success'] = 'Ο κάδος αδειάστηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά το άδειασμα του κάδου';
        }
        
        header('Location: ' . BASE_URL . '/?route=/trash&type=' . $type);
        exit;
    }
    
    /**
     * Προβολή ιστορικού διαγραφών
     */
    public function viewLog() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $type = $_GET['type'] ?? '';
        $action = $_GET['action'] ?? '';
        $perPage = 50;
        
        $logs = $this->trashModel->getDeletionLog($page, $perPage, $type, $action);
        
        require_once __DIR__ . '/../views/trash/log.php';
    }
}
