<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Logger;

class EnvironmentController extends Controller
{
    /**
     * Return environment information for client-side detection
     */
    public function getInfo()
    {
        // Check if we're running in a Docker container
        $isDocker = $this->isDockerEnvironment();
        
        // Get server IP addresses
        $serverIps = $this->getServerIpAddresses();
          // Get hostname information
        $hostname = gethostname();
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        
        // Return data as JSON
        return Response::json([
            'isDocker' => $isDocker,
            'serverIps' => $serverIps,
            'hostname' => $hostname,
            'serverName' => $serverName,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'remoteAddr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'containerMounted' => file_exists('/.dockerenv')
        ]);
    }

    /**
     * Check if we're running in a Docker container
     *
     * @return bool
     */
    private function isDockerEnvironment()
    {
        // Check for .dockerenv file
        if (file_exists('/.dockerenv')) {
            return true;
        }
        
        // Check for Docker in cgroups
        if (file_exists('/proc/self/cgroup')) {
            $cgroup = file_get_contents('/proc/self/cgroup');
            if (strpos($cgroup, 'docker') !== false) {
                return true;
            }
        }
        
        // Check hostname (often contains container ID)
        $hostname = gethostname();
        if (strlen($hostname) === 12 && ctype_xdigit($hostname)) {
            return true;
        }
        
        // Check environment variables often set in Docker
        if (getenv('DOCKER_HOST') || getenv('DOCKER_CONTAINER')) {
            return true;
        }
        
        // Check common Docker networking patterns
        $serverIps = $this->getServerIpAddresses();
        foreach ($serverIps as $ip) {
            // Docker internal network typically uses 172.17.x.x
            if (strpos($ip, '172.17.') === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all server IP addresses
     *
     * @return array
     */
    private function getServerIpAddresses()
    {
        $ips = [];
        
        // Try to get IPs from system tools
        if (function_exists('shell_exec')) {
            $command = PHP_OS === 'WINNT' ? 'ipconfig' : 'ip addr show';
            $output = @shell_exec($command);
            
            // Extract IPs from output
            if ($output) {
                preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $output, $matches);
                if (!empty($matches[0])) {
                    $ips = array_merge($ips, $matches[0]);
                }
            }
        }
        
        // Add server IP address from $_SERVER if available
        if (!empty($_SERVER['SERVER_ADDR'])) {
            $ips[] = $_SERVER['SERVER_ADDR'];
        }
        
        // Use hostname as fallback
        if (empty($ips)) {
            try {
                $ips[] = gethostbyname(gethostname());
            } catch (\Exception $e) {
                // Ignore failures
            }
        }
        
        return array_unique($ips);
    }
}
