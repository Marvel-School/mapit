<?php $layout = 'admin'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Details: <?= htmlspecialchars($user['username']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/users/<?= $user['id']; ?>/edit" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i>
                Edit User
            </a>
            <a href="/admin/users" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- User Information -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-initial rounded-circle bg-primary">
                            <?= strtoupper(substr($user['username'], 0, 2)); ?>
                        </div>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['username']); ?></h5>
                    <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'moderator' ? 'warning' : 'success'); ?>">
                        <?= ucfirst($user['role']); ?>
                    </span>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0"><strong>Email</strong></p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0"><?= htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0"><strong>Joined</strong></p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0"><?= date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0"><strong>Last Login</strong></p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">
                            <?= $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Activity -->
    <div class="col-lg-8">
        <!-- Trips -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Trips (<?= count($trips); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($trips)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Destination</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trips as $trip): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($trip['destination_name'] ?? 'Unknown'); ?></td>
                                        <td>
                                            <span class="badge badge-<?= $trip['status'] === 'visited' ? 'success' : 'secondary'; ?>">
                                                <?= ucfirst($trip['status']); ?>
                                            </span>
                                        </td>
                                        <td><?= ucfirst($trip['type']); ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M j, Y', strtotime($trip['created_at'])); ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No trips found.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Badges -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Badges (<?= count($badges); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($badges)): ?>
                    <div class="row">
                        <?php foreach ($badges as $badge): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-success">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-medal fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($badge['name']); ?></h6>
                                                <small class="text-muted">
                                                    Earned: <?= date('M j, Y', strtotime($badge['earned_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No badges earned yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Logs -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity Logs</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($logs)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Level</th>
                                    <th>Message</th>
                                    <th>Component</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($logs, 0, 10) as $log): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M j, g:i A', strtotime($log['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($log['level']) === 'error' ? 'danger' : (strtolower($log['level']) === 'warning' ? 'warning' : 'info'); ?>">
                                                <?= $log['level']; ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log['message']); ?></td>
                                        <td><?= htmlspecialchars($log['component'] ?? 'System'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No activity logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 80px;
    height: 80px;
}
.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 600;
    color: white;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
</style>
