<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;

class HealthController extends Controller
{
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'version' => '1.0.0',
            'checks' => []
        ];

        // Database health check
        try {
            $db = Database::getInstance();
            $db->query("SELECT 1");
            $health['checks']['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful'
            ];
        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // Redis health check (if available)
        if (class_exists('Redis')) {
            try {
                $redis = new \Redis();
                $redis->connect('redis', 6379);
                $redis->ping();
                $health['checks']['redis'] = [
                    'status' => 'healthy',
                    'message' => 'Redis connection successful'
                ];
                $redis->close();
            } catch (Exception $e) {
                $health['checks']['redis'] = [
                    'status' => 'unhealthy',
                    'message' => 'Redis connection failed: ' . $e->getMessage()
                ];
            }
        }

        // Storage health check
        $storageDir = __DIR__ . '/../../storage';
        if (is_writable($storageDir)) {
            $health['checks']['storage'] = [
                'status' => 'healthy',
                'message' => 'Storage directory is writable'
            ];
        } else {
            $health['status'] = 'unhealthy';
            $health['checks']['storage'] = [
                'status' => 'unhealthy',
                'message' => 'Storage directory is not writable'
            ];
        }

        // Log directory health check
        $logDir = __DIR__ . '/../../logs';
        if (is_writable($logDir)) {
            $health['checks']['logs'] = [
                'status' => 'healthy',
                'message' => 'Log directory is writable'
            ];
        } else {
            $health['status'] = 'unhealthy';
            $health['checks']['logs'] = [
                'status' => 'unhealthy',
                'message' => 'Log directory is not writable'
            ];
        }

        // Memory usage check
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;

        $health['checks']['memory'] = [
            'status' => $memoryPercent < 90 ? 'healthy' : 'warning',
            'usage' => $this->formatBytes($memoryUsage),
            'limit' => $memoryLimit,
            'percentage' => round($memoryPercent, 2)
        ];

        // Set appropriate HTTP status code
        $statusCode = $health['status'] === 'healthy' ? 200 : 503;
        
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($health, JSON_PRETTY_PRINT);
    }

    private function parseMemoryLimit($limit)
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $num = (int)$limit;

        switch ($last) {
            case 'g':
                $num *= 1024;
            case 'm':
                $num *= 1024;
            case 'k':
                $num *= 1024;
        }

        return $num;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
