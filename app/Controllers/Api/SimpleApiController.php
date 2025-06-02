<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class SimpleApiController extends Controller
{    
    public function destinations()
    {
        try {
            // Set headers
            header('Content-Type: application/json');
            
            // Check database config
            $dbConfig = [
                'host' => getenv('DB_HOST') ?: 'mysql',
                'database' => getenv('DB_DATABASE') ?: 'mapit',
                'username' => getenv('DB_USERNAME') ?: 'mapit_user',
                'password' => getenv('DB_PASSWORD') ? 'SET' : 'NOT_SET'
            ];
            
            $destinationModel = $this->model('Destination');
            
            // Test with a simple query
            $destinations = $destinationModel->getPublic();
            
            // Also try to get featured for comparison
            $featured = $destinationModel->getFeatured(5);
            
            $result = [
                'success' => true,
                'data' => $destinations,
                'count' => count($destinations),
                'featured_count' => count($featured),
                'db_config' => $dbConfig,
                'debug' => 'Simple API test with config check'
            ];
            
            echo json_encode($result);
            exit;
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => 'Exception caught',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            exit;
        }
    }
    
    public function testContacts()
    {
        try {
            // Set headers
            header('Content-Type: application/json');
            
            $contactModel = $this->model('Contact');
            
            // Test creating a contact
            $testContact = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'This is a test message from the API',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test API Client'
            ];
            
            $contactId = $contactModel->create($testContact);
            
            if ($contactId) {
                // Test retrieving the contact
                $contact = $contactModel->find($contactId);
                
                // Test getting stats
                $stats = $contactModel->getStats();
                
                // Test getting contacts list
                $contacts = $contactModel->getContacts(['per_page' => 5]);
                
                $result = [
                    'success' => true,
                    'message' => 'Contact model test successful',
                    'contact_id' => $contactId,
                    'contact' => $contact,
                    'stats' => $stats,
                    'recent_contacts' => $contacts,
                    'debug' => 'Contact functionality test completed'
                ];
            } else {
                $result = [
                    'success' => false,
                    'error' => 'Failed to create test contact',
                    'debug' => 'Contact creation failed'
                ];
            }
            
            echo json_encode($result, JSON_PRETTY_PRINT);
            exit;
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => 'Exception caught during contact test',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
}
?>
