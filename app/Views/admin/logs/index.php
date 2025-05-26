<?php $layout = 'admin'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">System Logs</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-danger" onclick="clearLogs()">
                        <i class="fas fa-trash"></i> Clear Logs
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshLogs()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="exportLogs()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <label for="level" class="form-label">Log Level</label>
                            <select name="level" id="level" class="form-control">
                                <option value="">All Levels</option>
                                <option value="debug" <?= (isset($_GET['level']) && $_GET['level'] === 'debug') ? 'selected' : '' ?>>Debug</option>
                                <option value="info" <?= (isset($_GET['level']) && $_GET['level'] === 'info') ? 'selected' : '' ?>>Info</option>
                                <option value="warning" <?= (isset($_GET['level']) && $_GET['level'] === 'warning') ? 'selected' : '' ?>>Warning</option>
                                <option value="error" <?= (isset($_GET['level']) && $_GET['level'] === 'error') ? 'selected' : '' ?>>Error</option>
                                <option value="critical" <?= (isset($_GET['level']) && $_GET['level'] === 'critical') ? 'selected' : '' ?>>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="component" class="form-label">Component</label>
                            <select name="component" id="component" class="form-control">
                                <option value="">All Components</option>
                                <option value="auth" <?= (isset($_GET['component']) && $_GET['component'] === 'auth') ? 'selected' : '' ?>>Authentication</option>
                                <option value="destination" <?= (isset($_GET['component']) && $_GET['component'] === 'destination') ? 'selected' : '' ?>>Destinations</option>
                                <option value="user" <?= (isset($_GET['component']) && $_GET['component'] === 'user') ? 'selected' : '' ?>>Users</option>
                                <option value="admin" <?= (isset($_GET['component']) && $_GET['component'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                                <option value="database" <?= (isset($_GET['component']) && $_GET['component'] === 'database') ? 'selected' : '' ?>>Database</option>
                                <option value="security" <?= (isset($_GET['component']) && $_GET['component'] === 'security') ? 'selected' : '' ?>>Security</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="<?= $_GET['date_from'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="<?= $_GET['date_to'] ?? '' ?>">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="/admin/logs" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Log Entries</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No log entries found</h5>
                            <p class="text-muted">No logs match your current filter criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Time</th>
                                        <th>Level</th>
                                        <th>Component</th>
                                        <th>Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr class="log-entry" data-level="<?= $log['level'] ?>">
                                            <td class="text-nowrap">
                                                <small><?= date('M j, Y', strtotime($log['created_at'])) ?></small><br>
                                                <small class="text-muted"><?= date('g:i:s A', strtotime($log['created_at'])) ?></small>
                                            </td>                                            <td>
                                                <span class="badge badge-<?= getLogLevelClass($log['level']) ?>">
                                                    <?= strtoupper($log['level']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary text-white">
                                                    <?= htmlspecialchars($log['component'] ?? 'System') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="log-message" style="max-width: 400px;">
                                                    <?= htmlspecialchars($log['message']) ?>
                                                    <?php if ($log['data']): ?>
                                                        <br><small class="text-muted">
                                                            <a href="#" onclick="showLogData(<?= $log['id'] ?>)">View Details</a>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($log['data']): ?>
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="showLogData(<?= $log['id'] ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteLog(<?= $log['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>                        <!-- Pagination -->
                        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Log pagination">
                                    <ul class="pagination pagination-sm justify-content-center mb-0">                                        <?php if ($pagination['page'] > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>                                        <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['totalPages'], $pagination['page'] + 2); $i++): ?>
                                            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>                                        <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                                    Next
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Data Modal -->
<div class="modal fade" id="logDataModal" tabindex="-1" aria-labelledby="logDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDataModalLabel">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre id="logDataContent" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.badge-debug { background-color: #6c757d; }
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-error { background-color: #dc3545; }
.badge-critical { background-color: #721c24; }

.log-entry[data-level="error"], 
.log-entry[data-level="critical"] {
    background-color: #f8d7da;
}

.log-entry[data-level="warning"] {
    background-color: #fff3cd;
}
</style>

<script>
function showLogData(logId) {
    fetch(`/admin/logs/${logId}/data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('logDataContent').textContent = JSON.stringify(data.data, null, 2);
                const modal = new bootstrap.Modal(document.getElementById('logDataModal'));
                modal.show();
            } else {
                alert('Error loading log data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading log data');
        });
}

function deleteLog(logId) {
    if (confirm('Are you sure you want to delete this log entry?')) {
        fetch(`/admin/logs/${logId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting log: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting log');
        });
    }
}

function clearLogs() {
    if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        fetch('/admin/logs/clear', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error clearing logs: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clearing logs');
        });
    }
}

function refreshLogs() {
    location.reload();
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '/admin/logs?' + params.toString();
}
</script>

<?php
function getLogLevelClass($level) {
    switch ($level) {
        case 'debug': return 'debug';
        case 'info': return 'info';
        case 'warning': return 'warning';
        case 'error': return 'error';
        case 'critical': return 'critical';
        default: return 'secondary';
    }
}
?>
