<?php
/**
 * Database Setup Script
 * Creates the sped_lms database if it doesn't exist
 * Run this once after setting up .env
 */

require_once 'config/env.php';

echo "<h1>Database Setup</h1>";

try {
    // Connect to MySQL server (without specifying database)
    $host = env('DB_HOST', 'localhost');
    $username = env('DB_USER', 'root');
    $password = env('DB_PASS', '');
    $dbname = env('DB_NAME', 'sped_lms');
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to MySQL server</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Database '$dbname' already exists</p>";
    } else {
        // Create database
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>✅ Database '$dbname' created successfully</p>";
    }
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to database '$dbname'</p>";
    
    // Check if schema.sql exists and offer to run it
    if (file_exists('config/schema.sql')) {
        echo "<p>📄 Found schema.sql file</p>";
        echo "<p><a href='run_schema.php' style='background: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Schema Setup</a></p>";
    } else {
        echo "<p>⚠️ schema.sql not found in config directory</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your .env database settings:</p>";
    echo "<ul>";
    echo "<li>DB_HOST: " . env('DB_HOST', 'localhost') . "</li>";
    echo "<li>DB_USER: " . env('DB_USER', 'root') . "</li>";
    echo "<li>DB_NAME: " . env('DB_NAME', 'sped_lms') . "</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='test_setup.php'>← Back to Setup Test</a></p>";
?>