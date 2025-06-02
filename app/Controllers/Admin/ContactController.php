<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

class ContactController extends Controller
{
    /**
     * Constructor - Require admin/moderator role
     */
    public function __construct()
    {
        $this->requireLogin();
        $this->requireRole(['admin', 'moderator']);
    }
    
    /**
     * Display all contacts
     * 
     * @return void
     */
    public function index()
    {
        $contactModel = $this->model('Contact');
        
        // Get filter parameters
        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;
        $assignedTo = $_GET['assigned_to'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;
        
        $filters = [
            'status' => $status,
            'priority' => $priority,
            'assigned_to' => $assignedTo,
            'search' => $search,
            'page' => $page,
            'per_page' => $perPage
        ];
        
        // Get contacts and stats
        $contacts = $contactModel->getContacts($filters);
        $totalContacts = $contactModel->getContactsCount($filters);
        $stats = $contactModel->getStats();
        
        $totalPages = ceil($totalContacts / $perPage);
        
        // Get admin users for assignment dropdown
        $userModel = $this->model('User');
        $adminUsers = $userModel->getByRole(['admin', 'moderator']);
          $this->view('admin/contacts/index', [
            'title' => 'Support Management',
            'contacts' => $contacts,
            'stats' => $stats,
            'adminUsers' => $adminUsers,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalContacts' => $totalContacts,
            'currentUserRole' => $_SESSION['role'] ?? 'user'
        ]);
    }
    
    /**
     * Display a single contact
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $contactModel = $this->model('Contact');
        $contact = $contactModel->find($id);
        
        if (!$contact) {
            $_SESSION['error'] = 'Contact not found';
            $this->redirect('/admin/contacts');
            return;
        }
        
        // Get admin users for assignment dropdown
        $userModel = $this->model('User');
        $adminUsers = $userModel->getByRole(['admin', 'moderator']);
          $this->view('admin/contacts/show', [
            'title' => 'Contact Details - ' . $contact['name'],
            'contact' => $contact,
            'adminUsers' => $adminUsers,
            'currentUserRole' => $_SESSION['role'] ?? 'user'
        ]);
    }
    
    /**
     * Update contact status
     * 
     * @param int $id
     * @return void
     */
    public function updateStatus($id)
    {
        // Validate CSRF token
        $this->validateCSRF('/admin/contacts/' . $id);
        
        $contactModel = $this->model('Contact');
        $contact = $contactModel->find($id);
        
        if (!$contact) {
            $_SESSION['error'] = 'Contact not found';
            $this->redirect('/admin/contacts');
            return;
        }
        
        $status = $_POST['status'] ?? '';
        $assignedTo = $_POST['assigned_to'] ?? null;
        $priority = $_POST['priority'] ?? $contact['priority'];
        
        // Validate status
        $validStatuses = ['new', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) {
            $_SESSION['error'] = 'Invalid status';
            $this->redirect('/admin/contacts/' . $id);
            return;
        }
        
        // Validate priority
        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($priority, $validPriorities)) {
            $_SESSION['error'] = 'Invalid priority';
            $this->redirect('/admin/contacts/' . $id);
            return;
        }
        
        // If assigned_to is empty, set to null
        if (empty($assignedTo)) {
            $assignedTo = null;
        }
        
        // Update contact
        $updateData = [
            'status' => $status,
            'priority' => $priority,
            'assigned_to' => $assignedTo
        ];
        
        // Set timestamps for status changes
        if ($status === 'resolved' && $contact['status'] !== 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'closed' && $contact['status'] !== 'closed') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }
        
        $updated = $contactModel->update($id, $updateData);
        
        if ($updated) {
            // Log the status change
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Contact #{$id} status updated to {$status}", [
                'contact_id' => $id,
                'old_status' => $contact['status'],
                'new_status' => $status,
                'admin_id' => $_SESSION['user_id']
            ], 'ContactManagement');
            
            $_SESSION['success'] = 'Contact status updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update contact status';
        }
        
        $this->redirect('/admin/contacts/' . $id);
    }
    
    /**
     * Add admin notes
     * 
     * @param int $id
     * @return void
     */
    public function addNotes($id)
    {
        // Validate CSRF token
        $this->validateCSRF('/admin/contacts/' . $id . '/notes');
        
        $contactModel = $this->model('Contact');
        $contact = $contactModel->find($id);
        
        if (!$contact) {
            $_SESSION['error'] = 'Contact not found';
            $this->redirect('/admin/contacts');
            return;
        }
        
        $notes = $_POST['notes'] ?? '';
        
        if (empty($notes)) {
            $_SESSION['error'] = 'Notes cannot be empty';
            $this->redirect('/admin/contacts/' . $id);
            return;
        }
        
        // Add notes with timestamp and admin info
        $success = $contactModel->addAdminNotes($id, $notes);
        
        if ($success) {
            // Log the note addition
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Admin notes added to contact #{$id}", [
                'contact_id' => $id,
                'admin_id' => $_SESSION['user_id'],
                'notes_preview' => substr($notes, 0, 100)
            ], 'ContactManagement');
            
            $_SESSION['success'] = 'Notes added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add notes';
        }
        
        $this->redirect('/admin/contacts/' . $id);
    }
    
    /**
     * Bulk actions on contacts
     * 
     * @return void
     */
    public function bulkAction()
    {
        // Validate CSRF token
        $this->validateCSRF('/admin/contacts/bulk');
        
        $action = $_POST['action'] ?? '';
        $contactIds = $_POST['contact_ids'] ?? [];
        
        if (empty($contactIds) || !is_array($contactIds)) {
            $_SESSION['error'] = 'No contacts selected';
            $this->redirect('/admin/contacts');
            return;
        }
        
        $contactModel = $this->model('Contact');
        $processed = 0;
        
        switch ($action) {
            case 'mark_resolved':
                foreach ($contactIds as $id) {
                    if ($contactModel->updateStatus($id, 'resolved')) {
                        $processed++;
                    }
                }
                $_SESSION['success'] = "Marked {$processed} contacts as resolved";
                break;
                
            case 'mark_closed':
                foreach ($contactIds as $id) {
                    if ($contactModel->updateStatus($id, 'closed')) {
                        $processed++;
                    }
                }
                $_SESSION['success'] = "Marked {$processed} contacts as closed";
                break;
                
            case 'assign_to':
                $assignTo = $_POST['assign_to'] ?? null;
                if ($assignTo) {
                    foreach ($contactIds as $id) {
                        if ($contactModel->update($id, ['assigned_to' => $assignTo])) {
                            $processed++;
                        }
                    }
                    $_SESSION['success'] = "Assigned {$processed} contacts";
                } else {
                    $_SESSION['error'] = 'No assignee selected';
                }
                break;
                
            case 'delete':
                // Only allow admins to delete
                if ($_SESSION['role'] === 'admin') {
                    foreach ($contactIds as $id) {
                        if ($contactModel->delete($id)) {
                            $processed++;
                        }
                    }
                    $_SESSION['success'] = "Deleted {$processed} contacts";
                } else {
                    $_SESSION['error'] = 'You do not have permission to delete contacts';
                }
                break;
                
            default:
                $_SESSION['error'] = 'Invalid action';
        }
        
        // Log bulk action
        if ($processed > 0) {
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Bulk action '{$action}' performed on {$processed} contacts", [
                'action' => $action,
                'processed_count' => $processed,
                'contact_ids' => $contactIds,
                'admin_id' => $_SESSION['user_id']
            ], 'ContactManagement');
        }
        
        $this->redirect('/admin/contacts');
    }
    
    /**
     * Export contacts to CSV
     * 
     * @return void
     */
    public function export()
    {
        $contactModel = $this->model('Contact');
        
        // Get all contacts (no pagination for export)
        $filters = [
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'search' => $_GET['search'] ?? null,
            'per_page' => 999999 // Get all matching records
        ];
        
        $contacts = $contactModel->getContacts($filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contacts_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID', 'Name', 'Email', 'Subject', 'Message', 'Status', 'Priority',
            'Assigned To', 'Created At', 'Updated At', 'Resolved At', 'Closed At'
        ]);
        
        // CSV data
        foreach ($contacts as $contact) {
            fputcsv($output, [
                $contact['id'],
                $contact['name'],
                $contact['email'],
                $contact['subject'] ?? '',
                $contact['message'],
                $contact['status'],
                $contact['priority'],
                $contact['assigned_username'] ?? '',
                $contact['created_at'],
                $contact['updated_at'],
                $contact['resolved_at'] ?? '',
                $contact['closed_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}
