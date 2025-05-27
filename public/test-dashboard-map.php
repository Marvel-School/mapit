<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config/app.php';

// Mock user session for testing
$_SESSION['user_id'] = 1;

// Get featured destinations
$destinationModel = new App\Models\Destination();
$featured = $destinationModel->getFeatured(10);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Map Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        #map { height: 400px; width: 100%; border: 1px solid #ccc; margin: 20px 0; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
        .destination { margin: 5px 0; padding: 5px; background: #e8f5e8; }
    </style>
</head>
<body>
    <h1>Dashboard Map Test - Featured Destinations</h1>
    
    <div class="info">
        <h3>Featured Destinations Data:</h3>
        <p>Count: <?= count($featured); ?></p>
        <?php foreach ($featured as $dest): ?>
            <div class="destination">
                <strong><?= htmlspecialchars($dest['name']); ?></strong> - 
                <?= htmlspecialchars($dest['country']); ?> 
                (<?= $dest['latitude']; ?>, <?= $dest['longitude']; ?>)
                - Featured: <?= $dest['featured'] ? 'Yes' : 'No'; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div id="map"></div>
    
    <div id="console-output" style="background: #000; color: #00ff00; padding: 10px; font-family: monospace; white-space: pre;"></div>

    <script>
        function log(message) {
            const output = document.getElementById('console-output');
            output.textContent += new Date().toLocaleTimeString() + ': ' + message + '\n';
            console.log(message);
        }

        function initializeGoogleMaps() {
            log('Google Maps API loaded successfully');
            
            try {
                // Initialize the map exactly like the dashboard
                const map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 2,
                    center: {lat: 20, lng: 0},
                    mapTypeId: 'terrain',
                    mapTypeControl: false,
                    streetViewControl: false,
                    mapId: 'MAPIT_DASHBOARD_MAP_TEST'
                });
                
                log('Map initialized successfully');
                
                // Load featured destinations exactly like the dashboard
                const featuredDestinations = <?= json_encode($featured ?? []); ?>;
                log('Featured destinations loaded: ' + featuredDestinations.length + ' destinations');
                
                // Add destinations to map
                addDestinationsToMap(map, featuredDestinations);
                
            } catch (error) {
                log('Error initializing map: ' + error.message);
            }
        }
        
        function addDestinationsToMap(map, destinations) {
            if (!destinations || destinations.length === 0) {
                log('No destinations to add to map');
                return;
            }
            
            log('Adding ' + destinations.length + ' destinations to map');
            const bounds = new google.maps.LatLngBounds();
            
            // Icon definitions
            const featuredIcon = {
                url: '/images/markers/featured.svg',
                scaledSize: new google.maps.Size(32, 32)
            };
            
            destinations.forEach((dest, index) => {
                // Skip destinations with invalid coordinates
                if (!dest.latitude || !dest.longitude) {
                    log('Skipping destination with invalid coordinates: ' + dest.name);
                    return;
                }
                
                const position = {
                    lat: parseFloat(dest.latitude), 
                    lng: parseFloat(dest.longitude)
                };
                
                // Skip if parsing failed
                if (isNaN(position.lat) || isNaN(position.lng)) {
                    log('Skipping destination with invalid coordinate format: ' + dest.name);
                    return;
                }
                
                log('Adding marker for: ' + dest.name + ' at (' + position.lat + ', ' + position.lng + ')');
                
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
                
                // Create marker with AdvancedMarkerElement or fallback
                let marker;
                try {
                    if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                        marker = new google.maps.marker.AdvancedMarkerElement({
                            position: position,
                            map: map,
                            title: dest.name,
                            content: markerElement
                        });
                        log('Created AdvancedMarkerElement for: ' + dest.name);
                    } else {
                        throw new Error('AdvancedMarkerElement not available');
                    }
                } catch (error) {
                    log('Falling back to legacy marker for: ' + dest.name);
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: dest.name,
                        icon: featuredIcon
                    });
                }
                
                // Create info window
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="info-window">
                            <h5>${dest.name}</h5>
                            <p>${dest.description || 'Featured destination'}</p>
                            <p><strong>Status:</strong> Featured</p>
                        </div>
                    `
                });
                
                // Add click listener
                if (google.maps.marker && google.maps.marker.AdvancedMarkerElement &&
                    marker instanceof google.maps.marker.AdvancedMarkerElement) {
                    marker.addEventListener('click', () => {
                        infoWindow.open({
                            anchor: marker,
                            map: map
                        });
                    });
                } else {
                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });
                }
                
                bounds.extend(position);
            });
            
            // Adjust map bounds if we have destinations
            if (destinations.length > 0) {
                map.fitBounds(bounds);
                log('Map bounds adjusted to fit all markers');
                
                // Prevent zooming in too much for single destinations
                const listener = google.maps.event.addListener(map, 'idle', function() {
                    if (map.getZoom() > 12) {
                        map.setZoom(12);
                        log('Zoom adjusted to maximum of 12');
                    }
                    google.maps.event.removeListener(listener);
                });
            }
        }
        
        // Load Google Maps API
        log('Loading Google Maps API...');
    </script>
    
    <!-- Load Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkuWJZSJfnkQI9t-9BDwXI1PVBpOCY1-U&libraries=marker&callback=initializeGoogleMaps&v=3.55" async defer></script>
</body>
</html>
