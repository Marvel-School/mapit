<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class SimpleApiController extends Controller
{    public function destinations()
    {
        try {
            // Set headers
            header('Content-Type: application/json');
            
            // Check database config
            $dbConfig = [
                'host' => getenv('DB_HOST') ?: 'mysql',
                'database' => getenv('DB_DATABASE') ?: 'mapit',
                'username' => getenv('DB_USERNAME') ?: 'mapit_user',
                'password' => getenv('DB_PASSWORD') ? 'SET' : 'NOT_SET'
            ];
            
            $destinationModel = $this->model('Destination');
            
            // Test with a simple query
            $destinations = $destinationModel->getPublic();
            
            // Also try to get featured for comparison
            $featured = $destinationModel->getFeatured(5);
            
            $result = [
                'success' => true,
                'data' => $destinations,
                'count' => count($destinations),
                'featured_count' => count($featured),
                'db_config' => $dbConfig,
                'debug' => 'Simple API test with config check'
            ];
            
            echo json_encode($result);
            exit;
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => 'Exception caught',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            exit;
        }
    }
}
?>
