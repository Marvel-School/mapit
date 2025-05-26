<?php

namespace App\Core;

class Logger
{
    const INFO = 'INFO';
    const WARN = 'WARN';
    const ERROR = 'ERROR';
    const DEBUG = 'DEBUG';
    
    private static $logFile = '/var/www/html/storage/logs/app.log';
    private static $enabled = true;
    
    /**
     * Set log file path
     *
     * @param string $path
     */
    public static function setLogFile($path)
    {
        self::$logFile = $path;
    }
    
    /**
     * Enable or disable logging
     *
     * @param bool $enabled
     */
    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
    }
    
    /**
     * Log a message
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public static function log($level, $message, $context = [])
    {        if (!self::$enabled) return;
        
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $log = "[$date] [$level] $message$contextStr" . PHP_EOL;
        
        // Create log directory if it doesn't exist
        self::ensureLogDirectoryExists();
        
        file_put_contents(self::$logFile, $log, FILE_APPEND);
    }
    
    /**
     * Log info message
     *
     * @param string $message
     * @param array $context
     */
    public static function info($message, $context = [])
    {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * Log warning message
     *
     * @param string $message
     * @param array $context
     */
    public static function warn($message, $context = [])
    {
        self::log(self::WARN, $message, $context);
    }
    
    /**
     * Log error message
     *
     * @param string $message
     * @param array $context
     */
    public static function error($message, $context = [])
    {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Log debug message
     *
     * @param string $message
     * @param array $context
     */    public static function debug($message, $context = [])
    {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Ensure that the log directory exists
     * 
     * @return void
     */
    private static function ensureLogDirectoryExists()
    {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
}
