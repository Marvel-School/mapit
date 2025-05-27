<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/app.php';

// Check if user is trying to login
if ($_POST['action'] ?? '' === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        header('Location: /dashboard');
        exit;
    }
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    // Simulate dashboard functionality
    $destinationModel = new App\Models\Destination();
    $featured = $destinationModel->getFeatured(10);
    $userDestinations = $destinationModel->getByUser($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Test - Featured vs User Destinations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { border: 1px solid #ddd; padding: 15px; margin: 15px 0; }
        .featured { background: #fff5f0; }
        .user { background: #f0f5ff; }
        .destination { margin: 5px 0; padding: 5px; background: #f9f9f9; }
        form { margin: 20px 0; }
        input { margin: 5px; padding: 8px; }
    </style>
</head>
<body>
    <h1>Dashboard Test - Featured vs User Destinations</h1>
    
    <?php if (!$isLoggedIn): ?>
        <div class="section">
            <h3>Login Required</h3>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="text" name="username" placeholder="Username (use: admin)" required>
                <input type="password" name="password" placeholder="Password (use: admin)" required>
                <button type="submit">Login</button>
            </form>
            <p><em>Use username: admin, password: admin</em></p>
        </div>
    <?php else: ?>
        <div class="section">
            <h3>Logged in as: <?= htmlspecialchars($_SESSION['username']); ?></h3>
            <a href="?logout=1">Logout</a>
        </div>
        
        <div class="section featured">
            <h3>Featured Destinations (What Dashboard Map Should Show)</h3>
            <p><strong>Count:</strong> <?= count($featured); ?></p>
            <?php foreach ($featured as $dest): ?>
                <div class="destination">
                    <strong><?= htmlspecialchars($dest['name']); ?></strong> - 
                    <?= htmlspecialchars($dest['country']); ?> 
                    (<?= $dest['latitude']; ?>, <?= $dest['longitude']; ?>)
                    - Featured: <?= $dest['featured'] ? 'Yes' : 'No'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section user">
            <h3>User's Personal Destinations (What Was Previously Shown)</h3>
            <p><strong>Count:</strong> <?= count($userDestinations); ?></p>
            <?php if (empty($userDestinations)): ?>
                <p><em>User has no personal destinations.</em></p>
            <?php else: ?>
                <?php foreach ($userDestinations as $dest): ?>
                    <div class="destination">
                        <strong><?= htmlspecialchars($dest['name']); ?></strong> - 
                        <?= htmlspecialchars($dest['country']); ?> 
                        (<?= $dest['latitude']; ?>, <?= $dest['longitude']; ?>)
                        - Status: <?= $dest['trip_count'] > 0 ? 'Visited' : 'Wishlist'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h3>Verification</h3>
            <p><strong>✅ Dashboard should now show Featured destinations instead of User destinations</strong></p>
            <p>The map on the actual dashboard page should display the <?= count($featured); ?> featured destinations listed above, not the user's personal destinations.</p>
            <p><a href="/dashboard" target="_blank">Open Actual Dashboard →</a></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['logout'])): ?>
        <?php session_destroy(); ?>
        <script>window.location.reload();</script>
    <?php endif; ?>
</body>
</html>
