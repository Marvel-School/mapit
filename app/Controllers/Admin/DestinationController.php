<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class DestinationController extends Controller
{    /**
     * Constructor - Require admin or moderator role
     */
    public function __construct()
    {
        // Check if user is logged in and has admin role
        $this->requireLogin();
        $this->requireRole(['admin', 'moderator']);
    }
    
    /**
     * Display all destinations
     * 
     * @return void
     */
    public function index()
    {        // Get filter parameters
        $status = $_GET['status'] ?? null;
        $privacy = $_GET['privacy'] ?? null;
        $featured = $_GET['featured'] ?? null;
          // Build query - ensure all destination fields are selected including city
        $sql = "
            SELECT d.id, d.name, d.description, d.country, d.city, d.latitude, d.longitude, 
                   d.user_id, d.privacy, d.approval_status, d.featured, d.notes, 
                   d.created_at, d.updated_at, u.username as creator
            FROM destinations d
            LEFT JOIN users u ON d.user_id = u.id
        ";
        
        $params = [];
        $whereClause = [];
        
        if ($status) {
            $whereClause[] = "d.approval_status = :status";
            $params[':status'] = $status;
        }
          if ($privacy) {
            $whereClause[] = "d.privacy = :privacy";
            $params[':privacy'] = $privacy;
        }
        
        if ($featured !== null && $featured !== '') {
            $whereClause[] = "d.featured = :featured";
            $params[':featured'] = (int)$featured;
        }
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
          $sql .= " ORDER BY d.created_at DESC";
        
        // Execute query
        $db = Database::getInstance();
        $db->query($sql);
        
        foreach ($params as $key => $value) {
            $db->bind($key, $value);
        }
        
        $destinations = $db->resultSet();
          // Get counts for filters
        $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN privacy = 'public' THEN 1 ELSE 0 END) as public,
                SUM(CASE WHEN privacy = 'private' THEN 1 ELSE 0 END) as private,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured
            FROM destinations
        ");
        
        $counts = $db->single();
        
        $this->view('admin/destinations/index', [
            'title' => 'Manage Destinations',
            'destinations' => $destinations,
            'counts' => $counts,            'filters' => [
                'status' => $status,
                'privacy' => $privacy,
                'featured' => $featured
            ]
        ]);
    }
    
    /**
     * Display a single destination
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
            $this->redirect('/admin/destinations');
            return;
        }
        
        // Get creator
        $userModel = $this->model('User');
        $creator = null;
        if ($destination['user_id']) {
            $creator = $userModel->find($destination['user_id']);
        }
          // Get trips for this destination
        $db = Database::getInstance();
        $db->query("
            SELECT t.*, u.username
            FROM trips t
            JOIN users u ON t.user_id = u.id
            WHERE t.destination_id = :destination_id
        ");
        $db->bind(':destination_id', $id);
        $trips = $db->resultSet();
        
        $this->view('admin/destinations/show', [
            'title' => 'Destination Details',
            'destination' => $destination,
            'creator' => $creator,
            'trips' => $trips
        ]);
    }
    
    /**
     * Approve a destination
     * 
     * @param int $id
     * @return void
     */
    public function approve($id)
    {
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/admin/destinations');
            return;
        }
        
        // Check if already approved
        if ($destination['approval_status'] === 'approved') {
            $_SESSION['error'] = 'Destination is already approved';
            $this->redirect('/admin/destinations/' . $id);
            return;
        }
        
        // Approve destination
        $approved = $destinationModel->approve($id);
        
        if (!$approved) {
            $_SESSION['error'] = 'Failed to approve destination';
            $this->redirect('/admin/destinations/' . $id);
            return;
        }
        
        // Log the approval
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination approved: {$destination['name']}", [
            'admin_id' => $_SESSION['user_id'],
            'destination_id' => $id
        ], 'Admin');
        
        // Set success message
        $_SESSION['success'] = 'Destination approved successfully';
        
        // Redirect back to the destination
        $this->redirect('/admin/destinations/' . $id);
    }
    
    /**
     * Reject a destination
     * 
     * @param int $id
     * @return void
     */
    public function reject($id)
    {
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/admin/destinations');
            return;
        }
        
        // Check if already rejected
        if ($destination['approval_status'] === 'rejected') {
            $_SESSION['error'] = 'Destination is already rejected';
            $this->redirect('/admin/destinations/' . $id);
            return;
        }
        
        // Reject destination
        $rejected = $destinationModel->reject($id);
        
        if (!$rejected) {
            $_SESSION['error'] = 'Failed to reject destination';
            $this->redirect('/admin/destinations/' . $id);
            return;
        }
        
        // Log the rejection
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination rejected: {$destination['name']}", [
            'admin_id' => $_SESSION['user_id'],
            'destination_id' => $id
        ], 'Admin');
        
        // Set success message
        $_SESSION['success'] = 'Destination rejected successfully';
        
        // Redirect back to the destination
        $this->redirect('/admin/destinations/' . $id);
    }    /**
     * Update destination status via AJAX
     * 
     * @param int $id
     * @return void
     */
    public function status($id)
    {
        // Ensure this is an AJAX request
        if (!\App\Core\Request::isAjax()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found'], 404);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['status'])) {
            $this->json(['success' => false, 'message' => 'Status is required'], 400);
            return;
        }

        $newStatus = $input['status'];
        
        // Validate status
        if (!in_array($newStatus, ['pending', 'approved', 'rejected'])) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }

        // Check if status is actually changing
        if ($destination['approval_status'] === $newStatus) {
            $this->json(['success' => false, 'message' => "Destination is already {$newStatus}"], 400);
            return;
        }

        // Update status
        $updated = $destinationModel->update($id, ['approval_status' => $newStatus]);
        
        if (!$updated) {
            $this->json(['success' => false, 'message' => 'Failed to update status'], 500);
            return;
        }

        // Log the status change
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination status changed: {$destination['name']} to {$newStatus}", [
            'admin_id' => $_SESSION['user_id'],
            'destination_id' => $id,
            'old_status' => $destination['approval_status'],
            'new_status' => $newStatus
        ], 'Admin');        $this->json([
            'success' => true, 
            'message' => "Destination {$newStatus} successfully",
            'status' => $newStatus
        ]);
    }

    /**
     * Show edit form for a destination
     * 
     * @param int $id
     * @return void
     */    public function edit($id)
    {
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/admin/destinations');
            return;
        }
          // Get creator
        $userModel = $this->model('User');
        $creator = null;
        if ($destination['user_id']) {
            $creator = $userModel->find($destination['user_id']);
        }
        
        // Debug logging
        $logModel = $this->model('Log');
        $logModel::write('DEBUG', "Admin edit view data", [
            'destination_id' => $id,
            'destination_exists' => !empty($destination),
            'destination_name' => $destination['name'] ?? 'N/A',
            'creator_exists' => !empty($creator),
            'session_user' => $_SESSION['user_id'] ?? 'none',
            'session_role' => $_SESSION['role'] ?? 'none'
        ], 'Admin');        $this->view('admin/destinations/edit', [
            'title' => 'Edit Destination',
            'destination' => $destination,
            'creator' => $creator
        ]);
    }

    /**
     * Update a destination
     * 
     * @param int $id
     * @return void
     */    public function update($id)
    {
        // Debug logging
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Admin destination update called for ID: {$id}", [
            'post_data' => $_POST,
            'user_id' => $_SESSION['user_id'] ?? 'none'
        ], 'Admin');
        
        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $_SESSION['error'] = 'Destination not found';
            $this->redirect('/admin/destinations');
            return;
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['name'])) {
            $errors[] = 'Name is required';
        }
        
        if (empty($_POST['country'])) {
            $errors[] = 'Country is required';
        }
        
        if (empty($_POST['latitude']) || empty($_POST['longitude'])) {
            $errors[] = 'Latitude and longitude are required';
        }
        
        if (!is_numeric($_POST['latitude']) || !is_numeric($_POST['longitude'])) {
            $errors[] = 'Latitude and longitude must be valid numbers';
        }
        
        if (!in_array($_POST['privacy'], ['public', 'private'])) {
            $errors[] = 'Invalid privacy setting';
        }
        
        if (!in_array($_POST['approval_status'], ['pending', 'approved', 'rejected'])) {
            $errors[] = 'Invalid approval status';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/admin/destinations/' . $id . '/edit');
            return;
        }
        
        // Prepare update data
        $updateData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'country' => trim($_POST['country']),
            'city' => trim($_POST['city'] ?? ''),
            'latitude' => (float)$_POST['latitude'],
            'longitude' => (float)$_POST['longitude'],
            'privacy' => $_POST['privacy'],
            'approval_status' => $_POST['approval_status'],
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Track what changed for logging
        $changes = [];
        foreach ($updateData as $field => $newValue) {
            if ($destination[$field] != $newValue) {
                $changes[] = "{$field}: '{$destination[$field]}' → '{$newValue}'";
            }
        }
        
        // Update destination
        $updated = $destinationModel->update($id, $updateData);
        
        if (!$updated) {
            $_SESSION['error'] = 'Failed to update destination';
            $this->redirect('/admin/destinations/' . $id . '/edit');
            return;
        }
        
        // Log the update
        if (!empty($changes)) {
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Destination updated: {$destination['name']}", [
                'admin_id' => $_SESSION['user_id'],
                'destination_id' => $id,
                'changes' => $changes
            ], 'Admin');
        }
        
        $_SESSION['success'] = 'Destination updated successfully';
        $this->redirect('/admin/destinations/' . $id);
    }

    /**
     * Delete a destination
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        // Ensure this is an AJAX request
        if (!\App\Core\Request::isAjax()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Get destination
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
        
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found'], 404);
            return;
        }

        // Check if there are trips associated with this destination
        $db = Database::getInstance();
        $db->query("SELECT COUNT(*) as trip_count FROM trips WHERE destination_id = :destination_id");
        $db->bind(':destination_id', $id);
        $tripCount = $db->single()['trip_count'];

        if ($tripCount > 0) {
            $this->json([
                'success' => false, 
                'message' => "Cannot delete destination. It has {$tripCount} associated trip(s). Please delete or reassign the trips first."
            ], 400);
            return;
        }

        // Delete the destination
        $deleted = $destinationModel->delete($id);
        
        if (!$deleted) {
            $this->json(['success' => false, 'message' => 'Failed to delete destination'], 500);
            return;
        }

        // Log the deletion
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Destination deleted: {$destination['name']}", [
            'admin_id' => $_SESSION['user_id'],
            'destination_id' => $id,
            'destination_name' => $destination['name']
        ], 'Admin');

        $this->json([
            'success' => true, 
            'message' => 'Destination deleted successfully'
        ]);
    }
}
