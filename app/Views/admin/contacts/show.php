<?php $layout = 'admin'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contact Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/contacts" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Contacts
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
    <!-- Contact Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Contact ID:</strong>
                    </div>
                    <div class="col-sm-9">
                        #<?= $contact['id'] ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Name:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?= htmlspecialchars($contact['name']) ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-sm-9">
                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                            <?= htmlspecialchars($contact['email']) ?>
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($contact['subject'])): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Subject:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?= htmlspecialchars($contact['subject']) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Message:</strong>
                    </div>
                    <div class="col-sm-9">
                        <div class="border p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($contact['message'])) ?>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Submitted:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?= date('F j, Y \a\t g:i A', strtotime($contact['created_at'])) ?>
                        <small class="text-muted">(<?= date('D, M j, Y g:i A T', strtotime($contact['created_at'])) ?>)</small>
                    </div>
                </div>
                
                <?php if ($contact['updated_at'] !== $contact['created_at']): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Last Updated:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?= date('F j, Y \a\t g:i A', strtotime($contact['updated_at'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['ip_address'])): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>IP Address:</strong>
                    </div>
                    <div class="col-sm-9">
                        <code><?= htmlspecialchars($contact['ip_address']) ?></code>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['user_agent'])): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>User Agent:</strong>
                    </div>
                    <div class="col-sm-9">
                        <small class="text-muted"><?= htmlspecialchars($contact['user_agent']) ?></small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Admin Notes -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Admin Notes</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($contact['admin_notes'])): ?>
                    <div class="mb-3">
                        <div class="border p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($contact['admin_notes'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3">No admin notes yet.</p>
                <?php endif; ?>
                
                <!-- Add Notes Form -->                <form method="POST" action="/admin/contacts/<?= $contact['id'] ?>/notes">
                    <?= \App\Core\View::csrfField(); ?>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Add Notes:</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" 
                                  placeholder="Enter your notes here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Add Notes
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Status and Actions -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status & Actions</h6>
            </div>            <div class="card-body">
                <form method="POST" action="/admin/contacts/<?= $contact['id'] ?>/status">
                    <?= \App\Core\View::csrfField(); ?>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="new" <?= $contact['status'] === 'new' ? 'selected' : '' ?>>New</option>
                            <option value="in_progress" <?= $contact['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="resolved" <?= $contact['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            <option value="closed" <?= $contact['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" name="priority" id="priority" required>
                            <option value="low" <?= $contact['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $contact['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $contact['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="urgent" <?= $contact['priority'] === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select class="form-select" name="assigned_to" id="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($adminUsers as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $contact['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>
                        Update Status
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Current Status Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Current Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Status:</strong>
                    <?php
                    $statusClasses = [
                        'new' => 'bg-primary',
                        'in_progress' => 'bg-warning',
                        'resolved' => 'bg-success',
                        'closed' => 'bg-secondary'
                    ];
                    $statusClass = $statusClasses[$contact['status']] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?= $statusClass ?> ms-2">
                        <?= ucfirst(str_replace('_', ' ', $contact['status'])) ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Priority:</strong>
                    <?php
                    $priorityClasses = [
                        'urgent' => 'text-danger',
                        'high' => 'text-warning',
                        'medium' => 'text-info',
                        'low' => 'text-secondary'
                    ];
                    $priorityClass = $priorityClasses[$contact['priority']] ?? 'text-secondary';
                    ?>
                    <span class="<?= $priorityClass ?> ms-2">
                        <i class="fas fa-circle me-1"></i>
                        <?= ucfirst($contact['priority']) ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong>Assigned To:</strong>
                    <?php if ($contact['assigned_to']): ?>
                        <span class="badge bg-info ms-2"><?= htmlspecialchars($contact['assigned_username'] ?? 'Unknown') ?></span>
                    <?php else: ?>
                        <span class="text-muted ms-2">Unassigned</span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($contact['resolved_at'])): ?>
                <div class="mb-3">
                    <strong>Resolved:</strong>
                    <br><small class="text-muted"><?= date('M j, Y g:i A', strtotime($contact['resolved_at'])) ?></small>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['closed_at'])): ?>
                <div class="mb-3">
                    <strong>Closed:</strong>
                    <br><small class="text-muted"><?= date('M j, Y g:i A', strtotime($contact['closed_at'])) ?></small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Re: <?= htmlspecialchars($contact['subject'] ?? 'Your inquiry') ?>" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-reply me-1"></i>
                        Reply via Email
                    </a>
                    
                    <?php if ($contact['status'] !== 'resolved'): ?>                    <form method="POST" action="/admin/contacts/<?= $contact['id'] ?>/status" class="d-inline">
                        <?= \App\Core\View::csrfField(); ?>
                        <input type="hidden" name="status" value="resolved">
                        <input type="hidden" name="priority" value="<?= $contact['priority'] ?>">
                        <input type="hidden" name="assigned_to" value="<?= $contact['assigned_to'] ?>">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-check me-1"></i>
                            Mark as Resolved
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($contact['status'] !== 'closed'): ?>                    <form method="POST" action="/admin/contacts/<?= $contact['id'] ?>/status" class="d-inline">
                        <?= \App\Core\View::csrfField(); ?>
                        <input type="hidden" name="status" value="closed">
                        <input type="hidden" name="priority" value="<?= $contact['priority'] ?>">
                        <input type="hidden" name="assigned_to" value="<?= $contact['assigned_to'] ?>">
                        <button type="submit" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-times me-1"></i>
                            Close Contact
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
