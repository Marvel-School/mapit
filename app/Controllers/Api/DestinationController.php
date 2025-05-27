<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

class DestinationController extends Controller
{    /**
     * Constructor - Require authentication for all API endpoints
     */
    public function __construct()
    {
        // Set JSON response headers
        header('Content-Type: application/json');
        
        // Check authentication for API endpoints
        if (!$this->isLoggedIn()) {
            $this->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
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
        $userId = $_SESSION['user_id'];
        $destinationModel = $this->model('Destination');
        
        $destinations = $destinationModel->getByUser($userId);
        
        $this->json([
            'success' => true,
            'data' => $destinations
        ]);
    }
    
    /**
     * Get a single destination
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found'], 404);
            return;
        }
        
        // Check if user has permission to view
        if ($destination['privacy'] === 'private' && $destination['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $this->json([
            'success' => true,
            'data' => $destination
        ]);
    }
    
    /**
     * Create a new destination via API (for map clicks)
     * 
     * @return void
     */
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid JSON input'], 400);
            return;
        }
        
        // Get form data
        $name = $input['name'] ?? '';
        $latitude = $input['latitude'] ?? '';
        $longitude = $input['longitude'] ?? '';
        $description = $input['description'] ?? '';
        $city = $input['city'] ?? '';
        $country = $input['country'] ?? '';
        $privacy = $input['privacy'] ?? 'public';
        $visited = $input['visited'] ?? 0;
        $visitDate = $input['visit_date'] ?? null;
        
        // Validate form data
        $validator = new Validator($input);
        $validator->validate([
            'name' => 'required|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'max:500',
            'city' => 'max:100',
            'country' => 'max:2'
        ]);
        
        $errors = $validator->errors();
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
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
            $this->json([
                'success' => false,
                'message' => 'Failed to create destination'
            ], 500);
            return;
        }
        
        // Get the created destination
        $destination = $destinationModel->find($destinationId);
        
        // Log the creation
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination created via map click: {$name}", [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $destinationId
        ], 'Destination');
        
        $this->json([
            'success' => true,
            'message' => 'Destination created successfully' . 
                (($privacy === 'public') ? '. It will be visible after approval.' : '.'),
            'data' => $destination
        ], 201);
    }
    
    /**
     * Update destination
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found'], 404);
            return;
        }
        
        // Check if user has permission to edit
        if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid JSON input'], 400);
            return;
        }
        
        // Update visited status (common use case for API)
        if (isset($input['visited'])) {
            $updated = $destinationModel->update($id, [
                'visited' => $input['visited'],
                'visit_date' => $input['visited'] ? date('Y-m-d') : null
            ]);
            
            if ($updated) {
                $this->json(['success' => true, 'message' => 'Destination updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update destination'], 500);
            }
        } else {
            $this->json(['success' => false, 'message' => 'No update data provided'], 400);
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
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found'], 404);
            return;
        }
          // Check if user has permission to delete
        if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }

        // Prevent deletion of featured destinations by non-admin users
        if ($destination['featured'] == 1 && !$this->hasRole('admin')) {
            $this->json(['success' => false, 'message' => 'Featured destinations cannot be deleted. Please contact an administrator if you need assistance.'], 403);
            return;
        }

        $deleted = $destinationModel->delete($id);
        
        if ($deleted) {
            // Log the deletion
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Destination deleted via API: {$destination['name']}", [
                'user_id' => $_SESSION['user_id'],
                'destination_id' => $id
            ], 'Destination');
            
            $this->json(['success' => true, 'message' => 'Destination deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete destination'], 500);
        }
    }
    
    /**
     * Quick destination creation from map click with location lookup
     * 
     * @return void
     */
    public function quickCreate()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid JSON input'], 400);
            return;
        }
        
        $latitude = $input['latitude'] ?? '';
        $longitude = $input['longitude'] ?? '';
        
        // Basic validation
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            $this->json([
                'success' => false,
                'message' => 'Valid latitude and longitude are required'
            ], 422);
            return;
        }
        
        // Create a basic destination with coordinates
        // The user can edit details later
        $destinationModel = $this->model('Destination');
        
        $destinationData = [
            'name' => 'New Location',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'description' => 'Location added from map click',
            'privacy' => 'private', // Default to private for quick creates
            'visited' => 0,
            'user_id' => $_SESSION['user_id'],
            'approval_status' => 'approved' // Private destinations are auto-approved
        ];
        
        $destinationId = $destinationModel->create($destinationData);
        
        if (!$destinationId) {
            $this->json([
                'success' => false,
                'message' => 'Failed to create destination'
            ], 500);
            return;
        }
        
        // Get the created destination
        $destination = $destinationModel->find($destinationId);
        
        // Log the creation
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Quick destination created via map click", [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $destinationId,
            'latitude' => $latitude,
            'longitude' => $longitude
        ], 'Destination');
        
        $this->json([
            'success' => true,
            'message' => 'Location added! You can edit the details by clicking on it.',
            'data' => $destination
        ], 201);    }
}
