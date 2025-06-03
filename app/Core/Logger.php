<?php

namespace App\Core;

class Logger //Vinden jullie dit echt nodig? Kan je het volledig uitleggen?
{
    const INFO = 'INFO';
    const WARN = 'WARN';
    const ERROR = 'ERROR';
    const DEBUG = 'DEBUG';
    const CRITICAL = 'CRITICAL';
    
    private static $logFile = '/var/www/html/storage/logs/app.log';
    private static $enabled = true;
    private static $maxLogSize = 52428800; // 50MB
    
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
     * Set maximum log file size before rotation
     *
     * @param int $size Size in bytes
     */
    public static function setMaxLogSize($size)
    {
        self::$maxLogSize = $size;
    }
    
    /**
     * Log a message with enhanced context
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public static function log($level, $message, $context = [])
    {
        if (!self::$enabled) return;
        
        $date = date('Y-m-d H:i:s');
        $microtime = sprintf('%06d', (microtime(true) - floor(microtime(true))) * 1000000);
        $timestamp = $date . '.' . $microtime;
        
        // Enhance context with additional information
        $enhancedContext = array_merge($context, [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200), // Truncate long user agents
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'session_id' => session_id() ?: 'no-session',
            'memory_usage' => self::formatBytes(memory_get_usage(true)),
            'peak_memory' => self::formatBytes(memory_get_peak_usage(true)),
            'pid' => getmypid(),
            'trace_id' => self::getTraceId()
        ]);
        
        $contextStr = !empty($enhancedContext) ? ' ' . json_encode($enhancedContext, JSON_UNESCAPED_SLASHES) : '';
        $log = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        // Create log directory if it doesn't exist
        self::ensureLogDirectoryExists();
        
        // Rotate log if it gets too large
        self::rotateLogIfNeeded();
        
        // Write to log file with file locking
        file_put_contents(self::$logFile, $log, FILE_APPEND | LOCK_EX);
        
        // Also log critical errors to error_log
        if ($level === self::CRITICAL || $level === self::ERROR) {
            error_log("MapIt [$level]: $message");
        }
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
     */
    public static function debug($message, $context = [])
    {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log critical message
     *
     * @param string $message
     * @param array $context
     */
    public static function critical($message, $context = [])
    {
        self::log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log exception with full stack trace
     *
     * @param \Exception|\Throwable $exception
     * @param string $level
     * @param array $context
     */
    public static function exception($exception, $level = self::ERROR, $context = [])
    {
        $context = array_merge($context, [
            'exception_class' => get_class($exception),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_trace' => $exception->getTraceAsString()
        ]);
        
        self::log($level, $exception->getMessage(), $context);
    }
    
    /**
     * Log performance metrics
     *
     * @param string $operation
     * @param float $startTime
     * @param array $context
     */
    public static function performance($operation, $startTime, $context = [])
    {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        
        $context = array_merge($context, [
            'operation' => $operation,
            'duration_ms' => $duration,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        
        $level = $duration > 1000 ? self::WARN : self::INFO; // Warn if over 1 second
        self::log($level, "Performance: $operation took {$duration}ms", $context);
    }
    
    /**
     * Log API request/response
     *
     * @param string $method
     * @param string $url
     * @param array $requestData
     * @param array $responseData
     * @param int $statusCode
     * @param float $duration
     */
    public static function apiCall($method, $url, $requestData = [], $responseData = [], $statusCode = null, $duration = null)
    {
        $context = [
            'api_method' => $method,
            'api_url' => $url,
            'api_request' => $requestData,
            'api_response' => $responseData,
            'api_status_code' => $statusCode,
            'api_duration_ms' => $duration ? round($duration * 1000, 2) : null
        ];
        
        $level = ($statusCode >= 400) ? self::ERROR : self::INFO;
        $message = "API Call: $method $url";
        if ($statusCode) {
            $message .= " (Status: $statusCode)";
        }
        
        self::log($level, $message, $context);
    }
    
    /**
     * Log database query
     *
     * @param string $query
     * @param array $params
     * @param float $duration
     * @param int $affectedRows
     */
    public static function dbQuery($query, $params = [], $duration = null, $affectedRows = null)
    {
        $context = [
            'db_query' => $query,
            'db_params' => $params,
            'db_duration_ms' => $duration ? round($duration * 1000, 2) : null,
            'db_affected_rows' => $affectedRows
        ];
        
        $level = ($duration && $duration > 1) ? self::WARN : self::DEBUG; // Warn if over 1 second
        self::log($level, "Database Query", $context);
    }
    
    /**
     * Log user action
     *
     * @param string $action
     * @param string $userId
     * @param array $context
     */
    public static function userAction($action, $userId = null, $context = [])
    {
        $context = array_merge($context, [
            'user_action' => $action,
            'user_id' => $userId,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null
        ]);
        
        self::log(self::INFO, "User Action: $action", $context);
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
    
    /**
     * Rotate log file if it exceeds maximum size
     * 
     * @return void
     */
    private static function rotateLogIfNeeded()
    {
        if (!file_exists(self::$logFile)) {
            return;
        }
        
        if (filesize(self::$logFile) > self::$maxLogSize) {
            $rotatedFile = self::$logFile . '.' . date('Y-m-d-H-i-s');
            rename(self::$logFile, $rotatedFile);
            
            // Compress old log file
            if (function_exists('gzencode')) {
                $content = file_get_contents($rotatedFile);
                file_put_contents($rotatedFile . '.gz', gzencode($content));
                unlink($rotatedFile);
            }
        }
    }
    
    /**
     * Format bytes into human readable format
     *
     * @param int $bytes
     * @return string
     */
    private static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Generate or retrieve trace ID for request correlation
     *
     * @return string
     */
    private static function getTraceId()
    {
        static $traceId = null;
        
        if ($traceId === null) {
            $traceId = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        }
        
        return $traceId;
    }
    
    /**
     * Get recent log entries
     *
     * @param int $lines Number of lines to retrieve
     * @param string $level Filter by log level
     * @return array
     */
    public static function getRecentLogs($lines = 100, $level = null)
    {
        if (!file_exists(self::$logFile)) {
            return [];
        }
        
        $command = "tail -n $lines " . escapeshellarg(self::$logFile);
        $output = shell_exec($command);
        
        if (!$output) {
            return [];
        }
        
        $logLines = array_filter(explode("\n", $output));
        $logs = [];
        
        foreach ($logLines as $line) {
            if (preg_match('/^\[([\d\-: .]+)\] \[(\w+)\] (.+)$/', $line, $matches)) {
                $logLevel = $matches[2];
                
                // Filter by level if specified
                if ($level && $logLevel !== $level) {
                    continue;
                }
                
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => $logLevel,
                    'message' => $matches[3]
                ];
            }
        }
        
        return array_reverse($logs); // Most recent first
    }
    
    /**
     * Clear log file
     *
     * @return bool
     */
    public static function clearLogs()
    {
        if (file_exists(self::$logFile)) {
            return file_put_contents(self::$logFile, '') !== false;
        }
        return true;
    }
}
