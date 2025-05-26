<?php
// Trip Detail view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?= htmlspecialchars($trip['name']); ?></h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('M d, Y', strtotime($trip['start_date'])); ?> - 
                                <?= date('M d, Y', strtotime($trip['end_date'])); ?>
                                
                                <span class="ms-3">
                                    <?php if ($trip['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($trip['status'] === 'planned'): ?>
                                        <span class="badge bg-warning">Planned</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">In Progress</span>
                                    <?php endif; ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <a href="/trips" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Trips
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($trip['description'])): ?>
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Description</h5>
                            <p><?= nl2br(htmlspecialchars($trip['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Trip Itinerary</h5>
                        
                        <?php if (empty($tripDestinations)): ?>
                            <p class="text-muted">No destinations have been added to this trip yet.</p>
                            <a href="/trips/<?= $trip['id']; ?>/edit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Destinations
                            </a>
                        <?php else: ?>
                            <div id="trip-map" class="mb-4" style="height: 400px;"></div>
                            
                            <div class="timeline mt-4">
                                <?php foreach ($tripDestinations as $index => $destination): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"><?= $index + 1; ?></div>
                                        <div class="timeline-content">
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <?php if (!empty($destination['image'])): ?>
                                                                <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" 
                                                                    alt="<?= htmlspecialchars($destination['name']); ?>" 
                                                                    class="img-fluid rounded mb-3 mb-md-0 timeline-img">
                                                            <?php else: ?>
                                                                <img src="/images/destination-placeholder.svg" 
                                                                    alt="<?= htmlspecialchars($destination['name']); ?>" 
                                                                    class="img-fluid rounded mb-3 mb-md-0 timeline-img">
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <h5><?= htmlspecialchars($destination['name']); ?></h5>
                                                            <p class="text-muted">
                                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                                <?= htmlspecialchars($destination['city'] . ', ' . $destination['country_name']); ?>
                                                            </p>
                                                            <p><?= htmlspecialchars(substr($destination['description'], 0, 150) . (strlen($destination['description']) > 150 ? '...' : '')); ?></p>
                                                            <a href="/destinations/<?= $destination['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-info-circle me-1"></i> View Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Trip Statistics</h5>
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                            <strong>Destinations:</strong> <?= count($tripDestinations); ?>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-calendar-day me-2 text-primary"></i>
                                            <strong>Duration:</strong> 
                                            <?php
                                            $start = new DateTime($trip['start_date']);
                                            $end = new DateTime($trip['end_date']);
                                            $diff = $start->diff($end);
                                            echo $diff->days + 1 . ' ' . (($diff->days + 1) === 1 ? 'day' : 'days');
                                            ?>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-globe-americas me-2 text-primary"></i>
                                            <strong>Countries:</strong> 
                                            <?php 
                                            $countries = array_unique(array_column($tripDestinations, 'country'));
                                            echo count($countries); 
                                            ?>
                                        </li>
                                        <?php if (!empty($tripDestinations)): ?>
                                            <li>
                                                <i class="fas fa-route me-2 text-primary"></i>
                                                <strong>Total Distance:</strong> 
                                                <?= number_format($totalDistance ?? 0); ?> km
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <?php if (!empty($trip['notes'])): ?>
                                <h5 class="mb-3 mt-4">Trip Notes</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <?= nl2br(htmlspecialchars($trip['notes'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Actions</h5>
                            
                            <div class="list-group mb-4">
                                <a href="/trips/<?= $trip['id']; ?>/edit" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><i class="fas fa-edit me-2"></i> Edit Trip</h6>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                    <p class="mb-1 small text-muted">Modify trip details or update the itinerary</p>
                                </a>
                                
                                <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#statusModal">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><i class="fas fa-tag me-2"></i> Update Status</h6>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                    <p class="mb-1 small text-muted">Mark as planned, in progress, or completed</p>
                                </button>
                                
                                <?php if (count($tripDestinations) > 0): ?>
                                    <button type="button" class="list-group-item list-group-item-action" onclick="exportTripItinerary()">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><i class="fas fa-file-export me-2"></i> Export Itinerary</h6>
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                        <p class="mb-1 small text-muted">Download trip details as PDF or CSV</p>
                                    </button>
                                <?php endif; ?>
                                
                                <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#shareModal">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><i class="fas fa-share-alt me-2"></i> Share Trip</h6>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                    <p class="mb-1 small text-muted">Share your trip with friends or social media</p>
                                </button>
                                
                                <button type="button" class="list-group-item list-group-item-action text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><i class="fas fa-trash-alt me-2"></i> Delete Trip</h6>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                    <p class="mb-1 small text-muted">Permanently remove this trip</p>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Trip Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/trips/<?= $trip['id']; ?>/status" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">Select Status</label>
                        <select class="form-select" id="statusSelect" name="status">
                            <option value="planned" <?= $trip['status'] === 'planned' ? 'selected' : ''; ?>>Planned</option>
                            <option value="in_progress" <?= $trip['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?= $trip['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share Trip Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Share Your Trip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Share this trip with your friends and family:</p>
                
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="shareLink" value="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/shared/trips/' . ($trip['share_code'] ?? ''); ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyShareLink()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . '/shared/trips/' . ($trip['share_code'] ?? '')); ?>" target="_blank" class="btn btn-primary">
                        <i class="fab fa-facebook-f me-2"></i> Share on Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . '/shared/trips/' . ($trip['share_code'] ?? '')); ?>&text=<?= urlencode('Check out my trip: ' . $trip['name']); ?>" target="_blank" class="btn btn-info text-white">
                        <i class="fab fa-twitter me-2"></i> Share on Twitter
                    </a>
                    <a href="mailto:?subject=<?= urlencode('Check out my trip: ' . $trip['name']); ?>&body=<?= urlencode('I wanted to share my trip with you: ' . 'https://' . $_SERVER['HTTP_HOST'] . '/shared/trips/' . ($trip['share_code'] ?? '')); ?>" class="btn btn-secondary">
                        <i class="fas fa-envelope me-2"></i> Share via Email
                    </a>
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
                <p>Are you sure you want to delete <strong><?= htmlspecialchars($trip['name']); ?></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/trips/<?= $trip['id']; ?>/delete" method="POST">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0d6efd;
        color: white;
        text-align: center;
        line-height: 24px;
        font-weight: bold;
        font-size: 12px;
    }
    
    .timeline-content {
        padding-left: 15px;
    }
    
    .timeline-img {
        height: 150px;
        object-fit: cover;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 12px;
        height: calc(100% - 12px);
        width: 2px;
        background: #dee2e6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    initTripMap();
    
    function initTripMap() {
        const mapElement = document.getElementById('trip-map');
        if (!mapElement) return;
          const map = new google.maps.Map(mapElement, {
            center: { lat: 20, lng: 0 },
            zoom: 2,
            mapTypeId: 'terrain',
            mapTypeControl: false,
            mapId: 'MAPIT_TRIP_SHOW_MAP'
        });
        
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true
        });
        
        const destinations = <?= json_encode($tripDestinations); ?>;
        
        if (destinations.length === 0) return;
        
        // Add markers for each destination
        const markers = [];
        const bounds = new google.maps.LatLngBounds();
          destinations.forEach((dest, index) => {
            const position = {
                lat: parseFloat(dest.latitude), 
                lng: parseFloat(dest.longitude)
            };
            
            // Create marker with AdvancedMarkerElement or fallback
            let marker;
            try {
                if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                    const markerElement = document.createElement('div');
                    markerElement.innerHTML = (index + 1).toString();
                    markerElement.style.background = '#4285f4';
                    markerElement.style.color = 'white';
                    markerElement.style.borderRadius = '50%';
                    markerElement.style.width = '30px';
                    markerElement.style.height = '30px';
                    markerElement.style.display = 'flex';
                    markerElement.style.alignItems = 'center';
                    markerElement.style.justifyContent = 'center';
                    markerElement.style.fontWeight = 'bold';
                    markerElement.style.fontSize = '14px';
                    
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: position,
                        map: map,
                        title: dest.name,
                        content: markerElement
                    });
                } else {
                    throw new Error('AdvancedMarkerElement not available');
                }
            } catch (error) {
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: dest.name,
                    label: (index + 1).toString()
                });
            }
            
            markers.push(marker);
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="info-window">
                        <h5>${dest.name}</h5>
                        <p>${dest.city}, ${dest.country_name}</p>
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
        
        // If there are at least 2 destinations, draw a route
        if (destinations.length > 1) {
            const waypoints = destinations.slice(1, -1).map(dest => {
                return {
                    location: new google.maps.LatLng(parseFloat(dest.latitude), parseFloat(dest.longitude)),
                    stopover: true
                };
            });
            
            const origin = new google.maps.LatLng(
                parseFloat(destinations[0].latitude),
                parseFloat(destinations[0].longitude)
            );
            
            const destination = new google.maps.LatLng(
                parseFloat(destinations[destinations.length - 1].latitude),
                parseFloat(destinations[destinations.length - 1].longitude)
            );
            
            directionsService.route({
                origin: origin,
                destination: destination,
                waypoints: waypoints,
                optimizeWaypoints: false,
                travelMode: google.maps.TravelMode.DRIVING
            }, function(response, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                }
            });
        } else {
            map.fitBounds(bounds);
        }
    }
});

// Copy share link to clipboard
function copyShareLink() {
    const shareLinkInput = document.getElementById('shareLink');
    shareLinkInput.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}

// Export trip itinerary
function exportTripItinerary() {
    // This would normally call an API endpoint to generate and download the export
    // For now, we'll just show an alert
    alert('Export feature will be implemented in a future update.');
}
</script>
