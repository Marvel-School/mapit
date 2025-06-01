-- Enhanced Badges System
-- This script adds many more interesting and achievable badges

USE mapit;

-- First, let's clear existing badges and start fresh
DELETE FROM user_badges;
DELETE FROM badges;

-- Reset auto increment
ALTER TABLE badges AUTO_INCREMENT = 1;

-- Travel Distance & Exploration Badges
INSERT INTO badges (name, description, threshold, icon, category, difficulty) VALUES
('First Steps', 'Visit your first destination', 1, 'first-steps.svg', 'exploration', 'easy'),
('Explorer', 'Visit 5 different destinations', 5, 'explorer.svg', 'exploration', 'easy'),
('Globetrotter', 'Visit 15 different destinations', 15, 'globetrotter.svg', 'exploration', 'medium'),
('World Wanderer', 'Visit 30 different destinations', 30, 'world-wanderer.svg', 'exploration', 'hard'),
('Master Explorer', 'Visit 50 different destinations', 50, 'master-explorer.svg', 'exploration', 'legendary'),

-- Trip Type Badges
('Adventure Seeker', 'Complete 3 adventure trips', 3, 'adventure-seeker.svg', 'activity', 'easy'),
('Adrenaline Junkie', 'Complete 10 adventure trips', 10, 'adrenaline-junkie.svg', 'activity', 'medium'),
('Zen Master', 'Complete 5 relaxation trips', 5, 'zen-master.svg', 'activity', 'easy'),
('Relaxation Expert', 'Complete 15 relaxation trips', 15, 'relaxation-expert.svg', 'activity', 'medium'),

-- Geographic Badges
('Continental Explorer', 'Visit destinations in 2 different continents', 2, 'continental.svg', 'geography', 'medium'),
('Global Citizen', 'Visit destinations in 4 different continents', 4, 'global-citizen.svg', 'geography', 'hard'),
('Country Collector', 'Visit 5 different countries', 5, 'country-collector.svg', 'geography', 'easy'),
('International Traveler', 'Visit 10 different countries', 10, 'international.svg', 'geography', 'medium'),
('World Citizen', 'Visit 25 different countries', 25, 'world-citizen.svg', 'geography', 'legendary'),

-- Time-based Badges
('Quick Starter', 'Start a trip within 24 hours of planning', 1, 'quick-starter.svg', 'timing', 'easy'),
('Speed Traveler', 'Complete 3 trips in one month', 3, 'speed-traveler.svg', 'timing', 'medium'),
('Consistent Traveler', 'Complete at least one trip every month for 6 months', 6, 'consistent.svg', 'timing', 'hard'),

-- Planning Badges
('Planner', 'Have 5 planned trips at once', 5, 'planner.svg', 'planning', 'easy'),
('Strategic Planner', 'Have 10 planned trips at once', 10, 'strategic-planner.svg', 'planning', 'medium'),
('Dream Big', 'Have 20 planned trips at once', 20, 'dream-big.svg', 'planning', 'hard'),

-- Special Achievement Badges
('Early Adopter', 'Be among the first 100 users', 100, 'early-adopter.svg', 'special', 'rare'),
('Trendsetter', 'Visit a destination that becomes popular', 1, 'trendsetter.svg', 'special', 'rare'),
('Night Owl', 'Plan a trip between midnight and 6 AM', 1, 'night-owl.svg', 'special', 'easy'),
('Weekend Warrior', 'Complete 5 trips on weekends', 5, 'weekend-warrior.svg', 'special', 'medium'),

-- Social Badges (for future social features)
('Sharer', 'Share your first public destination', 1, 'sharer.svg', 'social', 'easy'),
('Inspiration', 'Have your destination visited by 10 other users', 10, 'inspiration.svg', 'social', 'hard'),

-- Milestone Badges
('Century Club', 'Complete 100 trips total', 100, 'century-club.svg', 'milestone', 'legendary'),
('Marathon Traveler', 'Have been active for 365 days', 365, 'marathon.svg', 'milestone', 'legendary'),
('Dedication', 'Use MapIt for 30 consecutive days', 30, 'dedication.svg', 'milestone', 'hard');

-- Add new columns to badges table if they don't exist
ALTER TABLE badges 
ADD COLUMN IF NOT EXISTS icon VARCHAR(100) DEFAULT 'default-badge.svg',
ADD COLUMN IF NOT EXISTS category ENUM('exploration', 'activity', 'geography', 'timing', 'planning', 'special', 'social', 'milestone') DEFAULT 'exploration',
ADD COLUMN IF NOT EXISTS difficulty ENUM('easy', 'medium', 'hard', 'legendary', 'rare') DEFAULT 'easy',
ADD COLUMN IF NOT EXISTS points INT DEFAULT 10;

-- Update points based on difficulty
UPDATE badges SET points = CASE difficulty
    WHEN 'easy' THEN 10
    WHEN 'medium' THEN 25
    WHEN 'hard' THEN 50
    WHEN 'legendary' THEN 100
    WHEN 'rare' THEN 75
    ELSE 10
END;

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
