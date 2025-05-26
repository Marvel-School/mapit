<?php

namespace App\Core;

/**
 * MapsLogger - Special logger for Maps API related issues
 * 
 * This class extends the main Logger functionality to provide
 * dedicated logging for Google Maps API issues.
 */
class MapsLogger
{
    /**
     * Log directory
     * 
     * @var string
     */
    private static $logDir;
    
    /**
     * Log file path
     * 
     * @var string
     */
    private static $logFile;
    
    /**
     * Initialize the logger
     * 
     * @return void
     */
    public static function init()
    {
        self::$logDir = dirname(dirname(__DIR__)) . '/storage/logs';
        self::$logFile = self::$logDir . '/maps_api.log';
        
        // Create log directory if it doesn't exist
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
    }
    
    /**
     * Log an information message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info($message, array $context = [])
    {
        self::writeLog('INFO', $message, $context);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error($message, array $context = [])
    {
        self::writeLog('ERROR', $message, $context);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warn($message, array $context = [])
    {
        self::writeLog('WARNING', $message, $context);
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug($message, array $context = [])
    {
        self::writeLog('DEBUG', $message, $context);
    }
    
    /**
     * Write log to file
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private static function writeLog($level, $message, array $context = [])
    {
        // Initialize logger if not already initialized
        if (!isset(self::$logFile)) {
            self::init();
        }
        
        // Format date for logging
        $date = date('Y-m-d H:i:s');
        
        // Format context data
        $contextString = empty($context) ? '' : ' ' . json_encode($context);
        
        // Format message
        $logMessage = "[$date] [$level] $message$contextString" . PHP_EOL;
        
        // Write to file
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Log API key status
     * 
     * @param string|null $apiKey
     * @param string $source
     * @return void
     */
    public static function logApiKeyStatus($apiKey, $source = 'unknown')
    {
        if (!$apiKey) {
            self::error("Maps API Key is missing or empty", ['source' => $source]);
            return;
        }
        
        // Mask most of the key for security
        $maskedKey = substr($apiKey, 0, 5) . '...' . substr($apiKey, -5);
        
        self::info("Maps API Key loaded", [
            'key' => $maskedKey,
            'source' => $source,
            'length' => strlen($apiKey)
        ]);
    }
    
    /**
     * Log the API key and complete environment context
     * 
     * @param string $note
     * @return void
     */
    public static function logEnvironmentContext($note = '')
    {
        // Get API key from environment and config
        $envApiKey = getenv('GOOGLE_MAPS_API_KEY') ?: $_ENV['GOOGLE_MAPS_API_KEY'] ?? 'not found';
        
        try {
            $config = require dirname(dirname(__DIR__)) . '/config/app.php';
            $configApiKey = $config['google_maps']['api_key'] ?? 'not found';
        } catch (\Exception $e) {
            $configApiKey = 'error loading config: ' . $e->getMessage();
        }
        
        // Check if we're in Docker environment
        $isDocker = file_exists('/.dockerenv');
        
        // Collect server information
        $serverInfo = [
            'env_key' => substr($envApiKey, 0, 5) . '...' . substr($envApiKey, -5),
            'config_key' => substr($configApiKey, 0, 5) . '...' . substr($configApiKey, -5),
            'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'docker' => $isDocker ? 'yes' : 'no',
            'note' => $note
        ];
        
        self::info("Environment context for Maps API", $serverInfo);
    }
    
    /**
     * Check Google Maps API key loading and validate
     * 
     * @return array
     */
    public static function validateMapsApiKey()
    {
        // Get API key from different sources
        $envApiKey = getenv('GOOGLE_MAPS_API_KEY') ?: $_ENV['GOOGLE_MAPS_API_KEY'] ?? null;
        
        try {
            $config = require dirname(dirname(__DIR__)) . '/config/app.php';
            $configApiKey = $config['google_maps']['api_key'] ?? null;
        } catch (\Exception $e) {
            $configApiKey = null;
            self::error("Failed to load config: " . $e->getMessage());
        }
        
        // Log results
        if ($envApiKey) {
            self::logApiKeyStatus($envApiKey, 'environment');
        } else {
            self::error("API key not found in environment variables");
        }
        
        if ($configApiKey) {
            self::logApiKeyStatus($configApiKey, 'config');
        } else {
            self::error("API key not found in config file");
        }
        
        // Compare keys
        if ($envApiKey && $configApiKey && $envApiKey !== $configApiKey) {
            self::warn("API key mismatch between environment and config");
        }
        
        // Return validation results
        return [
            'env_key_exists' => !empty($envApiKey),
            'config_key_exists' => !empty($configApiKey),
            'keys_match' => ($envApiKey && $configApiKey) ? ($envApiKey === $configApiKey) : false,
            'final_key' => $configApiKey ?: $envApiKey
        ];
    }
    
    /**
     * Test direct API connection
     * 
     * @param string $apiKey
     * @return array
     */
    public static function testApiConnection($apiKey)
    {
        if (empty($apiKey)) {
            self::error("Cannot test API connection - no API key provided");
            return [
                'success' => false,
                'error' => 'No API key provided'
            ];
        }
        
        // Test the Geocoding API
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=New+York&key=$apiKey";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Decode JSON response
        $data = json_decode($response, true);
        
        // Check response status
        $status = $data['status'] ?? 'UNKNOWN_ERROR';
        $errorMessage = $data['error_message'] ?? '';
        
        $result = [
            'success' => $httpCode === 200 && $status === 'OK',
            'http_code' => $httpCode,
            'api_status' => $status,
            'error' => !empty($error) ? $error : $errorMessage
        ];
        
        // Log the result
        if ($result['success']) {
            self::info("API connection test successful", $result);
        } else {
            self::error("API connection test failed", $result);
        }
        
        return $result;
    }
}
