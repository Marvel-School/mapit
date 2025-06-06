<?php

/**
 * Define application routes
 * 
 * This is where you define all the routes for your application.
 * Each route consists of a URI pattern, a controller, and an action.
 */

// Health check endpoint for production monitoring
$this->get('health', 'Api\\HealthController', 'check');

// Home routes
$this->get('', 'HomeController', 'index');
$this->get('home', 'HomeController', 'index');
$this->get('about', 'HomeController', 'about');
$this->get('contact', 'HomeController', 'contact');
$this->post('contact', 'HomeController', 'processContact');

// Auth routes
$this->get('login', 'AuthController', 'login');
$this->post('login', 'AuthController', 'processLogin');
$this->get('register', 'AuthController', 'register');
$this->post('register', 'AuthController', 'processRegister');
$this->get('logout', 'AuthController', 'logout');
$this->get('forgot-password', 'AuthController', 'forgotPassword');
$this->post('forgot-password', 'AuthController', 'processForgotPassword');
$this->get('reset-password/{token}', 'AuthController', 'resetPassword');
$this->post('reset-password', 'AuthController', 'processResetPassword');

// Dashboard routes
$this->get('dashboard', 'DashboardController', 'index');
$this->get('profile', 'DashboardController', 'profile');
$this->post('profile', 'DashboardController', 'updateProfile');
$this->post('profile/delete-avatar', 'DashboardController', 'deleteAvatar');

// Destination routes
$this->get('destinations', 'DestinationController', 'index');
$this->get('destinations/create', 'DestinationController', 'create');
$this->post('destinations', 'DestinationController', 'store');
$this->get('destinations/{id}', 'DestinationController', 'show');
$this->get('destinations/{id}/edit', 'DestinationController', 'edit');
$this->post('destinations/{id}', 'DestinationController', 'update');
$this->post('destinations/{id}/delete', 'DestinationController', 'delete');

// Trip routes
$this->get('trips', 'TripController', 'index');
$this->get('trips/create', 'TripController', 'create');
$this->post('trips', 'TripController', 'store');
$this->get('trips/{id}', 'TripController', 'show');
$this->get('trips/{id}/edit', 'TripController', 'edit');
$this->post('trips/{id}', 'TripController', 'update');
$this->post('trips/{id}/delete', 'TripController', 'delete');
$this->post('trips/{id}/status', 'TripController', 'updateStatus');

// Badge routes
$this->get('badges', 'BadgeController', 'index');
$this->get('badges/{id}', 'BadgeController', 'show');
$this->post('badges/mark-notifications-read', 'BadgeController', 'markNotificationsRead');

// Public destination routes (no authentication required)
$this->get('map', 'PublicDestinationController', 'map');
$this->get('featured', 'PublicDestinationController', 'featured');
$this->get('destination/{id}', 'PublicDestinationController', 'show');

// Public API routes (no authentication required)
$this->get('api/debug/test', 'Api\DebugController', 'test');
$this->get('api/simple/destinations', 'Api\SimpleApiController', 'destinations');
$this->get('api/public/destinations', 'Api\PublicDestinationController', 'index');
$this->get('api/public/destinations/featured', 'Api\PublicDestinationController', 'featured');
$this->get('api/public/destinations/{id}', 'Api\PublicDestinationController', 'show');

// API routes
$this->get('api/health', 'Api\AuthController', 'healthCheck');
$this->get('api/debug/user-badges-schema', 'Api\AuthController', 'debugUserBadgesSchema');
$this->post('api/migrate/user-badges-schema', 'Api\AuthController', 'migrateUserBadgesSchema');
$this->post('api/auth/login', 'Api\AuthController', 'login');
$this->post('api/auth/register', 'Api\AuthController', 'register');

$this->get('api/destinations', 'Api\DestinationController', 'index');
$this->get('api/destinations/{id}', 'Api\DestinationController', 'show');
$this->post('api/destinations', 'Api\DestinationController', 'store');
$this->post('api/destinations/quick-create', 'Api\DestinationController', 'quickCreate');
$this->put('api/destinations/{id}', 'Api\DestinationController', 'update');
$this->delete('api/destinations/{id}', 'Api\DestinationController', 'delete');

$this->get('api/trips', 'Api\TripController', 'index');
$this->get('api/trips/{id}', 'Api\TripController', 'show');
$this->post('api/trips', 'Api\TripController', 'store');
$this->put('api/trips/{id}', 'Api\TripController', 'update');
$this->delete('api/trips/{id}', 'Api\TripController', 'delete');
$this->post('api/trips/{id}/start', 'Api\TripController', 'start');
$this->post('api/trips/{id}/complete', 'Api\TripController', 'complete');

$this->get('api/badges', 'Api\BadgeController', 'index');
$this->get('api/badges/{id}', 'Api\BadgeController', 'show');

// Admin routes
$this->get('admin', 'Admin\DashboardController', 'index');
$this->get('admin/dashboard', 'Admin\DashboardController', 'index');
$this->get('admin/users', 'Admin\UserController', 'index');
$this->get('admin/users/{id}', 'Admin\UserController', 'show');
$this->get('admin/users/{id}/edit', 'Admin\UserController', 'edit');
$this->post('admin/users/{id}', 'Admin\UserController', 'update');
$this->post('admin/users/{id}/delete', 'Admin\UserController', 'delete');

$this->get('admin/destinations', 'Admin\DestinationController', 'index');
$this->get('admin/destinations/{id}', 'Admin\DestinationController', 'show');
$this->get('admin/destinations/{id}/edit', 'Admin\DestinationController', 'edit');
$this->post('admin/destinations/{id}', 'Admin\DestinationController', 'update');

// DEBUG ROUTE - TEMPORARY FOR IMAGE UPLOAD DEBUGGING
$this->post('admin/destinations/{id}/debug-update', 'Admin\DebugDestinationController', 'debugUpdate');

// TEMPORARY ROUTE - RELAXED SECURITY FOR COINCIDENTAL BINARY PATTERNS
$this->post('admin/destinations/{id}/temp-update', 'Admin\TempDestinationController', 'tempUpdate');

$this->post('admin/destinations/{id}/approve', 'Admin\DestinationController', 'approve');
$this->post('admin/destinations/{id}/reject', 'Admin\DestinationController', 'reject');
$this->post('admin/destinations/{id}/status', 'Admin\DestinationController', 'status');
$this->delete('admin/destinations/{id}', 'Admin\DestinationController', 'delete');

// Admin Contact/Support Management routes
$this->get('admin/contacts', 'Admin\ContactController', 'index');
$this->get('admin/contacts/{id}', 'Admin\ContactController', 'show');
$this->post('admin/contacts/{id}/status', 'Admin\ContactController', 'updateStatus');
$this->post('admin/contacts/{id}/notes', 'Admin\ContactController', 'addNotes');
$this->post('admin/contacts/bulk', 'Admin\ContactController', 'bulkAction');
$this->get('admin/contacts/export', 'Admin\ContactController', 'export');

$this->get('admin/logs', 'Admin\LogController', 'index');
$this->get('admin/logs/{id}/data', 'Admin\LogController', 'data');
$this->post('admin/logs/clear', 'Admin\LogController', 'clear');
$this->delete('admin/logs/{id}', 'Admin\LogController', 'delete');

// Debug Routes (disabled for production)
// $this->post('api/debug/log', 'DebugController', 'log');
// $this->get('api/debug/view-logs', 'DebugController', 'viewLogs');
// $this->get('api/debug/stats', 'DebugController', 'stats');
// $this->get('admin/debug', 'DebugController', 'adminPage');
$this->get('api/debug/environment', 'Api\EnvironmentController', 'getInfo');

// Test routes for contact system
$this->get('api/test/contacts', 'Api\SimpleApiController', 'testContacts');

// Test routes for contact admin interface (no auth)
$this->get('test/contacts', 'TestContactController', 'testContacts');
$this->get('test/contacts/{id}', 'TestContactController', 'testContact');

// Test routes for contact admin interface (no auth)
$this->get('test/contacts', 'TestContactController', 'testContacts');
$this->get('test/contacts/{id}', 'TestContactController', 'testContact');