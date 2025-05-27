<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use Exception;

class TripController extends Controller
{
    /**
     * Create or update a trip
     * 
     * @return void
     */
    public function store()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            return;
        }
        
        $destinationId = $input['destination_id'] ?? null;
        $status = $input['status'] ?? 'planned';
        $type = $input['type'] ?? 'adventure';
        
        if (!$destinationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Destination ID is required']);
            return;
        }
        
        try {
            $tripModel = $this->model('Trip');
            
            // Check if trip already exists for this user and destination
            $existingTrip = $tripModel->findUserDestinationTrip($_SESSION['user_id'], $destinationId);
            
            if ($existingTrip) {
                // Update existing trip
                $result = $tripModel->update($existingTrip['id'], [
                    'status' => $status,
                    'type' => $type,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Create new trip
                $result = $tripModel->create([
                    'user_id' => $_SESSION['user_id'],
                    'destination_id' => $destinationId,
                    'status' => $status,
                    'type' => $type
                ]);
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Trip updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update trip']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update trip status
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            return;
        }
        
        try {
            $tripModel = $this->model('Trip');
            
            // Verify trip belongs to current user
            $trip = $tripModel->find($id);
            if (!$trip || $trip['user_id'] != $_SESSION['user_id']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Trip not found']);
                return;
            }
            
            $updateData = [];
            if (isset($input['status'])) {
                $updateData['status'] = $input['status'];
            }
            if (isset($input['type'])) {
                $updateData['type'] = $input['type'];
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            $result = $tripModel->update($id, $updateData);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Trip updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update trip']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete a trip
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $tripModel = $this->model('Trip');
            
            // Verify trip belongs to current user
            $trip = $tripModel->find($id);
            if (!$trip || $trip['user_id'] != $_SESSION['user_id']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Trip not found']);
                return;
            }
            
            $result = $tripModel->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Trip deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete trip']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Start a planned trip
     * 
     * @param int $id Trip ID
     * @return void
     */
    public function start($id)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $tripModel = $this->model('Trip');
            $trip = $tripModel->findById($id);
            
            if (!$trip || $trip['user_id'] != $_SESSION['user_id']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Trip not found']);
                return;
            }
            
            $result = $tripModel->update($id, ['status' => 'in_progress']);
            
            if ($result) {
                // Redirect back to trips page
                header('Location: /trips?message=Trip started successfully');
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to start trip']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Complete an in-progress trip
     * 
     * @param int $id Trip ID
     * @return void
     */
    public function complete($id)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $tripModel = $this->model('Trip');
            $trip = $tripModel->findById($id);
            
            if (!$trip || $trip['user_id'] != $_SESSION['user_id']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Trip not found']);
                return;
            }
            
            $result = $tripModel->update($id, ['status' => 'completed']);
            
            if ($result) {
                // Redirect back to trips page
                header('Location: /trips?message=Trip completed successfully! Check your badges for new achievements.');
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to complete trip']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
