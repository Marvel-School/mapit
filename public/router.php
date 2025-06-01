<?php
/**
 * Router script for PHP development server
 * This file handles URL rewriting for the built-in PHP development server
 */

// Get the request URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the file exists and is not index.php, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Let the built-in server handle static files
}

// Otherwise, route through index.php
$_SERVER['REQUEST_URI'] = $uri;
require_once __DIR__ . '/index.php';
?>
