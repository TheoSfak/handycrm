<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Εγκατάσταση HandyCRM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 100%; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; border-radius: 20px 20px 0 0; }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .content { padding: 40px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-group small { display: block; margin-top: 5px; color: #666; font-size: 13px; }
        .btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-danger { background: #fee; border-left: 4px solid #f44336; color: #c62828; }
        .alert-success { background: #e8f5e9; border-left: 4px solid #4caf50; color: #2e7d32; }
        .info-box { background: #f5f5f5; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .info-box h3 { margin-bottom: 10px; }
        .info-box ul { list-style: none; }
        .info-box li { padding: 5px 0; }
        .delete-btn { background: #f44336; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛠️ HandyCRM</h1>
        <p>Εγκατάσταση v1.0.6</p>
    </div>
    <div class="content">
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (file_exists(__DIR__ . '/config/config.php')) {
    die('<div class="alert alert-danger">⚠️ Το HandyCRM είναι ήδη εγκατεστημένο!</div>');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_port = trim($_POST['db_port'] ?? '3306');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';

    if (empty($db_name)) $errors[] = 'Το όνομα της βάσης είναι υποχρεωτικό';
    if (empty($db_user)) $errors[] = 'Το username είναι υποχρεωτικό';

    if (empty($errors)) {
        try {
            $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$db_name}`");

            $sql_file = __DIR__ . '/database/handycrm.sql';
            if (!file_exists($sql_file)) throw new Exception('Το handycrm.sql δεν βρέθηκε!');

            // Disable foreign key checks for safe import
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

            $sql = file_get_contents($sql_file);
            
            // Remove only regular comments, keep MySQL directives
            $sql = preg_replace('/^--.*$/m', '', $sql);
            
            // Split by semicolon but keep important MySQL commands
            $statements = explode(';', $sql);
            
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (!empty($stmt) && 
                    stripos($stmt, 'CREATE DATABASE') === false && 
                    stripos($stmt, 'USE ') === false &&
                    !preg_match('/^\/\*!40\d+\s+SET\s+@OLD_/', $stmt)) {
                    try {
                        $pdo->exec($stmt);
                    } catch (PDOException $e) {
                        // Skip errors for SET commands and other MySQL directives
                        if (stripos($stmt, 'SET') === false && 
                            stripos($stmt, 'LOCK TABLES') === false &&
                            stripos($stmt, 'UNLOCK TABLES') === false) {
                            throw $e;
                        }
                    }
                }
            }

            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

            $config = "<?php\n";
            $config .= "/**\n";
            $config .= " * HandyCRM Configuration File\n";
            $config .= " * Auto-generated on " . date('Y-m-d H:i:s') . "\n";
            $config .= " */\n\n";
            $config .= "// Database Configuration\n";
            $config .= "define('DB_HOST', '{$db_host}');\n";
            $config .= "define('DB_PORT', '{$db_port}');\n";
            $config .= "define('DB_NAME', '{$db_name}');\n";
            $config .= "define('DB_USER', '{$db_user}');\n";
            $config .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
            $config .= "define('DB_CHARSET', 'utf8mb4');\n\n";
            $config .= "// Application Configuration\n";
            $config .= "define('APP_ROOT', __DIR__ . '/..');\n";
            $config .= "define('APP_NAME', 'HandyCRM');\n";
            $config .= "define('APP_VERSION', '1.0.6');\n";
            $config .= "define('BASE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . rtrim(dirname(\$_SERVER['SCRIPT_NAME']), '/'));\n\n";
            $config .= "// Session Configuration\n";
            $config .= "define('SESSION_LIFETIME', 7200);\n\n";
            $config .= "// CSRF Token Configuration\n";
            $config .= "define('CSRF_TOKEN_NAME', 'csrf_token');\n\n";
            $config .= "// Upload Configuration\n";
            $config .= "define('UPLOAD_DIR', __DIR__ . '/../uploads/');\n";
            $config .= "define('MAX_FILE_SIZE', 10485760);\n";
            $config .= "define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);\n\n";
            $config .= "// Timezone\n";
            $config .= "date_default_timezone_set('Europe/Athens');\n\n";
            $config .= "// Error Reporting (Disable in production)\n";
            $config .= "error_reporting(E_ALL);\n";
            $config .= "ini_set('display_errors', '1');\n";

            $config_dir = __DIR__ . '/config';
            if (!is_dir($config_dir)) mkdir($config_dir, 0755, true);
            
            if (file_put_contents($config_dir . '/config.php', $config) === false) {
                throw new Exception('Αποτυχία δημιουργίας config.php');
            }

            chmod($config_dir . '/config.php', 0644);
            $success = true;

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

if (isset($_GET['delete']) && file_exists(__DIR__ . '/config/config.php')) {
    if (@unlink(__FILE__)) {
        header('Location: index.php');
        exit;
    }
}

if ($success): ?>
    <div style="text-align: center; font-size: 48px; margin-bottom: 20px;">✅</div>
    <h2 style="color: #2e7d32; text-align: center; margin-bottom: 20px;">Επιτυχής Εγκατάσταση!</h2>
    
    <div class="alert alert-success"><strong>Ολοκληρώθηκε!</strong> Η βάση και το config.php δημιουργήθηκαν.</div>
    
    <div class="info-box">
        <h3>📋 Προεπιλεγμένοι Λογαριασμοί</h3>
        <ul>
            <li><strong>Admin:</strong> admin / admin123</li>
            <li><strong>Τεχνικός:</strong> tech / tech123</li>
        </ul>
    </div>
    
    <div class="info-box">
        <h3>⚠️ Σημαντικό</h3>
        <ul>
            <li>Αλλάξτε τους κωδικούς άμεσα</li>
            <li>Διαγράψτε το install.php</li>
        </ul>
    </div>
    
    <form method="GET"><input type="hidden" name="delete" value="1"><button type="submit" class="btn delete-btn">🗑️ Διαγραφή install.php</button></form>
    <a href="index.php"><button type="button" class="btn">🚀 Είσοδος</button></a>

<?php else: ?>
    <h2 style="margin-bottom: 20px;">Ρυθμίσεις Βάσης</h2>
    
    <?php if ($errors): ?>
        <div class="alert alert-danger"><strong>Σφάλματα:</strong><ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Database Host</label>
            <input type="text" name="db_host" value="localhost" required>
            <small>Συνήθως localhost</small>
        </div>
        <div class="form-group">
            <label>Database Port</label>
            <input type="text" name="db_port" value="3306" required>
        </div>
        <div class="form-group">
            <label>Database Name</label>
            <input type="text" name="db_name" value="handycrm" required>
            <small>Θα δημιουργηθεί αυτόματα</small>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="db_user" value="root" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="db_pass">
            <small>Κενό για XAMPP</small>
        </div>
        <button type="submit" class="btn">🚀 Εγκατάσταση</button>
    </form>
<?php endif; ?>
    </div>
</div>
</body>
</html>
