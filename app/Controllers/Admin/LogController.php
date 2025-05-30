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
    
    /**
     * Clear all logs
     * 
     * @return void
     */
    public function clear()
    {
        // Check if request is AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        try {
            $logModel = $this->model('Log');
            $cleared = $logModel->clearAll();
            
            if ($cleared) {
                // Log the action
                $logModel::write('INFO', 'System logs cleared by admin', [
                    'admin_id' => $_SESSION['user_id'],
                    'admin_username' => $_SESSION['username']
                ], 'Admin');
                
                $this->json(['success' => true, 'message' => 'All logs cleared successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to clear logs'], 500);
            }        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error clearing logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a specific log entry
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        // Ensure this is an AJAX request
        if (!\App\Core\Request::isAjax()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Only allow DELETE requests
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $logModel = $this->model('Log');
            
            // Check if log exists
            $log = $logModel->find($id);
            if (!$log) {
                $this->json(['success' => false, 'message' => 'Log entry not found'], 404);
                return;
            }

            // Delete the log entry
            $deleted = $logModel->delete($id);
            
            if ($deleted) {
                // Log the deletion action
                $logModel::write('INFO', "Log entry deleted by admin", [
                    'admin_id' => $_SESSION['user_id'],
                    'admin_username' => $_SESSION['username'],
                    'deleted_log_id' => $id
                ], 'Admin');
                
                $this->json(['success' => true, 'message' => 'Log entry deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete log entry'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error deleting log: ' . $e->getMessage()], 500);
        }
    }    /**
     * Get detailed log data for viewing
     * 
     * @param int $id
     * @return void
     */
    public function data($id)
    {
        // Ensure this is an AJAX request
        if (!\App\Core\Request::isAjax()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        try {
            $logModel = $this->model('Log');
            
            // Get the log entry
            $log = $logModel->find($id);
            if (!$log) {
                $this->json(['success' => false, 'message' => 'Log entry not found'], 404);
                return;
            }

            // Prepare the detailed data
            $logData = [
                'id' => $log['id'],
                'level' => $log['level'],
                'message' => $log['message'],
                'component' => $log['component'],
                'data' => $log['data'],
                'url' => $log['url'],
                'created_at' => $log['created_at'],
                'formatted_timestamp' => date('Y-m-d H:i:s', strtotime($log['created_at']))
            ];

            // If data is JSON, decode it for better display
            if (!empty($log['data'])) {
                $decodedData = json_decode($log['data'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $logData['data_decoded'] = $decodedData;
                }
            }

            $this->json(['success' => true, 'data' => $logData]);
            
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error retrieving log data: ' . $e->getMessage()], 500);
        }
    }
}
