<?php
// Public Destination Detail view
?>

<div class="container py-4">
    <?php if (empty($destination)): ?>
        <!-- Destination Not Found -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
            </div>
            <h2 class="text-muted">Destination Not Found</h2>
            <p class="text-muted">The destination you're looking for doesn't exist or is not publicly available.</p>
            <div class="mt-4">
                <a href="/featured" class="btn btn-primary me-3">
                    <i class="fas fa-star me-1"></i>
                    View Featured Destinations
                </a>
                <a href="/map" class="btn btn-outline-primary">
                    <i class="fas fa-map me-1"></i>
                    Explore Map
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Back Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/map">Map</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($destination['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Destination Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <?php if (!empty($destination['image'])): ?>
                        <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($destination['name']); ?>"
                             style="height: 400px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <p>No image available</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h1 class="card-title h2 mb-2"><?= htmlspecialchars($destination['name']); ?></h1>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($destination['city'] . ', ' . $destination['country']); ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <?php if ($destination['is_featured']): ?>
                                    <span class="badge bg-warning text-dark mb-2">
                                        <i class="fas fa-star me-1"></i>
                                        Featured
                                    </span><br>
                                <?php endif; ?>
                                <?php if ($destination['visit_status'] === 'visited'): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>
                                        Visited
                                    </span>
                                <?php elseif ($destination['visit_status'] === 'wishlist'): ?>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-heart me-1"></i>
                                        Wishlist
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($destination['description'])): ?>
                            <div class="mb-3">
                                <h5>About This Place</h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($destination['description'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($destination['notes'])): ?>
                            <div class="mb-3">
                                <h5>Travel Notes</h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($destination['notes'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" onclick="showOnMap()">
                                <i class="fas fa-map me-1"></i>
                                View on Map
                            </button>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="/register" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus me-1"></i>
                                    Join to Add Places
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Location Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Location Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-4"><strong>Country:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($destination['country']); ?></div>
                            
                            <div class="col-sm-4"><strong>City:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($destination['city']); ?></div>
                            
                            <?php if (!empty($destination['latitude']) && !empty($destination['longitude'])): ?>
                                <div class="col-sm-4"><strong>Coordinates:</strong></div>
                                <div class="col-sm-8">
                                    <small class="text-muted">
                                        <?= round($destination['latitude'], 4); ?>, <?= round($destination['longitude'], 4); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="col-sm-4"><strong>Added:</strong></div>
                            <div class="col-sm-8">
                                <small class="text-muted">
                                    <?= date('M j, Y', strtotime($destination['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mini Map -->
                <?php if (!empty($destination['latitude']) && !empty($destination['longitude'])): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-map text-primary me-2"></i>
                                Location
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="mini-map" style="height: 200px;">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary mb-2" role="status">
                                            <span class="visually-hidden">Loading map...</span>
                                        </div>
                                        <p class="text-muted small">Loading location...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Call to Action -->
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>Join MapIt</h5>
                            <p class="card-text small">Create your own travel map and share your favorite destinations!</p>
                            <a href="/register" class="btn btn-light btn-sm">
                                <i class="fas fa-user-plus me-1"></i>
                                Sign Up Free
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($destination)): ?>
<!-- Scripts for map functionality -->
<script>
// Destination data
window.currentDestination = <?= json_encode($destination); ?>;

// Show destination on main map
function showOnMap() {
    const lat = parseFloat(window.currentDestination.latitude);
    const lng = parseFloat(window.currentDestination.longitude);
    
    if (!isNaN(lat) && !isNaN(lng)) {
        // Redirect to map with coordinates
        window.location.href = `/map?lat=${lat}&lng=${lng}&zoom=12&highlight=${window.currentDestination.id}`;
    } else {
        // Fallback to general map
        window.location.href = '/map';
    }
}

// Initialize mini map
function initMiniMap() {
    const miniMapElement = document.getElementById('mini-map');
    if (!miniMapElement || typeof google === 'undefined') {
        return;
    }

    const lat = parseFloat(window.currentDestination.latitude);
    const lng = parseFloat(window.currentDestination.longitude);
    
    if (isNaN(lat) || isNaN(lng)) {
        miniMapElement.innerHTML = '<div class="text-center text-muted p-4">Location not available</div>';
        return;
    }

    const map = new google.maps.Map(miniMapElement, {
        zoom: 12,
        center: { lat: lat, lng: lng },
        mapTypeId: 'roadmap',
        disableDefaultUI: true,
        zoomControl: true
    });

    // Add marker for this destination
    new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        title: window.currentDestination.name,
        icon: {
            url: '/images/markers/marker-primary.png',
            scaledSize: new google.maps.Size(30, 30)
        }
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Google Maps to load
    setTimeout(initMiniMap, 100);
});
</script>
<?php endif; ?>
