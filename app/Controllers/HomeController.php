<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;

class HomeController extends Controller
{
    /**
     * Display home page
     * 
     * @return void
     */
    public function index()
    {
        $destinationModel = $this->model('Destination');
        $featured = $destinationModel->getFeatured(6);
        
        $this->view('home/index', [
            'title' => 'MapIt - Travel Destination Mapping',
            'featured' => $featured
        ]);
    }
    
    /**
     * Display about page
     * 
     * @return void
     */
    public function about()
    {
        $this->view('home/about', [
            'title' => 'About MapIt'
        ]);
    }
    
    /**
     * Display contact page
     * 
     * @return void
     */
    public function contact()
    {
        $this->view('home/contact', [
            'title' => 'Contact Us'
        ]);
    }
    
    /**
     * Process contact form
     * 
     * @return void
     */
    public function processContact()
    {
        // Process contact form submission
        // In a real application, you would send an email or save to database
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';
        
        // Validate inputs
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        }
        
        if (empty($message)) {
            $errors['message'] = 'Message is required';
        }
        
        if (!empty($errors)) {
            // Return back with errors
            $this->view('home/contact', [
                'title' => 'Contact Us',
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
                'message' => $message
            ]);
            return;
        }
        
        // Log the contact request
        $logModel = $this->model('Log');
        $logModel::write('INFO', "Contact form submission from {$name}", [
            'name' => $name,
            'email' => $email,
            'message' => $message
        ], 'ContactForm');
        
        // Redirect with success message
        $_SESSION['success'] = 'Your message has been sent. We will get back to you soon!';
        $this->redirect('/contact');
    }
}
