-- Step 2: Insert enhanced badges
USE mapit;

-- Clear existing badges and start fresh
DELETE FROM user_badges;
DELETE FROM badges;

-- Reset auto increment
ALTER TABLE badges AUTO_INCREMENT = 1;

-- Travel Distance & Exploration Badges
INSERT INTO badges (name, description, threshold, icon, category, difficulty, points) VALUES
('First Steps', 'Visit your first destination', 1, 'first-steps.svg', 'exploration', 'easy', 10),
('Explorer', 'Visit 5 different destinations', 5, 'explorer.svg', 'exploration', 'easy', 25),
('Globetrotter', 'Visit 15 different destinations', 15, 'globetrotter.svg', 'exploration', 'medium', 50),
('World Wanderer', 'Visit 30 different destinations', 30, 'world-wanderer.svg', 'exploration', 'hard', 100),
('Master Explorer', 'Visit 50 different destinations', 50, 'master-explorer.svg', 'exploration', 'legendary', 200),

-- Trip Type Badges
('Adventure Seeker', 'Complete 3 adventure trips', 3, 'adventure-seeker.svg', 'activity', 'easy', 15),
('Adrenaline Junkie', 'Complete 10 adventure trips', 10, 'adrenaline-junkie.svg', 'activity', 'medium', 50),
('Zen Master', 'Complete 5 relaxation trips', 5, 'zen-master.svg', 'activity', 'easy', 25),
('Relaxation Expert', 'Complete 15 relaxation trips', 15, 'relaxation-expert.svg', 'activity', 'medium', 75),

-- Geographic Badges
('Country Collector', 'Visit 3 different countries', 3, 'country-collector.svg', 'geography', 'easy', 20),
('International Traveler', 'Visit 8 different countries', 8, 'international.svg', 'geography', 'medium', 60),
('World Citizen', 'Visit 20 different countries', 20, 'world-citizen.svg', 'geography', 'legendary', 150),

-- Planning Badges
('Planner', 'Have 3 planned trips at once', 3, 'planner.svg', 'planning', 'easy', 10),
('Strategic Planner', 'Have 8 planned trips at once', 8, 'strategic-planner.svg', 'planning', 'medium', 30),
('Dream Big', 'Have 15 planned trips at once', 15, 'dream-big.svg', 'planning', 'hard', 75),

-- Special Achievement Badges
('Night Owl', 'Plan a trip between midnight and 6 AM', 1, 'night-owl.svg', 'special', 'easy', 15),
('Weekend Warrior', 'Complete 5 trips on weekends', 5, 'weekend-warrior.svg', 'special', 'medium', 40),

-- Milestone Badges
('Consistent Traveler', 'Complete at least one trip per month for 3 months', 3, 'consistent.svg', 'milestone', 'medium', 50),
('Dedication', 'Use MapIt for 30 days', 30, 'dedication.svg', 'milestone', 'hard', 100);
