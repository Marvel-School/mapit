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
     * Display all user badges with enhanced system
     * 
     * @return void
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Get badge model
        $badgeModel = $this->model('Badge');
        $userModel = $this->model('User');
        
        // Check for new badges and award them
        $newBadges = $userModel->checkAndAwardBadges($userId);
        
        // Get badges organized by category
        $badgesByCategory = $badgeModel->getBadgesByCategory();
        
        // Get badges with progress for the user
        $badgesWithProgress = $badgeModel->getBadgesWithProgress($userId);
        
        // Get user badge statistics
        $userBadgeStats = $badgeModel->getUserBadgeStats($userId);
        
        // Get recent achievements
        $recentAchievements = $badgeModel->getRecentAchievements($userId, 5);
        
        // Get unread notifications
        $notifications = $userModel->getUnreadBadgeNotifications($userId);
        
        // Calculate category progress
        $categoryProgress = $this->calculateCategoryProgress($badgesWithProgress);
        
        // If there are new badges, show success message
        if (!empty($newBadges)) {
            $badgeNames = array_column($newBadges, 'name');
            $_SESSION['success'] = 'Congratulations! You earned: ' . implode(', ', $badgeNames);
        }
        
        $this->view('badges/index', [
            'title' => 'My Badges',
            'badgesByCategory' => $badgesByCategory,
            'badgesWithProgress' => $badgesWithProgress,
            'userBadgeStats' => $userBadgeStats,
            'recentAchievements' => $recentAchievements,
            'notifications' => $notifications,
            'categoryProgress' => $categoryProgress,
            'newBadges' => $newBadges
        ]);
    }
    
    /**
     * Calculate progress by category
     * 
     * @param array $badgesWithProgress
     * @return array
     */
    private function calculateCategoryProgress($badgesWithProgress)
    {
        $categoryProgress = [];
        
        foreach ($badgesWithProgress as $badge) {
            $category = $badge['category'];
            if (!isset($categoryProgress[$category])) {
                $categoryProgress[$category] = [
                    'total' => 0,
                    'earned' => 0,
                    'progress' => 0
                ];
            }
            
            $categoryProgress[$category]['total']++;
            if ($badge['earned']) {
                $categoryProgress[$category]['earned']++;
            }
        }
        
        // Calculate percentage for each category
        foreach ($categoryProgress as $category => $data) {
            $categoryProgress[$category]['progress'] = 
                $data['total'] > 0 ? round(($data['earned'] / $data['total']) * 100) : 0;
        }
        
        return $categoryProgress;
    }
      /**
     * Display a single badge with enhanced details
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
            SELECT ub.*, b.name, b.description, b.points, b.difficulty, b.category
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = :user_id AND ub.badge_id = :badge_id
        ");
        $db->bind(':user_id', $userId);
        $db->bind(':badge_id', $id);
        $earnedBadge = $db->single();
        
        // Calculate progress if not earned
        $progress = null;
        if (!$earnedBadge) {
            $userModel = $this->model('User');
            $current = $userModel->calculateProgressForBadge($badge, $userId);
            $progress = [
                'current' => $current,
                'required' => $badge['threshold'],
                'percentage' => min(100, round(($current / $badge['threshold']) * 100))
            ];
        }
        
        // Get users who have this badge (for inspiration)
        $db->query("
            SELECT u.username, ub.earned_at
            FROM user_badges ub
            JOIN users u ON ub.user_id = u.id
            WHERE ub.badge_id = :badge_id
            ORDER BY ub.earned_at DESC
            LIMIT 10
        ");
        $db->bind(':badge_id', $id);
        $recentEarners = $db->resultSet();
        
        // Get related badges in the same category
        $db->query("
            SELECT * FROM badges
            WHERE category = :category AND id != :badge_id
            ORDER BY difficulty ASC, threshold ASC
        ");
        $db->bind(':category', $badge['category']);
        $db->bind(':badge_id', $id);
        $relatedBadges = $db->resultSet();
        
        $this->view('badges/show', [
            'title' => $badge['name'],
            'badge' => $badge,
            'earnedBadge' => $earnedBadge,
            'progress' => $progress,
            'recentEarners' => $recentEarners,
            'relatedBadges' => $relatedBadges
        ]);
    }
    
    /**
     * Mark badge notifications as read (AJAX endpoint)
     * 
     * @return void
     */
    public function markNotificationsRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $userModel = $this->model('User');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $notificationIds = $input['notification_ids'] ?? null;
        
        $result = $userModel->markNotificationsAsRead($userId, $notificationIds);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
}
