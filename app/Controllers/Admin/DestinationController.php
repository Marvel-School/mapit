<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class DestinationController extends Controller
{
    /**
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
        ], 'Admin');

        $this->json([
            'success' => true, 
            'message' => "Destination {$newStatus} successfully",
            'status' => $newStatus
        ]);
    }
}
