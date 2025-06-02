<?php

namespace App\Core;

class Request
{    /**
     * Get the request URI
     * 
     * @return string
     */
    public static function uri()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = trim(parse_url($requestUri, PHP_URL_PATH), '/');
        return $uri ?: 'home';
    }    /**
     * Get the request method
     * 
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get all request data
     * 
     * @return array
     */
    public static function all()
    {
        $data = [];

        if (self::method() === 'GET') {
            foreach ($_GET as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
        }

        if (in_array(self::method(), ['POST', 'PUT', 'DELETE'])) {
            // Handle form data
            foreach ($_POST as $key => $value) {
                $data[$key] = self::sanitize($value);
            }            // Handle JSON request
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $jsonData = json_decode($json, true);
                
                if ($jsonData) {
                    foreach ($jsonData as $key => $value) {
                        $data[$key] = self::sanitize($value);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get a specific input value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function input($key, $default = null)
    {
        $data = self::all();
        return isset($data[$key]) ? $data[$key] : $default;
    }

    /**
     * Check if input exists
     * 
     * @param string $key
     * @return boolean
     */
    public static function has($key)
    {
        return array_key_exists($key, self::all());
    }

    /**
     * Get only specific inputs
     * 
     * @param array $keys
     * @return array
     */
    public static function only(array $keys)
    {
        $data = self::all();
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Get all inputs except specified ones
     * 
     * @param array $keys
     * @return array
     */
    public static function except(array $keys)
    {
        $data = self::all();
        return array_diff_key($data, array_flip($keys));
    }

    /**
     * Sanitize input data
     * 
     * @param mixed $data
     * @return mixed
     */
    protected static function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        
        // Convert special characters to HTML entities
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get files from the request
     * 
     * @param string $key
     * @return array|null
     */
    public static function file($key)
    {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    /**
     * Check if request has file
     * 
     * @param string $key
     * @return boolean
     */
    public static function hasFile($key)
    {
        $file = self::file($key);
        return $file && $file['error'] != UPLOAD_ERR_NO_FILE;
    }    /**
     * Check if request is AJAX
     * 
     * @return boolean
     */
    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}