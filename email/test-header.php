<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Current directory: " . __DIR__ . "<br>";
echo "2. Checking config.php: ";
if (file_exists('../config/config.php')) {
    echo "EXISTS<br>";
    require_once '../config/config.php';
    echo "3. Config loaded successfully<br>";
} else {
    echo "NOT FOUND<br>";
}

echo "4. Checking header.php: ";
$headerPath = '../views/includes/header.php';
if (file_exists($headerPath)) {
    echo "EXISTS at: " . realpath($headerPath) . "<br>";
} else {
    echo "NOT FOUND<br>";
}

echo "5. Session status: " . (session_status() == PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "<br>";
echo "6. User logged in: " . (isset($_SESSION['user_id']) ? "YES (ID: " . $_SESSION['user_id'] . ")" : "NO") . "<br>";

$pageTitle = 'Test Page';

echo "<hr>7. Loading header...<br>";
require_once $headerPath;
?>

<h1>If you see this, the header loaded!</h1>

<?php require_once '../views/includes/footer.php'; ?>
