<?php
/**
 * Simple test script to verify the interactive map API functionality
 */

echo "Testing MapIt Interactive Map API\n";
echo "=================================\n\n";

// Test 1: Check if routes are properly configured
echo "1. Testing route configuration...\n";
$routes_content = file_get_contents(__DIR__ . '/config/routes.php');
if (strpos($routes_content, 'api/destinations/quick-create') !== false) {
    echo "   ✓ Quick create route found in routes.php\n";
} else {
    echo "   ✗ Quick create route NOT found in routes.php\n";
}

// Test 2: Check if API controller exists
echo "\n2. Testing API controller...\n";
$api_controller_path = __DIR__ . '/app/Controllers/Api/DestinationController.php';
if (file_exists($api_controller_path)) {
    echo "   ✓ API DestinationController exists\n";
    
    $controller_content = file_get_contents($api_controller_path);
    if (strpos($controller_content, 'quickCreate') !== false) {
        echo "   ✓ quickCreate method found in API controller\n";
    } else {
        echo "   ✗ quickCreate method NOT found in API controller\n";
    }
} else {
    echo "   ✗ API DestinationController does NOT exist\n";
}

// Test 3: Check if main.js has interactive functions
echo "\n3. Testing JavaScript functions...\n";
$main_js_path = __DIR__ . '/public/js/main.js';
if (file_exists($main_js_path)) {
    echo "   ✓ main.js exists\n";
    
    $js_content = file_get_contents($main_js_path);
    
    $functions_to_check = [
        'enableInteractiveMapClicking',
        'handleMapClick', 
        'initializeQuickDestinationCreate',
        'handleQuickDestinationSave'
    ];
    
    foreach ($functions_to_check as $function) {
        if (strpos($js_content, $function) !== false) {
            echo "   ✓ Function $function found\n";
        } else {
            echo "   ✗ Function $function NOT found\n";
        }
    }
} else {
    echo "   ✗ main.js does NOT exist\n";
}

// Test 4: Check if modals are added to views
echo "\n4. Testing view modifications...\n";
$views_to_check = [
    'app/Views/dashboard/index.php',
    'app/Views/destinations/index.php'
];

foreach ($views_to_check as $view_path) {
    $full_path = __DIR__ . '/' . $view_path;
    if (file_exists($full_path)) {
        echo "   ✓ $view_path exists\n";
        
        $view_content = file_get_contents($full_path);
        if (strpos($view_content, 'quickAddDestinationModal') !== false) {
            echo "   ✓ Quick add modal found in $view_path\n";
        } else {
            echo "   ✗ Quick add modal NOT found in $view_path\n";
        }
    } else {
        echo "   ✗ $view_path does NOT exist\n";
    }
}

echo "\n5. Testing database connectivity...\n";
echo "   ⚠ Database connectivity test skipped (requires proper setup)\n";

echo "\nTest completed!\n";
echo "\nTo test the interactive map feature:\n";
echo "1. Navigate to http://localhost:8000\n";
echo "2. Log in with valid credentials\n";
echo "3. Go to Dashboard or Destinations page\n";
echo "4. Click anywhere on the map\n";
echo "5. Fill out the quick-add destination modal\n";
echo "6. Save the destination\n";
?>
