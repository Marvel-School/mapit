<?php

// Set up the testing environment
define('APP_ROOT', dirname(__DIR__));

// Load the autoloader
require_once APP_ROOT . '/app/Core/Autoloader.php';

// Register the autoloader
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up test database configuration
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';

// Create test database tables
createTestTables();

function createTestTables()
{
    $db = App\Core\Database::getInstance();
    
    // Users table
    $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $db->execute();
    
    // Destinations table
    $db->query("
        CREATE TABLE IF NOT EXISTS destinations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            country VARCHAR(100) NOT NULL,
            city VARCHAR(100),
            latitude DECIMAL(10, 8),
            longitude DECIMAL(11, 8),
            user_id INTEGER,
            privacy ENUM('public', 'private') DEFAULT 'public',
            approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    $db->execute();
    
    // Trips table
    $db->query("
        CREATE TABLE IF NOT EXISTS trips (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            destination_id INTEGER NOT NULL,
            status ENUM('planned', 'visited') DEFAULT 'planned',
            type ENUM('leisure', 'business', 'adventure', 'cultural') DEFAULT 'leisure',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
        )
    ");
    $db->execute();
    
    // Badges table
    $db->query("
        CREATE TABLE IF NOT EXISTS badges (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            requirement_type VARCHAR(50),
            requirement_value INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $db->execute();
    
    // User badges table
    $db->query("
        CREATE TABLE IF NOT EXISTS user_badges (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            badge_id INTEGER NOT NULL,
            earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
            UNIQUE(user_id, badge_id)
        )
    ");
    $db->execute();
    
    // Logs table
    $db->query("
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            data TEXT,
            component VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $db->execute();
}
