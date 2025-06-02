<?php
// Enhanced Badges Index view
?>

<div class="container py-4">
    <!-- Badge Notifications -->
    <?php if (!empty($notifications)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <h5 class="alert-heading"><i class="fas fa-trophy me-2"></i>New Achievement<?= count($notifications) > 1 ? 's' : '' ?>!</h5>
            <?php foreach ($notifications as $notification): ?>
                <div class="mb-1">
                    <strong><?= htmlspecialchars($notification['name']) ?></strong> - <?= htmlspecialchars($notification['description']) ?>
                    <span class="badge bg-primary ms-2"><?= $notification['points'] ?> pts</span>
                </div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="markNotificationsRead()"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-medal me-2"></i>My Travel Achievements</h4>
                        <div class="badge bg-light text-dark fs-6">
                            Level <?= $userBadgeStats['level'] ?? 1 ?>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- User Stats Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-1"><?= $userBadgeStats['badges_earned'] ?? 0 ?></div>
                                    <div class="small">Badges Earned</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-1"><?= $userBadgeStats['total_points'] ?? 0 ?></div>
                                    <div class="small">Total Points</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-1"><?= $userBadgeStats['countries_visited'] ?? 0 ?></div>
                                    <div class="small">Countries Visited</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body text-center">
                                    <?php
                                    $completionRate = isset($userBadgeStats['completion_rate']) ? $userBadgeStats['completion_rate'] : 0;
                                    ?>
                                    <div class="display-4 mb-1"><?= round($completionRate) ?>%</div>
                                    <div class="small">Completion Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Achievements -->
                    <?php if (!empty($recentAchievements)): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Recent Achievements</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($recentAchievements as $achievement): ?>
                                        <div class="col-md-2 text-center mb-3">
                                            <div class="badge-icon-sm mb-2">
                                                <img src="/images/badges/<?= htmlspecialchars($achievement['icon'] ?: 'default.png') ?>" 
                                                     alt="<?= htmlspecialchars($achievement['name']) ?>" 
                                                     class="img-fluid rounded-circle" style="width: 50px; height: 50px;">
                                            </div>
                                            <div class="small fw-bold"><?= htmlspecialchars($achievement['name']) ?></div>
                                            <div class="small text-muted"><?= date('M d', strtotime($achievement['earned_date'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Category Progress -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Category Progress</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($categoryProgress as $category => $data): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-capitalize"><?= htmlspecialchars($category) ?></span>
                                            <small class="text-muted"><?= $data['earned'] ?>/<?= $data['total'] ?></small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $data['progress'] ?>%;" 
                                                 aria-valuenow="<?= $data['progress'] ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?= $data['progress'] ?>%
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Badges by Category -->
                    <nav>
                        <div class="nav nav-tabs mb-4" id="category-tabs" role="tablist">
                            <?php $isFirst = true; ?>
                            <?php foreach ($badgesByCategory as $category => $badges): ?>
                                <button class="nav-link <?= $isFirst ? 'active' : '' ?>" 
                                        id="<?= $category ?>-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#<?= $category ?>" 
                                        type="button" role="tab">
                                    <span class="text-capitalize"><?= htmlspecialchars($category) ?></span>
                                    <span class="badge bg-secondary ms-1"><?= count($badges) ?></span>
                                </button>
                                <?php $isFirst = false; ?>
                            <?php endforeach; ?>
                        </div>
                    </nav>

                    <div class="tab-content" id="category-content">
                        <?php $isFirst = true; ?>
                        <?php foreach ($badgesByCategory as $category => $badges): ?>
                            <div class="tab-pane fade <?= $isFirst ? 'show active' : '' ?>" 
                                 id="<?= $category ?>" role="tabpanel">
                                
                                <div class="row g-4">
                                    <?php foreach ($badges as $badge): ?>
                                        <?php
                                        // Find progress for this badge
                                        $badgeProgress = null;
                                        foreach ($badgesWithProgress as $progressBadge) {
                                            if ($progressBadge['id'] == $badge['id']) {
                                                $badgeProgress = $progressBadge;
                                                break;
                                            }
                                        }
                                        $isEarned = $badgeProgress ? $badgeProgress['earned'] : false;
                                        $progress = $badgeProgress ? $badgeProgress['progress'] : 0;
                                        ?>
                                        
                                        <div class="col-md-4 col-lg-3">
                                            <div class="card h-100 badge-card <?= $isEarned ? 'earned' : 'locked' ?>">
                                                <div class="card-body text-center p-3">
                                                    <div class="position-relative mb-3">
                                                        <div class="badge-icon-lg">
                                                            <img src="/images/badges/<?= htmlspecialchars($badge['icon'] ?: 'default.png') ?>" 
                                                                 alt="<?= htmlspecialchars($badge['name']) ?>" 
                                                                 class="img-fluid <?= $isEarned ? '' : 'grayscale' ?>"
                                                                 style="width: 80px; height: 80px;">
                                                        </div>
                                                        <?php if (!$isEarned): ?>
                                                            <div class="badge-overlay">
                                                                <i class="fas fa-lock text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Difficulty indicator -->
                                                        <span class="badge badge-difficulty bg-<?= $badge['difficulty'] == 'easy' ? 'success' : ($badge['difficulty'] == 'medium' ? 'warning' : 'danger') ?> position-absolute top-0 end-0">
                                                            <?= ucfirst($badge['difficulty']) ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <h6 class="card-title mb-2"><?= htmlspecialchars($badge['name']) ?></h6>
                                                    <p class="card-text small text-muted mb-2"><?= htmlspecialchars($badge['description']) ?></p>
                                                    
                                                    <?php if ($isEarned): ?>
                                                        <div class="text-success mb-2">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            <small>Earned!</small>
                                                        </div>
                                                        <div class="badge bg-primary">+<?= $badge['points'] ?> pts</div>
                                                    <?php else: ?>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar" role="progressbar" 
                                                                 style="width: <?= $progress ?>%;"
                                                                 aria-valuenow="<?= $progress ?>" 
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <div class="small text-muted mb-2"><?= round($progress) ?>% complete</div>
                                                        <div class="badge bg-outline-primary">+<?= $badge['points'] ?> pts</div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-2">
                                                        <a href="/badges/<?= $badge['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                            View Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $isFirst = false; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 2px solid transparent;
}

.badge-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.badge-card.earned {
    border-color: #28a745;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
}

.badge-card.locked {
    border-color: #dee2e6;
}

.badge-icon-lg {
    position: relative;
    display: inline-block;
}

.badge-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.grayscale {
    filter: grayscale(100%);
    opacity: 0.6;
}

.badge-difficulty {
    font-size: 0.65rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.nav-tabs .nav-link {
    border-radius: 0.5rem 0.5rem 0 0;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-color: #007bff;
}
</style>

<script>
function markNotificationsRead() {
    fetch('/badges/mark-notifications-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_ids: null // Mark all as read
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Notification will be hidden by Bootstrap dismiss
        }
    })
    .catch(error => {
        console.error('Error marking notifications as read:', error);
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
