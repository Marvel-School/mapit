<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

class DestinationController extends Controller
{
    /**
     * Constructor - Require authentication for all API endpoints
     */
    public function __construct()
    {
        try {
            // Set secure JSON response headers
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            
            // Initialize secure session
            $this->initializeSecureSession();
            
            // Check authentication for API endpoints
            if (!$this->isLoggedIn()) {
                $this->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error_code' => 'AUTH_REQUIRED'
                ], 401);
                exit();
            }
            
            // Check rate limiting
            if (!$this->checkRateLimit('api_destination', 60, 300)) { // 60 requests per 5 minutes
                $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                exit();
            }
            
        } catch (\Exception $e) {
            error_log("API Destination Controller initialization error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INITIALIZATION_ERROR'
            ], 500);
            exit();
        }
    }
      /**
     * Get destinations for the authenticated user
     * 
     * @return void
     */
    public function index()
    {
        try {
            // Additional rate limiting for data retrieval
            if (!$this->checkRateLimit('api_destination_index', 30, 300)) { // 30 requests per 5 minutes
                $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded for data retrieval',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }
            
            $userId = $this->validateNumeric($_SESSION['user_id']);
            if ($userId === false) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid user session',
                    'error_code' => 'INVALID_SESSION'
                ], 400);
                return;
            }
            
            $destinationModel = $this->model('Destination');
            $destinations = $destinationModel->getByUser($userId);
            
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
                    'privacy' => $destination['privacy'],
                    'visited' => (int)$destination['visited'],
                    'created_at' => $destination['created_at']
                ];
            }, $destinations);
            
            // Log successful API access
            $logModel = $this->model('Log');
            $logModel::write('INFO', "API destinations retrieved", [
                'user_id' => $userId,
                'count' => count($sanitizedDestinations)
            ], 'API');
            
            $this->json([
                'success' => true,
                'data' => $sanitizedDestinations,
                'count' => count($sanitizedDestinations)
            ]);
            
        } catch (\Exception $e) {
            error_log("API destination index error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to retrieve destinations',
                'error_code' => 'RETRIEVAL_ERROR'
            ], 500);
        }
    }
      /**
     * Get a single destination
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
                $this->json([
                    'success' => false,
                    'message' => 'Invalid destination ID',
                    'error_code' => 'INVALID_ID'
                ], 400);
                return;
            }
            
            $destinationModel = $this->model('Destination');
            $destination = $destinationModel->find($destinationId);
            
            if (!$destination) {
                $this->json([
                    'success' => false,
                    'message' => 'Destination not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
                return;
            }
            
            // Check if user has permission to view
            if ($destination['privacy'] === 'private' && $destination['user_id'] != $_SESSION['user_id']) {
                // Log unauthorized access attempt
                $logModel = $this->model('Log');
                $logModel::write('WARNING', "Unauthorized API access attempt to private destination", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ], 'Security');
                
                $this->json([
                    'success' => false,
                    'message' => 'Access denied',
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
                'privacy' => $destination['privacy'],
                'visited' => (int)$destination['visited'],
                'created_at' => $destination['created_at']
            ];
            
            $this->json([
                'success' => true,
                'data' => $sanitizedDestination
            ]);
            
        } catch (\Exception $e) {
            error_log("API destination show error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Failed to retrieve destination',
                'error_code' => 'RETRIEVAL_ERROR'
            ], 500);
        }
    }
      /**
     * Create a new destination via API (for map clicks)
     * 
     * @return void
     */
    public function store()
    {
        try {
            // Enhanced rate limiting for creation operations
            if (!$this->checkRateLimit('api_destination_create', 10, 300)) { // 10 creates per 5 minutes
                $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded for destination creation',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }
            
            // Validate JSON input
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                $this->json([
                    'success' => false,
                    'message' => 'No input data provided',
                    'error_code' => 'NO_INPUT'
                ], 400);
                return;
            }
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg(),
                    'error_code' => 'INVALID_JSON'
                ], 400);
                return;
            }
            
            // Sanitize and validate input data
            $name = $this->sanitizeInput($input['name'] ?? '');
            $latitude = $input['latitude'] ?? '';
            $longitude = $input['longitude'] ?? '';
            $description = $this->sanitizeInput($input['description'] ?? '');
            $city = $this->sanitizeInput($input['city'] ?? '');
            $country = $this->sanitizeInput($input['country'] ?? '');
            $privacy = $input['privacy'] ?? 'public';
            $visited = isset($input['visited']) ? (int)$input['visited'] : 0;
            $visitDate = $this->sanitizeInput($input['visit_date'] ?? '');
            
            // Enhanced validation
            $errors = [];
            
            if (empty($name) || strlen($name) > 100) {
                $errors['name'] = 'Name is required and must be less than 100 characters';
            }
            
            $latitude = $this->validateNumeric($latitude, -90, 90);
            if ($latitude === false) {
                $errors['latitude'] = 'Valid latitude (-90 to 90) is required';
            }
            
            $longitude = $this->validateNumeric($longitude, -180, 180);
            if ($longitude === false) {
                $errors['longitude'] = 'Valid longitude (-180 to 180) is required';
            }
            
            if (strlen($description) > 500) {
                $errors['description'] = 'Description must be less than 500 characters';
            }
            
            if (strlen($city) > 100) {
                $errors['city'] = 'City must be less than 100 characters';
            }
            
            if (strlen($country) > 2) {
                $errors['country'] = 'Country code must be 2 characters';
            }
            
            if (!in_array($privacy, ['public', 'private'])) {
                $errors['privacy'] = 'Privacy must be public or private';
            }
            
            if ($visited !== 0 && $visited !== 1) {
                $errors['visited'] = 'Visited status must be 0 or 1';
            }
            
            if (!empty($visitDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
                $errors['visit_date'] = 'Visit date must be in YYYY-MM-DD format';
            }
            
            if (!empty($errors)) {
                $this->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
                return;
            }
            
            // Create destination
            $destinationModel = $this->model('Destination');
            
            $destinationData = [
                'name' => $name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => $description,
                'city' => $city,
                'country' => $country,
                'privacy' => $privacy,
                'visited' => $visited,
                'user_id' => $_SESSION['user_id'],
                'approval_status' => ($privacy === 'public') ? 'pending' : 'approved'
            ];
            
            if ($visited && $visitDate) {
                $destinationData['visit_date'] = $visitDate;
            }
            
            $destinationId = $destinationModel->create($destinationData);
            
            if (!$destinationId) {
                // Log creation failure
                $logModel = $this->model('Log');
                $logModel::write('ERROR', "API destination creation failed", [
                    'user_id' => $_SESSION['user_id'],
                    'data' => $destinationData
                ], 'API');
                
                $this->json([
                    'success' => false,
                    'message' => 'Failed to create destination',
                    'error_code' => 'CREATION_ERROR'
                ], 500);
                return;
            }
            
            // Get the created destination
            $destination = $destinationModel->find($destinationId);
            
            // Log successful creation
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Destination created via API: {$name}", [
                'user_id' => $_SESSION['user_id'],
                'destination_id' => $destinationId,
                'privacy' => $privacy
            ], 'API');
            
            $this->json([
                'success' => true,
                'message' => 'Destination created successfully' . 
                    (($privacy === 'public') ? '. It will be visible after approval.' : '.'),
                'data' => [
                    'id' => (int)$destination['id'],
                    'name' => $this->sanitizeInput($destination['name']),
                    'latitude' => (float)$destination['latitude'],
                    'longitude' => (float)$destination['longitude'],
                    'privacy' => $destination['privacy'],
                    'visited' => (int)$destination['visited']
                ]
            ], 201);
            
        } catch (\Exception $e) {
            error_log("API destination store error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Internal server error during destination creation',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
      /**
     * Update destination
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        try {
            // Enhanced rate limiting for update operations
            if (!$this->checkRateLimit('api_destination_update', 20, 300)) { // 20 updates per 5 minutes
                $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded for destination updates',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }
            
            // Validate destination ID
            $destinationId = $this->validateNumeric($id, 1);
            if ($destinationId === false) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid destination ID',
                    'error_code' => 'INVALID_ID'
                ], 400);
                return;
            }
            
            $destinationModel = $this->model('Destination');
            $destination = $destinationModel->find($destinationId);
            
            if (!$destination) {
                $this->json([
                    'success' => false,
                    'message' => 'Destination not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
                return;
            }
            
            // Check if user has permission to edit
            if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
                // Log unauthorized access attempt
                $logModel = $this->model('Log');
                $logModel::write('WARNING', "Unauthorized API update attempt", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ], 'Security');
                
                $this->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'error_code' => 'ACCESS_DENIED'
                ], 403);
                return;
            }
            
            // Validate JSON input
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                $this->json([
                    'success' => false,
                    'message' => 'No update data provided',
                    'error_code' => 'NO_INPUT'
                ], 400);
                return;
            }
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg(),
                    'error_code' => 'INVALID_JSON'
                ], 400);
                return;
            }
            
            // Prepare update data with validation
            $updateData = [];
            $errors = [];
            
            // Update visited status (common use case for API)
            if (isset($input['visited'])) {
                $visited = (int)$input['visited'];
                if ($visited !== 0 && $visited !== 1) {
                    $errors['visited'] = 'Visited status must be 0 or 1';
                } else {
                    $updateData['visited'] = $visited;
                    $updateData['visit_date'] = $visited ? date('Y-m-d') : null;
                }
            }
            
            // Allow other field updates if provided
            if (isset($input['name'])) {
                $name = $this->sanitizeInput($input['name']);
                if (empty($name) || strlen($name) > 100) {
                    $errors['name'] = 'Name is required and must be less than 100 characters';
                } else {
                    $updateData['name'] = $name;
                }
            }
            
            if (isset($input['description'])) {
                $description = $this->sanitizeInput($input['description']);
                if (strlen($description) > 500) {
                    $errors['description'] = 'Description must be less than 500 characters';
                } else {
                    $updateData['description'] = $description;
                }
            }
            
            if (isset($input['privacy'])) {
                $privacy = $input['privacy'];
                if (!in_array($privacy, ['public', 'private'])) {
                    $errors['privacy'] = 'Privacy must be public or private';
                } else {
                    $updateData['privacy'] = $privacy;
                    // Reset approval status if changing to public
                    if ($privacy === 'public' && $destination['privacy'] !== 'public') {
                        $updateData['approval_status'] = 'pending';
                    }
                }
            }
            
            if (!empty($errors)) {
                $this->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
                return;
            }
            
            if (empty($updateData)) {
                $this->json([
                    'success' => false,
                    'message' => 'No valid update data provided',
                    'error_code' => 'NO_UPDATE_DATA'
                ], 400);
                return;
            }
            
            $updated = $destinationModel->update($destinationId, $updateData);
            
            if ($updated) {
                // Log successful update
                $logModel = $this->model('Log');
                $logModel::write('INFO', "Destination updated via API", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'updated_fields' => array_keys($updateData)
                ], 'API');
                
                $this->json([
                    'success' => true,
                    'message' => 'Destination updated successfully',
                    'updated_fields' => array_keys($updateData)
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update destination',
                    'error_code' => 'UPDATE_ERROR'
                ], 500);
            }
            
        } catch (\Exception $e) {
            error_log("API destination update error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Internal server error during destination update',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
      /**
     * Delete destination
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        try {
            // Enhanced rate limiting for delete operations
            if (!$this->checkRateLimit('api_destination_delete', 5, 300)) { // 5 deletes per 5 minutes
                $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded for destination deletion',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }
            
            // Validate destination ID
            $destinationId = $this->validateNumeric($id, 1);
            if ($destinationId === false) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid destination ID',
                    'error_code' => 'INVALID_ID'
                ], 400);
                return;
            }
            
            $destinationModel = $this->model('Destination');
            $destination = $destinationModel->find($destinationId);
            
            if (!$destination) {
                $this->json([
                    'success' => false,
                    'message' => 'Destination not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
                return;
            }
            
            // Check if user has permission to delete
            if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
                // Log unauthorized access attempt
                $logModel = $this->model('Log');
                $logModel::write('WARNING', "Unauthorized API deletion attempt", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ], 'Security');
                
                $this->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'error_code' => 'ACCESS_DENIED'
                ], 403);
                return;
            }
            
            // Prevent deletion of featured destinations by non-admin users
            if ($destination['featured'] == 1 && !$this->hasRole('admin')) {
                $this->json([
                    'success' => false,
                    'message' => 'Featured destinations cannot be deleted. Please contact an administrator if you need assistance.',
                    'error_code' => 'FEATURED_PROTECTED'
                ], 403);
                return;
            }
            
            // Store destination data for logging before deletion
            $destinationName = $this->sanitizeInput($destination['name']);
            
            $deleted = $destinationModel->delete($destinationId);
            
            if ($deleted) {
                // Log successful deletion
                $logModel = $this->model('Log');
                $logModel::write('INFO', "Destination deleted via API: {$destinationName}", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'destination_name' => $destinationName
                ], 'API');
                
                $this->json([
                    'success' => true,
                    'message' => 'Destination deleted successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete destination',
                    'error_code' => 'DELETION_ERROR'
                ], 500);
            }
            
        } catch (\Exception $e) {
            error_log("API destination delete error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Internal server error during destination deletion',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
      /**
     * Quick destination creation from map click with location lookup
     * 
     * @return void
     */
    public function quickCreate()
    {
        try {
            // Enhanced rate limiting for quick creation
            if (!$this->checkRateLimit('api_destination_quick_create', 5, 300)) { // 5 quick creates per 5 minutes
                $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded for quick destination creation',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
                return;
            }
            
            // Validate JSON input
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                $this->json([
                    'success' => false,
                    'message' => 'No input data provided',
                    'error_code' => 'NO_INPUT'
                ], 400);
                return;
            }
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg(),
                    'error_code' => 'INVALID_JSON'
                ], 400);
                return;
            }
            
            // Sanitize and validate input data
            $latitude = $input['latitude'] ?? '';
            $longitude = $input['longitude'] ?? '';
            $name = $this->sanitizeInput(trim($input['name'] ?? ''));
            $city = $this->sanitizeInput(trim($input['city'] ?? ''));
            $country = $this->sanitizeInput(trim($input['country'] ?? ''));
            $description = $this->sanitizeInput(trim($input['description'] ?? ''));
            $visited = isset($input['visited']) ? (int)$input['visited'] : 0;
            $privacy = $input['privacy'] ?? 'private';
            
            // Enhanced validation
            $errors = [];
            
            $latitude = $this->validateNumeric($latitude, -90, 90);
            if ($latitude === false) {
                $errors['latitude'] = 'Valid latitude (-90 to 90) is required';
            }
            
            $longitude = $this->validateNumeric($longitude, -180, 180);
            if ($longitude === false) {
                $errors['longitude'] = 'Valid longitude (-180 to 180) is required';
            }
            
            if (empty($name)) {
                $errors['name'] = 'Destination name is required';
            } elseif (strlen($name) > 100) {
                $errors['name'] = 'Destination name must be less than 100 characters';
            }
            
            if (strlen($city) > 100) {
                $errors['city'] = 'City must be less than 100 characters';
            }
            
            if (strlen($country) > 2) {
                $errors['country'] = 'Country code must be 2 characters';
            }
            
            if (strlen($description) > 500) {
                $errors['description'] = 'Description must be less than 500 characters';
            }
            
            if ($visited !== 0 && $visited !== 1) {
                $errors['visited'] = 'Visited status must be 0 or 1';
            }
            
            if (!in_array($privacy, ['public', 'private'])) {
                $errors['privacy'] = 'Privacy must be public or private';
            }
            
            if (!empty($errors)) {
                $this->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
                return;
            }
            
            // Create destination with user-provided data
            $destinationModel = $this->model('Destination');
            
            $destinationData = [
                'name' => $name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => !empty($description) ? $description : null,
                'city' => !empty($city) ? $city : null,
                'country' => !empty($country) ? $country : null,
                'privacy' => $privacy,
                'visited' => $visited,
                'user_id' => $_SESSION['user_id'],
                'approval_status' => $privacy === 'private' ? 'approved' : 'pending'
            ];
            
            $destinationId = $destinationModel->create($destinationData);
            
            if (!$destinationId) {
                // Log creation failure
                $logModel = $this->model('Log');
                $logModel::write('ERROR', "Quick destination creation failed", [
                    'user_id' => $_SESSION['user_id'],
                    'data' => $destinationData
                ], 'API');
                
                $this->json([
                    'success' => false,
                    'message' => 'Failed to create destination',
                    'error_code' => 'CREATION_ERROR'
                ], 500);
                return;
            }
            
            // Get the created destination
            $destination = $destinationModel->find($destinationId);
            
            // Create associated trip record with enhanced error handling
            $tripModel = $this->model('Trip');
            $tripData = [
                'user_id' => $_SESSION['user_id'],
                'destination_id' => $destinationId,
                'status' => $visited == 1 ? 'visited' : 'planned',
                'type' => 'adventure'
            ];
            
            $tripId = $tripModel->create($tripData);
            
            if (!$tripId) {
                // Log the error but don't fail the destination creation
                $logModel = $this->model('Log');
                $logModel::write('WARNING', "Failed to create trip for quick destination", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'trip_status' => $tripData['status']
                ], 'API');
            }
            
            // Log successful creation
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Quick destination created via API", [
                'user_id' => $_SESSION['user_id'],
                'destination_id' => $destinationId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'visited' => $visited,
                'privacy' => $privacy
            ], 'API');
            
            $this->json([
                'success' => true,
                'message' => 'Location added! You can edit the details by clicking on it.',
                'data' => [
                    'id' => (int)$destination['id'],
                    'name' => $this->sanitizeInput($destination['name']),
                    'latitude' => (float)$destination['latitude'],
                    'longitude' => (float)$destination['longitude'],
                    'privacy' => $destination['privacy'],
                    'visited' => (int)$destination['visited']
                ]
            ], 201);
            
        } catch (\Exception $e) {
            error_log("API quick destination create error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Internal server error during quick destination creation',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
