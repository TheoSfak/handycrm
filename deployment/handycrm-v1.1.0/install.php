<?php
// HandyCRM Installation Script - Simple and Clean

// Check if already installed
if (file_exists('config/config.php')) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Already Installed</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>';
    echo '<body class="bg-light"><div class="container mt-5"><div class="alert alert-warning">';
    echo '<h4>Το HandyCRM είναι ήδη εγκατεστημένο!</h4>';
    echo '<p>Για επανεγκατάσταση, διέγραψε το αρχείο config/config.php</p>';
    echo '<a href="index.php" class="btn btn-primary">Μετάβαση στο HandyCRM</a></div></div></body></html>';
    exit;
}

$step = $_GET['step'] ?? 1;
$errors = [];
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 1) {
    $dbHost = $_POST['db_host'];
    $dbName = $_POST['db_name'];
    $dbUser = $_POST['db_user'];
    $dbPass = $_POST['db_pass'];
    $appUrl = rtrim($_POST['app_url'], '/');
    
    try {
        // Connect and create database
        $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Try to create database (might fail if no CREATE privileges)
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            // Database might already exist or user doesn't have CREATE privilege
            // Try to use it instead
        }
        
        $pdo->exec("USE `$dbName`");
        
        // Import SQL
        $sql = file_get_contents('database/handycrm.sql');
        
        // Better SQL parsing - handle multi-line statements
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove block comments
        $statements = explode(';', $sql);
        
        $importedCount = 0;
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt) && strlen($stmt) > 5) {
                try {
                    $pdo->exec($stmt);
                    $importedCount++;
                } catch (PDOException $e) {
                    // Log but continue - table might already exist
                    error_log("SQL Error: " . $e->getMessage() . " in statement: " . substr($stmt, 0, 100));
                }
            }
        }
        
        // Create config file
        $configTemplate = file_get_contents('config/config.example.php');
        
        // Generate random secret key
        $secretKey = bin2hex(random_bytes(32));
        
        // Replace database configuration - using regex for better matching
        $config = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '$dbHost');", $configTemplate);
        $config = preg_replace("/define\('DB_NAME', '.*?'\);/", "define('DB_NAME', '$dbName');", $config);
        $config = preg_replace("/define\('DB_USER', '.*?'\);/", "define('DB_USER', '$dbUser');", $config);
        $config = preg_replace("/define\('DB_PASS', '.*?'\);/", "define('DB_PASS', '$dbPass');", $config);
        $config = preg_replace("/define\('APP_URL', '.*?'\);/", "define('APP_URL', '$appUrl');", $config);
        $config = preg_replace("/define\('SECRET_KEY', '.*?'\);/", "define('SECRET_KEY', '$secretKey');", $config);
        
        file_put_contents('config/config.php', $config);
        
        $step = 2;
        $messages[] = 'Εγκατάσταση ολοκληρώθηκε με επιτυχία!';
        $messages[] = "Εισήχθησαν $importedCount SQL statements επιτυχώς.";
    } catch (PDOException $e) {
        $errors[] = 'Σφάλμα Βάσης Δεδομένων: ' . $e->getMessage();
        $errors[] = 'Παρακαλώ ελέγξτε: 1) Τα στοιχεία σύνδεσης, 2) Αν η βάση υπάρχει, 3) Αν ο χρήστης έχει δικαιώματα';
    } catch (Exception $e) {
        $errors[] = 'Γενικό Σφάλμα: ' . $e->getMessage();
    }
}

$protocol = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$suggestedUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Theodore Sfakianakis - theodore.sfakianakis@gmail.com">
    <title>HandyCRM Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .install-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px 15px 0 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="install-card">
                    <div class="header text-center">
                        <h1><i class="fas fa-tools"></i> HandyCRM Installation</h1>
                        <p class="mb-0">Professional CRM System Setup</p>
                        <small style="opacity: 0.8;">© 2025 Theodore Sfakianakis</small>
                    </div>
                    <div class="p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <div><?= htmlspecialchars($error) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                            <h4 class="mb-4">Στοιχεία Βάσης Δεδομένων</h4>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Host</label>
                                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Name</label>
                                        <input type="text" name="db_name" class="form-control" value="handycrm" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Username</label>
                                        <input type="text" name="db_user" class="form-control" value="root" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Password</label>
                                        <input type="password" name="db_pass" class="form-control">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Application URL</label>
                                        <input type="url" name="app_url" class="form-control" value="<?= htmlspecialchars($suggestedUrl) ?>" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-rocket"></i> Εγκατάσταση HandyCRM
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <h4><i class="fas fa-check-circle"></i> Επιτυχής Εγκατάσταση!</h4>
                                <p>Το HandyCRM εγκαταστάθηκε με επιτυχία.</p>
                                <hr>
                                <h6>Στοιχεία Σύνδεσης:</h6>
                                <p><strong>Email:</strong> admin@handycrm.com<br>
                                <strong>Password:</strong> admin123</p>
                                <div class="alert alert-warning">
                                    <strong>Σημαντικό:</strong> Διέγραψε το install.php για ασφάλεια!
                                </div>
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Σύνδεση στο HandyCRM
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
