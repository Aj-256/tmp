<?php
session_start();

// Get DATABASE_URL from environment
$database_url = getenv('DATABASE_URL');

// If not found, try $_ENV or $_SERVER
if (!$database_url && isset($_ENV['DATABASE_URL'])) {
    $database_url = $_ENV['DATABASE_URL'];
}
if (!$database_url && isset($_SERVER['DATABASE_URL'])) {
    $database_url = $_SERVER['DATABASE_URL'];
}

// Fallback for Render - hardcode your credentials (temporary fix)
if (!$database_url) {
    // Your actual database connection string
    $database_url = "postgresql://aj_flmr_user:Jiep5CsAeNKniAGZKUklCCGuYjmXbnyc@dpg-d7usbobbc2fs73f0l1f0-a.oregon-postgres.render.com/aj_flmr";
}

// Parse the URL manually (more reliable than parse_url for postgresql://)
preg_match('/postgresql:\/\/([^:]+):([^@]+)@([^:]+):?(\d+)?\/(.+)/', $database_url, $matches);

if (count($matches) >= 5) {
    $user = $matches[1];
    $pass = $matches[2];
    $host = $matches[3];
    $port = isset($matches[4]) && $matches[4] ? $matches[4] : '5432';
    $dbname = $matches[5];
} else {
    // Try parse_url as fallback
    $db = parse_url($database_url);
    $host = $db['host'] ?? '';
    $port = $db['port'] ?? '5432';
    $user = $db['user'] ?? '';
    $pass = $db['pass'] ?? '';
    $dbname = ltrim($db['path'] ?? '', '/');
}

// Check if we have all required values
if (!$host || !$user || !$dbname) {
    die("Database configuration error. Please check DATABASE_URL environment variable.");
}

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(80) UNIQUE NOT NULL,
            email VARCHAR(120) UNIQUE NOT NULL,
            password VARCHAR(200) NOT NULL,
            api_key VARCHAR(64) UNIQUE,
            total_uploads INTEGER DEFAULT 0,
            storage_used BIGINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS files (
            id SERIAL PRIMARY KEY,
            file_id VARCHAR(64) UNIQUE NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            size BIGINT NOT NULL,
            mime_type VARCHAR(100),
            user_id INTEGER REFERENCES users(id),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            downloads INTEGER DEFAULT 0
        );
    ");
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>