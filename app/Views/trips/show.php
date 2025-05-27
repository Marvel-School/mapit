<?php
// Simplified Trip Detail view that works with actual database schema
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Trip to <?= htmlspecialchars($trip['destination_name']); ?></h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-1"></i>
                                Created on <?= date('M d, Y', strtotime($trip['created_at'])); ?>
                                
                                <span class="ms-3">
                                    <?php if ($trip['status'] === 'visited'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Visited
                                        </span>
                                    <?php elseif ($trip['status'] === 'planned'): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Planned
                                        </span>
                                    <?php elseif ($trip['status'] === 'in_progress'): ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-route me-1"></i>In Progress
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($trip['status']); ?></span>
                                    <?php endif; ?>
                                </span>
                                
                                <span class="ms-3">
                                    <?php if ($trip['type'] === 'adventure'): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-mountain me-1"></i>Adventure
                                        </span>
                                    <?php elseif ($trip['type'] === 'relaxation'): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-umbrella-beach me-1"></i>Relaxation
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-globe me-1"></i><?= ucfirst($trip['type']); ?>
                                        </span>
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
                    <!-- Destination Information -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-map-pin me-2"></i>Destination Details
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h6><?= htmlspecialchars($trip['destination_name']); ?></h6>
                                <?php if (!empty($trip['destination_description'])): ?>
                                    <p class="text-muted"><?= nl2br(htmlspecialchars($trip['destination_description'])); ?></p>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <a href="/destinations/<?= $trip['destination_id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View Destination Details
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?php if (!empty($trip['latitude']) && !empty($trip['longitude'])): ?>
                                    <div id="destination-map" style="height: 200px;" class="rounded"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trip Actions -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-cog me-2"></i>Trip Actions
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?php if ($trip['status'] === 'planned'): ?>
                                    <button class="btn btn-primary btn-lg w-100 mb-3" onclick="startTrip(<?= $trip['id']; ?>)">
                                        <i class="fas fa-play me-2"></i> Start This Trip
                                    </button>
                                    <p class="text-muted small">Mark your trip as in progress when you begin your journey.</p>
                                <?php elseif ($trip['status'] === 'in_progress'): ?>
                                    <button class="btn btn-success btn-lg w-100 mb-3" onclick="completeTrip(<?= $trip['id']; ?>)">
                                        <i class="fas fa-check me-2"></i> Complete This Trip
                                    </button>
                                    <p class="text-muted small">Mark your trip as visited when you've completed your journey.</p>
                                <?php elseif ($trip['status'] === 'visited'): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-trophy me-2"></i>
                                        <strong>Trip Completed!</strong> You've successfully visited this destination.
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-1"></i>Trip Information
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>Type:</strong> <?= ucfirst($trip['type']); ?></li>
                                            <li><strong>Status:</strong> <?= ucfirst(str_replace('_', ' ', $trip['status'])); ?></li>
                                            <li><strong>Created:</strong> <?= date('F j, Y', strtotime($trip['created_at'])); ?></li>
                                            <?php if (!empty($trip['latitude']) && !empty($trip['longitude'])): ?>
                                                <li><strong>Coordinates:</strong> <?= round($trip['latitude'], 4); ?>, <?= round($trip['longitude'], 4); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Travel Tips -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-lightbulb me-2"></i>Travel Tips
                        </h5>
                        
                        <div class="row">
                            <?php if ($trip['type'] === 'adventure'): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-hiking text-danger fa-2x mb-2"></i>
                                            <h6>Adventure Tips</h6>
                                            <small class="text-muted">Pack light but bring essential gear. Check weather conditions and have backup plans.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-first-aid text-warning fa-2x mb-2"></i>
                                            <h6>Safety First</h6>
                                            <small class="text-muted">Inform someone about your itinerary and carry emergency contacts.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($trip['type'] === 'relaxation'): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-spa text-info fa-2x mb-2"></i>
                                            <h6>Relaxation Tips</h6>
                                            <small class="text-muted">Take time to unwind and disconnect from daily stress. Enjoy local spa treatments.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-camera text-success fa-2x mb-2"></i>
                                            <h6>Capture Memories</h6>
                                            <small class="text-muted">Take photos but also take time to experience moments without a screen.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-compass text-primary fa-2x mb-2"></i>
                                            <h6>Explore Freely</h6>
                                            <small class="text-muted">Be open to unexpected discoveries and local recommendations.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <i class="fas fa-users text-secondary fa-2x mb-2"></i>
                                            <h6>Connect Locally</h6>
                                            <small class="text-muted">Engage with locals and try authentic regional experiences.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function startTrip(tripId) {
    if (confirm('Are you sure you want to start this trip?')) {
        fetch(`/api/trips/${tripId}/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error starting trip: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error starting trip');
        });
    }
}

function completeTrip(tripId) {
    if (confirm('Are you sure you want to mark this trip as completed?')) {
        fetch(`/api/trips/${tripId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error completing trip: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error completing trip');
        });
    }
}

// Initialize map if coordinates are available
document.addEventListener('DOMContentLoaded', function() {
    const mapElement = document.getElementById('destination-map');
    if (mapElement) {
        const lat = <?= !empty($trip['latitude']) ? $trip['latitude'] : 'null'; ?>;
        const lng = <?= !empty($trip['longitude']) ? $trip['longitude'] : 'null'; ?>;
        
        if (lat && lng) {
            // Simple map initialization (assumes Leaflet is available)
            if (typeof L !== 'undefined') {
                const map = L.map('destination-map').setView([lat, lng], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                L.marker([lat, lng]).addTo(map)
                    .bindPopup('<?= htmlspecialchars($trip['destination_name']); ?>')
                    .openPopup();
            }
        }
    }
});
</script>
