-- Step 1: Add new columns to badges table
USE mapit;

-- Add new columns to badges table if they don't exist
ALTER TABLE badges 
ADD COLUMN icon VARCHAR(100) DEFAULT 'default-badge.svg',
ADD COLUMN category ENUM('exploration', 'activity', 'geography', 'timing', 'planning', 'special', 'social', 'milestone') DEFAULT 'exploration',
ADD COLUMN difficulty ENUM('easy', 'medium', 'hard', 'legendary', 'rare') DEFAULT 'easy',
ADD COLUMN points INT DEFAULT 10;
