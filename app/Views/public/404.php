<?php
// 404 Not Found Error view
?>

<div class="container py-5">
    <div class="text-center">
        <div class="mb-4">
            <i class="fas fa-map-marked-alt text-muted" style="font-size: 5rem;"></i>
        </div>
        <h1 class="display-4 text-muted mb-3">404 - Destination Not Found</h1>
        <p class="lead text-muted mb-4">
            We couldn't find the destination you're looking for. It may have been moved, removed, or the link is incorrect.
        </p>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Let's get you back on track:</h5>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2">
                                <i class="fas fa-search text-primary me-2"></i>
                                Browse all destinations on our <a href="/map">interactive map</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                Discover popular places in <a href="/featured">featured destinations</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-home text-success me-2"></i>
                                Start fresh from our <a href="/">home page</a>
                            </li>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <li class="mb-2">
                                    <i class="fas fa-plus text-info me-2"></i>
                                    <a href="/register">Join MapIt</a> to add your own destinations
                                </li>
                            <?php else: ?>
                                <li class="mb-2">
                                    <i class="fas fa-plus text-info me-2"></i>
                                    <a href="/destinations/create">Add a new destination</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="/map" class="btn btn-primary me-3">
                <i class="fas fa-map me-1"></i>
                Explore Map
            </a>
            <a href="/featured" class="btn btn-outline-primary me-3">
                <i class="fas fa-star me-1"></i>
                Featured Places
            </a>
            <a href="/" class="btn btn-outline-secondary">
                <i class="fas fa-home me-1"></i>
                Go Home
            </a>
        </div>
    </div>
</div>
