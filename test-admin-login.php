<?php
// Test admin login and access
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Test Login</title>
</head>
<body>
    <h1>Test Admin Login</h1>
    
    <?php if (!isset($_POST['test_login'])): ?>
    <form method="POST">
        <p>Click to test admin login:</p>
        <button type="submit" name="test_login" value="1">Test Admin Login</button>
    </form>
    <?php else:
        // Test the login process
        session_start();
        
        echo "<h2>Testing Admin Login Process</h2>";
        
        // Simulate login POST request
        $_POST['username'] = 'admin';
        $_POST['password'] = 'admin123';
        
        echo "<p>Attempting login with:</p>";
        echo "<ul>";
        echo "<li>Username: " . htmlspecialchars($_POST['username']) . "</li>";
        echo "<li>Password: [hidden]</li>";
        echo "</ul>";
        
        // Make HTTP request to login endpoint
        $loginUrl = 'http://localhost/login';
        $postData = http_build_query([
            'username' => $_POST['username'],
            'password' => $_POST['password']
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $postData
            ]
        ]);
        
        echo "<p>Making login request...</p>";
        $result = file_get_contents($loginUrl, false, $context);
        
        if ($result !== false) {
            echo "<p>✓ Login request completed</p>";
            
            // Check response headers for redirect
            if (isset($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (strpos($header, 'Location:') === 0) {
                        echo "<p>✓ Redirect detected: " . htmlspecialchars($header) . "</p>";
                    }
                    if (strpos($header, 'Set-Cookie:') === 0) {
                        echo "<p>✓ Cookie set: " . htmlspecialchars($header) . "</p>";
                    }
                }
            }
            
            // Try to access admin dashboard
            echo "<h3>Testing Admin Dashboard Access</h3>";
            $adminUrl = 'http://localhost/admin/dashboard';
            $adminResult = file_get_contents($adminUrl, false);
            
            if ($adminResult !== false) {
                if (strpos($adminResult, 'Dashboard') !== false) {
                    echo "<p>✓ Admin dashboard accessible</p>";
                } else {
                    echo "<p>⚠ Admin dashboard response received but may need authentication</p>";
                }
            } else {
                echo "<p>✗ Admin dashboard not accessible</p>";
            }
            
        } else {
            echo "<p>✗ Login request failed</p>";
        }
        
        echo "<hr>";
        echo "<p><a href='?'>Try Again</a></p>";
    endif; ?>
</body>
</html>
