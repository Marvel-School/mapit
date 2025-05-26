<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    /**
     * Constructor - Require admin role
     */
    public function __construct()
    {
        // Check if user is logged in and has admin role
        $this->requireLogin();
        $this->requireRole('admin');
    }
    
    /**
     * Display admin dashboard
     * 
     * @return void
     */
    public function index()
    {
        // Get user statistics
        $userModel = $this->model('User');
        $userCount = $userModel->count();
        
        // Get destination statistics
        $destinationModel = $this->model('Destination');
        $destinationCount = $destinationModel->count();
          // Get pending destinations count
        $db = Database::getInstance();
        $db->query("
            SELECT COUNT(*) as count FROM destinations
            WHERE approval_status = 'pending'
        ");
        $pending = $db->single();
        $pendingCount = $pending['count'];
        
        // Get trip statistics
        $tripModel = $this->model('Trip');
        $tripCount = $tripModel->count();
          // Get visit statistics
        $db->query("
            SELECT COUNT(*) as count FROM trips
            WHERE status = 'visited'
        ");
        $visited = $db->single();
        $visitedCount = $visited['count'];
        
        // Get recent activity from logs
        $logModel = $this->model('Log');
        $db->query("
            SELECT l.*, u.username
            FROM logs l
            LEFT JOIN users u ON JSON_EXTRACT(l.data, '$.user_id') = u.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ");
        $recentActivity = $db->resultSet();
        
        $this->view('admin/dashboard/index', [
            'title' => 'Admin Dashboard',
            'statistics' => [
                'users' => $userCount,
                'destinations' => $destinationCount,
                'pending' => $pendingCount,
                'trips' => $tripCount,
                'visited' => $visitedCount
            ],
            'recentActivity' => $recentActivity
        ]);
    }
}
