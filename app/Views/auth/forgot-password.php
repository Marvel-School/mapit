<?php
// Forgot Password view
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3">Forgot Password</h1>
                        <p class="text-muted">Enter your email address to reset your password</p>
                    </div>
                    
                    <form action="/forgot-password" method="POST">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?= $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success mb-4">
                                <?= $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Send Reset Link</button>
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
