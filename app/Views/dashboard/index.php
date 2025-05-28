<?php
// User Dashboard view
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <!-- Stats Cards -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Countries Visited</h6>
                                    <h3 class="mb-0"><?= $stats['countries_visited']; ?></h3>
                                </div>
                                <div class="icon-bg bg-primary text-white rounded p-3">
                                    <i class="fas fa-globe-americas fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">                                <div>
                                    <h6 class="text-muted mb-1">Places Visited</h6>
                                    <h3 class="mb-0" data-stat="places_visited"><?= $stats['places_visited']; ?></h3>
                                </div>
                                <div class="icon-bg bg-success text-white rounded p-3">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">                                <div>
                                    <h6 class="text-muted mb-1">Wishlisted Places</h6>
                                    <h3 class="mb-0" data-stat="wishlist_count"><?= $stats['wishlist_count']; ?></h3>
                                </div>
                                <div class="icon-bg bg-warning text-white rounded p-3">
                                    <i class="fas fa-heart fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Badges Earned</h6>
                                    <h3 class="mb-0"><?= $stats['badges_earned']; ?></h3>
                                </div>
                                <div class="icon-bg bg-info text-white rounded p-3">
                                    <i class="fas fa-medal fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Your Travel Map</h5>
                        <div>
                            <a href="/destinations" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-search me-1"></i> Browse All
                            </a>
                            <a href="/destinations/create" class="btn btn-primary btn-sm ms-2">
                                <i class="fas fa-plus me-1"></i> Add New
                            </a>
                        </div>
                    </div>
                </div>                <div class="card-body p-0">
                    <div class="p-3 border-bottom bg-light">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Tip:</strong> Click anywhere on the map to quickly add a new destination
                        </small>
                    </div>
                    <div id="travel-map" class="map-container"></div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Trips -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Trips</h5>
                                <a href="/trips" class="btn btn-outline-primary btn-sm">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentTrips)): ?>
                                <p class="text-muted">You haven't created any trips yet.</p>
                                <div class="text-center py-3">
                                    <a href="/trips/create" class="btn btn-primary">Create Your First Trip</a>
                                </div>
                            <?php else: ?>                                <div class="list-group">
                                    <?php foreach ($recentTrips as $trip): ?>
                                        <a href="/trips/<?= $trip['id']; ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($trip['destination_name'] ?? 'Trip'); ?></h6>
                                                <small class="text-muted">
                                                    <?php if ($trip['status'] === 'visited'): ?>
                                                        <span class="badge bg-success">Visited</span>
                                                    <?php elseif ($trip['status'] === 'planned'): ?>
                                                        <span class="badge bg-warning">Planned</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><?= ucfirst($trip['status'] ?? 'Unknown'); ?></span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                <i class="fas fa-map-marker-alt me-1"></i> 
                                                <?= htmlspecialchars($trip['destination_description'] ?? 'No description'); ?>
                                            </p>
                                            <p class="mb-1 text-muted small">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('M d, Y', strtotime($trip['created_at'])); ?>
                                            </p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Badges -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Achievements</h5>
                                <a href="/badges" class="btn btn-outline-primary btn-sm">View All</a>
                            </div>
                        </div>
                        <div class="card-body">                            <?php if (empty($userBadges)): ?>
                                <p class="text-muted">You haven't earned any badges yet.</p>
                                <div class="text-center py-3">
                                    <a href="/destinations/create" class="btn btn-primary">Add Destinations to Earn Badges</a>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($userBadges as $badge): ?>
                                        <div class="col-md-4">
                                            <div class="card text-center h-100">
                                                <div class="card-body p-3">
                                                    <div class="badge-icon mb-2">
                                                        <i class="fas fa-medal fa-3x text-warning"></i>
                                                    </div>
                                                    <h6 class="card-title mb-1"><?= htmlspecialchars($badge['name']); ?></h6>
                                                    <p class="card-text small text-muted"><?= isset($badge['earned_date']) ? date('M d, Y', strtotime($badge['earned_date'])) : 'Earned'; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .map-container {
        height: 400px;
        width: 100%;
    }
    
    .icon-bg {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
    }
      .badge-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto;
    }
    
    /* Map marker status badges */
    .map-marker-container {
        position: relative;
        display: inline-block;
    }
    
    .marker-wrapper {
        position: relative;
    }
    
    .status-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        color: white;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    
    .status-badge.visited {
        background-color: #28a745;
    }
    
    .status-badge.in-progress {
        background-color: #007bff;
    }
    
    .status-badge.planned {
        background-color: #ffc107;
        color: #000;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced Google Maps loading check with retry mechanism
    function waitForGoogleMapsReady() {
        return new Promise((resolve, reject) => {
            const maxRetries = 50; // 5 seconds maximum wait
            let retries = 0;
            
            function checkReady() {
                if (typeof google !== 'undefined' && 
                    google.maps && 
                    google.maps.Map && 
                    google.maps.InfoWindow) {
                    
                    // Wait a bit more for marker library to be ready
                    setTimeout(resolve, 100);
                    return;
                }
                
                retries++;
                if (retries > maxRetries) {
                    reject(new Error('Google Maps failed to load within timeout'));
                    return;
                }
                
                setTimeout(checkReady, 100);
            }
            
            checkReady();
        });
    }
    
    // Initialize dashboard map with proper error handling
    function initializeDashboardMap() {
        waitForGoogleMapsReady()
            .then(() => {
                console.log('Google Maps ready, initializing dashboard map...');
                initTravelMap();
                initializeQuickDestinationCreate();
            })
            .catch(error => {
                console.error('Failed to initialize Google Maps:', error);
                showMapError();
            });
    }
    
    // Show error message if maps fail to load
    function showMapError() {
        const mapContainer = document.getElementById('travel-map');
        if (mapContainer) {
            mapContainer.innerHTML = `
                <div class="alert alert-warning text-center">
                    <h5><i class="fas fa-exclamation-triangle"></i> Map Loading Issue</h5>
                    <p>The map is taking longer than expected to load. <a href="#" onclick="window.location.reload()">Refresh the page</a> to try again.</p>
                </div>
            `;
        }
    }
    
    // Check if Google Maps is already loaded, otherwise wait for callback
    if (typeof google !== 'undefined' && google.maps) {
        initializeDashboardMap();
    } else {
        // Add to callback queue for when Google Maps loads
        window.googleMapsCallbacks = window.googleMapsCallbacks || [];
        window.googleMapsCallbacks.push(initializeDashboardMap);
    }
    
    // Function to initialize the map with user's destinations
    function initTravelMap() {
        const mapContainer = document.getElementById('travel-map');
        
        if (!mapContainer) return;
          window.travelMap = new google.maps.Map(mapContainer, {
            zoom: 2,
            center: {lat: 20, lng: 0},
            mapTypeId: 'terrain',
            mapTypeControl: false,
            streetViewControl: false,
            mapId: 'MAPIT_DASHBOARD_MAP'
        });        // Load both featured destinations and user destinations
        const featuredDestinations = <?= json_encode($featured ?? []); ?>;
        const userDestinations = <?= json_encode($userDestinations ?? []); ?>;
        
        // Mark featured destinations as featured
        featuredDestinations.forEach(dest => {
            dest.featured = true;
        });
        
        // Combine all destinations
        const allDestinations = [...featuredDestinations, ...userDestinations];
        
        addDestinationsToMap(window.travelMap, allDestinations);
        
        // Enable interactive map clicking for adding destinations
        enableInteractiveMapClicking(window.travelMap);
    }    // Function to add destinations to the map with enhanced error handling
    function addDestinationsToMap(map, destinations) {
        if (!destinations || destinations.length === 0) {
            return;
        }

        console.log(`Adding ${destinations.length} destinations to map...`);
        const bounds = new google.maps.LatLngBounds();
        let markersAdded = 0;
        
        // Helper function to get marker colors
        function getMarkerColor(markerType) {
            switch (markerType) {
                case 'featured': return '#ff6b35';
                case 'visited': return '#28a745';
                case 'in_progress': return '#007bff';
                case 'planned': return '#ffc107';
                default: return '#6c757d';
            }
        }
        
        // Helper function to get marker icons with preloading
        function getMarkerIcon(markerType) {
            const iconMap = {
                'featured': { url: '/images/markers/featured.svg', scaledSize: new google.maps.Size(32, 32) },
                'visited': { url: '/images/markers/visited.png', scaledSize: new google.maps.Size(32, 32) },
                'in_progress': { url: '/images/markers/in_progress.svg', scaledSize: new google.maps.Size(32, 32) },
                'planned': { url: '/images/markers/planned.svg', scaledSize: new google.maps.Size(32, 32) },
                'wishlist': { url: '/images/markers/wishlist.png', scaledSize: new google.maps.Size(32, 32) }
            };
            return iconMap[markerType] || iconMap['wishlist'];
        }
        
        // Helper function to get fallback markers
        function getFallbackMarker(markerType) {
            return {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: getMarkerColor(markerType),
                fillOpacity: 0.8,
                scale: 8,
                strokeColor: '#ffffff',
                strokeWeight: 2
            };
        }
        
        // Preload marker images to avoid loading issues
        function preloadMarkerImages() {
            const imageUrls = [
                '/images/markers/featured.svg',
                '/images/markers/visited.png', 
                '/images/markers/in_progress.svg',
                '/images/markers/planned.svg',
                '/images/markers/wishlist.png'
            ];
            
            imageUrls.forEach(url => {
                const img = new Image();
                img.src = url;
            });
        }
        
        // Preload images first
        preloadMarkerImages();
        
        // Add markers with proper error handling and retry mechanism
        destinations.forEach((dest, index) => {
            // Use setTimeout to prevent blocking and allow for better error handling
            setTimeout(() => {
                addSingleDestinationMarker(dest, map, bounds, () => {
                    markersAdded++;
                    
                    // When all markers are added, adjust map bounds
                    if (markersAdded === destinations.length && markersAdded > 0) {
                        adjustMapBounds(map, bounds);
                    }
                });
            }, index * 50); // Stagger marker creation to avoid overwhelming the API
        });
    }
    
    // Function to add a single destination marker with retry logic
    function addSingleDestinationMarker(dest, map, bounds, onComplete) {
        try {
            // Skip destinations with invalid coordinates
            if (!dest.latitude || !dest.longitude) {
                console.warn('Skipping destination with invalid coordinates:', dest);
                onComplete();
                return;
            }
            
            const position = {
                lat: parseFloat(dest.latitude), 
                lng: parseFloat(dest.longitude)
            };
            
            // Skip if parsing failed
            if (isNaN(position.lat) || isNaN(position.lng)) {
                console.warn('Skipping destination with invalid coordinate format:', dest);
                onComplete();
                return;
            }
            
            // Determine marker type and status
            const isFeatured = dest.featured || !dest.hasOwnProperty('trip_status');
            let markerType = 'wishlist'; // default
            let statusBadge = '';
            
            if (isFeatured) {
                markerType = 'featured';
            } else {
                // Handle trip status for user destinations
                switch (dest.trip_status) {
                    case 'visited':
                        markerType = 'visited';
                        statusBadge = '<div class="status-badge visited"><i class="fas fa-check"></i></div>';
                        break;
                    case 'in_progress':
                        markerType = 'in_progress';
                        statusBadge = '<div class="status-badge in-progress"><i class="fas fa-route"></i></div>';
                        break;
                    case 'planned':
                        markerType = 'planned';
                        statusBadge = '<div class="status-badge planned"><i class="fas fa-clock"></i></div>';
                        break;
                    default:
                        markerType = 'wishlist';
                        break;
                }
            }
            
            createMarkerWithFallback(dest, position, map, markerType, statusBadge, bounds, onComplete);
            
        } catch (error) {
            console.error('Error adding destination marker:', error, dest);
            onComplete();
        }
    }
    
    // Create marker with fallback options
    function createMarkerWithFallback(dest, position, map, markerType, statusBadge, bounds, onComplete) {
        let marker;
        let markerCreated = false;
        
        // Retry mechanism for marker creation
        function attemptMarkerCreation(attempt = 1) {
            try {
                // Try AdvancedMarkerElement first (with enhanced checking)
                if (google.maps.marker && 
                    google.maps.marker.AdvancedMarkerElement && 
                    typeof google.maps.marker.AdvancedMarkerElement === 'function') {
                    
                    // Create marker element for AdvancedMarkerElement with status badge
                    const markerElement = document.createElement('div');
                    markerElement.className = 'map-marker-container';
                    markerElement.innerHTML = `
                        <div class="marker-wrapper">
                            <img src="/images/markers/${markerType === 'in_progress' ? 'in_progress' : markerType}.${markerType === 'visited' || markerType === 'wishlist' ? 'png' : 'svg'}"
                                 style="width: 32px; height: 32px;"
                                 alt="${markerType} destination"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="
                                display: none;
                                width: 16px; 
                                height: 16px; 
                                background: ${getMarkerColor(markerType)};
                                border: 2px solid white; 
                                border-radius: 50%;
                                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                            "></div>
                            ${statusBadge}
                        </div>
                    `;
                    
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: position,
                        map: map,
                        title: dest.name,
                        content: markerElement
                    });
                    
                    markerCreated = true;
                    
                } else {
                    throw new Error('AdvancedMarkerElement not available, using legacy marker');
                }
                
            } catch (error) {
                console.warn(`Attempt ${attempt}: Error creating advanced marker, using legacy marker:`, error);
                
                // Fallback to legacy marker
                try {
                    const iconToUse = getMarkerIcon(markerType);
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: dest.name,
                        icon: iconToUse
                    });
                    
                    markerCreated = true;
                    
                    // Add error handling for marker icon loading
                    marker.addListener('icon_changed', function() {
                        const img = new Image();
                        img.onerror = function() {
                            const fallbackMarker = getFallbackMarker(markerType);
                            marker.setIcon(fallbackMarker);
                        };
                        img.src = iconToUse.url;
                    });
                    
                } catch (legacyError) {
                    console.error(`Attempt ${attempt}: Failed to create legacy marker:`, legacyError);
                    
                    if (attempt < 3) {
                        // Retry after a short delay
                        setTimeout(() => attemptMarkerCreation(attempt + 1), 100 * attempt);
                        return;
                    } else {
                        // Final fallback - create a simple marker
                        marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            title: dest.name,
                            icon: getFallbackMarker(markerType)
                        });
                        markerCreated = true;
                    }
                }
            }
            
            if (markerCreated && marker) {
                // Create and attach info window
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
                        // Prevent map click handler from triggering
                        if (window.onMarkerClick) window.onMarkerClick();
                        infoWindow.open({
                            anchor: marker,
                            map: map
                        });
                    });
                } else {
                    marker.addListener('click', () => {
                        // Prevent map click handler from triggering
                        if (window.onMarkerClick) window.onMarkerClick();
                        infoWindow.open(map, marker);
                    });
                }
                
                bounds.extend(position);
                console.log(`Successfully added marker for: ${dest.name}`);
            }
            
            onComplete();
        }
        
        // Start the marker creation attempt
        attemptMarkerCreation();
    }
    
    // Helper function to get marker colors (duplicate for scoping)
    function getMarkerColor(markerType) {
        switch (markerType) {
            case 'featured': return '#ff6b35';
            case 'visited': return '#28a745';
            case 'in_progress': return '#007bff';
            case 'planned': return '#ffc107';
            default: return '#6c757d';
        }
    }
    
    // Helper function to get marker icons (duplicate for scoping)
    function getMarkerIcon(markerType) {
        const iconMap = {
            'featured': { url: '/images/markers/featured.svg', scaledSize: new google.maps.Size(32, 32) },
            'visited': { url: '/images/markers/visited.png', scaledSize: new google.maps.Size(32, 32) },
            'in_progress': { url: '/images/markers/in_progress.svg', scaledSize: new google.maps.Size(32, 32) },
            'planned': { url: '/images/markers/planned.svg', scaledSize: new google.maps.Size(32, 32) },
            'wishlist': { url: '/images/markers/wishlist.png', scaledSize: new google.maps.Size(32, 32) }
        };
        return iconMap[markerType] || iconMap['wishlist'];
    }
    
    // Helper function to get fallback markers (duplicate for scoping)
    function getFallbackMarker(markerType) {
        return {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: getMarkerColor(markerType),
            fillOpacity: 0.8,
            scale: 8,
            strokeColor: '#ffffff',
            strokeWeight: 2
        };
    }
    
    // Function to adjust map bounds with better logic
    function adjustMapBounds(map, bounds) {
        try {
            if (bounds.isEmpty()) {
                console.log('No markers to fit bounds for');
                return;
            }
            
            map.fitBounds(bounds);
            
            // Prevent zooming in too much for single destinations or close destinations
            const listener = google.maps.event.addListener(map, 'idle', function() {
                const currentZoom = map.getZoom();
                if (currentZoom > 12) {
                    map.setZoom(12);
                }
                google.maps.event.removeListener(listener);
            });
            
            console.log('Map bounds adjusted successfully');
        } catch (error) {
            console.error('Error adjusting map bounds:', error);
        }
    }
});
</script>

<!-- Quick Add Destination Modal -->
<div class="modal fade" id="quickAddDestinationModal" tabindex="-1" aria-labelledby="quickAddDestinationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddDestinationModalLabel">Add Destination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddDestinationForm">
                    <div class="mb-3">
                        <label for="quickDestinationName" class="form-label">Destination Name *</label>
                        <input type="text" class="form-control" id="quickDestinationName" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="quickDestinationCity" name="city">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationCountry" class="form-label">Country</label>
                                <select class="form-select" id="quickDestinationCountry" name="country">
                                    <option value="">Select Country</option>
                                    <?php foreach ($countries as $code => $name): ?>
                                        <option value="<?= $code; ?>"><?= htmlspecialchars($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickDestinationDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="quickDestinationDescription" name="description" rows="3"></textarea>
                    </div>
                      <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationStatus" class="form-label">Status</label>
                                <select class="form-select" id="quickDestinationStatus" name="visited">
                                    <option value="0">Wishlist</option>
                                    <option value="1">Visited</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quickDestinationPrivacy" class="form-label">Privacy</label>
                                <select class="form-select" id="quickDestinationPrivacy" name="privacy">
                                    <option value="private">Private</option>
                                    <option value="public">Public (needs approval)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="visit-date-container" style="display: none;">
                        <div class="mb-3">
                            <label for="quickDestinationVisitDate" class="form-label">Visit Date</label>
                            <input type="date" class="form-control" id="quickDestinationVisitDate" name="visit_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Location: <span id="selectedCoordinates">Click on the map to set location</span>
                        </small>
                    </div>
                    
                    <input type="hidden" id="quickDestinationLat" name="latitude">
                    <input type="hidden" id="quickDestinationLng" name="longitude">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveQuickDestination">Save Destination</button>
            </div>
        </div>
    </div>
</div>
