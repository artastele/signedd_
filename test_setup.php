<?php
/**
 * Setup Test Script
 * Run this to verify .env and dependencies are working
 * Access via: http://localhost/Signedd/test_setup.php
 */

echo "<h1>SPED LMS Setup Test</h1>";

// Test 1: Environment Variables
echo "<h2>1. Environment Variables</h2>";
require_once 'config/env.php';

$envVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'APP_URL', 'ENCRYPTION_KEY'];
foreach ($envVars as $var) {
    $value = env($var, 'NOT SET');
    $display = ($var === 'DB_PASS' || $var === 'ENCRYPTION_KEY') ? '***HIDDEN***' : $value;
    echo "<p><strong>$var:</strong> $display</p>";
}

// Test 2: Composer Dependencies
echo "<h2>2. Composer Dependencies</h2>";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "<p>✅ Composer autoloader found</p>";
    
    // Test PHPMailer
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p>✅ PHPMailer loaded</p>";
    } else {
        echo "<p>❌ PHPMailer not found</p>";
    }
    
    // Test Google API Client
    if (class_exists('Google\Client')) {
        echo "<p>✅ Google API Client loaded</p>";
    } else {
        echo "<p>❌ Google API Client not found</p>";
    }
} else {
    echo "<p>❌ Composer dependencies not installed</p>";
}

// Test 3: Database Connection
echo "<h2>3. Database Connection</h2>";
try {
    require_once 'config/db.php';
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Test connection
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result['test'] == 1) {
        echo "<p>✅ Database connection successful</p>";
        
        // Check if database exists
        $dbName = env('DB_NAME', 'sped_lms');
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
        if ($stmt->fetch()) {
            echo "<p>✅ Database '$dbName' exists</p>";
        } else {
            echo "<p>⚠️ Database '$dbName' does not exist - you may need to create it</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 4: File Permissions
echo "<h2>4. File Permissions</h2>";
$writableDirs = ['logs', 'public/uploads'];
foreach ($writableDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "<p>✅ $dir is writable</p>";
    } else {
        echo "<p>❌ $dir is not writable or doesn't exist</p>";
    }
}

echo "<hr>";
echo "<p><strong>Setup Status:</strong> Review the results above. Green checkmarks (✅) indicate successful setup.</p>";
echo "<p><strong>Next Steps:</strong> If all tests pass, you can delete this file and start using the application.</p>";
?>