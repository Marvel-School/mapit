<?php
// Public Map view - no authentication required
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Explore Travel Destinations</h1>
                    <p class="text-muted mb-0">Discover amazing places shared by our community</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/featured" class="btn btn-outline-primary">
                        <i class="fas fa-star me-1"></i>
                        Featured Places
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="/register" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>
                            Join to Add Places
                        </a>
                    <?php else: ?>
                        <a href="/destinations/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Add Destination
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interactive Map -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div id="destinations-map" class="map-container" style="height: 600px; border-radius: 0.375rem;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading map...</span>
                                </div>
                                <p class="text-muted">Loading interactive map...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Info -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                About This Map
                            </h5>
                            <p class="card-text">
                                This interactive map shows public destinations shared by our community of travelers. 
                                Each marker represents a place someone has visited or recommends. Click on markers 
                                to learn more about each destination.
                            </p>
                            <div class="row g-3 mt-3">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center">
                                        <div class="marker-icon bg-primary rounded-circle me-3" style="width: 20px; height: 20px;"></div>
                                        <span class="text-sm">Public Destinations</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center">
                                        <div class="marker-icon bg-warning rounded-circle me-3" style="width: 20px; height: 20px;"></div>
                                        <span class="text-sm">Featured Places</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-globe text-success me-2"></i>
                                Destination Stats
                            </h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="h4 mb-1 text-primary" id="total-destinations"><?= count($destinations); ?></div>
                                        <small class="text-muted">Total Places</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="h4 mb-1 text-warning" id="featured-count"><?= count($featured); ?></div>
                                        <small class="text-muted">Featured</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Last updated: <?= date('M j, Y'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Destination data for JavaScript -->
<script>
// Make data available to the map
window.publicDestinations = <?= json_encode($destinations); ?>;
window.featuredDestinations = <?= json_encode($featured); ?>;
window.userDestinations = []; // No user destinations for anonymous users
window.isPublicMap = true;

// Debug logging
console.log('Public destinations count:', window.publicDestinations ? window.publicDestinations.length : 'undefined');
console.log('Featured destinations count:', window.featuredDestinations ? window.featuredDestinations.length : 'undefined');
console.log('Public destinations:', window.publicDestinations);
console.log('Featured destinations:', window.featuredDestinations);
</script>

<!-- Map Scripts -->
<script>
// Initialize public map when Google Maps is ready
function initPublicMap() {
    console.log('initPublicMap called');
    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
        if (typeof initDestinationsMap === 'function') {
            console.log('Calling initDestinationsMap');
            initDestinationsMap();
        } else {
            console.warn('initDestinationsMap function not found');
        }
    } else {
        console.warn('Google Maps not ready, retrying in 500ms');
        setTimeout(initPublicMap, 500);
    }
}

// Ensure destinations-map.js is loaded before trying to initialize
function waitForDestinationsMap() {
    if (typeof initDestinationsMap === 'function') {
        initPublicMap();
    } else {
        console.log('Waiting for destinations-map.js to load...');
        setTimeout(waitForDestinationsMap, 100);
    }
}

// Use the existing map loader
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, waiting for map scripts...');
    waitForDestinationsMap();
});

// Also try when Google Maps callback fires
window.googleMapsCallback = function() {
    console.log('Google Maps callback fired');
    setTimeout(initPublicMap, 100);
};
</script>
