<?php
// Public Featured Destinations view
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Featured Destinations</h1>
        <p class="lead text-muted">Discover the most popular and highly recommended places from our community</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="/map" class="btn btn-outline-primary">
                <i class="fas fa-map me-1"></i>
                View on Map
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/register" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i>
                    Join MapIt
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($featured)): ?>
        <!-- No Featured Destinations -->
        <div class="text-center py-5">
            <img src="/images/empty-destinations.svg" alt="No featured destinations" class="img-fluid mb-4" style="max-width: 300px;">
            <h3 class="text-muted">No Featured Destinations Yet</h3>
            <p class="text-muted">Check back soon as our community shares more amazing places!</p>
            <a href="/map" class="btn btn-primary mt-3">
                <i class="fas fa-map me-1"></i>
                Explore All Destinations
            </a>
        </div>
    <?php else: ?>
        <!-- Featured Destinations Grid -->
        <div class="row g-4">
            <?php foreach ($featured as $destination): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm destination-card">
                        <!-- Destination Image -->
                        <?php if (!empty($destination['image'])): ?>
                            <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" 
                                 class="card-img-top destination-img" 
                                 alt="<?= htmlspecialchars($destination['name']); ?>"
                                 style="height: 250px; object-fit: cover;">
                        <?php else: ?>
                            <img src="/images/destination-placeholder.svg" 
                                 class="card-img-top destination-img" 
                                 alt="<?= htmlspecialchars($destination['name']); ?>"
                                 style="height: 250px; object-fit: cover;">
                        <?php endif; ?>

                        <!-- Featured Badge -->
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-star me-1"></i>
                                Featured
                            </span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <!-- Destination Name -->
                            <h5 class="card-title mb-2"><?= htmlspecialchars($destination['name']); ?></h5>
                            
                            <!-- Location -->
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($destination['city'] . ', ' . $destination['country']); ?>
                            </p>

                            <!-- Description -->
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars(substr($destination['description'] ?? 'No description available.', 0, 120) . (strlen($destination['description'] ?? '') > 120 ? '...' : '')); ?>
                            </p>

                            <!-- Metadata -->
                            <div class="card-text">
                                <small class="text-muted d-flex justify-content-between align-items-center">                                    <span>
                                        <i class="fas fa-calendar me-1"></i>
                                        Added <?= date('M j, Y', strtotime($destination['created_at'])); ?>
                                    </span>
                                    <?php if ($destination['featured']): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-star me-1"></i>
                                            Featured
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>

                            <!-- Action Button -->
                            <div class="mt-3">
                                <a href="/destination/<?= $destination['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination or Load More (if needed) -->
        <?php if (count($featured) >= 12): ?>
            <div class="text-center mt-5">
                <p class="text-muted">Showing <?= count($featured); ?> featured destinations</p>
                <a href="/map" class="btn btn-primary">
                    <i class="fas fa-map me-1"></i>
                    View All on Map
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Call to Action -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 bg-primary text-white">
                    <div class="card-body text-center py-5">
                        <h3>Join Our Travel Community</h3>
                        <p class="mb-4">Share your own travel experiences and discover new destinations from fellow travelers around the world.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="/register" class="btn btn-light btn-lg">
                                <i class="fas fa-user-plus me-1"></i>
                                Sign Up Free
                            </a>
                            <a href="/login" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.destination-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.destination-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.destination-img {
    transition: transform 0.3s ease-in-out;
}

.destination-card:hover .destination-img {
    transform: scale(1.05);
}
</style>
