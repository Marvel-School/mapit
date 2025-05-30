<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\FileUpload;
use App\Core\Request;
use App\Core\Validator;

class DestinationController extends Controller
{
    /**
     * Constructor - Require authentication for all destination pages
     */
    public function __construct()
    {
        $this->requireLogin();
    }
      /**
     * Display all destinations
     * 
     * @return void
     */    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        $destinationModel = $this->model('Destination');
        
        // Get user's own destinations and public destinations they have trips for
        $destinations = $destinationModel->getUserDestinationsWithTripStatus($userId);
        
        // Add pagination variables (simple implementation for now)
        $totalDestinations = count($destinations);
        $perPage = 12; // destinations per page
        $currentPage = (int)($_GET['page'] ?? 1);
        $totalPages = ceil($totalDestinations / $perPage);
        
        // Apply pagination
        $offset = ($currentPage - 1) * $perPage;
        $paginatedDestinations = array_slice($destinations, $offset, $perPage);
        
        // Get countries as array of objects for filtering
        $countries = $this->getCountries(true);
        
        $this->view('destinations/index', [
            'title' => 'Destinations',
            'destinations' => $paginatedDestinations,
            'countries' => $countries,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalDestinations' => $totalDestinations
        ]);
    }
    
    /**
     * Display new destination form
     * 
     * @return void
     */
    public function create()
    {
        // Get countries as associative array for dropdown
        $countries = $this->getCountries();
        
        $this->view('destinations/create', [
            'title' => 'Add New Destination',
            'countries' => $countries
        ]);
    }      /**
     * Store new destination
     * 
     * @return void
     */
    public function store()
    {
        // Validate CSRF token
        $this->validateCSRF('/destinations/create');
        
        // Get form data
        $name = $_POST['name'] ?? '';
        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        $description = $_POST['description'] ?? '';
        $privacy = $_POST['privacy'] ?? 'private';
        $notes = $_POST['notes'] ?? '';
        
        // Validate form data
        $validator = new Validator($_POST);
        $validator->validate([
            'name' => 'required|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'required'
        ]);
        
        $errors = $validator->errors();
          if (!empty($errors)) {
            // Get countries for dropdown
            $countries = $this->getCountries();
            
            $this->view('destinations/create', [
                'title' => 'Add New Destination',
                'errors' => $errors,
                'name' => $name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => $description,
                'privacy' => $privacy,
                'notes' => $notes,
                'countries' => $countries
            ]);
            return;
        }
          // Handle image upload if provided
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $fileUpload = new FileUpload();
                $imagePath = $fileUpload->uploadImage($_FILES['image'], 'destinations');
                
                if (!$imagePath) {
                    $errors = array_merge($errors, $fileUpload->getErrors());
                }
            } catch (\Exception $e) {
                $errors['image'] = 'Image upload failed: ' . $e->getMessage();
                
                // Log the error
                $logModel = $this->model('Log');
                $logModel::write('ERROR', 'Destination image upload failed', [
                    'user_id' => $_SESSION['user_id'],
                    'error' => $e->getMessage(),
                    'file_info' => [
                        'name' => $_FILES['image']['name'],
                        'size' => $_FILES['image']['size'],
                        'type' => $_FILES['image']['type']
                    ]
                ], 'FileUpload');
            }
        }
        
        // If there were image upload errors, show them
        if (!empty($errors)) {
            $countries = $this->getCountries();
            
            $this->view('destinations/create', [
                'title' => 'Add New Destination',
                'errors' => $errors,
                'name' => $name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => $description,
                'privacy' => $privacy,
                'notes' => $notes,
                'countries' => $countries
            ]);
            return;
        }
        
        // Create destination
        $destinationModel = $this->model('Destination');
        
        $destinationData = [
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'description' => $description,
            'privacy' => $privacy,
            'user_id' => $_SESSION['user_id'],
            'notes' => $notes,
            'approval_status' => ($privacy === 'public') ? 'pending' : 'approved'
        ];
        
        // Add image path if uploaded
        if ($imagePath) {
            $destinationData['image'] = $imagePath;
        }
        
        $destinationId = $destinationModel->create($destinationData);
          if (!$destinationId) {
            // Get countries for dropdown
            $countries = $this->getCountries();
            
            $this->view('destinations/create', [
                'title' => 'Add New Destination',
                'errors' => ['create' => 'Failed to create destination'],
                'name' => $name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => $description,
                'privacy' => $privacy,
                'notes' => $notes,
                'countries' => $countries
            ]);
            return;
        }
        
        // Log the creation
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination created: {$name}", [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $destinationId
        ], 'Destination');
        
        // Set success message
        $_SESSION['success'] = 'Destination created successfully' . 
            (($privacy === 'public') ? '. It will be visible after approval.' : '.');
        
        // Redirect to destinations
        $this->redirect('/destinations');
    }
    
    /**
     * Display single destination
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/destinations');
            return;
        }
        
        // Check if user has permission to view
        if ($destination['privacy'] === 'private' && $destination['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to view this destination';
            $this->redirect('/destinations');
            return;
        }
        
        // Get creator
        $userModel = $this->model('User');
        $creator = $userModel->find($destination['user_id']);
          // Check if user has a trip for this destination
        $tripModel = $this->model('Trip');
        $trip = $tripModel->findUserDestinationTrip($_SESSION['user_id'], $id);
        
        // Get nearby destinations
        $nearbyDestinations = $destinationModel->getNearby(
            $destination['latitude'], 
            $destination['longitude'], 
            $id, 
            100, // 100km radius
            6    // limit to 6 destinations
        );
        
        $this->view('destinations/show', [
            'title' => $destination['name'],
            'destination' => $destination,
            'creator' => $creator,
            'trip' => $trip,
            'nearbyDestinations' => $nearbyDestinations
        ]);
    }
    
    /**
     * Display edit destination form
     * 
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/destinations');
            return;
        }
        
        // Check if user has permission to edit
        if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
            $_SESSION['error'] = 'You do not have permission to edit this destination';
            $this->redirect('/destinations');
            return;
        }
        
        // Get countries as associative array for dropdown
        $countries = $this->getCountries();
        
        $this->view('destinations/edit', [
            'title' => 'Edit ' . $destination['name'],
            'destination' => $destination,
            'countries' => $countries
        ]);
    }
      /**
     * Update destination
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/destinations');
            return;
        }
        
        // Check if user has permission to edit
        if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
            $_SESSION['error'] = 'You do not have permission to edit this destination';
            $this->redirect('/destinations');
            return;
        }
        
        // Validate CSRF token
        $this->validateCSRF('/destinations/' . $id . '/edit');
          // Get form data
        $name = $_POST['name'] ?? '';
        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        $description = $_POST['description'] ?? '';
        $privacy = $_POST['privacy'] ?? 'private';
        $notes = $_POST['notes'] ?? '';
        
        // Validate form data
        $validator = new Validator($_POST);
        $validator->validate([
            'name' => 'required|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'required'
        ]);
        
        $errors = $validator->errors();
          if (!empty($errors)) {
            // Get countries for dropdown
            $countries = $this->getCountries();
            
            $this->view('destinations/edit', [
                'title' => 'Edit ' . $destination['name'],
                'destination' => $destination,
                'errors' => $errors,
                'countries' => $countries
            ]);
            return;        }
        
        // Handle image deletion if requested
        $deleteImage = isset($_POST['delete_image']) && $_POST['delete_image'] == '1';
          // Handle image upload if provided
        $imagePath = isset($destination['image']) ? $destination['image'] : null; // Keep existing image by default
          if ($deleteImage) {
            // Delete current image if it exists
            if (isset($destination['image']) && !empty($destination['image']) && file_exists($destination['image'])) {
                $imageFile = $destination['image'];
                unlink($imageFile);
            }
            $imagePath = null; // Clear image path
        }
          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $fileUpload = new FileUpload();
                $newImagePath = $fileUpload->uploadImage($_FILES['image'], 'destinations');                  if ($newImagePath) {
                    // Delete old image if it exists and we're not just replacing due to delete checkbox
                    if (!$deleteImage && isset($destination['image']) && !empty($destination['image']) && file_exists($destination['image'])) {
                        $oldImageFile = $destination['image'];
                        unlink($oldImageFile);
                    }
                    $imagePath = $newImagePath;
                } else {
                    $errors = array_merge($errors, $fileUpload->getErrors());
                }
            } catch (\Exception $e) {
                $errors['image'] = 'Image upload failed: ' . $e->getMessage();
                
                // Log the error
                $logModel = $this->model('Log');
                $logModel::write('ERROR', 'Destination image upload failed', [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $id,
                    'error' => $e->getMessage(),
                    'file_info' => [
                        'name' => $_FILES['image']['name'],
                        'size' => $_FILES['image']['size'],
                        'type' => $_FILES['image']['type']
                    ]
                ], 'FileUpload');
            }
        }
        
        // If there were image upload errors, show them
        if (!empty($errors)) {
            $countries = $this->getCountries();
            
            $this->view('destinations/edit', [
                'title' => 'Edit ' . $destination['name'],
                'destination' => $destination,
                'errors' => $errors,
                'countries' => $countries
            ]);
            return;
        }
        
        // Check if privacy changed
        $privacyChanged = $privacy !== $destination['privacy'];
        
        // Determine if approval is needed
        $approvalStatus = $destination['approval_status'];
        if ($privacyChanged && $privacy === 'public') {
            $approvalStatus = 'pending';
        }
        
        // Update destination
        $updateData = [
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'description' => $description,
            'privacy' => $privacy,
            'notes' => $notes,
            'approval_status' => $approvalStatus,
            'image' => $imagePath
        ];
        
        $updated = $destinationModel->update($id, $updateData);
          if (!$updated) {
            // Get countries for dropdown
            $countries = $this->getCountries();
            
            $this->view('destinations/edit', [
                'title' => 'Edit ' . $destination['name'],
                'destination' => $destination,
                'errors' => ['update' => 'Failed to update destination'],
                'countries' => $countries
            ]);
            return;
        }
        
        // Log the update
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination updated: {$name}", [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $id
        ], 'Destination');
        
        // Set success message
        $_SESSION['success'] = 'Destination updated successfully' . 
            (($privacyChanged && $privacy === 'public') ? '. It will be visible after approval.' : '.');
        
        // Redirect to destination
        $this->redirect('/destinations/' . $id);
    }
      /**
     * Delete destination
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        // Validate CSRF token
        $this->validateCSRF('/destinations/' . $id);
        
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/destinations');
            return;
        }
          // Check if user has permission to delete
        if ($destination['user_id'] != $_SESSION['user_id'] && !$this->hasRole('admin')) {
            $_SESSION['error'] = 'You do not have permission to delete this destination';
            $this->redirect('/destinations');
            return;
        }

        // Prevent deletion of featured destinations by non-admin users
        if ($destination['featured'] == 1 && !$this->hasRole('admin')) {
            $_SESSION['error'] = 'Featured destinations cannot be deleted. Please contact an administrator if you need assistance.';
            $this->redirect('/destinations/' . $id);
            return;
        }        // Delete destination
        $deleted = $destinationModel->delete($id);
        
        if (!$deleted) {
            $_SESSION['error'] = 'Failed to delete destination';
            $this->redirect('/destinations/' . $id);
            return;
        }        // Delete associated image file if it exists (check if image column exists and has value)
        if (isset($destination['image']) && !empty($destination['image']) && file_exists($destination['image'])) {
            try {
                $imageFile = $destination['image'];
                unlink($imageFile);
                
                // Log successful image deletion
                $logModel = $this->model('Log');
                $logModel::write('INFO', "Destination image deleted: {$imageFile}", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $id
                ], 'FileUpload');
            } catch (\Exception $e) {
                // Log image deletion failure
                $logModel = $this->model('Log');
                $imageFile = $destination['image'] ?? 'unknown';
                $logModel::write('WARNING', "Failed to delete destination image: {$imageFile}", [
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $id,
                    'error' => $e->getMessage()
                ], 'FileUpload');
            }
        }
          // Log the deletion
        $logModel = $this->model('Log');
        $destinationName = $destination['name'] ?? 'unknown';
        $logModel::write('INFO', "Destination deleted: {$destinationName}", [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $id
        ], 'Destination');
        
        // Set success message
        $_SESSION['success'] = 'Destination deleted successfully';
        
        // Redirect to destinations
        $this->redirect('/destinations');
    }
}
