<?php
// Login view
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3">Welcome Back</h1>
                        <p class="text-muted">Log in to access your travel dashboard</p>
                    </div>                    <form action="/login" method="POST">                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success mb-4">
                                <?= $_SESSION['success']; ?>
                                <?php unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errors['login'])): ?>
                            <div class="alert alert-danger mb-4">
                                <?= $errors['login']; ?>
                            </div>
                        <?php elseif (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    <?php foreach($errors as $field => $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                          <div class="mb-3">
                            <label for="username" class="form-label">Email or Username</label>
                            <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($username ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">Password</label>
                                <a href="/forgot-password" class="text-decoration-none small">Forgot Password?</a>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Log In</button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p>Don't have an account? <a href="/register" class="text-decoration-none">Register now</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
