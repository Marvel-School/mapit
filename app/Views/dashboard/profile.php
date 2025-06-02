<?php
// User Profile view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Profile</h4>
                    <button type="button" id="toggleEditMode" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i><span id="editModeText">Edit Profile</span>
                    </button>
                </div>
                <div class="card-body">
                    <!-- Success Message -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= htmlspecialchars($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- View Mode -->
                    <div id="viewMode">
                        <div class="row">                            <!-- Profile Image Display -->
                            <div class="col-md-3 text-center mb-4 mb-md-0">
                                <div class="mb-3">
                                    <div class="profile-image-large">                                        <?php if (!empty($user['avatar'])): ?>
                                            <?php 
                                            $avatarPath = __DIR__ . '/../../../public/images/avatars/' . $user['avatar'];
                                            $cacheParam = file_exists($avatarPath) ? '?v=' . filemtime($avatarPath) : '?v=' . time();
                                            ?>
                                            <img src="/images/avatars/<?= htmlspecialchars($user['avatar']); ?><?= $cacheParam; ?>" alt="Profile Picture" class="rounded-circle img-fluid border shadow">
                                        <?php else: ?>
                                            <img src="/images/default-avatar.png" alt="Default Profile" class="rounded-circle img-fluid border shadow">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($user['avatar'])): ?>
                                    <div class="text-center mt-3">
                                        <button type="button" id="delete-avatar-view-btn" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash me-2"></i>Delete
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- User Stats -->
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2">Profile Stats</h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="fw-bold text-primary"><?= $profileStats['trips_count'] ?? 0; ?></div>
                                                <small class="text-muted">Trips</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-success"><?= $profileStats['countries_visited'] ?? 0; ?></div>
                                                <small class="text-muted">Countries</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-warning"><?= $profileStats['badges_earned'] ?? 0; ?></div>
                                                <small class="text-muted">Badges</small>
                                            </div>
                                        </div>
                                        
                                        <?php if (($profileStats['trips_count'] ?? 0) === 0): ?>
                                            <div class="text-center mt-3">
                                                <p class="small text-muted mb-2">Ready to start your travel journey?</p>
                                                <a href="/destinations/create" class="btn btn-sm btn-primary me-1">
                                                    <i class="fas fa-plus me-1"></i>Add Destination
                                                </a>
                                                <a href="/trips/create" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-route me-1"></i>Plan Trip
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center mt-2">
                                                <a href="/trips" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-map-marked-alt me-1"></i>View Trips
                                                </a>
                                                <a href="/badges" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-medal me-1"></i>View Badges
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Next Badge Progress -->
                                <?php if (isset($nextBadge) && $nextBadge): ?>
                                    <div class="card bg-gradient-warning text-white mt-3">
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">
                                                <i class="fas fa-trophy me-2"></i>Next Achievement
                                            </h6>                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small"><?= htmlspecialchars($nextBadge['name']); ?></span>
                                                <span class="small fw-bold"><?= round($nextBadge['progress']); ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                     style="width: <?= $nextBadge['progress']; ?>%" 
                                                     aria-valuenow="<?= $nextBadge['progress']; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="opacity-75 mt-1 d-block">
                                                <?= $nextBadge['current'] ?? 0; ?> / <?= $nextBadge['threshold']; ?>
                                                <?= strtolower($nextBadge['badge']['description'] ?? 'completed'); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Profile Info Display -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-1">Full Name</h6>
                                        <p class="h5 mb-0"><?= htmlspecialchars($user['name'] ?? 'Not specified'); ?></p>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-1">Username</h6>
                                        <p class="h5 mb-0">@<?= htmlspecialchars($user['username'] ?? 'Not specified'); ?></p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Email Address</h6>
                                    <p class="h5 mb-0">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        <?= htmlspecialchars($user['email'] ?? 'Not specified'); ?>
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Bio</h6>
                                    <p class="mb-0">
                                        <?php if (!empty($user['bio'])): ?>
                                            <?= nl2br(htmlspecialchars($user['bio'])); ?>
                                        <?php else: ?>
                                            <em class="text-muted">No bio added yet. Click "Edit Profile" to add one!</em>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">Country</h6>
                                        <p class="mb-0">
                                            <?php if (!empty($user['country'])): ?>
                                                <i class="fas fa-globe me-2 text-muted"></i>
                                                <?= htmlspecialchars($countries[$user['country']] ?? $user['country']); ?>
                                            <?php else: ?>
                                                <em class="text-muted">Not specified</em>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">Website</h6>
                                        <p class="mb-0">
                                            <?php if (!empty($user['website'])): ?>
                                                <i class="fas fa-link me-2 text-muted"></i>
                                                <a href="<?= htmlspecialchars($user['website']); ?>" target="_blank" class="text-decoration-none">
                                                    <?= htmlspecialchars($user['website']); ?>
                                                </a>
                                            <?php else: ?>
                                                <em class="text-muted">Not specified</em>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Account Settings Display -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Account Settings</h6>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Public Profile</span>
                                            <span class="badge <?= (isset($user['settings']['public_profile']) && $user['settings']['public_profile']) ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?= (isset($user['settings']['public_profile']) && $user['settings']['public_profile']) ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Email Notifications</span>
                                            <span class="badge <?= (isset($user['settings']['email_notifications']) && $user['settings']['email_notifications']) ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?= (isset($user['settings']['email_notifications']) && $user['settings']['email_notifications']) ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Show Visited Places</span>
                                            <span class="badge <?= (isset($user['settings']['show_visited_places']) && $user['settings']['show_visited_places']) ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?= (isset($user['settings']['show_visited_places']) && $user['settings']['show_visited_places']) ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-3">Account Information</h6>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Member Since</span>
                                            <span class="text-muted">
                                                <?= date('M Y', strtotime($user['created_at'] ?? 'now')); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Account Type</span>
                                            <span class="badge bg-primary">
                                                <?= ucfirst($user['role'] ?? 'User'); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Last Updated</span>
                                            <span class="text-muted">
                                                <?= date('M j, Y', strtotime($user['updated_at'] ?? $user['created_at'] ?? 'now')); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode (hidden by default) -->
                    <div id="editMode" style="display: none;">
                        <form action="/profile" method="POST" enctype="multipart/form-data">
                            <?= \App\Core\View::csrfField(); ?>
                            
                            <div class="row">
                                <!-- Profile Image Edit -->
                                <div class="col-md-3 text-center mb-4 mb-md-0">                                    <div class="mb-3">
                                        <div class="profile-image">                                            <?php if (!empty($user['avatar'])): ?>
                                                <?php 
                                                $avatarPath = __DIR__ . '/../../../public/images/avatars/' . $user['avatar'];
                                                $cacheParam = file_exists($avatarPath) ? '?v=' . filemtime($avatarPath) : '?v=' . time();
                                                ?>
                                                <img src="/images/avatars/<?= htmlspecialchars($user['avatar']); ?><?= $cacheParam; ?>" alt="Profile" class="rounded-circle img-fluid">
                                            <?php else: ?>
                                                <img src="/images/default-avatar.png" alt="Profile" class="rounded-circle img-fluid">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="avatar" class="form-label">Change Profile Picture</label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="avatar" 
                                               name="avatar"
                                               accept="image/jpeg,image/png,image/gif,image/webp"
                                               style="display: none;">
                                        <input type="hidden" id="avatar_resized" name="avatar_resized">
                                          <div class="d-grid">
                                            <button type="button" id="select-avatar-btn" class="btn btn-outline-primary">
                                                <i class="fas fa-camera me-2"></i>Select Profile Picture
                                            </button>
                                        </div>
                                        
                                        <?php if (!empty($user['avatar'])): ?>
                                        <div class="d-grid mt-2">
                                            <button type="button" id="delete-avatar-btn" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash me-2"></i>Delete Profile Picture
                                            </button>
                                        </div>
                                        <?php endif; ?>

                                        <div class="form-text mt-2">
                                            <small class="text-muted">
                                                Allowed: JPG, PNG, GIF, WebP. Max size: 5MB.
                                                <br>Images will be automatically resized to 800x800px.
                                                <br>EXIF data will be automatically removed for privacy.
                                            </small>
                                        </div>
                                        <div id="avatar-upload-feedback" class="upload-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Profile Info Edit -->
                                <div class="col-md-9">
                                    <div class="row">                                    
                                        <div class="col-md-6 mb-3">
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
                                        <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <select class="form-select" id="country" name="country">
                                                <option value="">Select Country</option>
                                                <?php foreach ($countries as $code => $name): ?>
                                                    <option value="<?= $code; ?>" <?= (($user['country'] ?? '') == $code) ? 'selected' : ''; ?>>
                                                        <?= $name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" class="form-control" id="website" name="website" value="<?= htmlspecialchars($user['website'] ?? ''); ?>" placeholder="https://yourwebsite.com">
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h5>Change Password</h5>
                                    <p class="text-muted small">Leave blank if you don't want to change your password</p>
                                    
                                    <div class="row">                                        <div class="col-md-6 mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="off">
                                        </div>
                                    </div>
                                    
                                    <div class="row">                                        <div class="col-md-6 mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="new_password_confirm" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <h5>Account Settings</h5>
                                            
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="public_profile" name="settings[public_profile]" <?= isset($user['settings']['public_profile']) && $user['settings']['public_profile'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="public_profile">Public Profile</label>
                                                <div class="form-text">Allow others to view your profile</div>
                                            </div>
                                            
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="email_notifications" name="settings[email_notifications]" <?= isset($user['settings']['email_notifications']) && $user['settings']['email_notifications'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                                <div class="form-text">Receive updates about your account</div>
                                            </div>
                                            
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="show_visited_places" name="settings[show_visited_places]" <?= isset($user['settings']['show_visited_places']) && $user['settings']['show_visited_places'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="show_visited_places">Show Visited Places on Public Profile</label>
                                                <div class="form-text">Display your travel history publicly</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h5>Connected Accounts</h5>
                                            <p class="text-muted small mb-3">Connect your social accounts for easier login</p>
                                            
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
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                        <button type="button" id="cancelEdit" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
    
    .profile-image-large {
        width: 200px;
        height: 200px;
        overflow: hidden;
        margin: 0 auto;
    }
    
    .profile-image-large img {
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

    #viewMode {
        animation: fadeIn 0.3s ease-in;
    }

    #editMode {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }    .badge {
        font-size: 0.75em;
    }
</style>
<script src="/js/image-resizer.js?v=<?= time(); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Profile page DOM loaded');
          const toggleEditBtn = document.getElementById('toggleEditMode');
        const cancelEditBtn = document.getElementById('cancelEdit');
        const editModeText = document.getElementById('editModeText');
        const viewMode = document.getElementById('viewMode');
        const editMode = document.getElementById('editMode');
        
        console.log('🔍 Element detection:', {
            toggleEditBtn: !!toggleEditBtn,
            cancelEditBtn: !!cancelEditBtn,
            editModeText: !!editModeText,
            viewMode: !!viewMode,
            editMode: !!editMode
        });
        
        // Image resizer elements
        const avatarUpload = document.getElementById('avatar');
        const selectAvatarBtn = document.getElementById('select-avatar-btn');
        const avatarResizedInput = document.getElementById('avatar_resized');
        const profileImage = document.querySelector('.profile-image img');
        
        // Toggle between view and edit modes
        function toggleMode() {
            if (viewMode.style.display === 'none') {
                // Switch to view mode
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                editModeText.textContent = 'Edit Profile';
                toggleEditBtn.innerHTML = '<i class="fas fa-edit me-2"></i><span id="editModeText">Edit Profile</span>';
            } else {
                // Switch to edit mode
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                editModeText.textContent = 'View Profile';
                toggleEditBtn.innerHTML = '<i class="fas fa-eye me-2"></i><span id="editModeText">View Profile</span>';
            }
        }
        
        // Event listeners for mode toggle
        if (toggleEditBtn) {
            toggleEditBtn.addEventListener('click', toggleMode);
        }
        
        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function() {
                // Reset form and switch to view mode
                const form = document.querySelector('#editMode form');
                if (form) {
                    form.reset();
                }
                toggleMode();
            });
        }
          // Initialize image resizer (only works in edit mode)
        if (typeof ImageResizer !== 'undefined') {            console.log('✅ ImageResizer class found');
            
            // Verify the fix methods exist
            const testInstance = new ImageResizer();
            if (typeof testInstance.forceCleanupModal === 'function') {
                console.log('✅ forceCleanupModal method found - modal fix is loaded!');
            } else {
                console.error('❌ forceCleanupModal method NOT found - modal fix not loaded!');
            }
            
            const imageResizer = new ImageResizer({
                targetWidth: 800,
                targetHeight: 800,
                outputFormat: 'jpeg',
                outputQuality: 0.9,onResize: function(resizedFile, resizedDataUrl) {
                    console.log('🎯 onResize callback started');
                    console.log('📄 Resized file:', resizedFile);
                    console.log('🔗 Data URL length:', resizedDataUrl ? resizedDataUrl.length : 'null');
                    
                    try {
                        // Update hidden input with resized file data
                        console.log('🔄 Updating avatar_resized input...');
                        if (avatarResizedInput) {
                            avatarResizedInput.value = resizedDataUrl;
                            console.log('✅ avatar_resized input updated');
                        } else {
                            console.log('❌ avatarResizedInput not found!');
                        }
                        
                        // Update the profile image preview (try both edit and view mode images)
                        console.log('🔄 Updating profile image preview...');
                        if (profileImage) {
                            profileImage.src = resizedDataUrl;
                            console.log('✅ Edit mode profile image preview updated');
                        } else {
                            console.log('❌ Edit mode profileImage not found!');
                        }
                        
                        // Also update view mode image if it exists
                        const viewModeImage = document.querySelector('.profile-image-large img');
                        if (viewModeImage) {
                            viewModeImage.src = resizedDataUrl;
                            console.log('✅ View mode profile image preview updated');
                        }
                        
                        // Show success feedback
                        console.log('🔄 Showing success feedback...');
                        const feedback = document.getElementById('avatar-upload-feedback');
                        if (feedback) {
                            feedback.className = 'upload-feedback success';
                            feedback.innerHTML = '<i class="fas fa-check-circle"></i> Profile picture resized and ready for upload (800x800px)';
                            console.log('✅ Success feedback shown');
                        } else {
                            console.log('❌ Feedback element not found!');
                        }                        console.log('✅ onResize callback completed successfully');
                    } catch (error) {
                        console.error('❌ Error in onResize callback:', error);
                        // Don't re-throw the error, just log it and continue
                        // This prevents the modal from getting stuck
                        console.log('🔄 Continuing despite callback error...');
                    }
                },                onError: function(error) {
                    console.log('🚨 onError callback triggered');
                    console.log('❌ Error:', error);
                    
                    try {
                        // Show error feedback
                        const feedback = document.getElementById('avatar-upload-feedback');
                        if (feedback) {
                            feedback.className = 'upload-feedback error';
                            feedback.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${error}`;
                            console.log('✅ Error feedback shown');
                        } else {
                            console.log('❌ Feedback element not found in error handler!');
                        }
                    } catch (callbackError) {
                        console.error('❌ Error in onError callback:', callbackError);
                        // Don't throw - just log and continue to prevent modal freeze
                    }
                }
            });
            
            // Handle select avatar button click
            if (selectAvatarBtn) {
                selectAvatarBtn.addEventListener('click', function() {
                    if (avatarUpload) {
                        avatarUpload.click();
                    }
                });
            }
              if (avatarUpload) {                // Add event listeners for file selection
                avatarUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) {
                        return;
                    }
                    
                    // Basic validation
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    
                    if (!validTypes.includes(file.type)) {
                        alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                        return;
                    }
                    
                    if (file.size > maxSize) {
                        alert('File size must be less than 5MB');
                        return;
                    }
                    
                    // Open the resizer
                    imageResizer.openModal(file);
                });
                  // Prevent paste events on file inputs for security
                avatarUpload.addEventListener('paste', function(e) {
                    e.preventDefault();
                    alert('Paste operations are not allowed for security reasons. Please use the file browser.');
                });
            }
        } else {
            // Fallback if ImageResizer is not available
            if (selectAvatarBtn) {
                selectAvatarBtn.addEventListener('click', function() {
                    if (avatarUpload) {
                        avatarUpload.click();
                    }
                });
            }
        }
  
        
        // Clear resized data when form is reset
        const form = document.querySelector('#editMode form');
        if (form) {
            form.addEventListener('reset', function() {
                if (avatarResizedInput) {
                    avatarResizedInput.value = '';
                }
                
                // Reset profile image to original
                if (profileImage && profileImage.dataset.originalSrc) {
                    profileImage.src = profileImage.dataset.originalSrc;
                }
            });
        }
        
        // Store original profile image src for reset functionality
        if (profileImage && !profileImage.dataset.originalSrc) {
            profileImage.dataset.originalSrc = profileImage.src;
        }
        
        // Handle delete avatar button (edit mode)
        const deleteAvatarBtn = document.getElementById('delete-avatar-btn');
        if (deleteAvatarBtn) {
            deleteAvatarBtn.addEventListener('click', function() {
                deleteAvatarConfirm();
            });
        }
        
        // Handle delete avatar button (view mode)
        const deleteAvatarViewBtn = document.getElementById('delete-avatar-view-btn');
        if (deleteAvatarViewBtn) {
            deleteAvatarViewBtn.addEventListener('click', function() {
                deleteAvatarConfirm();
            });
        }
        
        // Function to handle avatar deletion with confirmation
        function deleteAvatarConfirm() {
            if (confirm('Are you sure you want to delete your profile picture? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = '/profile/delete-avatar';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = document.querySelector('input[name="csrf_token"]').value;
                deleteForm.appendChild(csrfInput);
                
                // Submit the form
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            }
        }
        
        // Auto-switch to edit mode if there are errors
        <?php if (isset($errors) && !empty($errors)): ?>
            toggleMode();
        <?php endif; ?>
    });
</script>
