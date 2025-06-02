-- Production Database Schema Creation
-- Create all missing tables for MapIt application

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id int NOT NULL AUTO_INCREMENT,
    username varchar(50) NOT NULL UNIQUE,
    email varchar(100) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    role enum('user','admin') DEFAULT 'user',
    avatar varchar(255) DEFAULT NULL,
    bio text,
    location varchar(100) DEFAULT NULL,
    website varchar(255) DEFAULT NULL,
    email_verified_at timestamp NULL DEFAULT NULL,
    remember_token varchar(100) DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY email_idx (email),
    KEY username_idx (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Destinations table
CREATE TABLE IF NOT EXISTS destinations (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    description text,
    image varchar(255) DEFAULT NULL,
    country varchar(100) DEFAULT NULL,
    city varchar(100) DEFAULT NULL,
    latitude decimal(10,8) NOT NULL,
    longitude decimal(11,8) NOT NULL,
    privacy enum('public','private') DEFAULT 'public',
    user_id int DEFAULT NULL,
    featured tinyint(1) DEFAULT 0,
    notes text,
    approval_status enum('pending','approved','rejected') DEFAULT 'approved',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id_idx (user_id),
    KEY privacy_idx (privacy),
    KEY approval_status_idx (approval_status),
    KEY featured_idx (featured),
    KEY country_idx (country),
    KEY latitude_longitude_idx (latitude, longitude),
    CONSTRAINT destinations_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trips table
CREATE TABLE IF NOT EXISTS trips (
    id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    destination_id int NOT NULL,
    status enum('planned','in_progress','visited','completed') DEFAULT 'planned',
    type enum('adventure','relaxation','business','cultural','family') DEFAULT 'adventure',
    notes text,
    start_date date DEFAULT NULL,
    end_date date DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id_idx (user_id),
    KEY destination_id_idx (destination_id),
    KEY status_idx (status),
    KEY type_idx (type),
    KEY created_at_idx (created_at),
    UNIQUE KEY user_destination_unique (user_id, destination_id),
    CONSTRAINT trips_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT trips_destination_id_fk FOREIGN KEY (destination_id) REFERENCES destinations (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Badges table
CREATE TABLE IF NOT EXISTS badges (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(50) NOT NULL UNIQUE,
    description text NOT NULL,
    icon varchar(100) DEFAULT NULL,
    points int DEFAULT 0,
    requirement_type enum('trip_count','country_count','destination_count','streak','special') DEFAULT 'trip_count',
    requirement_value int DEFAULT 1,
    category enum('achievement','milestone','special') DEFAULT 'achievement',
    is_active tinyint(1) DEFAULT 1,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY category_idx (category),
    KEY is_active_idx (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User badges table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS user_badges (
    id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    badge_id int NOT NULL,
    earned_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_badge_unique (user_id, badge_id),
    KEY user_id_idx (user_id),
    KEY badge_id_idx (badge_id),
    KEY earned_at_idx (earned_at),
    CONSTRAINT user_badges_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT user_badges_badge_id_fk FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User stats table for performance
CREATE TABLE IF NOT EXISTS user_stats (
    id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL UNIQUE,
    total_badges int DEFAULT 0,
    total_points int DEFAULT 0,
    total_trips int DEFAULT 0,
    total_destinations int DEFAULT 0,
    countries_visited int DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id_idx (user_id),
    CONSTRAINT user_stats_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    subject varchar(200) NOT NULL,
    message text NOT NULL,
    status enum('new','read','replied') DEFAULT 'new',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY email_idx (email),
    KEY status_idx (status),
    KEY created_at_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default badges
INSERT IGNORE INTO badges (name, description, icon, points, requirement_type, requirement_value, category) VALUES
('First Steps', 'Complete your first trip', 'fas fa-baby', 10, 'trip_count', 1, 'milestone'),
('Explorer', 'Complete 5 trips', 'fas fa-compass', 25, 'trip_count', 5, 'achievement'),
('Adventurer', 'Complete 10 trips', 'fas fa-hiking', 50, 'trip_count', 10, 'achievement'),
('World Traveler', 'Complete 25 trips', 'fas fa-globe-americas', 100, 'trip_count', 25, 'achievement'),
('Globe Trotter', 'Complete 50 trips', 'fas fa-plane', 200, 'trip_count', 50, 'achievement'),
('Jet Setter', 'Complete 100 trips', 'fas fa-rocket', 500, 'trip_count', 100, 'achievement'),
('Country Collector', 'Visit 5 different countries', 'fas fa-flag', 75, 'country_count', 5, 'achievement'),
('International Traveler', 'Visit 10 different countries', 'fas fa-passport', 150, 'country_count', 10, 'achievement'),
('World Citizen', 'Visit 25 different countries', 'fas fa-earth-americas', 300, 'country_count', 25, 'achievement'),
('Adventure Seeker', 'Complete 10 adventure trips', 'fas fa-mountain', 60, 'special', 10, 'achievement'),
('Relaxation Expert', 'Complete 10 relaxation trips', 'fas fa-spa', 60, 'special', 10, 'achievement'),
('Culture Enthusiast', 'Complete 10 cultural trips', 'fas fa-theater-masks', 60, 'special', 10, 'achievement'),
('Planner', 'Have 10 planned trips', 'fas fa-calendar-alt', 30, 'special', 10, 'achievement'),
('Dream Big', 'Have 25 planned trips', 'fas fa-star', 75, 'special', 25, 'achievement'),
('Weekend Warrior', 'Complete 5 trips in weekends', 'fas fa-calendar-weekend', 40, 'special', 5, 'achievement'),
('Night Owl', 'Visit 3 nightlife destinations', 'fas fa-moon', 35, 'special', 3, 'achievement'),
('Consistent Traveler', 'Travel for 6 consecutive months', 'fas fa-calendar-check', 80, 'streak', 6, 'achievement'),
('Dedication', 'Be a member for 1 year', 'fas fa-trophy', 100, 'special', 12, 'milestone'),
('Pioneer', 'Be among the first 100 users', 'fas fa-medal', 250, 'special', 1, 'special');

-- Show table creation results
SHOW TABLES;
