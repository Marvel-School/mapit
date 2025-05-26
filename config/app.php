<?php

return [
    // Application settings
    'name' => 'MapIt',
    'env' => 'development', // 'production', 'development'
    'url' => 'http://localhost',
    'timezone' => 'UTC',
    'display_errors' => true,    // Database configuration
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'mysql',
        'dbname' => $_ENV['DB_DATABASE'] ?? 'mapit',
        'username' => $_ENV['DB_USERNAME'] ?? 'mapit_user',
        'password' => $_ENV['DB_PASSWORD'] ?? 'mapit_password',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    ],
      // Google Maps API configuration
    'google_maps' => [
        'api_key' => $_ENV['GOOGLE_MAPS_API_KEY'] ?? ''
    ],
    
    // Session configuration
    'session' => [
        'lifetime' => 120, // in minutes
        'secure' => false, // true in production
        'http_only' => true,
        'same_site' => 'lax' // 'strict', 'lax', 'none'
    ],
    
    // Mail configuration
    'mail' => [
        'from_address' => 'noreply@mapit.com',
        'from_name' => 'MapIt Application'
    ]
];