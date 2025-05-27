<?php
// Simplified Trips Index view that works with actual database schema
?>

<div class="container py-4">
    <!-- Trip Statistics Overview -->
    <div class="row mb-4 trip-stats-cards">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-map-marked-alt fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= count($trips); ?></h5>
                            <small>Total Trips</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= count(array_filter($trips, function($t) { return $t['status'] === 'visited'; })); ?></h5>
                            <small>Visited</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= count(array_filter($trips, function($t) { return $t['status'] === 'planned'; })); ?></h5>
                            <small>Planned</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-globe-americas fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">
                                <?php 
                                // Count unique destinations from trips
                                $uniqueDestinations = [];
                                foreach($trips as $trip) {
                                    if (!empty($trip['destination_id'])) {
                                        $uniqueDestinations[$trip['destination_id']] = true;
                                    }
                                }
                                echo count($uniqueDestinations);
                                ?>
                            </h5>
                            <small>Destinations</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Your Travel Journal</h4>
                            <small class="text-muted">Track your adventures and plan new ones</small>
                        </div>
                        <div>
                            <a href="/destinations" class="btn btn-outline-primary me-2">
                                <i class="fas fa-map me-1"></i> View Destinations
                            </a>
                            <a href="/trips/create" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Trip
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($trips)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No trips yet!</h4>
                            <p class="text-muted mb-4">Start your travel journey by creating your first trip.</p>
                            <a href="/trips/create" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i> Create Your First Trip
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tripsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th><i class="fas fa-map-pin me-1"></i> Destination</th>
                                        <th><i class="fas fa-tag me-1"></i> Type</th>
                                        <th><i class="fas fa-flag me-1"></i> Status</th>
                                        <th><i class="fas fa-calendar me-1"></i> Created</th>
                                        <th><i class="fas fa-cog me-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trips as $trip): ?>
                                        <tr class="trip-row">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-map-pin text-primary me-2"></i>
                                                    <div>
                                                        <span class="fw-medium d-block">
                                                            <?= htmlspecialchars($trip['destination_name']); ?>
                                                        </span>
                                                        <?php if (!empty($trip['destination_description'])): ?>
                                                            <small class="text-muted">
                                                                <?= htmlspecialchars(substr($trip['destination_description'], 0, 50)); ?>
                                                                <?= strlen($trip['destination_description']) > 50 ? '...' : ''; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($trip['type'] === 'adventure'): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-mountain me-1"></i> Adventure
                                                    </span>
                                                <?php elseif ($trip['type'] === 'relaxation'): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-umbrella-beach me-1"></i> Relaxation
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-globe me-1"></i> <?= ucfirst($trip['type']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($trip['status'] === 'planned'): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i> Planned
                                                    </span>
                                                <?php elseif ($trip['status'] === 'visited'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i> Visited
                                                    </span>
                                                <?php elseif ($trip['status'] === 'in_progress'): ?>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-route me-1"></i> In Progress
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <?= ucfirst($trip['status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($trip['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($trip['status'] === 'planned'): ?>
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="startTrip(<?= $trip['id']; ?>)">
                                                            <i class="fas fa-play me-1"></i> Start
                                                        </button>
                                                    <?php elseif ($trip['status'] === 'in_progress'): ?>
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="completeTrip(<?= $trip['id']; ?>)">
                                                            <i class="fas fa-check me-1"></i> Complete
                                                        </button>
                                                    <?php endif; ?>
                                                    <a href="/destinations/<?= $trip['destination_id']; ?>" 
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Trips pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $currentPage - 1; ?>&status=<?= $filters['status']; ?>&type=<?= $filters['type']; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $i; ?>&status=<?= $filters['status']; ?>&type=<?= $filters['type']; ?>">
                                                <?= $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $currentPage + 1; ?>&status=<?= $filters['status']; ?>&type=<?= $filters['type']; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Travel Tips Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-route fa-2x text-primary mb-2"></i>
                                <h6>Plan Your Route</h6>
                                <small class="text-muted">Add destinations and create your perfect itinerary</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-camera fa-2x text-success mb-2"></i>
                                <h6>Track Your Journey</h6>
                                <small class="text-muted">Mark places as visited and track your progress</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                                <h6>Earn Badges</h6>
                                <small class="text-muted">Complete trips and unlock achievement badges</small>
                            </div>
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
</script>
