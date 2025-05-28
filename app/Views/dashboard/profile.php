<?php
// User Profile view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">                    <form action="/profile" method="POST" enctype="multipart/form-data">
                        <?= \App\Core\View::csrfField(); ?>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <!-- Profile Image -->
                            <div class="col-md-3 text-center mb-4 mb-md-0">
                                <div class="mb-3">
                                    <div class="profile-image">                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="/images/avatars/<?= htmlspecialchars($user['avatar'] ?? ''); ?>" alt="Profile" class="rounded-circle img-fluid">
                                        <?php else: ?>
                                            <img src="/images/default-avatar.png" alt="Profile" class="rounded-circle img-fluid">
                                        <?php endif; ?>
                                    </div>
                                </div>                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Change Profile Picture</label>
                                    <input type="file" 
                                           class="form-control secure-upload" 
                                           id="avatar" 
                                           name="avatar"
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           data-max-size="5242880"
                                           data-upload-type="avatars">
                                    <div class="form-text">
                                        <small class="text-muted">
                                            Allowed: JPG, PNG, GIF, WebP. Max size: 5MB. Max dimensions: 800x800px.
                                            <br>EXIF data will be automatically removed for privacy.
                                        </small>
                                    </div>
                                    <div id="avatar-upload-feedback" class="upload-feedback"></div>
                                </div>
                            </div>
                            
                            <!-- Profile Info -->
                            <div class="col-md-9">
                                <div class="row">                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                  <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <select class="form-select" id="country" name="country">
                                            <option value="">Select Country</option>
                                            <!-- Countries would be populated from a list -->                                            <?php foreach ($countries as $code => $name): ?>
                                                <option value="<?= $code; ?>" <?= (($user['country'] ?? '') == $code) ? 'selected' : ''; ?>>
                                                    <?= $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="website" class="form-label">Website</label>
                                        <input type="url" class="form-control" id="website" name="website" value="<?= htmlspecialchars($user['website'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5>Change Password</h5>
                                <p class="text-muted small">Leave blank if you don't want to change your password</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password_confirm" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm">
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h5>Account Settings</h5>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="public_profile" name="settings[public_profile]" <?= isset($user['settings']['public_profile']) && $user['settings']['public_profile'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="public_profile">Public Profile</label>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" name="settings[email_notifications]" <?= isset($user['settings']['email_notifications']) && $user['settings']['email_notifications'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                        </div>
                                        
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="show_visited_places" name="settings[show_visited_places]" <?= isset($user['settings']['show_visited_places']) && $user['settings']['show_visited_places'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="show_visited_places">Show Visited Places on Public Profile</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5>Connected Accounts</h5>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="fab fa-facebook-f me-2"></i> Connect with Facebook
                                            </a>
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="fab fa-google me-2"></i> Connect with Google
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-image {
        width: 150px;
        height: 150px;
        overflow: hidden;
        margin: 0 auto;
    }
    
    .profile-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .upload-feedback {
        margin-top: 5px;
    }
    
    .upload-feedback.error {
        color: #dc3545;
        font-size: 0.875em;
    }
    
    .upload-feedback.success {
        color: #198754;
        font-size: 0.875em;
    }
    
    .upload-feedback.warning {
        color: #fd7e14;
        font-size: 0.875em;
    }
</style>

<script src="/js/secure-upload.js"></script>
<script>
    // Initialize secure upload for profile avatar
    document.addEventListener('DOMContentLoaded', function() {
        const avatarUpload = document.getElementById('avatar');
        if (avatarUpload) {
            // Add event listeners for real-time validation
            avatarUpload.addEventListener('change', function(e) {
                validateFileUpload(e.target, 'avatar-upload-feedback');
            });
            
            // Prevent paste events on file inputs for security
            avatarUpload.addEventListener('paste', function(e) {
                e.preventDefault();
                showUploadError('avatar-upload-feedback', 'Paste operations are not allowed for security reasons. Please use the file browser.');
            });
        }
    });
</script>
