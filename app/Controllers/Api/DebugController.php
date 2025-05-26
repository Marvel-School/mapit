<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Logger;

class DebugController extends Controller
{
    /**
     * Log client-side debug messages
     * 
     * @return void
     */
    public function log()
    {
        // Check if this is a valid JSON request
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            $this->json(['error' => 'Invalid content type'], 400);
            return;
        }
        
        // Get the raw POST data
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        
        if (!$data) {
            $this->json(['error' => 'Invalid JSON data'], 400);
            return;
        }
        
        // Required fields
        $level = $data['level'] ?? 'info';
        $message = $data['message'] ?? 'No message provided';
        $url = $data['url'] ?? 'unknown';
        $userAgent = $data['userAgent'] ?? 'unknown';
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
        
        // Format the log message
        $logMessage = "[Client Debug: {$url}] [{$userAgent}] {$message}";
        
        // Log to the appropriate channel based on level
        switch ($level) {
            case 'error':
                Logger::error($logMessage);
                break;
            case 'warning':
                Logger::warn($logMessage);
                break;
            case 'info':
            default:
                Logger::info($logMessage);
                break;
        }
        
        // Save to database if configured
        $this->saveToDatabase($data);
        
        // Return success
        $this->json(['success' => true, 'message' => 'Log saved']);
    }
    
    /**
     * Save debug log to database for later analysis
     * 
     * @param array $data
     * @return void
     */
    private function saveToDatabase($data)
    {
        try {
            $logModel = $this->model('Log');
            
            $logData = [
                'type' => 'client_debug',
                'level' => $data['level'] ?? 'info',
                'message' => $data['message'] ?? 'No message provided',
                'context' => json_encode([
                    'url' => $data['url'] ?? 'unknown',
                    'userAgent' => $data['userAgent'] ?? 'unknown',
                    'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s')
                ]),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $logModel->create($logData);
        } catch (\Exception $e) {
            // Silent fail - we don't want to cause errors for logging
            Logger::error("Failed to save client debug log: " . $e->getMessage());
        }
    }
}
