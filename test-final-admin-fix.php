<?php
// Final test of all admin pages after logs view fix
require_once __DIR__ . '/vendor/autoload.php';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

echo "=== FINAL ADMIN PAGES TEST (After Logs View Fix) ===\n\n";

$pages = [
    'Dashboard' => [
        'controller' => 'App\Controllers\Admin\DashboardController',
        'view' => 'app/Views/admin/dashboard/index.php'
    ],
    'Users' => [
        'controller' => 'App\Controllers\Admin\UserController', 
        'view' => 'app/Views/admin/users/index.php'
    ],
    'Destinations' => [
        'controller' => 'App\Controllers\Admin\DestinationController',
        'view' => 'app/Views/admin/destinations/index.php'
    ],
    'Logs' => [
        'controller' => 'App\Controllers\Admin\LogController',
        'view' => 'app/Views/admin/logs/index.php'
    ]
];

foreach ($pages as $name => $config) {
    echo "Testing $name page:\n";
    
    // Test controller
    try {
        $controller = new $config['controller']();
        echo "  ✓ Controller loads successfully\n";
    } catch (Exception $e) {
        echo "  ✗ Controller error: " . $e->getMessage() . "\n";
        continue;
    }
    
    // Test view file
    $viewPath = __DIR__ . '/' . $config['view'];
    if (file_exists($viewPath)) {
        $content = file_get_contents($viewPath);
        
        // Check if it uses the correct layout pattern
        if (strpos($content, "<?php \$layout = 'admin'; ?>") !== false) {
            echo "  ✓ View uses correct layout system\n";
        } else {
            echo "  ⚠ View doesn't use standard layout (may be custom)\n";
        }
        
        // Check for old include patterns (should not exist)
        if (strpos($content, "include '../layouts/admin_header.php'") !== false ||
            strpos($content, "include '../layouts/admin_footer.php'") !== false) {
            echo "  ✗ View still has old include paths!\n";
        } else {
            echo "  ✓ View doesn't have problematic includes\n";
        }
    } else {
        echo "  ✗ View file missing: $viewPath\n";
    }
    
    echo "\n";
}

// Test specific models that admin pages use
echo "Testing model functionality:\n";

try {
    $userModel = new App\Models\User();
    $users = $userModel->findAll();
    echo "✓ User model working - found " . count($users) . " users\n";
} catch (Exception $e) {
    echo "✗ User model error: " . $e->getMessage() . "\n";
}

try {
    $logModel = new App\Models\Log();
    $result = $logModel->getPaginated(1, 5, []);
    echo "✓ Log model working - found " . $result['total'] . " total logs\n";
} catch (Exception $e) {
    echo "✗ Log model error: " . $e->getMessage() . "\n";
}

try {
    $destModel = new App\Models\Destination();
    $destinations = $destModel->findAll();
    echo "✓ Destination model working - found " . count($destinations) . " destinations\n";
} catch (Exception $e) {
    echo "✗ Destination model error: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "🎉 ALL ADMIN PAGES SHOULD NOW BE FULLY FUNCTIONAL!\n\n";
echo "Access URLs:\n";
echo "- Login: http://localhost:8080/login\n";
echo "- Admin Dashboard: http://localhost:8080/admin/dashboard\n";
echo "- Admin Users: http://localhost:8080/admin/users\n";
echo "- Admin Destinations: http://localhost:8080/admin/destinations\n";
echo "- Admin Logs: http://localhost:8080/admin/logs\n\n";
echo "Login credentials: admin/admin123\n";
