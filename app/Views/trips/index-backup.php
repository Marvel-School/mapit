<?php
// Enhanced Trips Index view
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
                        </div>                        <div>
                            <h5 class="mb-0"><?= count(array_filter($trips, function($t) { return $t['status'] === 'completed'; })); ?></h5>
                            <small>Completed</small>
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
                        </div>                        <div>
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
                        </div>                        <div>
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
                            <a href="/map" class="btn btn-outline-primary me-2">
                                <i class="fas fa-map me-1"></i> View Map
                            </a>
                            <a href="/trips/create" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Trip
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search trips...">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="planned">Planned</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterYear">
                                <option value="">All Years</option>
                                <?php 
                                $currentYear = date('Y');
                                for($i = $currentYear + 1; $i >= $currentYear - 5; $i--) {
                                    echo "<option value=\"{$i}\">{$i}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortBy">
                                <option value="start_date_desc">Newest First</option>
                                <option value="start_date_asc">Oldest First</option>
                                <option value="name">Trip Name</option>
                                <option value="duration">Duration</option>
                            </select>
                        </div>
                    </div>
                      <?php if (empty($trips)): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-map-marked-alt fa-5x text-muted mb-3"></i>
                            </div>
                            <h5>Your Adventure Awaits!</h5>
                            <p class="text-muted mb-4">Start your travel journey by creating your first trip.<br>
                            Plan destinations, track visits, and earn achievement badges!</p>
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <a href="/trips/create" class="btn btn-primary btn-lg me-3">
                                        <i class="fas fa-plus me-2"></i> Create Your First Trip
                                    </a>
                                    <a href="/map" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-map me-2"></i> Explore Map
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Getting Started Tips -->
                            <div class="row mt-5">
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
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tripsTable">                                <thead class="bg-light">
                                    <tr>
                                        <th><i class="fas fa-map-marked-alt me-1"></i> Trip Name</th>
                                        <th><i class="fas fa-map-pin me-1"></i> Destinations</th>
                                        <th><i class="fas fa-calendar me-1"></i> Dates</th>
                                        <th><i class="fas fa-clock me-1"></i> Duration</th>
                                        <th><i class="fas fa-flag me-1"></i> Status</th>
                                        <th><i class="fas fa-cog me-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trips as $trip): ?>
                                        <tr class="trip-row" 
                                            data-name="<?= strtolower(htmlspecialchars($trip['name'])); ?>"
                                            data-status="<?= $trip['status']; ?>"
                                            data-year="<?= date('Y', strtotime($trip['start_date'])); ?>">                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-route text-primary me-2"></i>
                                                    <div>
                                                        <a href="/trips/<?= $trip['id']; ?>" class="text-decoration-none fw-medium d-block">
                                                            <?= htmlspecialchars($trip['name']); ?>
                                                        </a>
                                                        <?php if (!empty($trip['description'])): ?>
                                                            <small class="text-muted"><?= htmlspecialchars(substr($trip['description'], 0, 50)); ?>...</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-map-pin text-success me-2"></i>
                                                    <div>
                                                        <?php 
                                                        $count = count($trip['destinations'] ?? []);
                                                        echo '<span class="fw-medium">' . $count . '</span>';
                                                        echo ' ' . ($count === 1 ? 'place' : 'places');
                                                        
                                                        // Show visited count if available
                                                        $visited = count(array_filter($trip['destinations'] ?? [], fn($d) => $d['visited'] ?? false));
                                                        if ($visited > 0) {
                                                            echo '<br><small class="text-success"><i class="fas fa-check-circle me-1"></i>' . $visited . ' visited</small>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($trip['start_date'])); ?> - 
                                                <?= date('M d, Y', strtotime($trip['end_date'])); ?>
                                            </td>
                                            <td>
                                                <?php
                                                $start = new DateTime($trip['start_date']);
                                                $end = new DateTime($trip['end_date']);
                                                $diff = $start->diff($end);
                                                echo $diff->days + 1 . ' ' . (($diff->days + 1) === 1 ? 'day' : 'days');
                                                ?>
                                            </td>                                            <td>
                                                <?php if ($trip['status'] === 'completed'): ?>
                                                    <span class="badge bg-success fs-6">
                                                        <i class="fas fa-check-circle me-1"></i> Completed
                                                    </span>
                                                <?php elseif ($trip['status'] === 'planned'): ?>
                                                    <span class="badge bg-warning fs-6">
                                                        <i class="fas fa-calendar me-1"></i> Planned
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-info fs-6">
                                                        <i class="fas fa-play-circle me-1"></i> In Progress
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($trip['status'] === 'completed'): ?>
                                                    <br><small class="text-success mt-1">
                                                        <i class="fas fa-trophy me-1"></i> Trip finished!
                                                    </small>
                                                <?php elseif ($trip['status'] === 'planned'): ?>
                                                    <?php 
                                                    $daysUntil = (new DateTime($trip['start_date']))->diff(new DateTime())->days;
                                                    if ($daysUntil <= 7): ?>
                                                        <br><small class="text-warning mt-1">
                                                            <i class="fas fa-clock me-1"></i> Starting soon!
                                                        </small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/trips/<?= $trip['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Trip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/trips/<?= $trip['id']; ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit Trip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal" 
                                                            data-id="<?= $trip['id']; ?>" 
                                                            data-name="<?= htmlspecialchars($trip['name']); ?>"
                                                            title="Delete Trip">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Quick Status Toggle -->
                                                <?php if ($trip['status'] === 'planned'): ?>
                                                    <form method="POST" action="/api/trips/<?= $trip['id']; ?>/start" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-success mt-1" title="Start Trip">
                                                            <i class="fas fa-play me-1"></i> Start
                                                        </button>
                                                    </form>
                                                <?php elseif ($trip['status'] === 'in_progress'): ?>
                                                    <form method="POST" action="/api/trips/<?= $trip['id']; ?>/complete" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-warning mt-1" title="Complete Trip">
                                                            <i class="fas fa-check me-1"></i> Complete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                                                <i class="fas fa-trash-alt me-2"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Travel Tips & Motivation -->
                        <?php if (!empty($trips)): ?>
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="card bg-gradient-primary text-white">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-2"><i class="fas fa-lightbulb me-2"></i> Travel Tip</h5>
                                                <?php 
                                                $tips = [
                                                    "Keep a travel journal to remember the special moments from each destination.",
                                                    "Share photos from your trips to inspire other travelers in the community.",
                                                    "Complete all destinations in a trip to earn achievement badges!",
                                                    "Use the interactive map to discover new destinations near your planned routes.",
                                                    "Mark destinations as 'visited' to track your travel progress over time."
                                                ];
                                                echo '<p class="mb-0">' . $tips[array_rand($tips)] . '</p>';
                                                ?>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-compass fa-3x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
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
            
            <?php if (!empty($upcomingTrips)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Upcoming Trips</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($upcomingTrips as $trip): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="/trips/<?= $trip['id']; ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($trip['name']); ?>
                                                </a>
                                            </h5>
                                            
                                            <p class="card-text text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('M d, Y', strtotime($trip['start_date'])); ?> - 
                                                <?= date('M d, Y', strtotime($trip['end_date'])); ?>
                                            </p>
                                            
                                            <?php if (!empty($trip['destinations'])): ?>
                                                <p class="card-text small">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= count($trip['destinations']); ?> destinations
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php
                                            $now = new DateTime();
                                            $start = new DateTime($trip['start_date']);
                                            $diff = $now->diff($start);
                                            $daysUntilTrip = $diff->days;
                                            if ($diff->invert === 0 && $daysUntilTrip > 0): // Future trip
                                            ?>
                                                <div class="alert alert-info py-2 small">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Starts in <?= $daysUntilTrip; ?> days
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                <p>Are you sure you want to delete <span id="tripName"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup delete modal
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            
            document.getElementById('tripName').textContent = name;
            document.getElementById('deleteForm').action = `/trips/${id}/delete`;
        });
    }
    
    // Setup filtering
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const filterStatus = document.getElementById('filterStatus');
    const filterYear = document.getElementById('filterYear');
    const sortBy = document.getElementById('sortBy');
    
    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        const yearFilter = filterYear.value;
        
        const tripRows = document.querySelectorAll('.trip-row');
        
        tripRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const status = row.getAttribute('data-status');
            const year = row.getAttribute('data-year');
            
            let showRow = true;
            
            if (searchText && !name.includes(searchText)) {
                showRow = false;
            }
            
            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }
            
            if (yearFilter && year !== yearFilter) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    if (searchButton) searchButton.addEventListener('click', applyFilters);
    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    if (filterYear) filterYear.addEventListener('change', applyFilters);
    
    // Setup sorting
    if (sortBy) {
        sortBy.addEventListener('change', function() {
            const table = document.getElementById('tripsTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr.trip-row'));
            
            rows.sort(function(rowA, rowB) {
                const sortOption = sortBy.value;
                
                if (sortOption === 'name') {
                    const nameA = rowA.getAttribute('data-name');
                    const nameB = rowB.getAttribute('data-name');
                    return nameA.localeCompare(nameB);
                } 
                else if (sortOption === 'start_date_asc' || sortOption === 'start_date_desc') {
                    // Using the existing date cells in the table
                    const dateA = new Date(rowA.cells[2].textContent.split(' - ')[0]);
                    const dateB = new Date(rowB.cells[2].textContent.split(' - ')[0]);
                    return sortOption === 'start_date_asc' ? dateA - dateB : dateB - dateA;
                }
                else if (sortOption === 'duration') {
                    // Using the duration cells in the table
                    const durationA = parseInt(rowA.cells[3].textContent);
                    const durationB = parseInt(rowB.cells[3].textContent);
                    return durationA - durationB;
                }
            });
            
            // Re-append in sorted order
            rows.forEach(row => tbody.appendChild(row));
        });
    }
    
    // Handle success messages from URL params
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        // Create and show a success alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            ${decodeURIComponent(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert at the top of the page
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
        
        // Clean URL
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
    
    // Add trip stats card animations
    const statsCards = document.querySelectorAll('.trip-stats-cards .card');
    statsCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate__animated', 'animate__fadeInUp');
    });
});
</script>

<style>
/* Add some subtle animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate__animated {
    animation-duration: 0.6s;
    animation-fill-mode: both;
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}
</style>
