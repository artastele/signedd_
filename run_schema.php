<?php
/**
 * Schema Runner Script
 * Executes the schema.sql file to set up database tables
 */

require_once 'config/env.php';

echo "<h1>Schema Setup</h1>";

try {
    // Connect to database
    $host = env('DB_HOST', 'localhost');
    $username = env('DB_USER', 'root');
    $password = env('DB_PASS', '');
    $dbname = env('DB_NAME', 'sped_lms');
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to database '$dbname'</p>";
    
    // Read and execute schema.sql
    if (!file_exists('config/schema.sql')) {
        throw new Exception('schema.sql file not found in config directory');
    }
    
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
                // Log but continue (some statements might fail if tables already exist)
                echo "<p>⚠️ Statement warning: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p>✅ Schema executed successfully ($executed statements processed)</p>";
    
    // Check what tables were created
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p>✅ Tables created:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Schema setup failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='test_setup.php'>← Back to Setup Test</a></p>";
?>