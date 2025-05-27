<?php

namespace App\Models;

use App\Core\Model;

class Trip extends Model
{
    protected $table = 'trips';
    protected $fillable = [
        'user_id', 'destination_id', 'status', 'type'
    ];
    
    /**
     * Get a user's trips with destination details
     * 
     * @param int $userId
     * @param array $filters
     * @return array
     */
    public function getUserTrips($userId, $filters = [])
    {
        $sql = "
            SELECT t.*, d.name as destination_name, 
                d.latitude, d.longitude, d.description as destination_description
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.user_id = :user_id
        ";
        
        $params = [':user_id' => $userId];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params[':type'] = $filters['type'];
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        $this->db->query($sql);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Update trip status
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Check if a trip exists for a user and destination
     * 
     * @param int $userId
     * @param int $destinationId
     * @return array|bool
     */
    public function findUserDestinationTrip($userId, $destinationId)
    {
        $this->db->query("
            SELECT * FROM trips
            WHERE user_id = :user_id AND destination_id = :destination_id
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':destination_id', $destinationId);
        
        return $this->db->single();
    }
    
    /**
     * Get trip statistics for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUserStats($userId)
    {
        $stats = [];
        
        // Count planned trips
        $this->db->query("
            SELECT COUNT(*) as count
            FROM trips
            WHERE user_id = :user_id AND status = 'planned'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        $stats['planned'] = (int) $result['count'];
        
        // Count visited trips
        $this->db->query("
            SELECT COUNT(*) as count
            FROM trips
            WHERE user_id = :user_id AND status = 'visited'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        $stats['visited'] = (int) $result['count'];
        
        // Count adventure trips
        $this->db->query("
            SELECT COUNT(*) as count
            FROM trips
            WHERE user_id = :user_id AND type = 'adventure'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        $stats['adventure'] = (int) $result['count'];
        
        // Count relaxation trips
        $this->db->query("
            SELECT COUNT(*) as count
            FROM trips
            WHERE user_id = :user_id AND type = 'relaxation'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        $stats['relaxation'] = (int) $result['count'];
        
        return $stats;
    }    /**
     * Count the number of different countries visited by a user
     * 
     * @param int $userId
     * @return int
     */
    public function getCountriesVisitedCount($userId)
    {
        // Count distinct countries from visited destinations
        $this->db->query("
            SELECT COUNT(DISTINCT d.country) as count
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.user_id = :user_id AND t.status = 'visited' AND d.country IS NOT NULL AND d.country != ''
        ");
        
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        
        return $result ? (int) $result['count'] : 0;
    }
    
    /**
     * Check if user has earned any new badges
     * 
     * @param int $userId
     * @return void
     */
    public function checkBadges($userId)
    {
        $userModel = new \App\Models\User();
        $userModel->checkBadgeProgress($userId);
    }
}
