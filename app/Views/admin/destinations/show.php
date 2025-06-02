<?php 
$layout = 'admin';
$title = 'Destination Details - Admin';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Destination Details</h1>
                <div>
                    <a href="/admin/destinations" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Destinations
                    </a>
                    <div class="btn-group" role="group">
                        <?php if ($destination['approval_status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?= $destination['id'] ?>, 'approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button type="button" class="btn btn-danger" onclick="updateStatus(<?= $destination['id'] ?>, 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        <?php elseif ($destination['approval_status'] === 'approved'): ?>
                            <button type="button" class="btn btn-warning" onclick="updateStatus(<?= $destination['id'] ?>, 'pending')">
                                <i class="fas fa-clock"></i> Set Pending
                            </button>
                            <button type="button" class="btn btn-danger" onclick="updateStatus(<?= $destination['id'] ?>, 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?= $destination['id'] ?>, 'approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button type="button" class="btn btn-warning" onclick="updateStatus(<?= $destination['id'] ?>, 'pending')">
                                <i class="fas fa-clock"></i> Set Pending
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Destination Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Name</h6>
                                    <p class="font-weight-bold"><?= htmlspecialchars($destination['name']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Status</h6>
                                    <span class="badge badge-<?= $destination['approval_status'] === 'approved' ? 'success' : ($destination['approval_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($destination['approval_status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Country</h6>
                                    <p><?= htmlspecialchars($destination['country']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">City</h6>
                                    <p><?= htmlspecialchars($destination['city'] ?? 'Not specified') ?></p>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Privacy</h6>
                                    <span class="badge badge-<?= $destination['privacy'] === 'public' ? 'info' : 'secondary' ?>">
                                        <?= ucfirst($destination['privacy']) ?>
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Added by</h6>
                                    <p>
                                        <a href="/admin/users/<?= $destination['user_id'] ?>">
                                            <?= htmlspecialchars($destination['username'] ?? 'Unknown User') ?>
                                        </a>
                                    </p>
                                </div>
                            </div>                            <?php if ($destination['description']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-muted">Description</h6>
                                    <p><?= nl2br(htmlspecialchars($destination['description'])) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Destination Image -->
                            <?php if (!empty($destination['image'])): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-muted">Destination Image</h6>
                                    <div class="text-center">
                                        <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" 
                                             alt="<?= htmlspecialchars($destination['name']); ?>" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="max-height: 300px; max-width: 100%; object-fit: cover;"
                                             onclick="showImageModal('<?= htmlspecialchars($destination['image']); ?>', '<?= htmlspecialchars($destination['name']); ?>')">
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-search-plus"></i> Click to view full size
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($destination['latitude'] && $destination['longitude']): ?>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Latitude</h6>
                                    <p><?= htmlspecialchars($destination['latitude']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Longitude</h6>
                                    <p><?= htmlspecialchars($destination['longitude']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Created</h6>
                                    <p><?= date('M j, Y g:i A', strtotime($destination['created_at'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Last Updated</h6>
                                    <p><?= date('M j, Y g:i A', strtotime($destination['updated_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Map placeholder -->
                    <?php if ($destination['latitude'] && $destination['longitude']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Location</h5>
                        </div>
                        <div class="card-body">
                            <div id="map" style="height: 300px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <p class="text-muted">Map would be displayed here</p>
                            </div>
                            <div class="mt-2 text-center">
                                <a href="https://maps.google.com/?q=<?= $destination['latitude'] ?>,<?= $destination['longitude'] ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> View on Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Statistics -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>Total Trips:</span>
                                <strong><?= $stats['total_trips'] ?? 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Planned Trips:</span>
                                <strong><?= $stats['planned_trips'] ?? 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Completed Trips:</span>
                                <strong><?= $stats['visited_trips'] ?? 0 ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="/admin/destinations/<?= $destination['id'] ?>/edit" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-edit"></i> Edit Destination
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-block mt-2" 
                                    onclick="deleteDestination(<?= $destination['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete Destination
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

function deleteDestination(destinationId) {
    if (confirm('Are you sure you want to delete this destination? This action cannot be undone.')) {
        fetch(`/admin/destinations/${destinationId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/admin/destinations';
            } else {
                alert('Error deleting destination: ' + data.message);
            }
        })        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting destination');        });
    }
}

function showImageModal(imagePath, altText) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('imageModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Destination Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="modalImage" src="" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Update image and show modal
    const modalImage = document.getElementById('modalImage');
    modalImage.src = '/images/destinations/' + imagePath;
    modalImage.alt = altText;
    
    // Show modal using Bootstrap
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}
</script>
