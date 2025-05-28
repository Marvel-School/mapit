<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Core\Database;

class UserController extends Controller
{
    /**
     * Constructor - Require admin role
     */
    public function __construct()
    {
        // Check if user is logged in and has admin role
        $this->requireLogin();
        $this->requireRole('admin');
    }
    
    /**
     * Display all users
     * 
     * @return void
     */
    public function index()
    {
        $userModel = $this->model('User');
        $users = $userModel->all();
        
        $this->view('admin/users/index', [
            'title' => 'Manage Users',
            'users' => $users
        ]);
    }
    
    /**
     * Display a single user
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        // Get user
        $userModel = $this->model('User');
        $user = $userModel->find($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            $this->redirect('/admin/users');
            return;
        }
        
        // Get user trips
        $tripModel = $this->model('Trip');
        $trips = $tripModel->getUserTrips($id);
        
        // Get user badges
        $badgeModel = $this->model('Badge');
        $badges = $badgeModel->getUserBadges($id);
          // Get user logs
        $db = Database::getInstance();
        $db->query("
            SELECT * FROM logs
            WHERE JSON_EXTRACT(data, '$.user_id') = :user_id
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $db->bind(':user_id', $id);
        $logs = $db->resultSet();
        
        $this->view('admin/users/show', [
            'title' => 'User Details',
            'user' => $user,
            'trips' => $trips,
            'badges' => $badges,
            'logs' => $logs
        ]);
    }
    
    /**
     * Display edit user form
     * 
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        // Get user
        $userModel = $this->model('User');
        $user = $userModel->find($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            $this->redirect('/admin/users');
            return;
        }
        
        $this->view('admin/users/edit', [
            'title' => 'Edit User',
            'user' => $user
        ]);
    }
      /**
     * Update user
     * 
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        // Validate CSRF token
        $this->validateCSRF('/admin/users/' . $id . '/edit');
        
        // Get user
        $userModel = $this->model('User');
        $user = $userModel->find($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            $this->redirect('/admin/users');
            return;
        }
        
        // Get form data
        $username = $_POST['username'] ?? $user['username'];
        $email = $_POST['email'] ?? $user['email'];
        $role = $_POST['role'] ?? $user['role'];
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate form data
        $errors = [];
        
        // Validate username
        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif ($username !== $user['username']) {
            // Check if username is already taken
            $existingUser = $userModel->findByUsername($username);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors['username'] = 'Username is already taken';
            }
        }
        
        // Validate email
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        } elseif ($email !== $user['email']) {
            // Check if email is already taken
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                $errors['email'] = 'Email is already taken';
            }
        }
        
        // Validate role
        $validRoles = ['user', 'admin', 'moderator'];
        if (!in_array($role, $validRoles)) {
            $errors['role'] = 'Role is invalid';
        }
        
        // Validate password if provided
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $errors['new_password'] = 'Password must be at least 6 characters';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }
        
        if (!empty($errors)) {
            $this->view('admin/users/edit', [
                'title' => 'Edit User',
                'user' => $user,
                'errors' => $errors
            ]);
            return;
        }
        
        // Prepare user data
        $userData = [
            'username' => $username,
            'email' => $email,
            'role' => $role
        ];
        
        // Update password if provided
        if (!empty($newPassword)) {
            $userData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        // Update user
        $updated = $userModel->update($id, $userData);
        
        if (!$updated) {
            $this->view('admin/users/edit', [
                'title' => 'Edit User',
                'user' => $user,
                'errors' => ['update' => 'Failed to update user']
            ]);
            return;
        }
        
        // Log the update
        $logModel = $this->model('Log');
        $logModel::write('INFO', "User updated by admin: {$username}", [
            'admin_id' => $_SESSION['user_id'],
            'user_id' => $id
        ], 'Admin');
        
        // Set success message
        $_SESSION['success'] = 'User updated successfully';
        
        // Redirect to user details
        $this->redirect('/admin/users/' . $id);
    }
      /**
     * Delete user
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        // Validate CSRF token
        $this->validateCSRF('/admin/users/' . $id);
        
        // Prevent deleting own account
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'You cannot delete your own account';
            $this->redirect('/admin/users');
            return;
        }
        
        // Get user
        $userModel = $this->model('User');
        $user = $userModel->find($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            $this->redirect('/admin/users');
            return;
        }
        
        // Delete user
        $deleted = $userModel->delete($id);
        
        if (!$deleted) {
            $_SESSION['error'] = 'Failed to delete user';
            $this->redirect('/admin/users');
            return;
        }
        
        // Log the deletion
        $logModel = $this->model('Log');
        $logModel::write('INFO', "User deleted by admin: {$user['username']}", [
            'admin_id' => $_SESSION['user_id'],
            'user_id' => $id
        ], 'Admin');
        
        // Set success message
        $_SESSION['success'] = 'User deleted successfully';
        
        // Redirect to users
        $this->redirect('/admin/users');
    }
}
