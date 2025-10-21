<?php
/**
 * Quick Configuration Check
 * Upload this to 1stop.gr to check date format settings
 * Access: https://1stop.gr/check_config.php
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='el'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Config Check - HandyCRM</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-top: 0;
        }
        .status-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            font-family: monospace;
            color: #333;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 HandyCRM Configuration Check</h1>";

// Check if DATE_FORMAT is defined
if (defined('DATE_FORMAT')) {
    $dateClass = DATE_FORMAT === 'd/m/Y' ? 'success' : 'warning';
    echo "<div class='status-item $dateClass'>
            <div class='label'>DATE_FORMAT:</div>
            <div class='value'>" . DATE_FORMAT . "</div>
            <small>" . (DATE_FORMAT === 'd/m/Y' ? '✅ Correct (Greek format)' : '❌ Wrong! Should be: d/m/Y') . "</small>
          </div>";
    
    // Test the format
    echo "<div class='status-item'>
            <div class='label'>Example Date (2025-10-21):</div>
            <div class='value'>" . date(DATE_FORMAT, strtotime('2025-10-21')) . "</div>
            <small>Should display: 21/10/2025</small>
          </div>";
} else {
    echo "<div class='status-item warning'>
            <div class='label'>DATE_FORMAT:</div>
            <div class='value'>NOT DEFINED ❌</div>
          </div>";
}

// Check if DATETIME_FORMAT is defined
if (defined('DATETIME_FORMAT')) {
    $datetimeClass = DATETIME_FORMAT === 'd/m/Y H:i' ? 'success' : 'warning';
    echo "<div class='status-item $datetimeClass'>
            <div class='label'>DATETIME_FORMAT:</div>
            <div class='value'>" . DATETIME_FORMAT . "</div>
            <small>" . (DATETIME_FORMAT === 'd/m/Y H:i' ? '✅ Correct (Greek format)' : '❌ Wrong! Should be: d/m/Y H:i') . "</small>
          </div>";
    
    // Test the format
    echo "<div class='status-item'>
            <div class='label'>Example DateTime (2025-10-21 14:30:00):</div>
            <div class='value'>" . date(DATETIME_FORMAT, strtotime('2025-10-21 14:30:00')) . "</div>
            <small>Should display: 21/10/2025 14:30</small>
          </div>";
} else {
    echo "<div class='status-item warning'>
            <div class='label'>DATETIME_FORMAT:</div>
            <div class='value'>NOT DEFINED ❌</div>
          </div>";
}

// Check formatDate function
if (function_exists('formatDate')) {
    $testDate = formatDate('2025-10-21');
    $testClass = $testDate === '21/10/2025' ? 'success' : 'warning';
    echo "<div class='status-item $testClass'>
            <div class='label'>formatDate('2025-10-21'):</div>
            <div class='value'>$testDate</div>
            <small>" . ($testDate === '21/10/2025' ? '✅ Working correctly' : '❌ Wrong output!') . "</small>
          </div>";
    
    $testDateTime = formatDate('2025-10-21 14:30:00', true);
    echo "<div class='status-item'>
            <div class='label'>formatDate('2025-10-21 14:30:00', true):</div>
            <div class='value'>$testDateTime</div>
            <small>Should display: 21/10/2025 14:30</small>
          </div>";
} else {
    echo "<div class='status-item warning'>
            <div class='label'>formatDate() function:</div>
            <div class='value'>NOT DEFINED ❌</div>
          </div>";
}

// Check timezone
echo "<div class='status-item'>
        <div class='label'>Timezone:</div>
        <div class='value'>" . date_default_timezone_get() . "</div>
        <small>Should be: Europe/Athens</small>
      </div>";

// Check current time
echo "<div class='status-item'>
        <div class='label'>Current Server Time:</div>
        <div class='value'>" . date('d/m/Y H:i:s') . "</div>
      </div>";

echo "<hr>
      <div class='status-item'>
        <strong>⚠️ DELETE THIS FILE AFTER CHECKING!</strong><br>
        <small>This file exposes configuration details.</small>
      </div>
    </div>
</body>
</html>";
?>
