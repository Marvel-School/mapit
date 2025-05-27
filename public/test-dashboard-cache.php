<?php
// Test page to verify cache busting is working for main.js
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cache Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .test-button { 
            background-color: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            margin: 5px;
        }
        .test-button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h1>Dashboard Cache Busting Test</h1>
    
    <div class="info">
        <h3>Cache Busting Status</h3>
        <?php
        $cacheBustFile = __DIR__ . '/cache-bust.txt';
        if (file_exists($cacheBustFile)) {
            $cacheBust = trim(file_get_contents($cacheBustFile));
            echo "<p><strong>Current cache-bust version:</strong> $cacheBust</p>";
            echo "<p><strong>main.js URL will be:</strong> /js/main.js?v=$cacheBust</p>";
        } else {
            echo "<p class='error'>Cache-bust file not found!</p>";
        }
        ?>
    </div>

    <div class="info">
        <h3>Test Instructions</h3>
        <ol>
            <li>Click the button below to test the fixed dashboard</li>
            <li>On the map, try clicking on a <strong>featured destination marker</strong> (should show info window)</li>
            <li>Try clicking on an <strong>empty area of the map</strong> (should show quick-add modal)</li>
            <li>If both work correctly, the fix is successful!</li>
        </ol>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <button class="test-button" onclick="window.open('/dashboard', '_blank')">
            🗺️ Test Dashboard (New Tab)
        </button>
        <button class="test-button" onclick="updateCacheBust()">
            🔄 Force Cache Refresh
        </button>
    </div>

    <div class="info">
        <h3>Expected Behavior</h3>
        <ul>
            <li><strong>✅ Featured Destination Markers:</strong> Should show info windows with destination details</li>
            <li><strong>✅ Empty Map Areas:</strong> Should show quick-add destination modal</li>
            <li><strong>❌ Wrong Behavior:</strong> Featured markers showing quick-add modal (this was the bug)</li>
        </ul>
    </div>

    <div id="test-results"></div>

    <script>
        function updateCacheBust() {
            const timestamp = new Date().toISOString().replace(/[-:]/g, '').replace(/\..+/, '').replace('T', '_');
            
            fetch('/test-dashboard-cache.php?action=update&v=' + timestamp)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('test-results').innerHTML = 
                        '<div class="success">Cache-bust updated! Please refresh the dashboard page.</div>';
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    document.getElementById('test-results').innerHTML = 
                        '<div class="error">Error updating cache-bust: ' + error + '</div>';
                });
        }

        // Check if this is an update request
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['v'])) {
            $newVersion = preg_replace('/[^0-9_]/', '', $_GET['v']);
            if ($newVersion) {
                file_put_contents(__DIR__ . '/cache-bust.txt', $newVersion);
                echo "console.log('Cache-bust updated to: $newVersion');";
            }
        }
        ?>
    </script>
</body>
</html>
