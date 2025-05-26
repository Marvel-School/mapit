<?php
// Simple test to verify Google Maps API key is available in meta tag
// This file goes through the application's routing system

require_once '../app/Core/Autoloader.php';

// Bootstrap the application
$config = require_once '../config/app.php';

// Initialize controller to get common view data
require_once '../app/Core/Controller.php';

class TestController extends \App\Core\Controller {
    public function testApiKey() {
        $viewData = $this->getCommonViewData();
        return $viewData;
    }
}

$testController = new TestController();
$viewData = $testController->testApiKey();

$apiKey = $viewData['googleMapsApiKey'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Maps API Key Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .test-result { padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        code { background-color: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
    </style>
    <!-- Google Maps API Key for JS Fallback -->
    <meta name="google-maps-api-key" content="<?= htmlspecialchars($apiKey); ?>">
</head>
<body>
    <h1>Google Maps API Key Test</h1>
    
    <div class="test-result <?= !empty($apiKey) ? 'success' : 'error' ?>">
        <h3>PHP Backend Test</h3>
        <p><strong>API Key from Controller:</strong> 
            <?php if (!empty($apiKey)): ?>
                <code><?= htmlspecialchars(substr($apiKey, 0, 10)) ?>...</code> ✅
                <br><small>Length: <?= strlen($apiKey) ?> characters</small>
            <?php else: ?>
                <code>EMPTY</code> ❌
            <?php endif; ?>
        </p>
    </div>
    
    <div class="test-result info" id="meta-test">
        <h3>Meta Tag Test</h3>
        <p><strong>API Key from Meta Tag:</strong> <span id="meta-key">Testing...</span></p>
    </div>
    
    <div class="test-result info" id="js-test">
        <h3>JavaScript Function Test</h3>
        <p><strong>getGoogleMapsApiKey() Result:</strong> <span id="js-key">Testing...</span></p>
    </div>
    
    <div class="test-result info" id="maps-test">
        <h3>Google Maps API Load Test</h3>
        <p><strong>Maps API Status:</strong> <span id="maps-status">Loading...</span></p>
        <div id="test-map" style="height: 200px; width: 100%; border: 1px solid #ddd; margin-top: 10px;"></div>
    </div>
    
    <script>
        // Test 1: Check meta tag
        function testMetaTag() {
            const metaTag = document.querySelector('meta[name="google-maps-api-key"]');
            const metaKeySpan = document.getElementById('meta-key');
            const metaTestDiv = document.getElementById('meta-test');
            
            if (metaTag && metaTag.content) {
                const key = metaTag.content;
                metaKeySpan.innerHTML = `<code>${key.substring(0, 10)}...</code> ✅`;
                metaTestDiv.className = 'test-result success';
                return key;
            } else {
                metaKeySpan.innerHTML = '<code>NOT FOUND</code> ❌';
                metaTestDiv.className = 'test-result error';
                return null;
            }
        }        // Test 2: Check if getGoogleMapsApiKey function works (from main.js)
        function testJSFunction() {
            const jsKeySpan = document.getElementById('js-key');
            const jsTestDiv = document.getElementById('js-test');
            
            console.log('Testing getGoogleMapsApiKey function...');
            console.log('typeof getGoogleMapsApiKey:', typeof getGoogleMapsApiKey);
            console.log('window.getGoogleMapsApiKey:', window.getGoogleMapsApiKey);
            console.log('All window functions:', Object.getOwnPropertyNames(window).filter(name => typeof window[name] === 'function' && name.includes('google')));
            
            // Wait a bit for main.js to load
            setTimeout(() => {
                console.log('After timeout - typeof getGoogleMapsApiKey:', typeof getGoogleMapsApiKey);
                
                if (typeof getGoogleMapsApiKey === 'function') {
                    const key = getGoogleMapsApiKey();
                    console.log('Function found, returned key:', key ? key.substring(0, 10) + '...' : 'null');
                    if (key) {
                        jsKeySpan.innerHTML = `<code>${key.substring(0, 10)}...</code> ✅`;
                        jsTestDiv.className = 'test-result success';
                    } else {
                        jsKeySpan.innerHTML = '<code>EMPTY RETURN</code> ❌';
                        jsTestDiv.className = 'test-result error';
                    }
                } else {
                    console.log('Function not found. Checking if script loaded...');
                    console.log('Script elements:', document.querySelectorAll('script[src*="main.js"]'));
                    
                    // Try to manually define the function as a test
                    window.getGoogleMapsApiKey = function() {
                        const metaTag = document.querySelector('meta[name="google-maps-api-key"]');
                        return metaTag ? metaTag.getAttribute('content') : null;
                    };
                    
                    const key = window.getGoogleMapsApiKey();
                    if (key) {
                        jsKeySpan.innerHTML = `<code>${key.substring(0, 10)}...</code> ✅ (manually defined)`;
                        jsTestDiv.className = 'test-result success';
                    } else {
                        jsKeySpan.innerHTML = '<code>FUNCTION NOT FOUND</code> ❌';
                        jsTestDiv.className = 'test-result error';
                    }
                }
            }, 3000); // Increased wait time
        }
        
        // Test 3: Try to load Google Maps
        function testGoogleMaps() {
            const mapsStatusSpan = document.getElementById('maps-status');
            const mapsTestDiv = document.getElementById('maps-test');
            
            // Check if Google Maps is available
            if (typeof google !== 'undefined' && google.maps) {
                try {
                    const map = new google.maps.Map(document.getElementById('test-map'), {
                        center: { lat: 37.7749, lng: -122.4194 },
                        zoom: 10
                    });
                    mapsStatusSpan.innerHTML = 'Maps loaded and initialized ✅';
                    mapsTestDiv.className = 'test-result success';
                } catch (error) {
                    mapsStatusSpan.innerHTML = `Map creation failed: ${error.message} ❌`;
                    mapsTestDiv.className = 'test-result error';
                }
            } else {
                mapsStatusSpan.innerHTML = 'Google Maps API not loaded ❌';
                mapsTestDiv.className = 'test-result error';
            }
        }
        
        // Run tests
        document.addEventListener('DOMContentLoaded', function() {
            testMetaTag();
            testJSFunction();
            
            // Wait for Google Maps to potentially load
            setTimeout(testGoogleMaps, 2000);
        });
        
        // Callback for when Google Maps loads
        function initTestMap() {
            testGoogleMaps();
        }
    </script>
    
    <!-- Include main.js to test getGoogleMapsApiKey function -->
    <script src="/js/main.js"></script>
    
    <!-- Load Google Maps API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($apiKey) ?>&libraries=places&callback=initTestMap"></script>
</body>
</html>
