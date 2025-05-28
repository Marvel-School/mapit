<?php

namespace App\Models;

use App\Core\Model;

class Destination extends Model
{
    protected $table = 'destinations';    protected $fillable = [
        'name', 'description', 'country', 'city', 'latitude', 'longitude', 
        'privacy', 'user_id', 'featured', 'notes', 'approval_status'
    ];
      /**
     * Get public destinations
     * 
     * @return array
     */
    public function getPublic()
    {
        $this->db->query("
            SELECT d.*, 
                u.username as creator,
                d.country as country_name,
                0 as visited,
                NULL as visit_date,
                0 as trip_count
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.privacy = 'public' AND d.approval_status = 'approved'
            ORDER BY d.created_at DESC
        ");
        
        return $this->db->resultSet();
    }
    
    /**
     * Get featured destinations
     * 
     * @param int $limit
     * @return array
     */
    public function getFeatured($limit = 6)
    {
        $this->db->query("
            SELECT d.*, u.username as creator
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.featured = 1 AND d.approval_status = 'approved'
            ORDER BY d.created_at DESC
            LIMIT :limit
        ");
        
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get destinations by user
     * 
     * @param int $userId
     * @return array
     */    public function getByUser($userId)
    {
        $this->db->query("
            SELECT d.*, 
                d.country as country_name,
                CASE 
                    WHEN EXISTS(SELECT 1 FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id AND status = 'visited') 
                    THEN 1 
                    ELSE 0 
                END as visited,
                (SELECT created_at FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id2 AND status = 'visited' ORDER BY created_at DESC LIMIT 1) as visit_date,
                (SELECT COUNT(*) FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id3) as trip_count
            FROM destinations d
            WHERE d.user_id = :user_id
            ORDER BY d.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':trip_user_id', $userId);
        $this->db->bind(':trip_user_id2', $userId);
        $this->db->bind(':trip_user_id3', $userId);
        
        return $this->db->resultSet();
    }
      /**
     * Get destinations by user - only their own destinations and public ones they have explicitly added trips for
     * 
     * @param int $userId
     * @return array
     */
    public function getUserDestinationsWithTrips($userId)
    {
        $this->db->query("
            SELECT DISTINCT d.*, 
                d.country as country_name,
                u.username as creator,
                CASE 
                    WHEN EXISTS(SELECT 1 FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id AND status = 'visited') 
                    THEN 1 
                    ELSE 0 
                END as visited,
                (SELECT created_at FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id2 AND status = 'visited' ORDER BY created_at DESC LIMIT 1) as visit_date,
                (SELECT COUNT(*) FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id3) as trip_count
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.user_id = :user_id
            ORDER BY d.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':trip_user_id', $userId);
        $this->db->bind(':trip_user_id2', $userId);
        $this->db->bind(':trip_user_id3', $userId);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get user's destinations with trip status information for dashboard map
     * This includes the latest trip status (planned, in_progress, visited) for each destination
     * 
     * @param int $userId
     * @return array
     */
    public function getUserDestinationsWithTripStatus($userId)
    {
        $this->db->query("
            SELECT DISTINCT d.*, 
                d.country as country_name,
                u.username as creator,
                t.status as trip_status,
                t.created_at as trip_date,
                t.id as trip_id,
                CASE 
                    WHEN t.status = 'visited' THEN 1 
                    ELSE 0 
                END as visited,
                (SELECT COUNT(*) FROM trips WHERE destination_id = d.id AND user_id = :trip_user_id) as trip_count
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
            LEFT JOIN trips t ON d.id = t.destination_id AND t.user_id = :trip_user_id2 
                AND t.id = (
                    SELECT id FROM trips t2 
                    WHERE t2.destination_id = d.id AND t2.user_id = :trip_user_id3 
                    ORDER BY 
                        CASE t2.status 
                            WHEN 'visited' THEN 1
                            WHEN 'in_progress' THEN 2
                            WHEN 'planned' THEN 3
                            ELSE 4
                        END,
                        t2.created_at DESC 
                    LIMIT 1
                )            WHERE d.user_id = :user_id
            ORDER BY d.created_at DESC
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':trip_user_id', $userId);
        $this->db->bind(':trip_user_id2', $userId);
        $this->db->bind(':trip_user_id3', $userId);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get destinations pending approval
     * 
     * @return array
     */
    public function getPending()
    {
        $this->db->query("
            SELECT d.*, u.username as creator
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.approval_status = 'pending'
            ORDER BY d.created_at DESC
        ");
        
        return $this->db->resultSet();
    }
    
    /**
     * Approve a destination
     * 
     * @param int $id
     * @return bool
     */
    public function approve($id)
    {
        return $this->update($id, ['approval_status' => 'approved']);
    }
    
    /**
     * Reject a destination
     * 
     * @param int $id
     * @return bool
     */
    public function reject($id)
    {
        return $this->update($id, ['approval_status' => 'rejected']);
    }
      /**
     * Set featured status
     * 
     * @param int $id
     * @param bool $featured
     * @return bool
     */
    public function setFeatured($id, $featured = true)
    {
        return $this->update($id, ['featured' => $featured ? 1 : 0]);
    }
    
    /**
     * Get nearby destinations within a certain radius
     * 
     * @param float $latitude
     * @param float $longitude
     * @param int $excludeId
     * @param int $radiusKm
     * @param int $limit
     * @return array
     */
    public function getNearby($latitude, $longitude, $excludeId = null, $radiusKm = 100, $limit = 6)
    {
        $sql = "
            SELECT *,
            (6371 * acos(cos(radians(:lat1)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(:lng1)) + sin(radians(:lat2)) * 
            sin(radians(latitude)))) AS distance
            FROM destinations
            WHERE privacy = 'public' 
            AND approval_status = 'approved'
        ";
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $sql .= "
            HAVING distance < :radius
            ORDER BY distance
            LIMIT :limit
        ";
        
        $this->db->query($sql);
        $this->db->bind(':lat1', $latitude);
        $this->db->bind(':lng1', $longitude);
        $this->db->bind(':lat2', $latitude);
        $this->db->bind(':radius', $radiusKm);
        $this->db->bind(':limit', $limit);
        
        if ($excludeId) {
            $this->db->bind(':exclude_id', $excludeId);
        }        return $this->db->resultSet();
    }
}
