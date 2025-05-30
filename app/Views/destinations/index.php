<?php
// Destinations Index view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">My Destinations</h4>
                        <a href="/destinations/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Destination
                        </a>
                    </div>                </div>
                <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search destinations...">
                                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterStatus">
                                    <option value="">All Statuses</option>
                                    <option value="visited">Visited</option>
                                    <option value="wishlist">Wishlist</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterCountry">
                                    <option value="">All Countries</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= $country['code']; ?>"><?= htmlspecialchars($country['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="sortBy">
                                    <option value="name">Name</option>
                                    <option value="date_desc">Newest</option>
                                    <option value="date_asc">Oldest</option>
                                    <option value="country">Country</option>
                                </select>
                            </div>
                        </div>
                        
                        <?php if (empty($destinations)): ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <img src="/images/empty-destinations.svg" alt="No destinations" style="max-width: 200px;">
                                </div>
                                <h5>You haven't added any destinations yet</h5>
                                <p class="text-muted">Start building your travel map by adding places you've visited or want to visit.</p>
                                <a href="/destinations/create" class="btn btn-primary">Add Your First Destination</a>
                            </div>
                        <?php else: ?>
                            <div class="row" id="destinationsContainer">
                                <?php foreach ($destinations as $destination): ?>                                    <div class="col-md-6 col-lg-4 mb-4 destination-card" 
                                         data-name="<?= strtolower(htmlspecialchars($destination['name'])); ?>"
                                         data-country="<?= htmlspecialchars($destination['country']); ?>"
                                         data-status="<?= !empty($destination['trip_status']) ? $destination['trip_status'] : 'wishlist'; ?>">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <?php if (!empty($destination['image'])): ?>
                                                <img src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" class="card-img-top destination-img" alt="<?= htmlspecialchars($destination['name']); ?>">
                                            <?php else: ?>
                                                <img src="/images/destination-placeholder.svg" class="card-img-top destination-img" alt="<?= htmlspecialchars($destination['name']); ?>">
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($destination['trip_status'])): ?>
                                                <?php if ($destination['trip_status'] == 'visited'): ?>
                                                    <div class="destination-badge bg-success text-white">
                                                        <i class="fas fa-check"></i> Visited
                                                    </div>
                                                <?php elseif ($destination['trip_status'] == 'in_progress'): ?>
                                                    <div class="destination-badge bg-info text-white">
                                                        <i class="fas fa-route"></i> In Progress
                                                    </div>
                                                <?php elseif ($destination['trip_status'] == 'completed'): ?>
                                                    <div class="destination-badge bg-primary text-white">
                                                        <i class="fas fa-flag-checkered"></i> Completed
                                                    </div>
                                                <?php else: ?>
                                                    <div class="destination-badge bg-secondary text-white">
                                                        <i class="fas fa-calendar"></i> Planned
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="destination-badge bg-warning text-white">
                                                    <i class="fas fa-heart"></i> Wishlist
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($destination['name']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= htmlspecialchars($destination['city'] . ', ' . $destination['country_name']); ?>
                                                </p>
                                                
                                                <?php if (!empty($destination['visit_date']) && $destination['visited']): ?>
                                                    <p class="card-text small">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Visited on: <?= date('M d, Y', strtotime($destination['visit_date'])); ?>
                                                    </p>
                                                <?php endif; ?>
                                                  <p class="card-text">
                                                    <?= htmlspecialchars(substr($destination['description'] ?? '', 0, 100) . '...'); ?>
                                                </p>
                                                
                                                <div class="d-flex justify-content-between">
                                                    <a href="/destinations/<?= $destination['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                      <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">                                                            <?php if ($destination['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')): ?>
                                                                <li><a class="dropdown-item" href="/destinations/<?= $destination['id']; ?>/edit">
                                                                    <i class="fas fa-edit me-2"></i> Edit
                                                                </a></li>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Trip Status Actions -->
                                                            <?php if (!empty($destination['trip_status'])): ?>
                                                                <?php if ($destination['trip_status'] == 'visited'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 0); return false;">
                                                                        <i class="fas fa-heart me-2"></i> Move to Wishlist
                                                                    </a></li>
                                                                <?php elseif ($destination['trip_status'] == 'in_progress'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="completeTrip(<?= $destination['trip_id']; ?>); return false;">
                                                                        <i class="fas fa-flag-checkered me-2"></i> Complete Trip
                                                                    </a></li>
                                                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 1); return false;">
                                                                        <i class="fas fa-check me-2"></i> Mark as Visited
                                                                    </a></li>
                                                                <?php elseif ($destination['trip_status'] == 'completed'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 1); return false;">
                                                                        <i class="fas fa-check me-2"></i> Mark as Visited
                                                                    </a></li>
                                                                <?php else: // planned ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="startTrip(<?= $destination['trip_id']; ?>); return false;">
                                                                        <i class="fas fa-play me-2"></i> Start Trip
                                                                    </a></li>
                                                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 1); return false;">
                                                                        <i class="fas fa-check me-2"></i> Mark as Visited
                                                                    </a></li>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <!-- No trip exists - show wishlist actions -->
                                                                <li><a class="dropdown-item" href="/trips/create?destination_id=<?= $destination['id']; ?>">
                                                                    <i class="fas fa-plus me-2"></i> Plan Trip
                                                                </a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $destination['id']; ?>, 1); return false;">
                                                                    <i class="fas fa-check me-2"></i> Mark as Visited
                                                                </a></li>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($destination['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')): ?>
                                                                <?php if ($destination['featured'] == 1 && !(isset($_SESSION['role']) && $_SESSION['role'] == 'admin')): ?>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><span class="dropdown-item-text text-muted small">
                                                                        <i class="fas fa-star me-2"></i> Featured destination (cannot delete)
                                                                    </span></li>
                                                                <?php else: ?>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item text-danger" href="#" 
                                                                           data-bs-toggle="modal" 
                                                                           data-bs-target="#deleteModal" 
                                                                           data-id="<?= $destination['id']; ?>" 
                                                                           data-name="<?= htmlspecialchars($destination['name']); ?>">
                                                                        <i class="fas fa-trash-alt me-2"></i> Delete
                                                                    </a></li>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $currentPage - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?= $currentPage == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $currentPage + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="destinationName"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <?= \App\Core\View::csrfField(); ?>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>



<style>
    .destination-img {
        height: 180px;
        object-fit: cover;
    }
    
    .destination-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup page controls (filtering, sorting, modals)
    setupPageControls();
    
    function setupPageControls() {
        // Setup delete modal
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                
                document.getElementById('destinationName').textContent = name;
                document.getElementById('deleteForm').action = `/destinations/${id}/delete`;
            });
        }
        
        // Setup filtering
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const filterStatus = document.getElementById('filterStatus');
        const filterCountry = document.getElementById('filterCountry');
        const sortBy = document.getElementById('sortBy');
        
        function applyFilters() {
            const searchText = searchInput.value.toLowerCase();
            const statusFilter = filterStatus.value;
            const countryFilter = filterCountry.value;
            
            const destinationCards = document.querySelectorAll('.destination-card');
            
            destinationCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const country = card.getAttribute('data-country');
                const status = card.getAttribute('data-status');
                
                let showCard = true;
                
                if (searchText && !name.includes(searchText)) {
                    showCard = false;
                }
                
                if (statusFilter && status !== statusFilter) {
                    showCard = false;
                }
                
                if (countryFilter && country !== countryFilter) {
                    showCard = false;
                }
                
                card.style.display = showCard ? '' : 'none';
            });
        }
        
        if (searchButton) searchButton.addEventListener('click', applyFilters);
        if (searchInput) searchInput.addEventListener('keyup', applyFilters);
        if (filterStatus) filterStatus.addEventListener('change', applyFilters);
        if (filterCountry) filterCountry.addEventListener('change', applyFilters);
        
        // Setup sorting
        if (sortBy) {
            sortBy.addEventListener('change', function() {
                const container = document.getElementById('destinationsContainer');
                const cards = Array.from(container.querySelectorAll('.destination-card'));
                
                cards.sort(function(a, b) {
                    switch(sortBy.value) {
                        case 'name':
                            return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                        case 'country':
                            return a.getAttribute('data-country').localeCompare(b.getAttribute('data-country'));
                        case 'date_desc':
                        case 'date_asc':
                            // You would need to add a data-date attribute to sort by date
                            // This is placeholder logic
                            return sortBy.value === 'date_desc' ? -1 : 1;
                    }
                });
                
                cards.forEach(card => container.appendChild(card));
            });
        }
    }
});

// Function to update destination status
function updateStatus(id, visited) {
    // Show loading state
    const statusText = visited ? 'visited' : 'planned';
    console.log(`Updating destination ${id} status to: ${statusText}`);
    
    fetch(`/api/destinations/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ visited: visited })
    })
    .then(response => {
        console.log('API Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response data:', data);
        if (data.success) {
            // Show success message briefly before reload
            const message = data.message || `Destination marked as ${statusText}`;
            console.log('Success:', message);
            window.location.reload();
        } else {
            const errorMsg = data.message || 'Failed to update destination status';
            console.error('API Error:', errorMsg);
            alert('Failed to update destination status: ' + errorMsg);
        }
    })
    .catch(error => {
        console.error('Request Error:', error);
        alert('An error occurred while updating the destination status: ' + error.message);
    });
}

// Function to start a trip
function startTrip(tripId) {
    fetch(`/api/trips/${tripId}/start`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to start trip: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred while starting the trip');
    });
}

// Function to complete a trip
function completeTrip(tripId) {
    fetch(`/api/trips/${tripId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to complete trip: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred while completing the trip');
    });
}
</script>
