<?php
// Quick dashboard test - bypasses authentication for testing purposes
session_start();

// Define base path
define('BASE_PATH', __DIR__ . '/..');

// Include autoloader
require_once BASE_PATH . '/app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Load vendor dependencies
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// Simulate logged in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

// Get featured destinations
try {
    $db = \App\Core\Database::getInstance();
    $featured = $db->query("
        SELECT d.*, u.username, u.email
        FROM destinations d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.featured = 1 AND d.approval_status = 'approved'
        ORDER BY d.created_at DESC
        LIMIT 20
    ")->resultSet();
    
    echo "<!-- Found " . count($featured) . " featured destinations -->\n";
} catch (Exception $e) {
    echo "<!-- Error loading destinations: " . $e->getMessage() . " -->\n";
    $featured = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Test - Featured Destinations Clicking</title>
    <meta name="google-maps-api-key" content="AIzaSyDsR974k1b4C4zkmFjFFYR3eSX3HWJ66A0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .map-container { height: 500px; }
        .test-log { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 15px; 
            margin-top: 20px;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
        }
        .test-success { color: #28a745; }
        .test-error { color: #dc3545; }
        .test-info { color: #007bff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-map-marked-alt me-2"></i>MapIt - Dashboard Test
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-globe me-2"></i>Travel Map
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Test:</strong> Click featured destinations for details, empty areas for quick-add
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div id="travel-map" class="map-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-bug me-2"></i>Test Log</h6>
                    </div>
                    <div class="card-body">
                        <div id="test-log" class="test-log">
                            <div class="test-info">Initializing test...</div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-map-marker-alt me-2"></i>Featured Destinations</h6>
                    </div>
                    <div class="card-body">
                        <small><?= count($featured) ?> featured destinations loaded</small>
                        <?php if (count($featured) > 0): ?>
                        <ul class="list-unstyled mt-2">
                            <?php foreach (array_slice($featured, 0, 5) as $dest): ?>
                            <li class="small">
                                <i class="fas fa-star text-warning"></i>
                                <?= htmlspecialchars($dest['name']) ?>
                                <small class="text-muted">(<?= htmlspecialchars($dest['city']) ?>)</small>
                            </li>
                            <?php endforeach; ?>
                            <?php if (count($featured) > 5): ?>
                            <li class="small text-muted">... and <?= count($featured) - 5 ?> more</li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Destination Modal -->
    <div class="modal fade" id="quickAddDestinationModal" tabindex="-1" aria-labelledby="quickAddDestinationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickAddDestinationModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Quick Add Destination
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>TEST PASSED:</strong> Quick-add modal opened correctly!
                    </div>
                    <p>This modal should only appear when clicking on empty map areas, not on featured destination markers.</p>
                    <form id="quickAddForm">
                        <input type="hidden" id="quickLatitude" name="latitude">
                        <input type="hidden" id="quickLongitude" name="longitude">
                        <div class="mb-3">
                            <label for="quickName" class="form-label">Destination Name</label>
                            <input type="text" class="form-control" id="quickName" name="name" placeholder="Enter destination name">
                        </div>
                        <div class="mb-3">
                            <label for="quickDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="quickDescription" name="description" rows="3" placeholder="Brief description..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add Destination</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
    <script>
        let testLog = [];
        
        function logTest(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logElement = document.getElementById('test-log');
            const logEntry = document.createElement('div');
            logEntry.className = `test-${type}`;
            logEntry.innerHTML = `[${timestamp}] ${message}`;
            logElement.appendChild(logEntry);
            logElement.scrollTop = logElement.scrollHeight;
            
            console.log(`[TEST ${type.toUpperCase()}] ${message}`);
        }
        
        // Override handleMapClick to test modal behavior
        window.originalHandleMapClick = window.handleMapClick;
        window.handleMapClick = function(latLng, map) {
            logTest('Empty map area clicked - should show quick-add modal', 'success');
            
            // Set coordinates in form
            document.getElementById('quickLatitude').value = latLng.lat();
            document.getElementById('quickLongitude').value = latLng.lng();
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('quickAddDestinationModal'));
            modal.show();
        };
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            logTest('DOM loaded, initializing test dashboard...');
            
            // Check if Google Maps is already loaded
            if (typeof google !== 'undefined' && google.maps) {
                initializeDashboardTest();
            } else {
                logTest('Waiting for Google Maps API to load...');
                window.googleMapsCallbacks = window.googleMapsCallbacks || [];
                window.googleMapsCallbacks.push(initializeDashboardTest);
            }
        });
        
        function initializeDashboardTest() {
            logTest('Google Maps API loaded, initializing map...');
            
            // Initialize the travel map
            initTravelMap();
            
            // Initialize quick destination functionality
            initializeQuickDestinationCreate();
            
            logTest('Test dashboard initialized successfully', 'success');
        }
        
        function initTravelMap() {
            const mapContainer = document.getElementById('travel-map');
            
            if (!mapContainer) {
                logTest('Map container not found!', 'error');
                return;
            }
            
            // Create map
            window.travelMap = new google.maps.Map(mapContainer, {
                zoom: 2,
                center: {lat: 20, lng: 0},
                mapTypeId: 'terrain',
                mapTypeControl: false,
                streetViewControl: false,
                mapId: 'MAPIT_DASHBOARD_TEST'
            });
            
            logTest('Travel map created');
            
            // Add featured destinations
            const featuredDestinations = <?= json_encode($featured ?? []); ?>;
            addDestinationsToMap(window.travelMap, featuredDestinations);
            
            // Enable interactive clicking (our fixed version)
            enableInteractiveMapClicking(window.travelMap);
            
            logTest(`Interactive map clicking enabled with ${featuredDestinations.length} featured destinations`, 'success');
        }
        
        function addDestinationsToMap(map, destinations) {
            if (!destinations || destinations.length === 0) {
                logTest('No destinations to add to map', 'info');
                return;
            }
            
            logTest(`Adding ${destinations.length} featured destinations to map`);
            const bounds = new google.maps.LatLngBounds();
            
            destinations.forEach(dest => {
                if (!dest.latitude || !dest.longitude) {
                    logTest(`Skipping ${dest.name} - invalid coordinates`, 'error');
                    return;
                }
                
                const position = {
                    lat: parseFloat(dest.latitude), 
                    lng: parseFloat(dest.longitude)
                };
                
                if (isNaN(position.lat) || isNaN(position.lng)) {
                    logTest(`Skipping ${dest.name} - invalid coordinate format`, 'error');
                    return;
                }
                
                // Create marker
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: dest.name,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: '#ff6b35',
                        fillOpacity: 0.8,
                        scale: 8,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    }
                });
                
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="info-window">
                            <h5>${dest.name}</h5>
                            <p>${dest.description ? dest.description.substring(0, 100) + '...' : 'No description'}</p>
                            <a href="/destinations/${dest.id}" class="btn btn-sm btn-primary" onclick="logTest('View Details clicked for ${dest.name}', 'success')">
                                View Details
                            </a>
                        </div>
                    `
                });
                
                marker.addListener('click', () => {
                    logTest(`Featured destination marker clicked: ${dest.name} - showing info window`, 'success');
                    
                    // Call marker click handler to prevent map click
                    if (window.onMarkerClick) window.onMarkerClick();
                    
                    infoWindow.open(map, marker);
                });
                
                bounds.extend(position);
            });
            
            // Fit bounds if we have destinations
            if (destinations.length > 0) {
                map.fitBounds(bounds);
                
                const listener = google.maps.event.addListener(map, 'idle', function() {
                    if (map.getZoom() > 12) {
                        map.setZoom(12);
                    }
                    google.maps.event.removeListener(listener);
                });
            }
            
            logTest(`Successfully added ${destinations.length} markers to map`, 'success');
        }
    </script>
    
    <!-- Load Google Maps API -->
    <script async defer 
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDsR974k1b4C4zkmFjFFYR3eSX3HWJ66A0&libraries=places&callback=initializeGoogleMaps">
    </script>
    <script>
        function initializeGoogleMaps() {
            console.log('Google Maps API callback triggered');
            if (window.googleMapsCallbacks) {
                window.googleMapsCallbacks.forEach(callback => callback());
                window.googleMapsCallbacks = [];
            }
        }
    </script>
</body>
</html>
