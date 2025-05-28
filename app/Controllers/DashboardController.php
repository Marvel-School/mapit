<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Core\FileUpload;

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
          // Get recent trips (unique by destination to avoid duplicates)
        $recentTrips = $tripModel->getRecentTripsUnique($userId, 5);
        
        // Get badge progress
        $badges = $userModel->checkBadgeProgress($userId);
        
        // Get most recent badge
        $userBadges = $userModel->getBadges($userId);
        $recentBadge = !empty($userBadges) ? $userBadges[0] : null;        // Get featured destinations for the map
        $destinationModel = $this->model('Destination');
        $featured = $destinationModel->getFeatured(10);
        
        // Get user's destinations with trip status for the map
        $userDestinations = $destinationModel->getUserDestinationsWithTripStatus($userId);
        
        // Get public destinations for the map
        $publicDestinations = $destinationModel->getPublic();
        
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
            'userDestinations' => $userDestinations,
            'publicDestinations' => $publicDestinations,
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
    }    /**
     * Update user profile
     * 
     * @return void
     */    public function updateProfile()
    {
        try {
            // Add debugging
            error_log("DashboardController::updateProfile called");
            
            $userId = $_SESSION['user_id'];
            
            // Validate CSRF token
            $this->validateCSRF('/profile');
            
            // Get form data
            $name = $_POST['name'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $website = $_POST['website'] ?? '';
            $country = $_POST['country'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['new_password_confirm'] ?? '';
            $settings = $_POST['settings'] ?? [];
            
            // Debug form data
            error_log("Form data received: " . print_r($_POST, true));
              // Get user data
            $userModel = $this->model('User');
            $user = $userModel->find($userId);
            
            // Validate form data
            $errors = [];
            
            // Handle avatar upload securely with error handling
            $avatarFilename = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $fileUpload = new FileUpload();
                    $avatarFilename = $fileUpload->uploadImage($_FILES['avatar'], 'avatars', $userId);
                    
                    if (!$avatarFilename) {
                        $errors = array_merge($errors, $fileUpload->getErrors());
                    } else {
                        // Delete old avatar if exists (explicit file cleanup)
                        if (!empty($user['avatar'])) {
                            $oldAvatarPath = 'public/images/avatars/' . $user['avatar'];
                            if (file_exists($oldAvatarPath)) {
                                unlink($oldAvatarPath); // Explicit file cleanup
                            }
                            // Also use FileUpload method for secure deletion
                            $fileUpload->deleteFile($user['avatar'], 'avatars', $userId);
                        }
                    }
                } catch (\Exception $e) {
                    $errors['avatar'] = 'Avatar upload failed: ' . $e->getMessage();
                    error_log("Avatar upload error: " . $e->getMessage());
                }
            }
        
        // Validate name
        if (empty($name)) {
            $errors['name'] = 'Full name is required';
        }
        
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
            $errors['email'] = 'Email is invalid';        } elseif ($email !== $user['email']) {
            // Check if email is already taken by another user
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors['email'] = 'Email is already taken';
            }
        }
        
        // Validate website URL if provided
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Website must be a valid URL';
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
                $errors['new_password_confirm'] = 'Passwords do not match';
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
        }        // Update user data
        $userData = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'bio' => $bio,
            'website' => $website,
            'country' => $country
        ];
        
        // Add avatar filename if uploaded
        if ($avatarFilename) {
            $userData['avatar'] = $avatarFilename;
        }
        
        // Handle settings as JSON
        if (!empty($settings)) {
            $userData['settings'] = json_encode([
                'public_profile' => isset($settings['public_profile']),
                'email_notifications' => isset($settings['email_notifications']),
                'show_visited_places' => isset($settings['show_visited_places'])
            ]);
        }
        
        // Update password if needed
        if ($updatePassword) {
            $userData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
          // Update user in database
        $updated = $userModel->update($userId, $userData);
        
        // Debug update result
        error_log("User update result: " . ($updated ? 'SUCCESS' : 'FAILED'));
        error_log("Updated data: " . print_r($userData, true));
        
        if (!$updated) {
            error_log("Update failed - showing errors");
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
        error_log("Profile update successful - redirecting with success message");
        $_SESSION['success'] = 'Profile updated successfully';
        $this->redirect('/profile');
        
        } catch (\Exception $e) {
            // Handle any unexpected errors
            error_log("Profile update error: " . $e->getMessage());
            
            // Get user data for error display
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
                'countries' => $countries,
                'errors' => ['update' => 'An unexpected error occurred while updating your profile.']
            ]);
        }
    }
}
