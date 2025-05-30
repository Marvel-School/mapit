<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use Exception;

class TripController extends Controller
{    /**
     * Create or update a trip with comprehensive security and validation
     * 
     * @return void
     */    public function store()
    {
        try {
            // Debug logging
            error_log("TripController::store() called");
            
            // Session is already configured and started in App.php
            // Just ensure we have a session (should already be active)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Check authentication
            if (!isset($_SESSION['user_id'])) {
                error_log("No user_id in session");
                $this->jsonError('Unauthorized', 401);
                return;
            }
            
            error_log("User authenticated: " . $_SESSION['user_id']);
            
            // Rate limiting for API calls
            if (!$this->checkRateLimit('api_trip_store', 10, 60)) { // 10 per minute
                error_log("Rate limit exceeded");
                $this->jsonError('Too many requests. Please slow down.', 429);
                return;
            }
            
            // Get and validate JSON input
            $rawInput = file_get_contents('php://input');
            error_log("Raw input: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            
            if (!$input || json_last_error() !== JSON_ERROR_NONE) {
                error_log("Invalid JSON: " . json_last_error_msg());
                $this->jsonError('Invalid JSON data', 400);
                return;
            }
            
            error_log("Parsed input: " . print_r($input, true));
            
            // Sanitize and validate input
            $destinationId = $this->validateNumeric($input['destination_id'] ?? null, 1);
            $status = $this->sanitizeInput($input['status'] ?? 'planned');
            $type = $this->sanitizeInput($input['type'] ?? 'adventure');
            
            if (!$destinationId) {
                $this->jsonError('Valid destination ID is required', 400);
                return;
            }
            
            // Validate status and type values
            if (!in_array($status, ['planned', 'visited'])) {
                $this->jsonError('Invalid status. Must be planned or visited', 400);
                return;
            }
            
            if (!in_array($type, ['adventure', 'business', 'leisure', 'cultural', 'nature'])) {
                $this->jsonError('Invalid trip type', 400);
                return;
            }
            
            $tripModel = $this->model('Trip');
            $destinationModel = $this->model('Destination');
            
            // Verify destination exists and user has access
            $destination = $destinationModel->find($destinationId);
            if (!$destination) {
                $this->jsonError('Destination not found', 404);
                return;
            }
            
            // Check if user has permission to access this destination
            if ($destination['privacy'] === 'private' && $destination['user_id'] != $_SESSION['user_id']) {
                $this->jsonError('Access denied to private destination', 403);
                return;
            }
            
            // Check if trip already exists for this user and destination
            $existingTrip = $tripModel->findUserDestinationTrip($_SESSION['user_id'], $destinationId);
            
            if ($existingTrip) {
                // Update existing trip
                $result = $tripModel->update($existingTrip['id'], [
                    'status' => $status,
                    'type' => $type
                ]);
                
                if ($result) {                    // Log the update
                    $logModel = $this->model('Log');
                    $destinationName = $destination['name'] ?? 'unknown';
                    $logModel::write('INFO', "Trip updated via API for {$destinationName}", [
                        'user_id' => $_SESSION['user_id'],
                        'trip_id' => $existingTrip['id'],
                        'destination_id' => $destinationId,
                        'status' => $status,
                        'type' => $type
                    ], 'API');
                    
                    $this->json([
                        'success' => true,
                        'message' => 'Trip updated successfully',
                        'trip' => array_merge($existingTrip, ['status' => $status, 'type' => $type])
                    ]);
                } else {
                    $this->jsonError('Failed to update trip', 500);
                }
            } else {
                // Create new trip
                $tripId = $tripModel->create([
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'status' => $status,
                    'type' => $type
                ]);
                
                if ($tripId) {
                    // Check for badges
                    $tripModel->checkBadges($_SESSION['user_id']);
                      // Log the creation
                    $logModel = $this->model('Log');
                    $destinationName = $destination['name'] ?? 'unknown';
                    $logModel::write('INFO', "Trip created via API for {$destinationName}", [
                        'user_id' => $_SESSION['user_id'],
                        'trip_id' => $tripId,
                        'destination_id' => $destinationId,
                        'status' => $status,
                        'type' => $type
                    ], 'API');
                    
                    $this->json([
                        'success' => true,
                        'message' => 'Trip created successfully',
                        'trip' => [
                            'id' => $tripId,
                            'destination_id' => $destinationId,
                            'status' => $status,
                            'type' => $type
                        ]
                    ]);
                } else {
                    $this->jsonError('Failed to create trip', 500);
                }
            }
            
        } catch (\Exception $e) {
            // Log the error
            $logModel = $this->model('Log');
            $logModel::write('ERROR', 'API Trip store error: ' . $e->getMessage(), [
                'user_id' => $_SESSION['user_id'] ?? null,
                'destination_id' => $destinationId ?? null,
                'trace' => $e->getTraceAsString()
            ], 'API');
            
            $this->jsonError('An error occurred while processing your request', 500);
        }
    }
      /**
     * Delete a trip with security validation
     * 
     * @return void
     */
    public function delete()
    {
        try {
            // Session is already configured and started in App.php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Check authentication
            if (!isset($_SESSION['user_id'])) {
                $this->jsonError('Unauthorized', 401);
                return;
            }
            
            // Rate limiting
            if (!$this->checkRateLimit('api_trip_delete', 20, 60)) { // 20 per minute
                $this->jsonError('Too many requests. Please slow down.', 429);
                return;
            }
            
            // Get and validate JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonError('Invalid JSON data', 400);
                return;
            }
            
            $destinationId = $this->validateNumeric($input['destination_id'] ?? null, 1);
            
            if (!$destinationId) {
                $this->jsonError('Valid destination ID is required', 400);
                return;
            }
            
            $tripModel = $this->model('Trip');
            
            // Find the trip
            $trip = $tripModel->findUserDestinationTrip($_SESSION['user_id'], $destinationId);
            
            if (!$trip) {
                $this->jsonError('Trip not found', 404);
                return;
            }
            
            // Delete the trip
            $result = $tripModel->delete($trip['id']);
            
            if ($result) {
                // Log the deletion
                $logModel = $this->model('Log');
                $logModel::write('INFO', "Trip deleted via API", [
                    'user_id' => $_SESSION['user_id'],
                    'trip_id' => $trip['id'],
                    'destination_id' => $destinationId
                ], 'API');
                
                $this->json([
                    'success' => true,
                    'message' => 'Trip deleted successfully'
                ]);
            } else {
                $this->jsonError('Failed to delete trip', 500);
            }
            
        } catch (\Exception $e) {
            // Log the error
            $logModel = $this->model('Log');
            $logModel::write('ERROR', 'API Trip delete error: ' . $e->getMessage(), [
                'user_id' => $_SESSION['user_id'] ?? null,
                'destination_id' => $destinationId ?? null,
                'trace' => $e->getTraceAsString()
            ], 'API');
            
            $this->jsonError('An error occurred while processing your request', 500);
        }
    }
      /**
     * Get user trips with filtering and pagination
     * 
     * @return void
     */
    public function index()
    {
        try {
            // Session is already configured and started in App.php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Check authentication
            if (!isset($_SESSION['user_id'])) {
                $this->jsonError('Unauthorized', 401);
                return;
            }
            
            // Rate limiting
            if (!$this->checkRateLimit('api_trip_index', 30, 60)) { // 30 per minute
                $this->jsonError('Too many requests. Please slow down.', 429);
                return;
            }
            
            // Get query parameters
            $status = $this->sanitizeInput($_GET['status'] ?? null);
            $type = $this->sanitizeInput($_GET['type'] ?? null);
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = min(50, max(1, intval($_GET['per_page'] ?? 10))); // Limit to 50 per page
            
            // Validate filters
            if ($status && !in_array($status, ['planned', 'visited'])) {
                $this->jsonError('Invalid status filter', 400);
                return;
            }
            
            if ($type && !in_array($type, ['adventure', 'business', 'leisure', 'cultural', 'nature'])) {
                $this->jsonError('Invalid type filter', 400);
                return;
            }
            
            $tripModel = $this->model('Trip');
            
            // Get trips with filters
            $trips = $tripModel->getUserTrips($_SESSION['user_id'], [
                'status' => $status,
                'type' => $type,
                'page' => $page,
                'per_page' => $perPage
            ]);
            
            // Get total count
            $totalTrips = $tripModel->getUserTripsCount($_SESSION['user_id'], [
                'status' => $status,
                'type' => $type
            ]);
            
            $this->json([
                'success' => true,
                'trips' => $trips,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalTrips,
                    'total_pages' => ceil($totalTrips / $perPage)
                ]
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            $logModel = $this->model('Log');
            $logModel::write('ERROR', 'API Trip index error: ' . $e->getMessage(), [
                'user_id' => $_SESSION['user_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ], 'API');
            
            $this->jsonError('An error occurred while processing your request', 500);
        }
    }
    
    /**
     * Helper method to send JSON error response
     * 
     * @param string $message
     * @param int $code
     * @return void
     */
    protected function jsonError($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
    }
}
