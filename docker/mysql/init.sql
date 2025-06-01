-- Create database
CREATE DATABASE IF NOT EXISTS mapit;
USE mapit;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'moderator') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Destinations table
CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100) NULL,
    latitude DECIMAL(10,8) NOT NULL,    longitude DECIMAL(11,8) NOT NULL,
    description TEXT,
    image VARCHAR(255) NULL,
    privacy ENUM('public', 'private') DEFAULT 'public',
    user_id INT,
    featured BOOLEAN DEFAULT FALSE,
    notes TEXT,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Trips table
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination_id INT NOT NULL,
    status ENUM('planned', 'visited') DEFAULT 'planned',
    type ENUM('adventure', 'relaxation') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- Badges table
CREATE TABLE IF NOT EXISTS badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    threshold INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Badges table
CREATE TABLE IF NOT EXISTS user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_badge_unique (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- Badge Notifications table
CREATE TABLE IF NOT EXISTS badge_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- Logs table
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    data TEXT NULL,
    component VARCHAR(50) NULL,
    url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_level (level),
    INDEX idx_logs_component (component),
    INDEX idx_logs_created_at (created_at)
);

-- Insert default badges
INSERT INTO badges (name, description, threshold) VALUES
('Globetrotter', 'Visit 10 different destinations', 10),
('Adventurer', 'Complete 5 adventure trips', 5),
('Relaxation Master', 'Complete 5 relaxation trips', 5);

-- Create admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@mapit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');