-- Fix trip status enum to include missing values
-- This resolves the "Failed to start trip" error

-- Update the trips table status column to include 'in_progress' and 'completed'
ALTER TABLE trips MODIFY COLUMN status ENUM('planned', 'in_progress', 'visited', 'completed') DEFAULT 'planned';

-- Update type column to include more trip types that are used in the application
ALTER TABLE trips MODIFY COLUMN type ENUM('adventure', 'relaxation', 'business', 'leisure', 'cultural', 'nature') NOT NULL DEFAULT 'adventure';
