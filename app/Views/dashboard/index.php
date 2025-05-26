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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Google Maps API is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API not loaded');
        const mapContainer = document.getElementById('travel-map');
        if (mapContainer) {
            mapContainer.innerHTML = '<div class="alert alert-warning">Map could not be loaded. Google Maps API key may be missing.</div>';
        }
        return;
    }
      // Initialize the map
    initTravelMap();
    
    // Initialize quick destination create functionality
    initializeQuickDestinationCreate();
    
    // Function to initialize the map with user's destinations
    function initTravelMap() {
        const mapContainer = document.getElementById('travel-map');
        
        if (!mapContainer) return;
        
        window.travelMap = new google.maps.Map(mapContainer, {
            zoom: 2,
            center: {lat: 20, lng: 0},
            mapTypeId: 'terrain',
            mapTypeControl: false,
            streetViewControl: false
        });
        
        // Use the destinations data passed from PHP instead of making an AJAX call
        const userDestinations = <?= json_encode($userDestinations ?? []); ?>;
        addDestinationsToMap(window.travelMap, userDestinations);
        
        // Enable interactive map clicking for adding destinations
        enableInteractiveMapClicking(window.travelMap);
    }    // Function to add destinations to the map
    function addDestinationsToMap(map, destinations) {
        if (!destinations || destinations.length === 0) {
            console.log('No destinations to display on the map');
            return;
        }
        
        const bounds = new google.maps.LatLngBounds();
        // Use the same marker icons as destinations page
        const visitedIcon = {
            url: '/images/markers/visited.png',
            scaledSize: new google.maps.Size(32, 32)
        };
        const wishlistIcon = {
            url: '/images/markers/wishlist.png',
            scaledSize: new google.maps.Size(32, 32)
        };
        
        // Fallback markers if images fail to load
        const visitedMarker = {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#28a745',
            fillOpacity: 0.8,
            scale: 8,
            strokeColor: '#ffffff',
            strokeWeight: 2
        };
        const plannedMarker = {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#ffc107',
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
              // Check if this destination has been visited based on trip_count
            const hasBeenVisited = (dest.trip_count && dest.trip_count > 0);
            
            // Create marker element for AdvancedMarkerElement
            const markerElement = document.createElement('div');
            markerElement.innerHTML = `
                <img src="/images/markers/${hasBeenVisited ? 'visited' : 'wishlist'}.png" 
                     style="width: 32px; height: 32px;" 
                     alt="${hasBeenVisited ? 'Visited' : 'Wishlist'} destination"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="
                    display: none;
                    width: 16px; 
                    height: 16px; 
                    background: ${hasBeenVisited ? '#28a745' : '#ffc107'}; 
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
                        icon: hasBeenVisited ? visitedIcon : wishlistIcon
                    });
                    
                    // Add error handling for marker icon loading
                    marker.addListener('icon_changed', function() {
                        const img = new Image();
                        img.onerror = function() {
                            marker.setIcon(hasBeenVisited ? visitedMarker : plannedMarker);
                        };
                        img.src = (hasBeenVisited ? visitedIcon : wishlistIcon).url;
                    });
                }
            } catch (error) {
                console.warn('Error creating advanced marker, using legacy marker:', error);
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: dest.name,
                    icon: hasBeenVisited ? visitedIcon : wishlistIcon
                });
                
                // Add error handling for marker icon loading
                marker.addListener('icon_changed', function() {
                    const img = new Image();
                    img.onerror = function() {
                        marker.setIcon(hasBeenVisited ? visitedMarker : plannedMarker);
                    };
                    img.src = (hasBeenVisited ? visitedIcon : wishlistIcon).url;
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
        
        // Only adjust bounds if we have destinations
        if (destinations.length > 0) {
            map.fitBounds(bounds);
            
            // Prevent zooming in too much for single destinations
            const listener = google.maps.event.addListener(map, 'idle', function() {
                if (map.getZoom() > 12) {
                    map.setZoom(12);
                }
                google.maps.event.removeListener(listener);
            });        }
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
