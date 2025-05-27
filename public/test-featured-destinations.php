<?php
// Test script to verify featured destinations clicking functionality
// This bypasses authentication to test the map directly

// Define base path
define('BASE_PATH', __DIR__ . '/..');

// Include autoloader
require_once BASE_PATH . '/app/Core/Autoloader.php';
spl_autoload_register(['App\\Core\\Autoloader', 'load']);

// Load vendor dependencies
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

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
    
    echo "Found " . count($featured) . " featured destinations.\n";
    foreach ($featured as $dest) {
        echo "- {$dest['name']} (ID: {$dest['id']}) at {$dest['latitude']}, {$dest['longitude']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $featured = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Featured Destinations Map</title>
    <meta name="google-maps-api-key" content="AIzaSyAOVYRIgupAurZup5y1PRh8Ismb1A3lLao">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .map-container { height: 500px; }
        .info-window h5 { margin: 0 0 10px 0; }
        .info-window p { margin: 0 0 10px 0; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Featured Destinations Map Test</h1>
        <p class="text-muted">Click on featured destination markers - they should show info windows with "View Details" links, not the quick-add modal.</p>
        
        <div class="card">
            <div class="card-header">
                <h5>Travel Map</h5>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Tip:</strong> Click anywhere on the map to quickly add a new destination
                </small>
            </div>
            <div class="card-body p-0">
                <div id="travel-map" class="map-container"></div>
            </div>
        </div>
        
        <div class="mt-3">
            <h6>Featured Destinations Found:</h6>
            <ul>
                <?php foreach ($featured as $dest): ?>
                    <li><?= htmlspecialchars($dest['name']) ?> (ID: <?= $dest['id'] ?>) - <?= htmlspecialchars($dest['city']) ?>, <?= htmlspecialchars($dest['country']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Quick Add Destination Modal -->
    <div class="modal fade" id="quickAddDestinationModal" tabindex="-1" aria-labelledby="quickAddDestinationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickAddDestinationModalLabel">Add Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>This modal should NOT appear when clicking on featured destination markers!</p>
                    <p>It should only appear when clicking on empty areas of the map.</p>
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
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Google Maps is already loaded, otherwise wait for callback
            if (typeof google !== 'undefined' && google.maps) {
                initializeTestMap();
            } else {
                // Add to callback queue for when Google Maps loads
                window.googleMapsCallbacks = window.googleMapsCallbacks || [];
                window.googleMapsCallbacks.push(initializeTestMap);
            }
            
            function initializeTestMap() {
                // Initialize the map
                initTravelMap();
                
                // Initialize quick destination create functionality
                initializeQuickDestinationCreate();
            }
            
            // Function to initialize the map with featured destinations
            function initTravelMap() {
                const mapContainer = document.getElementById('travel-map');
                
                if (!mapContainer) return;
                
                window.travelMap = new google.maps.Map(mapContainer, {
                    zoom: 2,
                    center: {lat: 20, lng: 0},
                    mapTypeId: 'terrain',
                    mapTypeControl: false,
                    streetViewControl: false,
                    mapId: 'MAPIT_TEST_MAP'
                });
                
                // Use the featured destinations data passed from PHP
                const featuredDestinations = <?= json_encode($featured ?? []); ?>;
                addDestinationsToMap(window.travelMap, featuredDestinations);
                
                // Enable interactive map clicking for adding destinations
                enableInteractiveMapClicking(window.travelMap);
            }
            
            // Function to add destinations to the map
            function addDestinationsToMap(map, destinations) {
                if (!destinations || destinations.length === 0) {
                    console.log('No destinations to add to map');
                    return;
                }
                
                console.log('Adding', destinations.length, 'destinations to map');
                const bounds = new google.maps.LatLngBounds();
                
                // Use the same marker icons as destinations page
                const featuredIcon = {
                    url: '/images/markers/featured.svg',
                    scaledSize: new google.maps.Size(32, 32)
                };
                
                // Fallback markers if images fail to load
                const featuredMarker = {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#ff6b35',
                    fillOpacity: 0.8,
                    scale: 8,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                };
                
                destinations.forEach(dest => {
                    // Skip destinations with invalid coordinates
                    if (!dest.latitude || !dest.longitude) {
                        console.warn('Skipping destination with invalid coordinates:', dest);
                        return;
                    }
                    
                    const position = {
                        lat: parseFloat(dest.latitude), 
                        lng: parseFloat(dest.longitude)
                    };
                    
                    // Skip if parsing failed
                    if (isNaN(position.lat) || isNaN(position.lng)) {
                        console.warn('Skipping destination with invalid coordinate format:', dest);
                        return;
                    }
                    
                    console.log('Adding marker for:', dest.name, 'at', position);
                    
                    // Create marker element for AdvancedMarkerElement
                    const markerElement = document.createElement('div');
                    markerElement.innerHTML = `
                        <img src="/images/markers/featured.svg"
                             style="width: 32px; height: 32px;"
                             alt="Featured destination"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="
                            display: none;
                            width: 16px; 
                            height: 16px; 
                            background: #ff6b35;
                            border: 2px solid white; 
                            border-radius: 50%;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                        "></div>
                    `;
                    
                    // Create marker using new API if available, fallback to legacy
                    let marker;
                    try {
                        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                            marker = new google.maps.marker.AdvancedMarkerElement({
                                position: position,
                                map: map,
                                title: dest.name,
                                content: markerElement
                            });
                        } else {
                            // Fallback to legacy marker
                            marker = new google.maps.Marker({
                                position: position,
                                map: map,
                                title: dest.name,
                                icon: featuredIcon
                            });
                            
                            // Add error handling for marker icon loading
                            marker.addListener('icon_changed', function() {
                                const img = new Image();
                                img.onerror = function() {
                                    marker.setIcon(featuredMarker);
                                };
                                img.src = featuredIcon.url;
                            });
                        }
                    } catch (error) {
                        console.warn('Error creating advanced marker, using legacy marker:', error);
                        marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            title: dest.name,
                            icon: featuredIcon
                        });
                        
                        // Add error handling for marker icon loading
                        marker.addListener('icon_changed', function() {
                            const img = new Image();
                            img.onerror = function() {
                                marker.setIcon(featuredMarker);
                            };
                            img.src = featuredIcon.url;
                        });
                    }
                    
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="info-window">
                                <h5>${dest.name}</h5>
                                <p>${dest.description ? dest.description.substring(0, 100) + '...' : 'No description'}</p>
                                <a href="/destinations/${dest.id}" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        `
                    });
                    
                    // Add click listener based on marker type
                    if (google.maps.marker && google.maps.marker.AdvancedMarkerElement &&
                        marker instanceof google.maps.marker.AdvancedMarkerElement) {
                        marker.addEventListener('click', () => {
                            console.log('Featured destination marker clicked:', dest.name);
                            // Prevent map click handler from triggering
                            if (window.onMarkerClick) window.onMarkerClick();
                            infoWindow.open({
                                anchor: marker,
                                map: map
                            });
                        });
                    } else {
                        marker.addListener('click', () => {
                            console.log('Featured destination marker clicked:', dest.name);
                            // Prevent map click handler from triggering
                            if (window.onMarkerClick) window.onMarkerClick();
                            infoWindow.open(map, marker);
                        });
                    }
                    
                    bounds.extend(position);
                });
                
                // Only adjust bounds if we have destinations
                if (destinations.length > 0) {
                    map.fitBounds(bounds);
                    
                    // Prevent zooming in too much for single destinations
                    const listener = google.maps.event.addListener(map, 'idle', function() {
                        if (map.getZoom() > 12) {
                            map.setZoom(12);
                        }
                        google.maps.event.removeListener(listener);
                    });
                }
            }
        });
    </script>
    
    <!-- Load Google Maps API -->
    <script async defer 
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAOVYRIgupAurZup5y1PRh8Ismb1A3lLao&libraries=places&callback=initializeGoogleMaps">
    </script>
    <script>
        // Google Maps initialization callback
        function initializeGoogleMaps() {
            console.log('Google Maps API loaded');
            if (window.googleMapsCallbacks) {
                window.googleMapsCallbacks.forEach(callback => callback());
                window.googleMapsCallbacks = [];
            }
        }
    </script>
</body>
</html>
