<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;

class TripController extends Controller
{
    /**
     * Constructor - Require authentication
     */
    public function __construct()
    {
        $this->requireLogin();
    }
    
    /**
     * Display all user trips
     * 
     * @return void
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Get filter parameters
        $status = $_GET['status'] ?? null;
        $type = $_GET['type'] ?? null;
        
        // Get trips
        $tripModel = $this->model('Trip');
        $trips = $tripModel->getUserTrips($userId, [
            'status' => $status,
            'type' => $type
        ]);
        
        // Get trip statistics
        $stats = $tripModel->getUserStats($userId);
        
        $this->view('trips/index', [
            'title' => 'My Trips',
            'trips' => $trips,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'type' => $type
            ]
        ]);
    }
    
    /**
     * Display create trip form
     * 
     * @return void
     */
    public function create()
    {
        $userId = $_SESSION['user_id'];
        
        // Get user destinations (those not already in trips)
        $destinationModel = $this->model('Destination');
        
        // Get all public approved destinations and user's private destinations
        $db = Database::getInstance();
        $db->query("
            SELECT d.*
            FROM destinations d
            WHERE (d.privacy = 'public' AND d.approval_status = 'approved')
            OR d.user_id = :user_id
            ORDER BY d.name
        ");
        $db->bind(':user_id', $userId);
        $destinations = $db->resultSet();
          $this->view('trips/create', [
            'title' => 'Create New Trip',
            'userDestinations' => $destinations
        ]);
    }
    
    /**
     * Store a new trip
     * 
     * @return void
     */
    public function store()
    {
        $userId = $_SESSION['user_id'];
        
        // Get form data
        $destinationId = $_POST['destination_id'] ?? null;
        $status = $_POST['status'] ?? 'planned';
        $type = $_POST['type'] ?? 'adventure';
        
        // Validate destination
        if (!$destinationId) {
            $_SESSION['error'] = 'Please select a destination';
            $this->redirect('/trips/create');
            return;
        }
        
        // Check if destination exists
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($destinationId);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/trips/create');
            return;
        }
        
        // Check if a trip for this destination already exists
        $tripModel = $this->model('Trip');
        $existingTrip = $tripModel->findUserDestinationTrip($userId, $destinationId);
        
        if ($existingTrip) {
            $_SESSION['error'] = 'You already have a trip for this destination';
            $this->redirect('/trips/create');
            return;
        }
        
        // Create trip
        $tripId = $tripModel->create([
            'user_id' => $userId,
            'destination_id' => $destinationId,
            'status' => $status,
            'type' => $type
        ]);
        
        if (!$tripId) {
            $_SESSION['error'] = 'Failed to create trip';
            $this->redirect('/trips/create');
            return;
        }
        
        // Check for badges
        $tripModel->checkBadges($userId);
        
        // Log the creation
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Trip created for {$destination['name']}", [
            'user_id' => $userId,
            'trip_id' => $tripId,
            'destination_id' => $destinationId
        ], 'Trip');
        
        // Set success message
        $_SESSION['success'] = 'Trip created successfully';
        
        // Redirect to trips
        $this->redirect('/trips');
    }
    
    /**
     * Display a trip
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $userId = $_SESSION['user_id'];
        
        // Get trip
        $tripModel = $this->model('Trip');
        
        $db = Database::getInstance();
        $db->query("
            SELECT t.*, d.name as destination_name, d.latitude, d.longitude, 
                d.description as destination_description
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.id = :id AND t.user_id = :user_id
        ");
        $db->bind(':id', $id);
        $db->bind(':user_id', $userId);
        $trip = $db->single();
        
        if (!$trip) {
            $_SESSION['error'] = 'Trip not found or you do not have permission to view it';
            $this->redirect('/trips');
            return;
        }
        
        $this->view('trips/show', [
            'title' => 'Trip to ' . $trip['destination_name'],
            'trip' => $trip
        ]);
    }
    
    /**
     * Display edit trip form
     * 
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        $userId = $_SESSION['user_id'];
        
        // Get trip
        $tripModel = $this->model('Trip');
        
        $db = Database::getInstance();
        $db->query("
            SELECT t.*, d.name as destination_name
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.id = :id AND t.user_id = :user_id
        ");
        $db->bind(':id', $id);
        $db->bind(':user_id', $userId);
        $trip = $db->single();
          if (!$trip) {
            $_SESSION['error'] = 'Trip not found or you do not have permission to edit it';
            $this->redirect('/trips');
            return;
        }

        // Get all public approved destinations and user's private destinations for editing
        $db->query("
            SELECT d.*
            FROM destinations d
            WHERE (d.privacy = 'public' AND d.approval_status = 'approved')
            OR d.user_id = :user_id
            ORDER BY d.name
        ");
        $db->bind(':user_id', $userId);
        $destinations = $db->resultSet();

        $this->view('trips/edit', [
            'title' => 'Edit Trip to ' . $trip['destination_name'],
            'trip' => $trip,
            'userDestinations' => $destinations
        ]);
    }
    
    /**
     * Update a trip
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        $userId = $_SESSION['user_id'];
        
        // Get trip
        $tripModel = $this->model('Trip');
        
        $db = Database::getInstance();
        $db->query("
            SELECT t.*, d.name as destination_name
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.id = :id AND t.user_id = :user_id
        ");
        $db->bind(':id', $id);
        $db->bind(':user_id', $userId);
        $trip = $db->single();
        
        if (!$trip) {
            $_SESSION['error'] = 'Trip not found or you do not have permission to edit it';
            $this->redirect('/trips');
            return;
        }
        
        // Get form data
        $status = $_POST['status'] ?? $trip['status'];
        $type = $_POST['type'] ?? $trip['type'];
        
        // Update trip
        $updated = $tripModel->update($id, [
            'status' => $status,
            'type' => $type
        ]);
        
        if (!$updated) {
            $_SESSION['error'] = 'Failed to update trip';
            $this->redirect('/trips/' . $id . '/edit');
            return;
        }
        
        // Check for badges
        $tripModel->checkBadges($userId);
        
        // Log the update
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Trip updated for {$trip['destination_name']}", [
            'user_id' => $userId,
            'trip_id' => $id
        ], 'Trip');
        
        // Set success message
        $_SESSION['success'] = 'Trip updated successfully';
        
        // Redirect to trip
        $this->redirect('/trips/' . $id);
    }
    
    /**
     * Delete a trip
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $userId = $_SESSION['user_id'];
        
        // Get trip
        $tripModel = $this->model('Trip');
        
        $db = Database::getInstance();
        $db->query("
            SELECT t.*, d.name as destination_name
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.id = :id AND t.user_id = :user_id
        ");
        $db->bind(':id', $id);
        $db->bind(':user_id', $userId);
        $trip = $db->single();
        
        if (!$trip) {
            $_SESSION['error'] = 'Trip not found or you do not have permission to delete it';
            $this->redirect('/trips');
            return;
        }
        
        // Delete trip
        $deleted = $tripModel->delete($id);
        
        if (!$deleted) {
            $_SESSION['error'] = 'Failed to delete trip';
            $this->redirect('/trips/' . $id);
            return;
        }
        
        // Log the deletion
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Trip deleted for {$trip['destination_name']}", [
            'user_id' => $userId,
            'trip_id' => $id
        ], 'Trip');
        
        // Set success message
        $_SESSION['success'] = 'Trip deleted successfully';
        
        // Redirect to trips
        $this->redirect('/trips');
    }
    
    /**
     * Update trip status
     * 
     * @param int $id
     * @return void
     */
    public function updateStatus($id)
    {
        $userId = $_SESSION['user_id'];
        
        // Get trip
        $tripModel = $this->model('Trip');
        
        $db = Database::getInstance();
        $db->query("
            SELECT t.*, d.name as destination_name
            FROM trips t
            JOIN destinations d ON t.destination_id = d.id
            WHERE t.id = :id AND t.user_id = :user_id
        ");
        $db->bind(':id', $id);
        $db->bind(':user_id', $userId);
        $trip = $db->single();
        
        if (!$trip) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Trip not found'], 404);
            } else {
                $_SESSION['error'] = 'Trip not found or you do not have permission to update it';
                $this->redirect('/trips');
            }
            return;
        }
        
        // Get new status
        $status = $_POST['status'] ?? ($trip['status'] === 'planned' ? 'visited' : 'planned');
        
        // Update trip status
        $updated = $tripModel->updateStatus($id, $status);
        
        if (!$updated) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Failed to update trip status'], 500);
            } else {
                $_SESSION['error'] = 'Failed to update trip status';
                $this->redirect('/trips/' . $id);
            }
            return;
        }
        
        // Check for badges
        $tripModel->checkBadges($userId);
        
        // Log the status update
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Trip status updated for {$trip['destination_name']} to {$status}", [
            'user_id' => $userId,
            'trip_id' => $id,
            'status' => $status
        ], 'Trip');
        
        if (Request::isAjax()) {
            $this->json([
                'success' => true,
                'message' => 'Trip status updated successfully',
                'status' => $status
            ]);
        } else {
            // Set success message
            $_SESSION['success'] = 'Trip status updated successfully';
            
            // Redirect to trip
            $this->redirect('/trips/' . $id);
        }
    }
}
