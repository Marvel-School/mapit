<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class PublicDestinationController extends Controller
{
    /**
     * Constructor - No authentication required for public API endpoints
     */
    public function __construct()
    {
        try {
            // Start output buffering to capture any unwanted output
            if (!ob_get_level()) {
                ob_start();
            }
            
            // Set secure JSON response headers
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            
            // Check rate limiting for anonymous users (more restrictive)
            if (!$this->checkRateLimit('api_public_destination', 30, 300)) { // 30 requests per 5 minutes
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                exit();
            }
            
        } catch (\Exception $e) {
            error_log("Public API Destination Controller initialization error: " . $e->getMessage());
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INITIALIZATION_ERROR'
            ], 500);
            exit();
        }
    }

    /**
     * Get public destinations for the map
     * 
     * @return void
     */    public function index()
    {
        try {
            $destinationModel = $this->model('Destination');
            
            // Get all public approved destinations
            $destinations = $destinationModel->getPublic();
            
            error_log("API: Retrieved " . count($destinations) . " destinations from model");
            
            // Sanitize output data
            $sanitizedDestinations = array_map(function($destination) {
                try {
                    return [
                        'id' => (int)$destination['id'],
                        'name' => htmlspecialchars($destination['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'latitude' => (float)$destination['latitude'],
                        'longitude' => (float)$destination['longitude'],
                        'description' => htmlspecialchars($destination['description'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'city' => htmlspecialchars($destination['city'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'country' => htmlspecialchars($destination['country'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'country_name' => htmlspecialchars($destination['country_name'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'creator' => htmlspecialchars($destination['creator'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'featured' => (int)($destination['featured'] ?? 0),
                        'created_at' => $destination['created_at']
                    ];                } catch (\Exception $e) {
                    error_log("Error sanitizing destination " . ($destination['id'] ?? 'unknown') . ": " . $e->getMessage());
                    return null;
                }
            }, $destinations);
            
            // Remove null entries
            $sanitizedDestinations = array_filter($sanitizedDestinations);
            
            error_log("API: Sanitized " . count($sanitizedDestinations) . " destinations");
            
            $this->cleanJsonResponse([
                'success' => true,
                'data' => array_values($sanitizedDestinations),
                'count' => count($sanitizedDestinations)
            ]);
            
        } catch (\Exception $e) {
            error_log("Public API destinations index error: " . $e->getMessage());
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve destinations',
                'error_code' => 'RETRIEVAL_ERROR'
            ], 500);
        }
    }

    /**
     * Get featured destinations
     * 
     * @return void
     */
    public function featured()
    {
        try {
            $destinationModel = $this->model('Destination');
            
            // Get featured destinations
            $destinations = $destinationModel->getFeatured(50);
            
            // Sanitize output data
            $sanitizedDestinations = array_map(function($destination) {
                return [
                    'id' => (int)$destination['id'],
                    'name' => $this->sanitizeInput($destination['name']),
                    'latitude' => (float)$destination['latitude'],
                    'longitude' => (float)$destination['longitude'],
                    'description' => $this->sanitizeInput($destination['description'] ?? ''),
                    'city' => $this->sanitizeInput($destination['city'] ?? ''),
                    'country' => $this->sanitizeInput($destination['country'] ?? ''),
                    'creator' => $this->sanitizeInput($destination['creator'] ?? ''),
                    'featured' => 1,
                    'created_at' => $destination['created_at']
                ];
            }, $destinations);
            
            $this->cleanJsonResponse([
                'success' => true,
                'data' => $sanitizedDestinations,
                'count' => count($sanitizedDestinations)
            ]);
            
        } catch (\Exception $e) {
            error_log("Public API featured destinations error: " . $e->getMessage());
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve featured destinations',
                'error_code' => 'RETRIEVAL_ERROR'
            ], 500);
        }
    }

    /**
     * Get a single public destination
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        try {
            // Validate and sanitize destination ID
            $destinationId = $this->validateNumeric($id, 1);
            if ($destinationId === false) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Invalid destination ID',
                    'error_code' => 'INVALID_ID'
                ], 400);
                return;
            }
            
            $destinationModel = $this->model('Destination');
            $destination = $destinationModel->find($destinationId);
            
            if (!$destination) {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Destination not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
                return;
            }
            
            // Check if destination is publicly viewable
            if ($destination['privacy'] !== 'public' || $destination['approval_status'] !== 'approved') {
                $this->cleanJsonResponse([
                    'success' => false,
                    'message' => 'Destination not publicly available',
                    'error_code' => 'ACCESS_DENIED'
                ], 403);
                return;
            }
            
            // Sanitize output data
            $sanitizedDestination = [
                'id' => (int)$destination['id'],
                'name' => $this->sanitizeInput($destination['name']),
                'latitude' => (float)$destination['latitude'],
                'longitude' => (float)$destination['longitude'],
                'description' => $this->sanitizeInput($destination['description'] ?? ''),
                'city' => $this->sanitizeInput($destination['city'] ?? ''),
                'country' => $this->sanitizeInput($destination['country'] ?? ''),
                'featured' => (int)($destination['featured'] ?? 0),
                'created_at' => $destination['created_at']
            ];
            
            $this->cleanJsonResponse([
                'success' => true,
                'data' => $sanitizedDestination
            ]);
            
        } catch (\Exception $e) {
            error_log("Public API destination show error: " . $e->getMessage());
            $this->cleanJsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve destination',
                'error_code' => 'RETRIEVAL_ERROR'
            ], 500);
        }
    }

    /**
     * Send clean JSON response by discarding any previous output
     * 
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    protected function cleanJsonResponse(array $data, int $statusCode = 200)
    {
        // Clean any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set HTTP status code
        http_response_code($statusCode);
        
        // Output clean JSON
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Validate numeric input
     * 
     * @param mixed $value
     * @param float $min
     * @param float $max
     * @return float|false
     */
    protected function validateNumeric($value, $min = null, $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $numValue = (float)$value;
        
        if ($min !== null && $numValue < $min) {
            return false;
        }
        
        if ($max !== null && $numValue > $max) {
            return false;
        }
        
        return $numValue;
    }

    /**
     * Sanitize input to prevent XSS
     * 
     * @param string $input
     * @return string
     */
    protected function sanitizeInput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }    /**
     * Check rate limiting
     * 
     * @param string $action
     * @param int $maxAttempts
     * @param int $timeWindow
     * @return bool
     */    protected function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300)
    {
        // Simple rate limiting implementation
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitKey = $action . '_' . $clientIP;
        
        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = [
                'count' => 1,
                'reset_time' => time() + $timeWindow
            ];
            return true;
        }
        
        $rateData = $_SESSION[$rateLimitKey];
        
        // Reset if window has passed
        if (time() > $rateData['reset_time']) {
            $_SESSION[$rateLimitKey] = [
                'count' => 1,
                'reset_time' => time() + $timeWindow
            ];
            return true;
        }
        
        // Check if limit exceeded
        if ($rateData['count'] >= $maxAttempts) {
            return false;
        }
        
        // Increment count
        $_SESSION[$rateLimitKey]['count']++;
        return true;
    }
}
