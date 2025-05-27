<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Clicking Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .map-container { height: 400px; }
        .test-result { margin-top: 20px; }
        .test-pass { color: green; }
        .test-fail { color: red; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Map Clicking Behavior Test</h1>
        <p class="text-muted">This tests the fixed behavior for featured destination clicking vs map clicking.</p>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Map</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="test-map" class="map-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="test-results">
                            <p>Click on the map to test:</p>
                            <ul>
                                <li><strong>Red markers:</strong> Should show info windows (not modal)</li>
                                <li><strong>Empty areas:</strong> Should show quick-add modal</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Modal (should only appear for empty map clicks) -->
    <div class="modal fade" id="quickAddDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><span class="test-pass">✓ SUCCESS:</span> This modal appeared correctly when clicking on an empty map area!</p>
                    <p><em>This should NOT appear when clicking on red markers.</em></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
    <script>
        let testResults = [];
        let testMap;
        
        function addTestResult(message, success = true) {
            const results = document.getElementById('test-results');
            const resultDiv = document.createElement('div');
            resultDiv.className = success ? 'test-pass' : 'test-fail';
            resultDiv.innerHTML = (success ? '✓' : '✗') + ' ' + message;
            results.appendChild(resultDiv);
        }
        
        function initializeTestMap() {
            const mapContainer = document.getElementById('test-map');
            
            testMap = new google.maps.Map(mapContainer, {
                zoom: 6,
                center: {lat: 40.7128, lng: -74.0060}, // New York
                mapTypeId: 'terrain'
            });
            
            // Add a few test markers representing featured destinations
            const testDestinations = [
                {id: 1, name: 'Times Square', lat: 40.7580, lng: -73.9855},
                {id: 2, name: 'Central Park', lat: 40.7829, lng: -73.9654},
                {id: 3, name: 'Brooklyn Bridge', lat: 40.7061, lng: -73.9969}
            ];
            
            testDestinations.forEach(dest => {
                const marker = new google.maps.Marker({
                    position: {lat: dest.lat, lng: dest.lng},
                    map: testMap,
                    title: dest.name,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: '#ff0000',
                        fillOpacity: 0.8,
                        scale: 10,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    }
                });
                
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div>
                            <h6>${dest.name}</h6>
                            <p>This is a test featured destination.</p>
                            <button class="btn btn-sm btn-primary" onclick="addTestResult('Featured destination info window clicked correctly!', true)">
                                View Details
                            </button>
                        </div>
                    `
                });
                
                marker.addListener('click', () => {
                    // Signal that a marker was clicked (should prevent map click handler)
                    if (window.onMarkerClick) window.onMarkerClick();
                    
                    addTestResult(`Marker clicked: ${dest.name} - Info window opened correctly!`, true);
                    infoWindow.open(testMap, marker);
                });
            });
            
            // Enable the interactive map clicking (our fixed version)
            enableInteractiveMapClicking(testMap);
            
            addTestResult('Test map initialized with 3 red markers', true);
        }
        
        // Override the handleMapClick function to show our test modal instead
        window.originalHandleMapClick = window.handleMapClick;
        window.handleMapClick = function(latLng, map) {
            addTestResult('Empty map area clicked - showing quick-add modal', true);
            
            // Show the test modal
            const modal = new bootstrap.Modal(document.getElementById('quickAddDestinationModal'));
            modal.show();
        };
        
        // Initialize when Google Maps loads
        function initGoogleMaps() {
            console.log('Google Maps loaded');
            initializeTestMap();
        }
        
        // Check if Google Maps is already loaded
        if (typeof google !== 'undefined' && google.maps) {
            initializeTestMap();
        }
    </script>
    
    <!-- Load Google Maps API -->
    <script async defer 
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDsR974k1b4C4zkmFjFFYR3eSX3HWJ66A0&libraries=places&callback=initGoogleMaps">
    </script>
</body>
</html>
