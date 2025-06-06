<?php
// Register view
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3">Create an Account</h1>
                        <p class="text-muted">Join MapIt and start tracking your travels</p>
                    </div>
                    
                    <form action="/register" method="POST">
                        <?= \App\Core\View::csrfField(); ?>
                        
                        <?php if (isset($errors) && is_array($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    <?php foreach($errors as $field => $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors['register'])): ?>
                            <div class="alert alert-danger mb-4">
                                <?= $errors['register']; ?>
                            </div>
                        <?php endif; ?>
                          <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($username ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="form-text">Must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                            <label for="terms" class="form-check-label">I agree to the <a href="/terms" class="text-decoration-none">Terms of Service</a> and <a href="/privacy" class="text-decoration-none">Privacy Policy</a></label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p>Already have an account? <a href="/login" class="text-decoration-none">Log in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
