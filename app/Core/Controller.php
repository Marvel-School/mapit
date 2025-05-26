<?php

namespace App\Core;

class Controller
{
    /**
     * Load a model
     * 
     * @param string $model
     * @return object
     */
    public function model($model)
    {
        $modelClass = "App\\Models\\{$model}";
        return new $modelClass();
    }
    
    /**
     * Load a view
     * 
     * @param string $view
     * @param array $data
     * @return void
     */
    public function view($view, $data = [])
    {
        // Add common data that all views might need
        $data = array_merge($this->getCommonViewData(), $data);
        
        // Extract data to make variables available in view
        extract($data);
        
        // Define the path to the view file
        $viewFile = __DIR__ . "/../Views/{$view}.php";
        
        if (file_exists($viewFile)) {
            // Start output buffering
            ob_start();
            
            // Include the view
            require $viewFile;
            
            // Get the contents of the buffer and clear it
            $content = ob_get_clean();
            
            // Check if layout is specified
            $layout = isset($layout) ? $layout : 'main';
            $layoutFile = __DIR__ . "/../Views/layouts/{$layout}.php";
            
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo "View {$view} not found";
        }
    }
    
    /**
     * Redirect to a specific page
     * 
     * @param string $url
     * @return void
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Return JSON response
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user has specific role
     * 
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        if (is_array($roles)) {
            return in_array($_SESSION['user_role'], $roles);
        }
        
        return $_SESSION['user_role'] == $roles;
    }
    
    /**
     * Require authentication to access a page
     * 
     * @param string $redirect
     * @return void
     */
    public function requireLogin($redirect = '/login')
    {
        if (!$this->isLoggedIn()) {
            $this->redirect($redirect);
        }
    }
    
    /**
     * Require specific role to access a page
     * 
     * @param string|array $roles
     * @param string $redirect
     * @return void
     */
    public function requireRole($roles, $redirect = '/login')
    {
        if (!$this->hasRole($roles)) {
            $this->redirect($redirect);
        }
    }

    /**
     * Get common data that should be available to all views
     * 
     * @return array
     */
    protected function getCommonViewData()
    {
        $commonData = [];
        
        // Add Google Maps API key
        try {
            $config = require __DIR__ . '/../../config/app.php';
            $commonData['googleMapsApiKey'] = $config['google_maps']['api_key'] ?? '';
        } catch (\Exception $e) {
            $commonData['googleMapsApiKey'] = '';
        }
        
        return $commonData;
    }

    /**
     * Get list of countries
     * 
     * @param bool $asArray Whether to return as array with code/name keys (true) or associative array (false)
     * @return array
     */
    protected function getCountries($asArray = false)
    {
        $countries = [
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'FR' => 'France',
            'DE' => 'Germany',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'PT' => 'Portugal',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'IE' => 'Ireland',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'JP' => 'Japan',
            'CN' => 'China',
            'IN' => 'India',
            'BR' => 'Brazil',
            'AR' => 'Argentina',
            'MX' => 'Mexico',
            // Add more countries as needed
        ];

        if ($asArray) {
            $result = [];
            foreach ($countries as $code => $name) {
                $result[] = ['code' => $code, 'name' => $name];
            }
            return $result;
        }

        return $countries;
    }
}
