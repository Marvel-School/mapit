<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{    protected $table = 'users';    protected $fillable = [
        'username', 'email', 'password', 'password_hash', 'role', 'name', 'bio', 'country', 'website', 'avatar', 'settings', 'last_login'
    ];
      /**
     * Get the password column name based on database schema
     * 
     * @return string
     */
    private function getPasswordColumn()
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->query("SHOW COLUMNS FROM users LIKE 'password%'");
            $columns = $db->resultSet();
            
            foreach ($columns as $column) {
                if ($column['Field'] === 'password_hash') {
                    return 'password_hash';
                } else if ($column['Field'] === 'password') {
                    return 'password';
                }
            }
            
            // Default fallback
            return 'password_hash';
        } catch (\Exception $e) {
            // Fallback in case of error
            return 'password_hash';
        }
    }
    
    /**
     * Find a user by email
     * 
     * @param string $email
     * @return array|bool
     */
    public function findByEmail($email)
    {
        return $this->findBy('email', $email);
    }
    
    /**
     * Find a user by username
     * 
     * @param string $username
     * @return array|bool
     */
    public function findByUsername($username)
    {
        return $this->findBy('username', $username);
    }
    
    /**
     * Create a new user
     * 
     * @param array $data
     * @return int|bool
     */    public function register($data)
    {
        $logModel = new \App\Models\Log();
        $logModel::write('DEBUG', "Registration attempt", ['username' => $data['username'], 'email' => $data['email']], 'Authentication');
        
        // Try password first (production), then password_hash (local)
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // First try with 'password' column
        try {
            $data['password'] = $hashedPassword;
            unset($data['password_confirm']);
            $userId = $this->create($data);
            
            if ($userId) {
                $logModel::write('INFO', "Registration successful", ['user_id' => $userId, 'username' => $data['username']], 'Authentication');
            }
            return $userId;
        } catch (\Exception $e) {
            // If 'password' column fails, try 'password_hash'
            unset($data['password']);
            $data['password_hash'] = $hashedPassword;
            
            $userId = $this->create($data);
        
        if ($userId) {
            $logModel::write('INFO', "Registration successful", ['user_id' => $userId, 'username' => $data['username']], 'Authentication');
        } else {
            $logModel::write('ERROR', "Registration failed", ['username' => $data['username'], 'email' => $data['email']], 'Authentication');
        }
        
        return $userId;
    }
    
    /**
     * Verify user credentials
     * 
     * @param string $username
     * @param string $password
     * @return array|bool
     */    public function authenticate($username, $password)
    {
        // Check if username is an email address
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        $logModel = new \App\Models\Log();
        
        // Get user by username or email
        if ($isEmail) {
            $user = $this->findByEmail($username);
            $logModel::write('DEBUG', "Login attempt with email: {$username}", [], 'Authentication');
        } else {
            $user = $this->findByUsername($username);
            $logModel::write('DEBUG', "Login attempt with username: {$username}", [], 'Authentication');
        }
        
        // Log if user not found
        if (!$user) {
            $logModel::write('WARN', "Login failed: User not found with identifier: {$username}", [], 'Authentication');
            return false;        }
          // Verify password if user exists
        $passwordColumn = $this->getPasswordColumn();
        if (password_verify($password, $user[$passwordColumn])) {
            $username = $user['username'] ?? 'unknown';
            $logModel::write('INFO', "Login successful for user: {$username}", ['user_id' => $user['id']], 'Authentication');
            return $user;
        } else {
            $username = $user['username'] ?? 'unknown';
            $logModel::write('WARN', "Login failed: Invalid password for user: {$username}", ['user_id' => $user['id']], 'Authentication');
            return false;
        }
    }
    
    /**
     * Update the last login timestamp for a user
     * 
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin($userId)
    {
        try {
            $updated = $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
            if ($updated) {
                $logModel = new \App\Models\Log();
                $logModel::write('DEBUG', "Last login updated for user ID: {$userId}", ['user_id' => $userId], 'Authentication');
            }
            return $updated;
        } catch (\Exception $e) {
            $logModel = new \App\Models\Log();
            $logModel::write('ERROR', "Failed to update last login for user ID: {$userId}", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ], 'Authentication');
            return false;
        }
    }
    
    /**
     * Get trips associated with a user
     * 
     * @param int $userId
     * @param string $status
     * @param string $type
     * @return array
     */
    public function getTrips($userId, $status = null, $type = null)
    {
        $sql = "
            SELECT t.*, d.name, d.latitude, d.longitude, d.description
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.user_id = :user_id
        ";
        
        $params = [':user_id' => $userId];
        
        if ($status) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $status;
        }
        
        if ($type) {
            $sql .= " AND t.type = :type";
            $params[':type'] = $type;
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get badges earned by a user
     * 
     * @param int $userId
     * @return array
     */
    public function getBadges($userId)
    {
        $this->db->query("
            SELECT b.*
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = :user_id
        ");
        
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }
      /**
     * Check badge progress for a user using the enhanced badge system
     * 
     * @param int $userId
     * @return array
     */
    public function checkBadgeProgress($userId)
    {
        $badgeModel = new Badge();
        return $badgeModel->getBadgesWithProgress($userId);
    }
      /**
     * Check if user has earned any new badges and award them
     * 
     * @param int $userId
     * @return array Array of newly earned badges
     */
    public function checkAndAwardBadges($userId)
    {
        $newBadges = [];
        $badgeModel = new Badge();
        
        // Get all badges that the user doesn't have yet
        $this->db->query("
            SELECT b.* FROM badges b
            WHERE b.id NOT IN (
                SELECT badge_id FROM user_badges WHERE user_id = :user_id
            )
        ");
        $this->db->bind(':user_id', $userId);
        $availableBadges = $this->db->resultSet();
        
        foreach ($availableBadges as $badge) {
            // Check progress for this specific badge
            $current = $this->calculateProgressForBadge($badge, $userId);
            
            // If user has met the requirements, award the badge
            if ($current >= $badge['threshold']) {
                $this->awardBadge($userId, $badge['id']);
                $newBadges[] = $badge;
                
                // Create notification
                $this->createBadgeNotification($userId, $badge['id']);
                
                // Update user stats
                $this->updateUserStatsForNewBadge($userId, $badge['points']);
            }
        }
        
        return $newBadges;
    }
    
    /**
     * Calculate progress for a specific badge
     * 
     * @param array $badge
     * @param int $userId
     * @return int
     */    public function calculateProgressForBadge($badge, $userId)
    {
        $current = 0;
        
        switch($badge['name']) {            case 'First Steps':
            case 'Explorer':
            case 'Globetrotter':
            case 'World Wanderer':
            case 'Master Explorer':
                // Count total trips completed
                $this->db->query("
                    SELECT COUNT(*) as count
                    FROM trips
                    WHERE user_id = :user_id AND status = 'visited'
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                  case 'Adventure Seeker':
            case 'Adrenaline Junkie':
                // Count adventure trips completed
                $this->db->query("
                    SELECT COUNT(*) as count
                    FROM trips
                    WHERE user_id = :user_id AND type = 'adventure' AND status = 'visited'
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                  case 'Zen Master':
            case 'Relaxation Expert':
                // Count relaxation trips completed
                $this->db->query("
                    SELECT COUNT(*) as count
                    FROM trips
                    WHERE user_id = :user_id AND type = 'relaxation' AND status = 'visited'
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                  case 'Country Collector':
            case 'International Traveler':
            case 'World Citizen':
                // Count unique countries visited
                $this->db->query("
                    SELECT COUNT(DISTINCT d.country) as count
                    FROM trips t
                    JOIN destinations d ON t.destination_id = d.id
                    WHERE t.user_id = :user_id AND t.status = 'visited'
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                
            case 'Planner':
            case 'Strategic Planner':
            case 'Dream Big':
                // Count planned trips
                $this->db->query("
                    SELECT COUNT(*) as count
                    FROM trips
                    WHERE user_id = :user_id AND status = 'planned'
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                  case 'Night Owl':
                // Count trips to cities/destinations known for nightlife
                $this->db->query("
                    SELECT COUNT(*) as count
                    FROM trips t
                    JOIN destinations d ON t.destination_id = d.id
                    WHERE t.user_id = :user_id AND t.status = 'visited'
                    AND (LOWER(d.description) LIKE '%nightlife%' 
                         OR LOWER(d.description) LIKE '%night%'
                         OR LOWER(d.name) LIKE '%vegas%'
                         OR LOWER(d.name) LIKE '%ibiza%'
                         OR LOWER(d.name) LIKE '%miami%')
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                  case 'Weekend Warrior':
                // Count trips that were created on weekends (Friday-Sunday)
                $this->db->query("
                    SELECT COUNT(*) as count
                    FROM trips
                    WHERE user_id = :user_id AND status IN ('visited')
                    AND DAYOFWEEK(created_at) IN (1, 6, 7)
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                  case 'Consistent Traveler':
                // Check if user has trips in multiple months (based on creation date)
                $this->db->query("
                    SELECT COUNT(DISTINCT MONTH(created_at)) as count
                    FROM trips
                    WHERE user_id = :user_id AND status IN ('visited')
                    AND YEAR(created_at) = YEAR(CURDATE())
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['count'];
                break;
                
            case 'Dedication':
                // Check account age in months
                $this->db->query("
                    SELECT TIMESTAMPDIFF(MONTH, created_at, NOW()) as months
                    FROM users
                    WHERE id = :user_id
                ");
                $this->db->bind(':user_id', $userId);
                $result = $this->db->single();
                $current = $result['months'] ?? 0;
                break;
                
            default:
                $current = 0;
        }
        
        return $current;
    }
    
    /**
     * Award a badge to a user
     * 
     * @param int $userId
     * @param int $badgeId
     * @return bool
     */    public function awardBadge($userId, $badgeId)
    {
        try {
            $this->db->query("
                INSERT INTO user_badges (user_id, badge_id, earned_date)
                VALUES (:user_id, :badge_id, NOW())
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':badge_id', $badgeId);
            
            return $this->db->execute();
        } catch (\Exception $e) {
            return false;
        }
    }
      /**
     * Create a badge notification for the user
     * 
     * @param int $userId
     * @param int $badgeId
     * @return bool
     */
    public function createBadgeNotification($userId, $badgeId)
    {
        try {
            // Check if badge_notifications table exists
            $this->db->query("SHOW TABLES LIKE 'badge_notifications'");
            $tableExists = $this->db->single();
            
            if (!$tableExists) {
                return true; // Skip if table doesn't exist
            }
            
            $this->db->query("
                INSERT INTO badge_notifications (user_id, badge_id, created_at, is_read)
                VALUES (:user_id, :badge_id, NOW(), 0)
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':badge_id', $badgeId);
            
            return $this->db->execute();
        } catch (\Exception $e) {
            return false;
        }
    }
      /**
     * Update user stats when a new badge is earned
     * 
     * @param int $userId
     * @param int $points
     * @return bool
     */
    public function updateUserStatsForNewBadge($userId, $points)
    {
        try {
            // Check if user_stats table exists
            $this->db->query("SHOW TABLES LIKE 'user_stats'");
            $tableExists = $this->db->single();
            
            if (!$tableExists) {
                return true; // Skip if table doesn't exist
            }
            
            // First, try to update existing stats
            $this->db->query("
                UPDATE user_stats 
                SET total_badges = total_badges + 1,
                    total_points = total_points + :points,
                    updated_at = NOW()
                WHERE user_id = :user_id
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':points', $points);
            
            $result = $this->db->execute();
            
            // If no rows were affected, create new stats record
            if ($this->db->rowCount() === 0) {
                $this->initializeUserStats($userId);
                // Try the update again
                $this->db->query("
                    UPDATE user_stats 
                    SET total_badges = total_badges + 1,
                        total_points = total_points + :points,
                        updated_at = NOW()
                    WHERE user_id = :user_id
                ");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':points', $points);
                $result = $this->db->execute();
            }
            
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Initialize user stats record
     * 
     * @param int $userId
     * @return bool
     */
    public function initializeUserStats($userId)
    {
        try {
            // Calculate current stats from existing data
            $this->db->query("
                SELECT COUNT(*) as badge_count FROM user_badges WHERE user_id = :user_id
            ");
            $this->db->bind(':user_id', $userId);
            $badgeResult = $this->db->single();
            
            $this->db->query("
                SELECT COALESCE(SUM(b.points), 0) as total_points
                FROM user_badges ub
                JOIN badges b ON ub.badge_id = b.id
                WHERE ub.user_id = :user_id
            ");
            $this->db->bind(':user_id', $userId);
            $pointsResult = $this->db->single();
            
            $this->db->query("
                SELECT COUNT(*) as trip_count FROM trips 
                WHERE user_id = :user_id AND status IN ('visited', 'completed')
            ");
            $this->db->bind(':user_id', $userId);
            $tripResult = $this->db->single();
            
            $this->db->query("
                SELECT COUNT(DISTINCT d.country) as country_count
                FROM trips t
                JOIN destinations d ON t.destination_id = d.id
                WHERE t.user_id = :user_id AND t.status IN ('visited', 'completed')
            ");
            $this->db->bind(':user_id', $userId);
            $countryResult = $this->db->single();
            
            $this->db->query("
                INSERT INTO user_stats (
                    user_id, total_badges, total_points, total_trips, 
                    countries_visited, created_at, updated_at
                ) VALUES (
                    :user_id, :total_badges, :total_points, :total_trips,
                    :countries_visited, NOW(), NOW()
                )
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':total_badges', $badgeResult['badge_count']);
            $this->db->bind(':total_points', $pointsResult['total_points']);
            $this->db->bind(':total_trips', $tripResult['trip_count']);
            $this->db->bind(':countries_visited', $countryResult['country_count']);
            
            return $this->db->execute();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get unread badge notifications for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUnreadBadgeNotifications($userId)
    {
        $this->db->query("
            SELECT bn.*, b.name, b.description, b.icon, b.points
            FROM badge_notifications bn
            JOIN badges b ON bn.badge_id = b.id
            WHERE bn.user_id = :user_id AND bn.is_read = 0
            ORDER BY bn.created_at DESC
        ");
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }
    
    /**
     * Mark badge notifications as read
     * 
     * @param int $userId
     * @param array $notificationIds
     * @return bool
     */
    public function markNotificationsAsRead($userId, $notificationIds = null)
    {
        try {
            if ($notificationIds && is_array($notificationIds)) {
                $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';
                $this->db->query("
                    UPDATE badge_notifications 
                    SET is_read = 1 
                    WHERE user_id = :user_id AND id IN ($placeholders)
                ");
                $this->db->bind(':user_id', $userId);
                foreach ($notificationIds as $index => $id) {
                    $this->db->bind($index + 1, $id);
                }
            } else {
                // Mark all as read
                $this->db->query("
                    UPDATE badge_notifications 
                    SET is_read = 1 
                    WHERE user_id = :user_id
                ");
                $this->db->bind(':user_id', $userId);
            }
            
            return $this->db->execute();
        } catch (\Exception $e) {
            return false;
        }
    }    /**
     * Get users by role(s)
     * 
     * @param string|array $roles
     * @return array
     */
    public function getByRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        $placeholders = str_repeat('?,', count($roles) - 1) . '?';
        $sql = "SELECT id, username, email, role FROM {$this->table} WHERE role IN ({$placeholders}) ORDER BY username";
        
        $this->db->query($sql);
        
        foreach ($roles as $index => $role) {
            $this->db->bind($index + 1, $role);
        }
        
        return $this->db->resultSet();
    }
}
