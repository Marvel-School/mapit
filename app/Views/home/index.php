<?php
// Home page view
?>

<section class="hero bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Map Your Adventures</h1>
                <p class="lead">Create your personalized travel map, track destinations, and share your journey with the world.</p>
                <div class="d-flex gap-3 mt-4">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="/register" class="btn btn-light btn-lg px-4">Get Started</a>
                        <a href="/about" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    <?php else: ?>
                        <a href="/dashboard" class="btn btn-light btn-lg px-4">My Dashboard</a>
                        <a href="/destinations/create" class="btn btn-outline-light btn-lg px-4">Add Destination</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 d-none d-md-block">
                <img src="/images/world-map.svg" alt="World Map" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="features py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Choose MapIt</h2>
            <p class="lead text-muted">Our platform offers everything you need to track and plan your travels</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                            <i class="fas fa-map-marked-alt fa-2x"></i>
                        </div>
                        <h3 class="h5">Interactive Maps</h3>
                        <p class="card-text">Pin and visualize all your visited and planned destinations on beautiful interactive maps.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                            <i class="fas fa-route fa-2x"></i>
                        </div>
                        <h3 class="h5">Trip Planning</h3>
                        <p class="card-text">Create and organize your trips with custom itineraries, notes, and destination lists.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                            <i class="fas fa-medal fa-2x"></i>
                        </div>
                        <h3 class="h5">Travel Badges</h3>
                        <p class="card-text">Earn badges and track achievements as you explore new countries, continents and landmarks.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featured)): ?>
<section class="featured-destinations py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Featured Destinations</h2>
            <p class="lead text-muted">Discover popular places from our community</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featured as $destination): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <?php if (!empty($destination['image'])): ?>
                            <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" class="card-img-top destination-img" alt="<?= htmlspecialchars($destination['name']); ?>">
                        <?php else: ?>
                            <img src="/images/destination-placeholder.svg" class="card-img-top destination-img" alt="<?= htmlspecialchars($destination['name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($destination['name']); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($destination['city'] . ', ' . $destination['country']); ?>
                            </p>
                            <p class="card-text"><?= htmlspecialchars(substr($destination['description'], 0, 100) . '...'); ?></p>
                            <a href="/destinations/<?= $destination['id']; ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta bg-primary text-white py-5">
    <div class="container text-center">
        <h2 class="mb-3">Ready to Start Your Journey?</h2>
        <p class="lead mb-4">Join thousands of travelers who are mapping their adventures.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="d-flex gap-3 justify-content-center">
                <a href="/register" class="btn btn-light btn-lg px-4">Sign Up Now</a>
                <a href="/login" class="btn btn-outline-light btn-lg px-4">Login</a>
            </div>
        <?php else: ?>
            <a href="/dashboard" class="btn btn-light btn-lg px-4">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</section>

<style>
    .hero {
        background-image: linear-gradient(rgba(13, 110, 253, 0.8), rgba(13, 110, 253, 0.9)), url('/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
    }
    
    .feature-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .destination-img {
        height: 200px;
        object-fit: cover;
    }
    
    .cta {
        background-image: linear-gradient(rgba(13, 110, 253, 0.8), rgba(13, 110, 253, 0.9)), url('/images/cta-bg.svg');
        background-size: cover;
        background-position: center;
    }
</style>
