<?php
/**
 * MapIt - Travel Destination Mapping Application
 * 
 * This is the main entry point of our application.
 * All requests are routed through this file.
 */

// Define the application start time for performance tracking
define('APP_START', microtime(true));

// Define the base path of the application
define('BASE_PATH', __DIR__ . '/..');

// Autoload dependencies from vendor directory
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// Include custom autoloader for our application
require BASE_PATH . '/app/Core/Autoloader.php';

// Register our custom autoloader
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Create and initialize application
$app = new App\Core\App();
$app->init();

// Run the application
$app->run();
