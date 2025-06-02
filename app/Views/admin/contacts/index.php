<?php $layout = 'admin'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Support Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/contacts/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i>
                Export CSV
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

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Contacts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New & In Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format(($stats['new_count'] ?? 0) + ($stats['in_progress_count'] ?? 0)) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Resolved</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['resolved_count'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Urgent Priority</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['urgent_count'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
        <form method="GET" action="/admin/contacts" class="row g-3">
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-select" name="priority" id="priority">
                    <option value="">All Priorities</option>
                    <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="assigned_to" class="form-label">Assigned To</label>
                <select class="form-select" name="assigned_to" id="assigned_to">
                    <option value="">All Users</option>
                    <option value="0" <?= ($filters['assigned_to'] ?? '') === '0' ? 'selected' : '' ?>>Unassigned</option>
                    <?php foreach ($adminUsers as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" name="search" id="search" 
                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                       placeholder="Search name, email, subject, or message...">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Contacts Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Support Contacts</h6>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleBulkActions()">
                <i class="fas fa-check-square me-1"></i>
                Bulk Actions
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($contacts)): ?>
            <!-- Bulk Actions Form -->
            <form id="bulkActionsForm" method="POST" action="/admin/contacts/bulk" style="display: none;" class="mb-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <select name="action" class="form-select" required>
                            <option value="">Select Action</option>
                            <option value="mark_resolved">Mark as Resolved</option>
                            <option value="mark_closed">Mark as Closed</option>                            <option value="assign_to">Assign To</option>
                            <?php if (($currentUserRole ?? $_SESSION['role'] ?? '') === 'admin'): ?>
                                <option value="delete">Delete</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3" id="assignToField" style="display: none;">
                        <select name="assign_to" class="form-select">
                            <option value="">Select User</option>
                            <?php foreach ($adminUsers as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Apply</button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-secondary w-100" onclick="toggleBulkActions()">Cancel</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-hover" id="contactsTable">
                    <thead>
                        <tr>
                            <th style="display: none;" class="bulk-checkbox">
                                <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                            </th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td style="display: none;" class="bulk-checkbox">
                                    <input type="checkbox" name="contact_ids[]" value="<?= $contact['id'] ?>" form="bulkActionsForm">
                                </td>
                                <td><?= $contact['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($contact['name']) ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                        <?= htmlspecialchars($contact['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($contact['subject'])): ?>
                                        <?= htmlspecialchars(substr($contact['subject'], 0, 50)) ?><?= strlen($contact['subject']) > 50 ? '...' : '' ?>
                                    <?php else: ?>
                                        <em class="text-muted">No subject</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'new' => 'bg-primary',
                                        'in_progress' => 'bg-warning',
                                        'resolved' => 'bg-success',
                                        'closed' => 'bg-secondary'
                                    ];
                                    $statusClass = $statusClasses[$contact['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $contact['status'])) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $priorityClasses = [
                                        'urgent' => 'text-danger',
                                        'high' => 'text-warning',
                                        'medium' => 'text-info',
                                        'low' => 'text-secondary'
                                    ];
                                    $priorityClass = $priorityClasses[$contact['priority']] ?? 'text-secondary';
                                    ?>
                                    <span class="<?= $priorityClass ?>">
                                        <i class="fas fa-circle me-1"></i>
                                        <?= ucfirst($contact['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($contact['assigned_username']): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($contact['assigned_username']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= date('M j, Y g:i A', strtotime($contact['created_at'])) ?></small>
                                </td>
                                <td>
                                    <a href="/admin/contacts/<?= $contact['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Contacts pagination">
                    <ul class="pagination justify-content-center">
                        <?php
                        $queryParams = $_GET;
                        for ($i = 1; $i <= $totalPages; $i++):
                            $queryParams['page'] = $i;
                            $isActive = ($i == $currentPage);
                        ?>
                            <li class="page-item <?= $isActive ? 'active' : '' ?>">
                                <a class="page-link" href="/admin/contacts?<?= http_build_query($queryParams) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No contacts found</h5>
                <p class="text-muted">No support contacts match your current filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleBulkActions() {
    const form = document.getElementById('bulkActionsForm');
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const isVisible = form.style.display !== 'none';
    
    if (isVisible) {
        form.style.display = 'none';
        checkboxes.forEach(cb => cb.style.display = 'none');
    } else {
        form.style.display = 'block';
        checkboxes.forEach(cb => cb.style.display = 'table-cell');
    }
}

function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="contact_ids[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Show/hide assign to field based on action selection
document.querySelector('select[name="action"]').addEventListener('change', function() {
    const assignToField = document.getElementById('assignToField');
    if (this.value === 'assign_to') {
        assignToField.style.display = 'block';
        assignToField.querySelector('select').required = true;
    } else {
        assignToField.style.display = 'none';
        assignToField.querySelector('select').required = false;
    }
});
</script>
