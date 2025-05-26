<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class LogController extends Controller
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
     * Display system logs
     * 
     * @return void
     */
    public function index()
    {
        // Get filter parameters
        $level = $_GET['level'] ?? null;
        $component = $_GET['component'] ?? null;
        $fromDate = $_GET['from_date'] ?? null;
        $toDate = $_GET['to_date'] ?? null;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 50;
        
        // Validate page
        if ($page < 1) {
            $page = 1;
        }
        
        // Get logs
        $logModel = $this->model('Log');
        $logsData = $logModel->getPaginated($page, $perPage, [
            'level' => $level,
            'component' => $component,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);
        
        // Get unique levels and components for filters
        $levels = $logModel->getLevels();
        $components = $logModel->getComponents();
        
        $this->view('admin/logs/index', [
            'title' => 'System Logs',
            'logs' => $logsData['logs'],
            'pagination' => [
                'page' => $logsData['page'],
                'perPage' => $logsData['perPage'],
                'total' => $logsData['total'],
                'totalPages' => $logsData['totalPages']
            ],
            'filters' => [
                'level' => $level,
                'component' => $component,
                'from_date' => $fromDate,
                'to_date' => $toDate
            ],
            'filterOptions' => [
                'levels' => $levels,
                'components' => $components
            ]
        ]);
    }
}
