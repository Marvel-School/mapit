<?php
// Destination Detail view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= htmlspecialchars($destination['name']); ?></h4>
                        <div>
                            <a href="/destinations" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Destinations
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php if (!empty($destination['image'])): ?>
                                <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" alt="<?= htmlspecialchars($destination['name']); ?>" class="img-fluid rounded mb-4 destination-detail-img">
                            <?php else: ?>
                                <img src="/images/destination-placeholder.svg" alt="<?= htmlspecialchars($destination['name']); ?>" class="img-fluid rounded mb-4 destination-detail-img">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">                            <div class="p-3 bg-light rounded mb-4">
                                <h5 class="border-bottom pb-2 mb-3">Destination Info</h5>
                                
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <strong>Location:</strong> <?= htmlspecialchars($destination['city'] . ', ' . $destination['country']); ?>
                                </p>
                                  <p class="mb-2">
                                    <i class="fas fa-tag text-primary me-2"></i>
                                    <strong>Status:</strong> 
                                    <?php if ($trip && $trip['status'] === 'visited'): ?>
                                        <span class="badge bg-success">Visited</span>
                                    <?php elseif ($trip && $trip['status'] === 'planned'): ?>
                                        <span class="badge bg-info">Planned</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Wishlist</span>
                                    <?php endif; ?>
                                </p>
                                
                                <?php if ($trip && $trip['status'] === 'visited' && !empty($trip['updated_at'])): ?>
                                    <p class="mb-2">
                                        <i class="fas fa-calendar text-primary me-2"></i>
                                        <strong>Visited On:</strong> <?= date('F j, Y', strtotime($trip['updated_at'])); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="mb-2">
                                    <i class="fas fa-globe text-primary me-2"></i>
                                    <strong>Coordinates:</strong> <?= $destination['latitude']; ?>, <?= $destination['longitude']; ?>
                                </p>
                                
                                <p class="mb-0">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <strong>Added On:</strong> <?= date('F j, Y', strtotime($destination['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="d-grid gap-2">                                <a href="/destinations/<?= $destination['id']; ?>/edit" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i> Edit Destination
                                </a>
                                
                                <?php if ($trip && $trip['status'] === 'visited'): ?>
                                    <button class="btn btn-outline-warning" onclick="updateTripStatus(<?= $destination['id']; ?>, 'planned')">
                                        <i class="fas fa-heart me-1"></i> Move to Wishlist
                                    </button>
                                <?php elseif ($trip && $trip['status'] === 'planned'): ?>
                                    <button class="btn btn-outline-success" onclick="updateTripStatus(<?= $destination['id']; ?>, 'visited')">
                                        <i class="fas fa-check me-1"></i> Mark as Visited
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-info" onclick="createTrip(<?= $destination['id']; ?>, 'planned')">
                                        <i class="fas fa-plus me-1"></i> Add to Wishlist
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash-alt me-1"></i> Delete Destination
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Description</h5>
                            <div class="destination-description">
                                <?php if (!empty($destination['description'])): ?>
                                    <p><?= nl2br(htmlspecialchars($destination['description'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No description provided.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Location on Map</h5>
                            <div id="destination-map" class="rounded" style="height: 400px;"></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($nearbyDestinations)): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Nearby Destinations</h5>
                            <div class="row">
                                <?php foreach ($nearbyDestinations as $nearby): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <?php if (!empty($nearby['image'])): ?>
                                                <img src="/images/destinations/<?= htmlspecialchars($nearby['image']); ?>" class="card-img-top nearby-img" alt="<?= htmlspecialchars($nearby['name']); ?>">
                                            <?php else: ?>
                                                <img src="/images/destination-placeholder.svg" class="card-img-top nearby-img" alt="<?= htmlspecialchars($nearby['name']); ?>">
                                            <?php endif; ?>
                                            
                                            <div class="card-body">                                                <h5 class="card-title"><?= htmlspecialchars($nearby['name']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= htmlspecialchars($nearby['city'] . ', ' . $nearby['country']); ?>
                                                </p>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <?= round($nearby['distance'], 1); ?> km away
                                                    </small>
                                                </p>
                                                <a href="/destinations/<?= $nearby['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($relatedTrips)): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">Related Trips</h5>
                            <div class="list-group">
                                <?php foreach ($relatedTrips as $trip): ?>
                                    <a href="/trips/<?= $trip['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($trip['name']); ?></h6>
                                            <small>
                                                <?php if ($trip['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php elseif ($trip['status'] === 'planned'): ?>
                                                    <span class="badge bg-warning">Planned</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">In Progress</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M d, Y', strtotime($trip['start_date'])); ?> - 
                                            <?= date('M d, Y', strtotime($trip['end_date'])); ?>
                                        </p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><?= htmlspecialchars($destination['name']); ?></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/destinations/<?= $destination['id']; ?>/delete" method="POST">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .destination-detail-img {
        width: 100%;
        height: 400px;
        object-fit: cover;
    }
    
    .nearby-img {
        height: 150px;
        object-fit: cover;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Google Maps to load
    function waitForGoogleMaps() {
        if (typeof google !== 'undefined' && google.maps) {
            initDestinationMap();
        } else {
            setTimeout(waitForGoogleMaps, 100);
        }
    }
    
    // Add to global callback queue if maps aren't loaded yet
    if (typeof google === 'undefined' || !google.maps) {
        if (!window.googleMapsCallbacks) {
            window.googleMapsCallbacks = [];
        }
        window.googleMapsCallbacks.push(initDestinationMap);
        waitForGoogleMaps();
    } else {
        initDestinationMap();
    }
    
    function initDestinationMap() {
        const mapElement = document.getElementById('destination-map');
        if (!mapElement) return;
        
        const lat = <?= $destination['latitude']; ?>;
        const lng = <?= $destination['longitude']; ?>;
          const map = new google.maps.Map(mapElement, {
            center: { lat, lng },
            zoom: 10,
            mapTypeId: 'terrain',
            mapTypeControl: false,
            mapId: 'MAPIT_DESTINATION_SHOW_MAP'
        });
          // Create marker for the destination with AdvancedMarkerElement or fallback
        let marker;
        try {
            // Try using AdvancedMarkerElement first
            if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                const iconUrl = '<?= ($trip && $trip['status'] === 'visited') ? '/images/markers/visited.png' : '/images/markers/wishlist.png'; ?>';
                const iconElement = document.createElement('img');
                iconElement.src = iconUrl;
                iconElement.style.width = '32px';
                iconElement.style.height = '32px';
                
                marker = new google.maps.marker.AdvancedMarkerElement({
                    position: { lat, lng },
                    map: map,
                    title: '<?= addslashes(htmlspecialchars($destination['name'])); ?>',
                    content: iconElement
                });
            } else {
                throw new Error('AdvancedMarkerElement not available');
            }
        } catch (error) {
            // Fallback to legacy Marker
            marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                title: '<?= addslashes(htmlspecialchars($destination['name'])); ?>',
                animation: google.maps.Animation.DROP,
                icon: {
                    url: '<?= ($trip && $trip['status'] === 'visited') ? '/images/markers/visited.png' : '/images/markers/wishlist.png'; ?>',
                    scaledSize: new google.maps.Size(32, 32)
                }
            });
        }
        
        // Add info window        const infoWindow = new google.maps.InfoWindow({
            content: `                <div class="info-window">
                    <h5><?= addslashes(htmlspecialchars($destination['name'])); ?></h5>
                    <p><?= addslashes(htmlspecialchars($destination['city'] . ', ' . $destination['country'])); ?></p>
                    <?php if ($trip && $trip['status'] === 'visited' && !empty($trip['updated_at'])): ?>
                        <p><small>Visited on: <?= date('M d, Y', strtotime($trip['updated_at'])); ?></small></p>
                    <?php endif; ?>
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
          // Show nearby destinations if available
        <?php if (!empty($nearbyDestinations)): ?>
            <?php foreach ($nearbyDestinations as $nearby): ?>
                // Create nearby marker with AdvancedMarkerElement or fallback
                let nearbyMarker;
                try {
                    if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                        const nearbyIconElement = document.createElement('img');
                        nearbyIconElement.src = '/images/markers/wishlist.png';
                        nearbyIconElement.style.width = '24px';
                        nearbyIconElement.style.height = '24px';
                        
                        nearbyMarker = new google.maps.marker.AdvancedMarkerElement({
                            position: { 
                                lat: <?= $nearby['latitude']; ?>, 
                                lng: <?= $nearby['longitude']; ?> 
                            },
                            map: map,
                            title: '<?= addslashes(htmlspecialchars($nearby['name'])); ?>',
                            content: nearbyIconElement
                        });
                    } else {
                        throw new Error('AdvancedMarkerElement not available');
                    }
                } catch (error) {
                    nearbyMarker = new google.maps.Marker({
                        position: { 
                            lat: <?= $nearby['latitude']; ?>, 
                            lng: <?= $nearby['longitude']; ?> 
                        },
                        map: map,
                        title: '<?= addslashes(htmlspecialchars($nearby['name'])); ?>',
                        icon: {
                            url: '/images/markers/wishlist.png',
                            scaledSize: new google.maps.Size(24, 24)
                        }
                    });
                }
                  const nearbyInfo = new google.maps.InfoWindow({
                    content: `                        <div class="info-window">
                            <h5><?= addslashes(htmlspecialchars($nearby['name'])); ?></h5>
                            <p><?= addslashes(htmlspecialchars($nearby['city'] . ', ' . $nearby['country'])); ?></p>
                            <p><small><?= round($nearby['distance'], 1); ?> km away</small></p>
                            <a href="/destinations/<?= $nearby['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    `
                });
                
                // Add click listener based on marker type
                if (google.maps.marker && google.maps.marker.AdvancedMarkerElement &&
                    nearbyMarker instanceof google.maps.marker.AdvancedMarkerElement) {
                    nearbyMarker.addEventListener('click', () => {
                        nearbyInfo.open({
                            anchor: nearbyMarker,
                            map: map
                        });
                    });
                } else {
                    nearbyMarker.addListener('click', () => {
                        nearbyInfo.open(map, nearbyMarker);
                    });
                }
            <?php endforeach; ?>
        <?php endif; ?>
    }
});

// Function to update trip status
function updateTripStatus(destinationId, status) {
    fetch(`/api/trips`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            destination_id: destinationId, 
            status: status 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update trip status');
        }
    })
    .catch(error => {
        console.error('Error updating trip status:', error);
        alert('An error occurred while updating the trip status');
    });
}

// Function to create a new trip
function createTrip(destinationId, status) {
    fetch(`/api/trips`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            destination_id: destinationId, 
            status: status,
            type: 'adventure' // Default type, could be made configurable
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to create trip');
        }
    })
    .catch(error => {
        console.error('Error creating trip:', error);
        alert('An error occurred while creating the trip');
    });
}
</script>
