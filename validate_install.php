<!--
HandyCRM v1.3.7 Installation Validator
Place this file at: ecowatt.gr/crm/validate_install.php
Run it: ecowatt.gr/crm/validate_install.php
DELETE after validation!
-->
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>HandyCRM v1.3.7 Validation</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .check { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 HandyCRM v1.3.7 Installation Validator</h1>
    <p><strong>⚠️ ΔΙΑΓΡΑΨΕ αυτό το αρχείο αμέσως μετά τον έλεγχο!</strong></p>
    <hr>
    
    <?php
    // Validation checks
    $errors = 0;
    $warnings = 0;
    $success = 0;
    
    // 1. Check config.php
    echo '<h2>1. Config File</h2>';
    if (file_exists('config/config.php')) {
        $config = file_get_contents('config/config.php');
        
        // Check for hardcoded € symbol
        if (strpos($config, "return \$formatted . ' €';") !== false) {
            echo '<div class="check success">✅ formatCurrency() έχει το hardcoded € symbol</div>';
            $success++;
        } else {
            echo '<div class="check error">❌ formatCurrency() δεν έχει το hardcoded € symbol (ΠΡΕΠΕΙ να το φτιάξεις χειροκίνητα!)</div>';
            $errors++;
        }
        
        // Check for formatNumber function
        if (strpos($config, 'function formatNumber(') !== false) {
            echo '<div class="check success">✅ Η συνάρτηση formatNumber() υπάρχει</div>';
            $success++;
        } else {
            echo '<div class="check warning">⚠️ Η συνάρτηση formatNumber() δε βρέθηκε (πρόσθεσε τη χειροκίνητα)</div>';
            $warnings++;
        }
    } else {
        echo '<div class="check error">❌ config/config.php δε βρέθηκε!</div>';
        $errors++;
    }
    
    // 2. Check Controllers
    echo '<h2>2. Controllers</h2>';
    $controllers = [
        'controllers/ProjectReportController.php',
        'controllers/ProjectTasksController.php'
    ];
    foreach ($controllers as $file) {
        if (file_exists($file)) {
            echo "<div class='check success'>✅ <code>$file</code> υπάρχει</div>";
            $success++;
        } else {
            echo "<div class='check error'>❌ <code>$file</code> δε βρέθηκε!</div>";
            $errors++;
        }
    }
    
    // 3. Check Views
    echo '<h2>3. Views</h2>';
    $views = [
        'views/projects/show.php',
        'views/projects/tasks/add.php',
        'views/projects/tasks/edit.php',
        'views/users/index.php'
    ];
    foreach ($views as $file) {
        if (file_exists($file)) {
            echo "<div class='check success'>✅ <code>$file</code> υπάρχει</div>";
            $success++;
        } else {
            echo "<div class='check error'>❌ <code>$file</code> δε βρέθηκε!</div>";
            $errors++;
        }
    }
    
    // 4. Check Languages
    echo '<h2>4. Languages</h2>';
    if (file_exists('languages/el.json')) {
        $lang = json_decode(file_get_contents('languages/el.json'), true);
        if (isset($lang['supervisor']) && isset($lang['assistant'])) {
            echo '<div class="check success">✅ el.json έχει τις μεταφράσεις supervisor και assistant</div>';
            $success++;
        } else {
            echo '<div class="check warning">⚠️ el.json δεν έχει supervisor ή assistant μεταφράσεις</div>';
            $warnings++;
        }
    } else {
        echo '<div class="check error">❌ languages/el.json δε βρέθηκε!</div>';
        $errors++;
    }
    
    // 5. Check Assets
    echo '<h2>5. Assets</h2>';
    if (file_exists('assets/js/date-formatter.js')) {
        echo '<div class="check success">✅ assets/js/date-formatter.js υπάρχει (fix για DD/MM/YYYY)</div>';
        $success++;
    } else {
        echo '<div class="check error">❌ assets/js/date-formatter.js δε βρέθηκε! (Οι ημερομηνίες θα είναι λάθος!)</div>';
        $errors++;
    }
    
    // 6. Check Libraries
    echo '<h2>6. Libraries (PDF Generation)</h2>';
    if (file_exists('lib/tcpdf')) {
        echo '<div class="check success">✅ lib/tcpdf υπάρχει (PDF generation θα δουλεύει)</div>';
        $success++;
    } else {
        echo '<div class="check error">❌ lib/tcpdf δε βρέθηκε! (Τα PDF reports ΔΕΝ θα δουλεύουν!)</div>';
        $errors++;
    }
    
    // 7. Check Database
    echo '<h2>7. Database Schema</h2>';
    require_once 'config/config.php';
    require_once 'config/database.php';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if task_labor has new columns
        $result = $conn->query("SHOW COLUMNS FROM task_labor LIKE 'technician_name'");
        if ($result->num_rows > 0) {
            echo '<div class="check success">✅ Η στήλη <code>task_labor.technician_name</code> υπάρχει</div>';
            $success++;
        } else {
            echo '<div class="check error">❌ Η στήλη <code>task_labor.technician_name</code> δε βρέθηκε! (Δεν έτρεξες το SQL migration!)</div>';
            $errors++;
        }
        
        $result = $conn->query("SHOW COLUMNS FROM task_labor LIKE 'hours_worked'");
        if ($result->num_rows > 0) {
            echo '<div class="check success">✅ Η στήλη <code>task_labor.hours_worked</code> υπάρχει</div>';
            $success++;
        } else {
            echo '<div class="check error">❌ Η στήλη <code>task_labor.hours_worked</code> δε βρέθηκε! (Δεν έτρεξες το SQL migration!)</div>';
            $errors++;
        }
        
    } catch (Exception $e) {
        echo '<div class="check error">❌ Σφάλμα σύνδεσης με τη βάση: ' . $e->getMessage() . '</div>';
        $errors++;
    }
    
    // Summary
    echo '<hr>';
    echo '<h2>📊 Συνολικό Αποτέλεσμα</h2>';
    echo "<p><strong>✅ Success:</strong> $success</p>";
    echo "<p><strong>⚠️ Warnings:</strong> $warnings</p>";
    echo "<p><strong>❌ Errors:</strong> $errors</p>";
    
    if ($errors == 0 && $warnings == 0) {
        echo '<div class="check success"><h3>🎉 Τέλεια! Η εγκατάσταση ολοκληρώθηκε επιτυχώς!</h3></div>';
    } elseif ($errors == 0) {
        echo '<div class="check warning"><h3>⚠️ Η εγκατάσταση ολοκληρώθηκε με προειδοποιήσεις. Έλεγξε τα warnings.</h3></div>';
    } else {
        echo '<div class="check error"><h3>❌ Υπάρχουν σφάλματα! Διόρθωσέ τα και ξανάτρεξε το validation.</h3></div>';
    }
    
    echo '<hr>';
    echo '<div class="check warning"><strong>⚠️ ΔΙΑΓΡΑΨΕ αυτό το αρχείο (validate_install.php) ΤΩΡΑ!</strong></div>';
    ?>
</div>
</body>
</html>
