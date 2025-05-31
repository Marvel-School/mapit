<?php $layout = 'admin'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Destinations</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i>
                Export
            </button>
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['pending']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['approved']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['rejected']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Public</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['public']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-globe fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <div class="col-xl-2 col-md-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Private</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['private']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-lock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Featured</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $counts['featured']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="/admin/destinations">
            <div class="row">                <div class="col-md-3">
                    <label for="status" class="form-label">Approval Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div><div class="col-md-3">
                    <label for="privacy" class="form-label">Privacy</label>
                    <select class="form-control" id="privacy" name="privacy">
                        <option value="">All Privacy</option>
                        <option value="public" <?= ($filters['privacy'] ?? '') === 'public' ? 'selected' : ''; ?>>Public</option>
                        <option value="private" <?= ($filters['privacy'] ?? '') === 'private' ? 'selected' : ''; ?>>Private</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="featured" class="form-label">Featured Status</label>
                    <select class="form-control" id="featured" name="featured">
                        <option value="">All Destinations</option>
                        <option value="1" <?= ($filters['featured'] ?? '') === '1' ? 'selected' : ''; ?>>Featured Only</option>
                        <option value="0" <?= ($filters['featured'] ?? '') === '0' ? 'selected' : ''; ?>>Not Featured</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>
                        Apply Filters
                    </button>
                    <a href="/admin/destinations" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Destinations Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Destinations</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($destinations)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="destinationsTable">
                    <thead>                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Country</th>
                            <th>Creator</th>
                            <th>Status</th>
                            <th>Privacy</th>
                            <th>Featured</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $destination): ?>
                            <tr>
                                <td><?= $destination['id']; ?></td>                                <td>
                                    <strong><?= htmlspecialchars($destination['name']); ?></strong>
                                    <?php if ($destination['city']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($destination['city']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($destination['image'])): ?>
                                        <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" 
                                             alt="Destination image" 
                                             class="img-thumbnail" 
                                             style="width: 40px; height: 40px; object-fit: cover;"
                                             title="<?= htmlspecialchars($destination['name']); ?>">
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-image" title="No image"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($destination['country']); ?></td>
                                <td><?= htmlspecialchars($destination['creator'] ?? 'Unknown'); ?></td>
                                <td>
                                    <span class="badge badge-<?= $destination['approval_status'] === 'approved' ? 'success' : ($destination['approval_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?= ucfirst($destination['approval_status']); ?>
                                    </span>
                                </td>                                <td>
                                    <span class="badge badge-<?= $destination['privacy'] === 'public' ? 'info' : 'secondary'; ?>">
                                        <?= ucfirst($destination['privacy']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($destination['featured']): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-circle"></i> Regular
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($destination['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/destinations/<?= $destination['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($destination['approval_status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="updateStatus(<?= $destination['id']; ?>, 'approved')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="updateStatus(<?= $destination['id']; ?>, 'rejected')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No destinations found.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-secondary { border-left: 0.25rem solid #6c757d !important; }
</style>

<script>
function updateStatus(destinationId, status) {
    if (confirm(`Are you sure you want to ${status} this destination?`)) {
        fetch(`/admin/destinations/${destinationId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating status');
        });
    }
}
</script>
