<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

class AuthController extends Controller
{
    /**
     * Display login form
     * 
     * @return void
     */
    public function login()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/login', [
            'title' => 'Login'
        ]);
    }
    
    /**
     * Process login form
     * 
     * @return void
     */
    public function processLogin()
    {
        // Get form data
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) ? true : false;
        
        // Validate form data
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'Username or email is required';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        if (!empty($errors)) {
            $this->view('auth/login', [
                'title' => 'Login',
                'errors' => $errors,
                'username' => $username
            ]);
            return;
        }
        
        // Authenticate user
        $userModel = $this->model('User');
        $user = $userModel->authenticate($username, $password);
        
        if (!$user) {
            $errors['login'] = 'Invalid username or password';
            
            $this->view('auth/login', [
                'title' => 'Login',
                'errors' => $errors,
                'username' => $username
            ]);
            return;
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        
        // If remember me is checked, set a cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (86400 * 30); // 30 days
            
            setcookie('remember_token', $token, $expires, '/');
            
            // In a real application, store the token in the database linked to the user
            // Here we'll just log it
            $logModel = $this->model('Log');
            $logModel::write('INFO', 'Remember me token set', [
                'user_id' => $user['id'],
                'expires' => date('Y-m-d H:i:s', $expires)
            ], 'Authentication');
        }
        
        // Log the login
        $logModel = $this->model('Log');
        $logModel::write('INFO', "User login: {$user['username']}", [
            'user_id' => $user['id']
        ], 'Authentication');
        
        // Redirect based on role
        if ($user['role'] == 'admin') {
            $this->redirect('/admin');
        } else {
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * Display registration form
     * 
     * @return void
     */
    public function register()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/register', [
            'title' => 'Register'
        ]);
    }
    
    /**
     * Process registration form
     * 
     * @return void
     */
    public function processRegister()
    {
        // Get form data
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
          // Validate form data
        $validator = new Validator($_POST);
        $validator->validate([
            'username' => 'required|min:3|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'password_confirm' => 'required|match:password'
        ]);
        
        $errors = $validator->errors();
        
        if (!empty($errors)) {
            $this->view('auth/register', [
                'title' => 'Register',
                'errors' => $errors,
                'username' => $username,
                'email' => $email
            ]);
            return;
        }
        
        // Register user
        $userModel = $this->model('User');
        $userId = $userModel->register([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'user'
        ]);
        
        if (!$userId) {
            $errors['register'] = 'Failed to register user';
            
            $this->view('auth/register', [
                'title' => 'Register',
                'errors' => $errors,
                'username' => $username,
                'email' => $email
            ]);
            return;
        }
        
        // Log the registration
        $logModel = $this->model('Log');
        $logModel::write('INFO', "User registered: {$username}", [
            'user_id' => $userId
        ], 'Authentication');
        
        // Set success message
        $_SESSION['success'] = 'Registration successful! You can now log in.';
        
        // Redirect to login
        $this->redirect('/login');
    }
    
    /**
     * Log out user
     * 
     * @return void
     */
    public function logout()
    {
        // If user is logged in, log the logout
        if ($this->isLoggedIn()) {
            $logModel = $this->model('Log');
            $logModel::write('INFO', "User logout: {$_SESSION['username']}", [
                'user_id' => $_SESSION['user_id']
            ], 'Authentication');
        }
        
        // Clear session
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role']);
        
        // Clear remember me cookie if it exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Redirect to home
        $this->redirect('/');
    }
    
    /**
     * Display forgot password form
     * 
     * @return void
     */
    public function forgotPassword()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/forgot-password', [
            'title' => 'Forgot Password'
        ]);
    }
    
    /**
     * Process forgot password form
     * 
     * @return void
     */
    public function processForgotPassword()
    {
        // Get form data
        $email = $_POST['email'] ?? '';
        
        // Validate email
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        }
        
        if (!empty($errors)) {
            $this->view('auth/forgot-password', [
                'title' => 'Forgot Password',
                'errors' => $errors,
                'email' => $email
            ]);
            return;
        }
        
        // Check if email exists
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);
        
        // Always show success message even if email doesn't exist
        // This prevents email enumeration
        $_SESSION['success'] = 'If your email address exists in our database, you will receive a password recovery link.';
        
        // If user exists, generate token and send email
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            
            // In a real application, save the token to the database
            // For this example, we'll just log it
            $logModel = $this->model('Log');
            $logModel::write('INFO', "Password reset requested for {$email}", [
                'user_id' => $user['id'],
                'token' => $token
            ], 'Authentication');
            
            // In a real application, send an email with the reset link
            // For this example, just log it
            $resetLink = "http://localhost/reset-password/{$token}";
            $logModel::write('INFO', "Password reset link: {$resetLink}", [
                'user_id' => $user['id'],
                'email' => $email
            ], 'Authentication');
        }
        
        // Redirect to login
        $this->redirect('/login');
    }
    
    /**
     * Display reset password form
     * 
     * @param string $token
     * @return void
     */
    public function resetPassword($token)
    {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        // In a real application, verify the token from the database
        // For this example, we'll accept any token
        
        $this->view('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token
        ]);
    }
    
    /**
     * Process reset password form
     * 
     * @return void
     */
    public function processResetPassword()
    {
        // Get form data
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        // Validate form data
        $errors = [];
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $this->view('auth/reset-password', [
                'title' => 'Reset Password',
                'token' => $token,
                'errors' => $errors
            ]);
            return;
        }
        
        // In a real application, find the user by token and update their password
        // For this example, we'll just log it
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Password reset attempted with token: {$token}", [], 'Authentication');
        
        // Set success message
        $_SESSION['success'] = 'Password has been reset successfully. You can now log in with your new password.';
        
        // Redirect to login
        $this->redirect('/login');
    }
}
