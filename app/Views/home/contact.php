<?php
// Contact page view
?>

<section class="contact-header py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1>Contact Us</h1>
                <p class="lead text-muted">Have questions or feedback? We'd love to hear from you!</p>
            </div>
        </div>
    </div>
</section>

<section class="contact-content py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <form action="/contact" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?= htmlspecialchars($name ?? ''); ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?= htmlspecialchars($email ?? ''); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : ''; ?>" id="message" name="message" rows="5" required><?= htmlspecialchars($message ?? ''); ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <div class="invalid-feedback"><?= $errors['message']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="row mt-5 g-4">
                    <div class="col-md-4">
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="contact-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                                <h3 class="h5">Our Office</h3>
                                <p class="mb-0">123 Travel Lane<br>San Francisco, CA 94107</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="contact-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                                    <i class="fas fa-envelope fa-lg"></i>
                                </div>
                                <h3 class="h5">Email</h3>
                                <p class="mb-0">
                                    <a href="mailto:info@mapit.com" class="text-decoration-none">info@mapit.com</a><br>
                                    <a href="mailto:support@mapit.com" class="text-decoration-none">support@mapit.com</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="contact-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                                    <i class="fas fa-phone fa-lg"></i>
                                </div>
                                <h3 class="h5">Phone</h3>
                                <p class="mb-0">
                                    <a href="tel:+14155550123" class="text-decoration-none">+1 (415) 555-0123</a><br>
                                    <a href="tel:+14155550124" class="text-decoration-none">+1 (415) 555-0124</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .contact-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
