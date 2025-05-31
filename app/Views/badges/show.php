<?php
// Enhanced Badge Show view - Individual badge details
?>

<div class="container py-4">
    <div class="row">
        <!-- Badge Header -->
        <div class="col-md-12 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/badges">Badges</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($badge['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Main Badge Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="badge-showcase">
                                <img src="/images/badges/<?= htmlspecialchars($badge['icon'] ?: 'default.png') ?>" 
                                     alt="<?= htmlspecialchars($badge['name']) ?>" 
                                     class="img-fluid <?= $earnedBadge ? 'earned-badge' : 'locked-badge' ?>"
                                     style="width: 120px; height: 120px;">
                                
                                <?php if (!$earnedBadge): ?>
                                    <div class="lock-overlay">
                                        <i class="fas fa-lock fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($earnedBadge): ?>
                                    <div class="earned-checkmark">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h1 class="h2 mb-0"><?= htmlspecialchars($badge['name']) ?></h1>
                                <div class="badge-meta">
                                    <span class="badge bg-<?= $badge['difficulty'] == 'easy' ? 'success' : ($badge['difficulty'] == 'medium' ? 'warning' : 'danger') ?> me-2">
                                        <?= ucfirst($badge['difficulty']) ?>
                                    </span>
                                    <span class="badge bg-primary">
                                        <?= $badge['points'] ?> pts
                                    </span>
                                </div>
                            </div>
                            
                            <p class="text-muted mb-3"><?= htmlspecialchars($badge['description']) ?></p>
                            
                            <div class="badge-details">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">Category</h6>
                                        <p class="mb-2">
                                            <span class="badge bg-info text-capitalize">
                                                <?= htmlspecialchars($badge['category']) ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">Requirement</h6>
                                        <p class="mb-2"><?= $badge['threshold'] ?> achievement<?= $badge['threshold'] > 1 ? 's' : '' ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($earnedBadge): ?>
                                <div class="earned-info mt-3">
                                    <div class="alert alert-success d-flex align-items-center">
                                        <i class="fas fa-trophy me-2"></i>                                        <div>
                                            <strong>Congratulations!</strong> You earned this badge on 
                                            <?= date('F j, Y \a\t g:i A', strtotime($earnedBadge['earned_date'])) ?>.
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($progress): ?>
                                <div class="progress-info mt-3">
                                    <h6 class="mb-2">Your Progress</h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= $progress['current'] ?> / <?= $progress['required'] ?></span>
                                        <span class="fw-bold"><?= $progress['percentage'] ?>%</span>
                                    </div>
                                    <div class="progress mb-3" style="height: 12px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: <?= $progress['percentage'] ?>%;"
                                             aria-valuenow="<?= $progress['percentage'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    
                                    <?php if ($progress['percentage'] < 100): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            You need <?= ($progress['required'] - $progress['current']) ?> more to earn this badge!
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Earners -->
            <?php if (!empty($recentEarners)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Achievers</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach (array_slice($recentEarners, 0, 8) as $earner): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-initial rounded-circle bg-primary">
                                                <?= strtoupper(substr($earner['username'], 0, 2)) ?>
                                            </div>
                                        </div>                                        <div>
                                            <div class="fw-bold small"><?= htmlspecialchars($earner['username']) ?></div>
                                            <div class="text-muted small"><?= date('M j', strtotime($earner['earned_date'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($recentEarners) > 8): ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    And <?= (count($recentEarners) - 8) ?> more user<?= (count($recentEarners) - 8) > 1 ? 's' : '' ?> have earned this badge.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/badges" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to All Badges
                        </a>
                        
                        <?php if (!$earnedBadge): ?>
                            <?php if ($badge['category'] === 'exploration'): ?>
                                <a href="/destinations" class="btn btn-primary">
                                    <i class="fas fa-map-marked-alt me-2"></i>Explore Destinations
                                </a>
                            <?php elseif ($badge['category'] === 'planning'): ?>
                                <a href="/trips/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Plan a Trip
                                </a>
                            <?php else: ?>
                                <a href="/dashboard" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Go to Dashboard
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <a href="/profile" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Related Badges -->
            <?php if (!empty($relatedBadges)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-medal me-2"></i>Related Badges</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($relatedBadges as $related): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <img src="/images/badges/<?= htmlspecialchars($related['icon'] ?: 'default.png') ?>" 
                                         alt="<?= htmlspecialchars($related['name']) ?>" 
                                         class="img-fluid rounded-circle"
                                         style="width: 40px; height: 40px;">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="/badges/<?= $related['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($related['name']) ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted"><?= $related['points'] ?> points</small>
                                </div>
                                <div>
                                    <span class="badge bg-<?= $related['difficulty'] == 'easy' ? 'success' : ($related['difficulty'] == 'medium' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($related['difficulty']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Badge Statistics -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Badge Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-0"><?= count($recentEarners) ?></h4>
                                <small class="text-muted">Total Earners</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-0"><?= ucfirst($badge['difficulty']) ?></h4>
                            <small class="text-muted">Difficulty</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-showcase {
    position: relative;
    display: inline-block;
}

.locked-badge {
    filter: grayscale(100%);
    opacity: 0.6;
}

.earned-badge {
    filter: none;
    opacity: 1;
}

.lock-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.earned-checkmark {
    position: absolute;
    bottom: -10px;
    right: -10px;
    background: white;
    border-radius: 50%;
    padding: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.badge-meta .badge {
    font-size: 0.8rem;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.125);
    background-color: #f8f9fa !important;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}
</style>
