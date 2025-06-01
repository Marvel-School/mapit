-- Step 3: Create additional tables for enhanced badge system
USE mapit;

-- Add user progression tracking
CREATE TABLE IF NOT EXISTS user_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_points INT DEFAULT 0,
    level INT DEFAULT 1,
    destinations_visited INT DEFAULT 0,
    countries_visited INT DEFAULT 0,
    continents_visited INT DEFAULT 0,
    trips_completed INT DEFAULT 0,
    current_streak INT DEFAULT 0,
    longest_streak INT DEFAULT 0,
    last_activity_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add badge notifications
CREATE TABLE IF NOT EXISTS badge_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- Update trips table to include in_progress status if not already there
ALTER TABLE trips MODIFY COLUMN status ENUM('planned', 'in_progress', 'visited', 'completed') DEFAULT 'planned';
