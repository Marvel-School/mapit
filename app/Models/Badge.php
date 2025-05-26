<?php

namespace App\Models;

use App\Core\Model;

class Badge extends Model
{
    protected $table = 'badges';
    protected $fillable = [
        'name', 'description', 'threshold'
    ];
    
    /**
     * Award a badge to a user
     * 
     * @param int $userId
     * @param int $badgeId
     * @return bool
     */
    public function awardToUser($userId, $badgeId)
    {
        // Check if the user already has this badge
        $this->db->query("
            SELECT * FROM user_badges
            WHERE user_id = :user_id AND badge_id = :badge_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':badge_id', $badgeId);
        
        $existing = $this->db->single();
        
        if ($existing) {
            // User already has this badge
            return true;
        }
        
        // Award the badge
        $this->db->query("
            INSERT INTO user_badges (user_id, badge_id)
            VALUES (:user_id, :badge_id)
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':badge_id', $badgeId);
        
        return $this->db->execute();
    }
    
    /**
     * Get badges earned by a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUserBadges($userId)
    {
        $this->db->query("
            SELECT b.*, ub.earned_date
            FROM badges b
            JOIN user_badges ub ON b.id = ub.badge_id
            WHERE ub.user_id = :user_id
            ORDER BY ub.earned_date DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get badges not yet earned by a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUserUnearned($userId)
    {
        $this->db->query("
            SELECT b.*
            FROM badges b
            WHERE b.id NOT IN (
                SELECT badge_id FROM user_badges WHERE user_id = :user_id
            )
        ");
        
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }
}
