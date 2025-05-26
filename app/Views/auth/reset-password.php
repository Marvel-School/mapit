<?php
// Reset Password view
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3">Reset Password</h1>
                        <p class="text-muted">Enter your new password below</p>
                    </div>
                    
                    <form action="/reset-password" method="POST">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?= $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? ''); ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? ''); ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="form-text">Must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p>Remember your password? <a href="/login" class="text-decoration-none">Log in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
