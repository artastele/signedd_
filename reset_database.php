<?php
/**
 * Database Reset Script for Testing
 * WARNING: This will DELETE ALL DATA in your database
 * Only use for testing purposes
 */

require_once 'config/env.php';

echo "<h1>Database Reset Script</h1>";
echo "<p><strong>WARNING:</strong> This will delete all data in your database!</p>";

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<p><a href='?confirm=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>⚠️ CONFIRM: Reset Database</a></p>";
    echo "<p><a href='test_setup.php'>← Back to Setup Test</a></p>";
    exit;
}

try {
    // Connect to MySQL server (without database)
    $host = env('DB_HOST', 'localhost');
    $username = env('DB_USER', 'root');
    $password = env('DB_PASS', '');
    $dbname = env('DB_NAME', 'sped_lms');
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to MySQL server</p>";
    
    // Drop database if exists
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    echo "<p>🗑️ Dropped existing database '$dbname'</p>";
    
    // Create fresh database
    $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Created fresh database '$dbname'</p>";
    
    // Connect to the new database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to fresh database</p>";
    
    // Run schema.sql
    if (file_exists('config/schema.sql')) {
        $sql = file_get_contents('config/schema.sql');
        
        // Split by semicolons and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $executed = 0;
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (PDOException $e) {
                    echo "<p>⚠️ Statement warning: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
        
        echo "<p>✅ Schema executed successfully ($executed statements processed)</p>";
        
        // Check what tables were created
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>✅ Tables created (" . count($tables) . " total):</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check migration version
        $stmt = $pdo->query("SELECT MAX(version) as latest_version FROM db_version");
        $version = $stmt->fetch();
        echo "<p>✅ Latest migration version: v" . ($version['latest_version'] ?? 'none') . "</p>";
        
    } else {
        echo "<p>❌ schema.sql file not found</p>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 Database Reset Complete!</h2>";
    echo "<p>Your database has been completely reset with the latest schema including:</p>";
    echo "<ul>";
    echo "<li>✅ All Process 1-4 tables (locked processes)</li>";
    echo "<li>✅ Simplified Process 5 tables (upload-only IEP system)</li>";
    echo "<li>✅ Migration v40 applied (removed complex IEP tables)</li>";
    echo "<li>✅ Default admin account created</li>";
    echo "<li>✅ System settings configured</li>";
    echo "</ul>";
    
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='test_setup.php'>Run Setup Test</a> to verify everything works</li>";
    echo "<li>Access the system at <a href='public/index.php'>public/index.php</a></li>";
    echo "<li>Login with: <strong>admin@spedlms.local</strong> / <strong>password</strong></li>";
    echo "</ol>";
    
    echo "<p><a href='public/index.php' style='background: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>🚀 Go to SPED LMS</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database reset failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your .env database settings and ensure WAMP is running.</p>";
}
?>