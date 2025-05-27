<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

class DashboardController extends Controller
{
    /**
     * Constructor - Require authentication for all dashboard pages
     */
    public function __construct()
    {
        $this->requireLogin();
    }
    
    /**
     * Display user dashboard
     * 
     * @return void
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Get user data
        $userModel = $this->model('User');
        $user = $userModel->find($userId);
          // Get trip statistics
        $tripModel = $this->model('Trip');
        $tripStats = $tripModel->getUserStats($userId);
        
        // Get recent trips
        $recentTrips = $tripModel->getUserTrips($userId, ['limit' => 5]);
        
        // Get badge progress
        $badges = $userModel->checkBadgeProgress($userId);
        
        // Get most recent badge
        $userBadges = $userModel->getBadges($userId);
        $recentBadge = !empty($userBadges) ? $userBadges[0] : null;
          // Get featured destinations for the map
        $destinationModel = $this->model('Destination');
        $featured = $destinationModel->getFeatured(10);
        
        // Get countries for the modal
        $countries = $this->getCountries();
        
        // Create stats for the dashboard view
        $stats = [
            'countries_visited' => $tripModel->getCountriesVisitedCount($userId),
            'places_visited' => $tripStats['visited'] ?? 0,
            'wishlist_count' => $tripStats['planned'] ?? 0,
            'trips_count' => ($tripStats['visited'] ?? 0) + ($tripStats['planned'] ?? 0),
            'badges_earned' => count($userBadges ?? []),
        ];        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'tripStats' => $tripStats,
            'stats' => $stats,
            'recentTrips' => $recentTrips,
            'badges' => $badges,
            'userBadges' => $userBadges,  // Pass the user's badges for the badges section
            'recentBadge' => $recentBadge,
            'featured' => $featured,
            'countries' => $countries
        ]);
    }
    
    /**
     * Display user profile
     * 
     * @return void
     */
    public function profile()
    {
        $userId = $_SESSION['user_id'];
        
        // Get user data
        $userModel = $this->model('User');
        $user = $userModel->find($userId);
        
        // Get user badges
        $badgeModel = $this->model('Badge');
        $badges = $badgeModel->getUserBadges($userId);
        
        // Get countries as associative array for dropdown
        $countries = $this->getCountries();
        
        $this->view('dashboard/profile', [
            'title' => 'My Profile',
            'user' => $user,
            'badges' => $badges,
            'countries' => $countries
        ]);
    }
    
    /**
     * Update user profile
     * 
     * @return void
     */
    public function updateProfile()
    {
        $userId = $_SESSION['user_id'];
        
        // Get form data
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Get user data
        $userModel = $this->model('User');
        $user = $userModel->find($userId);
        
        // Validate form data
        $errors = [];
        
        // Validate username
        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif ($username !== $user['username']) {
            // Check if username is already taken by another user
            $existingUser = $userModel->findByUsername($username);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors['username'] = 'Username is already taken';
            }
        }
        
        // Validate email
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        } elseif ($email !== $user['email']) {
            // Check if email is already taken by another user
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors['email'] = 'Email is already taken';
            }
        }
        
        // Check if password is being updated
        $updatePassword = !empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword);
        
        if ($updatePassword) {
            // Validate current password
            if (empty($currentPassword)) {
                $errors['current_password'] = 'Current password is required';
            } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect';
            }
            
            // Validate new password
            if (empty($newPassword)) {
                $errors['new_password'] = 'New password is required';
            } elseif (strlen($newPassword) < 6) {
                $errors['new_password'] = 'New password must be at least 6 characters';
            }
            
            // Validate confirm password
            if ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }
          // If there are errors, return to profile page with errors
        if (!empty($errors)) {
            // Get user badges
            $badgeModel = $this->model('Badge');
            $badges = $badgeModel->getUserBadges($userId);
            
            // Get countries as associative array for dropdown
            $countries = $this->getCountries();
            
            $this->view('dashboard/profile', [
                'title' => 'My Profile',
                'user' => $user,
                'badges' => $badges,
                'countries' => $countries,
                'errors' => $errors
            ]);
            return;
        }
        
        // Update user data
        $userData = [
            'username' => $username,
            'email' => $email
        ];
        
        // Update password if needed
        if ($updatePassword) {
            $userData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        // Update user in database
        $updated = $userModel->update($userId, $userData);
          if (!$updated) {
            $errors['update'] = 'Failed to update profile';
            
            // Get user badges
            $badgeModel = $this->model('Badge');
            $badges = $badgeModel->getUserBadges($userId);
            
            // Get countries as associative array for dropdown
            $countries = $this->getCountries();
            
            $this->view('dashboard/profile', [
                'title' => 'My Profile',
                'user' => $user,
                'badges' => $badges,
                'countries' => $countries,
                'errors' => $errors
            ]);
            return;
        }
        
        // Log the profile update
        $logModel = $this->model('Log');
        $logModel::write('INFO', "User profile updated: {$username}", [
            'user_id' => $userId
        ], 'User');
        
        // Update session data
        $_SESSION['username'] = $username;
        
        // Redirect with success message
        $_SESSION['success'] = 'Profile updated successfully';
        $this->redirect('/profile');
    }
}
