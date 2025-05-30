<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';    protected $fillable = [
        'username', 'email', 'password_hash', 'role', 'name', 'bio', 'country', 'website', 'avatar', 'settings', 'last_login'
    ];
    
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
        
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        unset($data['password_confirm']);
        
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
            return false;
        }
          // Verify password if user exists
        if (password_verify($password, $user['password_hash'])) {
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
     * Check badge progress for a user
     * 
     * @param int $userId
     * @return array
     */
    public function checkBadgeProgress($userId)
    {
        // Get all badges
        $this->db->query("SELECT * FROM badges");
        $badges = $this->db->resultSet();
        
        $progress = [];
        
        foreach ($badges as $badge) {
            // Check if user already has this badge
            $this->db->query("
                SELECT * FROM user_badges 
                WHERE user_id = :user_id AND badge_id = :badge_id
            ");
            
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':badge_id', $badge['id']);
            
            $existing = $this->db->single();
            $earned = !empty($existing);
            
            // Get progress based on badge type
            $count = 0;
            
            switch($badge['name']) {                case 'Globetrotter':
                    // Count all destinations visited (handle both 'visited' and 'completed' statuses)
                    $this->db->query("
                        SELECT COUNT(*) as count 
                        FROM trips 
                        WHERE user_id = :user_id AND status IN ('visited', 'completed')
                    ");
                    $this->db->bind(':user_id', $userId);
                    $result = $this->db->single();
                    $count = $result['count'];
                    break;
                  case 'Adventurer':
                    // Count adventure trips (handle both 'visited' and 'completed' statuses)
                    $this->db->query("
                        SELECT COUNT(*) as count 
                        FROM trips 
                        WHERE user_id = :user_id AND type = 'adventure' AND status IN ('visited', 'completed')
                    ");
                    $this->db->bind(':user_id', $userId);
                    $result = $this->db->single();
                    $count = $result['count'];
                    break;
                    
                case 'Relaxation Master':
                    // Count relaxation trips (handle both 'visited' and 'completed' statuses)
                    $this->db->query("
                        SELECT COUNT(*) as count 
                        FROM trips 
                        WHERE user_id = :user_id AND type = 'relaxation' AND status IN ('visited', 'completed')
                    ");
                    $this->db->bind(':user_id', $userId);
                    $result = $this->db->single();
                    $count = $result['count'];
                    break;
            }
            
            $progress[] = [
                'badge' => $badge,
                'earned' => $earned,
                'count' => $count,
                'percent' => min(100, ($count / $badge['threshold']) * 100)
            ];
            
            // If badge is earned now but wasn't before, add it to user_badges
            if (!$earned && $count >= $badge['threshold']) {
                $this->db->query("
                    INSERT INTO user_badges (user_id, badge_id)
                    VALUES (:user_id, :badge_id)
                ");
                
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':badge_id', $badge['id']);
                $this->db->execute();
            }
        }
        
        return $progress;
    }
}
