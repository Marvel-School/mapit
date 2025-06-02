<?php
// 403 Forbidden Error view
?>

<div class="container py-5">
    <div class="text-center">
        <div class="mb-4">
            <i class="fas fa-lock text-warning" style="font-size: 5rem;"></i>
        </div>
        <h1 class="display-4 text-muted mb-3">403 - Access Forbidden</h1>
        <p class="lead text-muted mb-4">
            Sorry, you don't have permission to access this destination. It may be private or require special access.
        </p>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">What you can do:</h5>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2">
                                <i class="fas fa-map text-primary me-2"></i>
                                Explore other destinations on our <a href="/map">interactive map</a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                Check out our <a href="/featured">featured destinations</a>
                            </li>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <li class="mb-2">
                                    <i class="fas fa-user-plus text-success me-2"></i>
                                    <a href="/register">Join MapIt</a> to access more destinations
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-sign-in-alt text-info me-2"></i>
                                    <a href="/login">Login</a> if you already have an account
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
