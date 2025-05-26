<?php
// Badges Index view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">My Travel Achievements</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">                            <div class="progress-stats">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-body text-center">
                                                <div class="display-4 mb-1"><?= count($earnedBadges ?? []); ?></div>
                                                <div class="text-muted">Badges Earned</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-body text-center">
                                                <div class="display-4 mb-1"><?= isset($userStats['countries_visited']) ? $userStats['countries_visited'] : 0; ?></div>
                                                <div class="text-muted">Countries Visited</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-body text-center">
                                                <div class="display-4 mb-1"><?= isset($userStats['continents_visited']) ? $userStats['continents_visited'] : 0; ?></div>
                                                <div class="text-muted">Continents Visited</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-body text-center">
                                                <?php
                                                $totalBadges = count($badges ?? []);
                                                $earnedCount = count($earnedBadges ?? []);
                                                $percentage = $totalBadges > 0 ? round(($earnedCount / $totalBadges) * 100) : 0;
                                                ?>
                                                <div class="display-4 mb-1"><?= $percentage; ?>%</div>
                                                <div class="text-muted">All Badges</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <nav>
                        <div class="nav nav-tabs mb-4" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="earned-tab" data-bs-toggle="tab" data-bs-target="#earned" type="button" role="tab" aria-controls="earned" aria-selected="true">Earned Badges</button>
                            <button class="nav-link" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab" aria-controls="available" aria-selected="false">Available Badges</button>
                        </div>
                    </nav>
                    
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="earned" role="tabpanel" aria-labelledby="earned-tab">
                            <?php if (empty($earnedBadges)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <img src="/images/empty-badges.svg" alt="No badges" style="max-width: 200px;">
                                    </div>
                                    <h5>You haven't earned any badges yet</h5>
                                    <p class="text-muted">Add destinations and complete trips to earn badges.</p>
                                    <a href="/destinations/create" class="btn btn-primary">Add Destinations</a>
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($earnedBadges as $badge): ?>
                                        <div class="col-md-3">
                                            <div class="card h-100 badge-card">
                                                <div class="card-body text-center">
                                                    <div class="badge-icon-lg mb-3">
                                                        <img src="/images/badges/<?= htmlspecialchars($badge['icon']); ?>" alt="<?= htmlspecialchars($badge['name']); ?>" class="img-fluid">
                                                    </div>
                                                    <h5 class="card-title"><?= htmlspecialchars($badge['name']); ?></h5>
                                                    <p class="card-text text-muted mb-1"><?= htmlspecialchars($badge['description']); ?></p>
                                                    <p class="card-text small text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Earned on <?= date('M d, Y', strtotime($badge['earned_date'])); ?>
                                                    </p>
                                                    <a href="/badges/<?= $badge['id']; ?>" class="btn btn-outline-primary btn-sm">Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="tab-pane fade" id="available" role="tabpanel" aria-labelledby="available-tab">
                            <?php if (empty($availableBadges)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <img src="/images/trophy.svg" alt="All badges earned" style="max-width: 200px;">
                                    </div>
                                    <h5>Congratulations! You've earned all available badges.</h5>
                                    <p class="text-muted">You're a true globetrotter!</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($availableBadges as $badge): ?>
                                        <div class="col-md-3">
                                            <div class="card h-100 badge-card locked">
                                                <div class="card-body text-center">
                                                    <div class="badge-icon-lg mb-3">
                                                        <div class="badge-locked">
                                                            <img src="/images/badges/<?= htmlspecialchars($badge['icon']); ?>" alt="<?= htmlspecialchars($badge['name']); ?>" class="img-fluid grayscale">
                                                            <div class="badge-lock">
                                                                <i class="fas fa-lock"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5 class="card-title"><?= htmlspecialchars($badge['name']); ?></h5>
                                                    <p class="card-text text-muted mb-1"><?= htmlspecialchars($badge['description']); ?></p>
                                                    <div class="progress mb-2">
                                                        <div class="progress-bar" role="progressbar" style="width: <?= $badge['progress']; ?>%;" aria-valuenow="<?= $badge['progress']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <p class="card-text small">
                                                        <span class="text-primary"><?= $badge['progress']; ?>% complete</span>
                                                    </p>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($badge['requirement']); ?>">
                                                        How to Earn
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-icon-lg {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        position: relative;
    }
    
    .badge-card {
        transition: transform 0.2s;
    }
    
    .badge-card:hover {
        transform: translateY(-5px);
    }
    
    .badge-locked {
        position: relative;
    }
    
    .grayscale {
        filter: grayscale(100%);
        opacity: 0.5;
    }
    
    .badge-lock {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .progress {
        height: 8px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
