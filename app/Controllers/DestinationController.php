<?php

namespace App\Controllers;

use App\Core\Controller;
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
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        $destinationModel = $this->model('Destination');
        
        // Get user's destinations
        $userDestinations = $destinationModel->getByUser($userId);
        
        // Get public destinations
        $publicDestinations = $destinationModel->getPublic();
        
        // Get countries as array of objects for filtering
        $countries = $this->getCountries(true);
        
        $this->view('destinations/index', [
            'title' => 'Destinations',
            'userDestinations' => $userDestinations,
            'publicDestinations' => $publicDestinations,
            'countries' => $countries
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
    }
      /**
     * Store new destination
     * 
     * @return void
     */
    public function store()
    {
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
        
        // Create destination
        $destinationModel = $this->model('Destination');
        
        $destinationId = $destinationModel->create([
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'description' => $description,
            'privacy' => $privacy,
            'user_id' => $_SESSION['user_id'],
            'notes' => $notes,
            'approval_status' => ($privacy === 'public') ? 'pending' : 'approved'
        ]);
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
        $updated = $destinationModel->update($id, [
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'description' => $description,
            'privacy' => $privacy,
            'notes' => $notes,
            'approval_status' => $approvalStatus
        ]);
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
        
        // Delete destination
        $deleted = $destinationModel->delete($id);
        
        if (!$deleted) {
            $_SESSION['error'] = 'Failed to delete destination';
            $this->redirect('/destinations/' . $id);
            return;
        }
        
        // Log the deletion
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination deleted: {$destination['name']}", [
            'user_id' => $_SESSION['user_id'],
            'destination_id' => $id
        ], 'Destination');
        
        // Set success message
        $_SESSION['success'] = 'Destination deleted successfully';
        
        // Redirect to destinations
        $this->redirect('/destinations');
    }
}
