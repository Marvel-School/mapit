<?php

namespace App\Core;

class View
{
    /**
     * Render a view with data
     * 
     * @param string $view
     * @param array $data
     * @param string $layout
     * @return void
     */
    public static function render($view, $data = [], $layout = 'main')
    {
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
            
            // Check if layout is needed
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
     * Render a partial view
     * 
     * @param string $partial
     * @param array $data
     * @return void
     */
    public static function partial($partial, $data = [])
    {
        // Extract data to make variables available in partial
        extract($data);
        
        $partialFile = __DIR__ . "/../Views/partials/{$partial}.php";
        
        if (file_exists($partialFile)) {
            include $partialFile;
        } else {
            echo "Partial {$partial} not found";
        }
    }
    
    /**
     * Escape HTML output
     * 
     * @param string $value
     * @return string
     */
    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Create CSRF token
     * 
     * @return string
     */
    public static function csrf()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generate CSRF token field
     * 
     * @return string
     */
    public static function csrfField()
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrf() . '">';
    }
    
    /**
     * Check if CSRF token is valid
     * 
     * @param string $token
     * @return bool
     */
    public static function verifyCSRF($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
