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
                // Make content available to the layout
                $data['content'] = $content;
                extract($data);
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
     * Enhanced CSRF token generation with additional entropy
     * 
     * @return string
     */
    public static function generateCSRF()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate token with additional entropy
        $token = bin2hex(random_bytes(32)) . '_' . time() . '_' . bin2hex(random_bytes(16));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_time'] = time();
        
        return $token;
    }
    
    /**
     * Enhanced CSRF verification with time-based validation
     * 
     * @param string $token
     * @return bool
     */
    public static function verifyCSRF($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_time'])) {
            return false;
        }
        
        // Check if token has expired (30 minutes)
        if (time() - $_SESSION['csrf_time'] > 1800) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_time']);
            return false;
        }
        
        // Verify token using timing-safe comparison
        $isValid = hash_equals($_SESSION['csrf_token'], $token);
        
        if ($isValid) {
            // Regenerate token after successful verification for additional security
            self::generateCSRF();
        }
        
        return $isValid;
    }
    
    /**
     * Enhanced output encoding for XSS prevention
     * 
     * @param mixed $data
     * @param string $context
     * @return mixed
     */
    public static function encode($data, $context = 'html')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::encode($value, $context);
            }
            return $data;
        }
        
        if (!is_string($data)) {
            return $data;
        }
        
        switch ($context) {
            case 'html':
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            case 'html_attr':
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            case 'js':
                return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            case 'css':
                return preg_replace('/[^a-zA-Z0-9\-_]/', '', $data);
            case 'url':
                return rawurlencode($data);
            default:
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Content Security Policy header generation
     * 
     * @return string
     */
    public static function generateCSPHeader()
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://maps.googleapis.com",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'"
        ];
        
        return implode('; ', $policies);
    }
    
    /**
     * Security headers setup
     * 
     * @return void
     */
    public static function setSecurityHeaders()
    {
        // Only set headers if not already sent
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Content-Security-Policy: ' . self::generateCSPHeader());
            
            // HSTS header for HTTPS
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
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
}
