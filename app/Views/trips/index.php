<?php
// Trips Index view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">My Trips</h4>
                        <a href="/trips/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Trip
                        </a>
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
                                <img src="/images/empty-trips.svg" alt="No trips" style="max-width: 200px;">
                            </div>
                            <h5>You haven't created any trips yet</h5>
                            <p class="text-muted">Start planning your adventures by creating a trip.</p>
                            <a href="/trips/create" class="btn btn-primary">Create Your First Trip</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tripsTable">
                                <thead>
                                    <tr>
                                        <th>Trip Name</th>
                                        <th>Destinations</th>
                                        <th>Dates</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trips as $trip): ?>
                                        <tr class="trip-row" 
                                            data-name="<?= strtolower(htmlspecialchars($trip['name'])); ?>"
                                            data-status="<?= $trip['status']; ?>"
                                            data-year="<?= date('Y', strtotime($trip['start_date'])); ?>">
                                            <td>
                                                <a href="/trips/<?= $trip['id']; ?>" class="text-decoration-none fw-medium">
                                                    <?= htmlspecialchars($trip['name']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php 
                                                $count = count($trip['destinations'] ?? []);
                                                echo $count . ' ' . ($count === 1 ? 'place' : 'places');
                                                ?>
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
                                            </td>
                                            <td>
                                                <?php if ($trip['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php elseif ($trip['status'] === 'planned'): ?>
                                                    <span class="badge bg-warning">Planned</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">In Progress</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="/trips/<?= $trip['id']; ?>">
                                                                <i class="fas fa-eye me-2"></i> View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="/trips/<?= $trip['id']; ?>/edit">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               data-bs-toggle="modal" 
                                                               data-bs-target="#deleteModal" 
                                                               data-id="<?= $trip['id']; ?>" 
                                                               data-name="<?= htmlspecialchars($trip['name']); ?>">
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
});
</script>
