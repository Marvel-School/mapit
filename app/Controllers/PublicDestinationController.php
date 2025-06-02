<?php

namespace App\Controllers;

use App\Core\Controller;

class PublicDestinationController extends Controller
{
    /**
     * Constructor - No authentication required for public access
     */
    public function __construct()
    {
        // No authentication required for public access
    }    /**
     * Display public interactive map with featured and public destinations
     * Redirects logged-in users to their dashboard since they have a better map there
     * 
     * @return void
     */    public function map()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // If user is logged in, redirect to dashboard which has their personal map
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $destinationModel = $this->model('Destination');
        
        // Get featured destinations
        $featured = $destinationModel->getFeatured(20);
        
        // Get all public approved destinations
        $publicDestinations = $destinationModel->getPublic();
        
        // DEBUG: Log what we're getting
        error_log("PublicDestinationController::map - Featured count: " . count($featured));
        error_log("PublicDestinationController::map - Public count: " . count($publicDestinations));
        
        // Get countries for filtering (if needed)
        $countries = $this->getCountries(true);
          $this->view('public/map', [
            'title' => 'Explore Destinations - MapIt',
            'featured' => $featured,
            'destinations' => $publicDestinations,
            'countries' => $countries
        ]);
    }

    /**
     * Display featured destinations page
     * 
     * @return void
     */
    public function featured()
    {
        $destinationModel = $this->model('Destination');
        $featured = $destinationModel->getFeatured(50);
          $this->view('public/featured', [
            'title' => 'Featured Destinations - MapIt',
            'featured' => $featured
        ]);
    }

    /**
     * Display single destination (public view)
     * 
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->find($id);
          if (!$destination) {
            $this->view('public/404', [
                'title' => 'Destination Not Found',
                'message' => 'The destination you are looking for could not be found.'
            ]);
            return;
        }
        
        // Check if destination is publicly viewable
        if ($destination['privacy'] !== 'public' || $destination['approval_status'] !== 'approved') {
            $this->view('public/403', [
                'title' => 'Access Denied',
                'message' => 'This destination is not publicly available.'
            ]);
            return;
        }
        
        // Get creator info
        $userModel = $this->model('User');
        $creator = $userModel->find($destination['user_id']);
        
        // Get nearby public destinations
        $nearbyDestinations = $destinationModel->getNearby(
            $destination['latitude'], 
            $destination['longitude'], 
            $id, 
            100, // 100km radius
            6    // limit to 6 destinations
        );        // Filter nearby destinations to only include public ones
        $nearbyDestinations = array_filter($nearbyDestinations, function($dest) {
            return $dest['privacy'] === 'public' && $dest['approval_status'] === 'approved';
        });
        
        // Add is_featured field for template compatibility
        $destination['is_featured'] = $destination['featured'] == 1;
        
        // Add visit_status field for template compatibility (public destinations have no visit status)
        $destination['visit_status'] = null;
        
        $this->view('public/destination', [
            'title' => $destination['name'] . ' - MapIt',
            'destination' => $destination,
            'creator' => $creator,
            'nearbyDestinations' => array_slice($nearbyDestinations, 0, 6)
        ]);
    }    /**
     * Get countries list helper
     * 
     * @param bool $asObjects
     * @return array
     */
    protected function getCountries($asObjects = false)
    {
        $countries = [
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'FR' => 'France',
            'DE' => 'Germany',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'JP' => 'Japan',
            'AU' => 'Australia',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'IN' => 'India',
            'CN' => 'China',
            'RU' => 'Russia',
            'ZA' => 'South Africa',
            'EG' => 'Egypt',
            'NG' => 'Nigeria',
            'KE' => 'Kenya',
            'TH' => 'Thailand',
            'ID' => 'Indonesia',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'PH' => 'Philippines',
            'VN' => 'Vietnam',
            'KR' => 'South Korea',
            'NZ' => 'New Zealand',
            'AR' => 'Argentina',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'PE' => 'Peru'
        ];
        
        if ($asObjects) {
            $result = [];
            foreach ($countries as $code => $name) {
                $result[] = ['code' => $code, 'name' => $name];
            }
            return $result;
        }
        
        return $countries;
    }
}
