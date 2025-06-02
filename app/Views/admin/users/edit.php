<?php $layout = 'admin'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit User: <?= htmlspecialchars($user['username']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/users/<?= $user['id']; ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to User
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

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">                <form method="POST" action="/admin/users/<?= $user['id']; ?>/update">
                    <?= \App\Core\View::csrfField(); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                       id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['username']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['email']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control <?= isset($errors['role']) ? 'is-invalid' : ''; ?>" id="role" name="role" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback">
                                <?= $errors['role']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Change Password (Optional)</h6>
                    <p class="text-muted">Leave blank to keep current password.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                       id="new_password" name="new_password" minlength="6">
                                <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['new_password']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="confirm_password" name="confirm_password" minlength="6">
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['confirm_password']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($errors['update'])): ?>
                        <div class="alert alert-danger">
                            <?= $errors['update']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/admin/users/<?= $user['id']; ?>" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
