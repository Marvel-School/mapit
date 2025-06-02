<?php

namespace App\Models;

use App\Core\Model;

class Badge extends Model
{
    protected $table = 'badges';    protected $fillable = [
        'name', 'description', 'threshold', 'icon', 'category', 'difficulty', 'points'
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
    
    /**
     * Get badges grouped by category
     * 
     * @return array
     */
    public function getBadgesByCategory()
    {
        $this->db->query("
            SELECT * FROM badges 
            ORDER BY category, difficulty, threshold
        ");
        
        $badges = $this->db->resultSet();
        $grouped = [];
        
        foreach ($badges as $badge) {
            $grouped[$badge['category']][] = $badge;
        }
        
        return $grouped;
    }
    
    /**
     * Get badge statistics for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUserBadgeStats($userId)
    {
        // Get total badges
        $this->db->query("SELECT COUNT(*) as total FROM badges");
        $total = $this->db->single()['total'];
        
        // Get earned badges count
        $this->db->query("
            SELECT COUNT(*) as earned 
            FROM user_badges 
            WHERE user_id = :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $earned = $this->db->single()['earned'];
        
        // Get total points earned
        $this->db->query("
            SELECT COALESCE(SUM(b.points), 0) as total_points
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $points = $this->db->single()['total_points'];
        
        // Get badges by category
        $this->db->query("
            SELECT b.category, COUNT(*) as earned_count,
                   (SELECT COUNT(*) FROM badges WHERE category = b.category) as total_count
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = :user_id
            GROUP BY b.category
        ");
        $this->db->bind(':user_id', $userId);
        $categories = $this->db->resultSet();
        
        return [
            'total_badges' => $total,
            'earned_badges' => $earned,
            'total_points' => $points,
            'completion_percentage' => $total > 0 ? round(($earned / $total) * 100, 1) : 0,
            'categories' => $categories
        ];
    }
    
    /**
     * Get recent badge achievements for a user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentAchievements($userId, $limit = 5)
    {
        $this->db->query("
            SELECT b.*, ub.earned_date
            FROM badges b
            JOIN user_badges ub ON b.id = ub.badge_id
            WHERE ub.user_id = :user_id
            ORDER BY ub.earned_date DESC
            LIMIT :limit
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get badges with progress for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getBadgesWithProgress($userId)
    {
        // Get all badges
        $this->db->query("
            SELECT b.*, 
                   CASE WHEN ub.badge_id IS NOT NULL THEN 1 ELSE 0 END as earned,
                   ub.earned_date
            FROM badges b
            LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = :user_id
            ORDER BY b.category, b.difficulty, b.threshold
        ");
        
        $this->db->bind(':user_id', $userId);
        $badges = $this->db->resultSet();
        
        // Calculate progress for each badge
        foreach ($badges as &$badge) {
            if (!$badge['earned']) {
                $badge['progress'] = $this->calculateBadgeProgress($userId, $badge);
            } else {
                $badge['progress'] = 100;
            }
        }
        
        return $badges;
    }
    
    /**
     * Calculate progress for a specific badge
     * 
     * @param int $userId
     * @param array $badge
     * @return int Progress percentage (0-100)
     */
    public function calculateBadgeProgress($userId, $badge)
    {
        $current = 0;
        
        switch ($badge['name']) {
            case 'First Steps':
            case 'Explorer':
            case 'Globetrotter':
            case 'World Wanderer':
            case 'Master Explorer':
                // Count unique destinations visited
                $this->db->query("
                    SELECT COUNT(DISTINCT destination_id) as count
                    FROM trips
                    WHERE user_id = :user_id AND status IN ('visited', 'completed')
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
                    WHERE user_id = :user_id AND type = 'adventure' AND status IN ('visited', 'completed')
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
                    WHERE user_id = :user_id AND type = 'relaxation' AND status IN ('visited', 'completed')
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
                    WHERE t.user_id = :user_id AND t.status IN ('visited', 'completed')
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
                
            default:
                // For badges without clear progress calculation
                $current = 0;
        }
        
        return min(100, round(($current / $badge['threshold']) * 100));
    }
}
