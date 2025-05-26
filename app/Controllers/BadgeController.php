<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BadgeController extends Controller
{
    /**
     * Constructor - Require authentication
     */
    public function __construct()
    {
        $this->requireLogin();
    }
    
    /**
     * Display all user badges
     * 
     * @return void
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Get all badges
        $badgeModel = $this->model('Badge');
        $badges = $badgeModel->all();
        
        // Get earned badges
        $earnedBadges = $badgeModel->getUserBadges($userId);
        $earnedIds = array_column($earnedBadges, 'id');
          // Get progress on unearned badges
        $userModel = $this->model('User');
        $progress = $userModel->checkBadgeProgress($userId);
        
        // Get trip statistics
        $tripModel = $this->model('Trip');
        $tripStats = $tripModel->getUserStats($userId);
        
        // Calculate user stats for the view
        $userStats = [
            'countries_visited' => $tripModel->getCountriesVisitedCount($userId),
            'continents_visited' => 0,  // We don't have continent data yet
            'badges_earned' => count($earnedBadges)
        ];
        
        $this->view('badges/index', [
            'title' => 'My Badges',
            'badges' => $badges,
            'earnedBadges' => $earnedBadges,
            'earnedIds' => $earnedIds,
            'progress' => $progress,
            'userStats' => $userStats,
            'tripStats' => $tripStats
        ]);
    }
    
    /**
     * Display a single badge
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $userId = $_SESSION['user_id'];
        
        // Get badge
        $badgeModel = $this->model('Badge');
        $badge = $badgeModel->find($id);
        
        if (!$badge) {
            $_SESSION['error'] = 'Badge not found';
            $this->redirect('/badges');
            return;
        }
          // Check if user has earned this badge
        $db = \App\Core\Database::getInstance();
        $db->query("
            SELECT * FROM user_badges
            WHERE user_id = :user_id AND badge_id = :badge_id
        ");
        $db->bind(':user_id', $userId);
        $db->bind(':badge_id', $id);
        $earned = $db->single();
        
        // Get progress
        $userModel = $this->model('User');
        $progress = $userModel->checkBadgeProgress($userId);
        
        // Find the progress for this badge
        $badgeProgress = null;
        foreach ($progress as $p) {
            if ($p['badge']['id'] == $id) {
                $badgeProgress = $p;
                break;
            }
        }
        
        $this->view('badges/show', [
            'title' => $badge['name'],
            'badge' => $badge,
            'earned' => $earned,
            'progress' => $badgeProgress
        ]);
    }
}
