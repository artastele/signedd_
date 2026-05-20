<?php
/**
 * Database Connection Test Script
 * This script tests the connection to Supabase PostgreSQL
 */

// Load environment variables
require_once __DIR__ . '/config/helpers.php';

echo "🔍 Testing Supabase PostgreSQL Connection...\n";
echo "================================================\n\n";

// Display connection details (without password)
echo "Connection Details:\n";
echo "  Host: " . env('DB_HOST', 'Not set') . "\n";
echo "  Port: " . env('DB_PORT', 'Not set') . "\n";
echo "  Database: " . env('DB_NAME', 'Not set') . "\n";
echo "  User: " . env('DB_USER', 'Not set') . "\n";
echo "  Password: " . (env('DB_PASS') ? '***' : 'Not set') . "\n\n";

// Attempt connection
try {
    $host = env('DB_HOST', 'localhost');
    $dbname = env('DB_NAME', 'sped_lms');
    $username = env('DB_USER', 'root');
    $password = env('DB_PASS', '');
    $port = env('DB_PORT', '5432');

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    echo "Attempting connection...\n";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "✅ CONNECTION SUCCESSFUL!\n\n";
    
    // Test basic queries
    echo "Running database tests...\n";
    echo "================================================\n\n";
    
    // Check if users table exists
    $result = $pdo->query("SELECT COUNT(*) as count FROM users LIMIT 1");
    $row = $result->fetch();
    echo "✅ Users table exists (Records: " . $row['count'] . ")\n";
    
    // Check if enrollments table exists
    $result = $pdo->query("SELECT COUNT(*) as count FROM enrollments LIMIT 1");
    $row = $result->fetch();
    echo "✅ Enrollments table exists (Records: " . $row['count'] . ")\n";
    
    // Check if schools table exists
    $result = $pdo->query("SELECT COUNT(*) as count FROM schools LIMIT 1");
    $row = $result->fetch();
    echo "✅ Schools table exists (Records: " . $row['count'] . ")\n";
    
    // List all tables
    $result = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    $tables = $result->fetchAll();
    
    echo "\n📊 Total Tables Created: " . count($tables) . "\n";
    echo "================================================\n";
    
    foreach ($tables as $table) {
        echo "  • " . $table['table_name'] . "\n";
    }
    
    echo "\n✅ ALL TESTS PASSED! Your Supabase connection is ready.\n";
    
} catch (PDOException $e) {
    echo "❌ CONNECTION FAILED\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Common issues:\n";
    echo "  1. Incorrect password in .env file\n";
    echo "  2. Supabase project not initialized\n";
    echo "  3. Network/firewall blocking connection\n";
    echo "  4. Database does not exist\n\n";
    echo "Please check your Supabase credentials and try again.\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
