<?php 
$layout = 'admin';
$title = 'Edit Destination - Admin';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                <li class="breadcrumb-item"><a href="/admin/destinations">Destinations</a></li>
                <li class="breadcrumb-item"><a href="/admin/destinations/<?= $destination['id']; ?>"><?= htmlspecialchars($destination['name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="/admin/destinations/<?= $destination['id']; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Details
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Form -->    <div class="col-lg-8">
        <form action="/admin/destinations/<?= $destination['id']; ?>" method="POST" enctype="multipart/form-data">
            <?= \App\Core\View::csrfField(); ?>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Destination Information</h5>
                </div>
                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Destination Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($destination['name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="featured" class="form-label">Featured</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                       <?= isset($destination['featured']) && $destination['featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="featured">
                                    Featured destination
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?= htmlspecialchars($destination['city'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="country" name="country" 
                                   value="<?= htmlspecialchars($destination['country'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" id="latitude" name="latitude" 
                                   value="<?= htmlspecialchars($destination['latitude'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" id="longitude" name="longitude" 
                                   value="<?= htmlspecialchars($destination['longitude'] ?? ''); ?>" required>
                        </div>
                    </div>                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($destination['description'] ?? ''); ?></textarea>
                    </div>                    <!-- Image Upload Section -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Destination Image</label>
                        <?php if (!empty($destination['image'])): ?>
                            <div class="mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <img id="current-image" src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" 
                                             alt="Current destination image" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="h6 text-muted">Current Image</div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                                            <label class="form-check-label text-danger" for="delete_image">
                                                <i class="fas fa-trash"></i> Delete current image
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <input type="hidden" id="image_resized" name="image_resized" value="">
                        <div class="form-text">
                            <strong>Image Requirements:</strong><br>
                            <i class="fas fa-info-circle text-primary"></i> Max file size: 5MB<br>
                            <i class="fas fa-info-circle text-primary"></i> Allowed formats: JPG, PNG, GIF, WebP<br>
                            <i class="fas fa-info-circle text-primary"></i> Large images will be automatically resized for optimal performance<br>
                            <i class="fas fa-info-circle text-primary"></i> EXIF data will be removed for privacy
                        </div>
                        <div id="image-upload-feedback" class="upload-feedback mt-2" style="display: none;"></div>
                        <?php if (isset($errors['image'])): ?>
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errors['image']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="image-preview-container mt-3" style="display: none;">
                            <label class="form-label">New Image Preview</label>
                            <div class="border rounded p-2 text-center">
                                <img id="image-preview" src="" alt="Image preview" class="img-fluid rounded" style="max-height: 200px;">
                                <div class="mt-2 small text-muted">
                                    <span id="image-dimensions"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="approval_status" class="form-label">Approval Status</label>
                            <select class="form-select" id="approval_status" name="approval_status">
                                <option value="pending" <?= $destination['approval_status'] === 'pending' ? 'selected' : ''; ?>>
                                    Pending Review
                                </option>
                                <option value="approved" <?= $destination['approval_status'] === 'approved' ? 'selected' : ''; ?>>
                                    Approved
                                </option>
                                <option value="rejected" <?= $destination['approval_status'] === 'rejected' ? 'selected' : ''; ?>>
                                    Rejected
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="privacy" class="form-label">Privacy Setting</label>
                            <select class="form-select" id="privacy" name="privacy">
                                <option value="private" <?= $destination['privacy'] === 'private' ? 'selected' : ''; ?>>
                                    Private
                                </option>
                                <option value="public" <?= $destination['privacy'] === 'public' ? 'selected' : ''; ?>>
                                    Public
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Internal notes for admin use only..."><?= htmlspecialchars($destination['notes'] ?? ''); ?></textarea>
                        <div class="form-text">These notes are only visible to administrators</div>
                    </div>

                    <!-- Map Preview -->
                    <hr class="my-4">
                    <h6>Location Preview</h6>
                    <div id="map" style="height: 300px; border-radius: 0.375rem;" class="mb-4"></div>                    <!-- Form Actions -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Update Destination
                        </button>
                        <a href="/admin/destinations/<?= $destination['id']; ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Creator Information -->
        <?php if ($creator): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Creator Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3">
                        <?= strtoupper(substr($creator['username'] ?? '', 0, 2)); ?>
                    </div>
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($creator['username']); ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($creator['email']); ?></small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <a href="/admin/users/<?= $creator['id']; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user"></i> View User Profile
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Destination Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Details</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="text-muted mb-1">Created</h6>
                            <small><?= date('M j, Y', strtotime($destination['created_at'])); ?></small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Updated</h6>
                        <small><?= date('M j, Y', strtotime($destination['updated_at'])); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const lat = <?= $destination['latitude'] ?? 0; ?>;
    const lng = <?= $destination['longitude'] ?? 0; ?>;
    
    const map = L.map('map').setView([lat, lng], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add marker
    const marker = L.marker([lat, lng]).addTo(map);
    
    // Update coordinates when map is clicked
    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        
        marker.setLatLng([lat, lng]);
    });
    
    // Update marker when coordinates are manually changed
    function updateMarker() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], map.getZoom());
        }
    }
      document.getElementById('latitude').addEventListener('change', updateMarker);
    document.getElementById('longitude').addEventListener('change', updateMarker);
    
    // Initialize image resizer for destination images
    const imageUpload = document.getElementById('image');
    const imageResizedInput = document.getElementById('image_resized');
    const imagePreview = document.getElementById('image-preview');
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    const imageDimensions = document.getElementById('image-dimensions');
    
    if (imageUpload) {
        // Initialize ImageResizer for destination images (landscape format, larger dimensions)
        const destinationImageResizer = new ImageResizer({
            targetWidth: 1200,
            targetHeight: 800,
            outputFormat: 'jpeg',
            outputQuality: 0.85,
            onResize: function(resizedFile, resizedDataUrl) {
                console.log('🎯 Admin destination image resized');
                
                try {
                    // Update hidden input with resized data
                    if (imageResizedInput) {
                        imageResizedInput.value = resizedDataUrl;
                    }
                    
                    // Update preview
                    if (imagePreview && imagePreviewContainer) {
                        imagePreview.src = resizedDataUrl;
                        imagePreviewContainer.style.display = 'block';
                        
                        // Show dimensions
                        if (imageDimensions) {
                            imageDimensions.textContent = `Resized to 1200x800px • ${(resizedDataUrl.length / 1024).toFixed(0)}KB`;
                        }
                    }
                    
                    // Show success feedback
                    const feedback = document.getElementById('image-upload-feedback');
                    if (feedback) {
                        feedback.className = 'alert alert-success small';
                        feedback.innerHTML = '<i class="fas fa-check-circle"></i> Image resized and optimized for web display';
                        feedback.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error in admin destination image resize callback:', error);
                }
            },
            onError: function(error) {
                console.error('Admin destination image resize error:', error);
                
                // Show error feedback
                const feedback = document.getElementById('image-upload-feedback');
                if (feedback) {
                    feedback.className = 'alert alert-danger small';
                    feedback.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${error}`;
                    feedback.style.display = 'block';
                }
            }
        });
        
        // Handle file selection
        imageUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                // Clear preview and inputs if no file selected
                if (imagePreviewContainer) {
                    imagePreviewContainer.style.display = 'none';
                }
                if (imageResizedInput) {
                    imageResizedInput.value = '';
                }
                const feedback = document.getElementById('image-upload-feedback');
                if (feedback) {
                    feedback.style.display = 'none';
                }
                return;
            }
            
            // Check if image is oversized and needs resizing
            const img = new Image();
            img.onload = function() {
                if (img.width > 1200 || img.height > 800) {
                    // Image is oversized, trigger resizer
                    destinationImageResizer.processFile(file);
                } else {
                    // Image is appropriately sized, show preview directly
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (imagePreview && imagePreviewContainer) {
                            imagePreview.src = e.target.result;
                            imagePreviewContainer.style.display = 'block';
                            
                            if (imageDimensions) {
                                imageDimensions.textContent = `${img.width}x${img.height}px • ${(file.size / 1024).toFixed(0)}KB`;
                            }
                        }
                        
                        // Show info feedback
                        const feedback = document.getElementById('image-upload-feedback');
                        if (feedback) {
                            feedback.className = 'alert alert-info small';
                            feedback.innerHTML = '<i class="fas fa-info-circle"></i> Image size is optimal, no resizing needed';
                            feedback.style.display = 'block';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            };
            img.src = URL.createObjectURL(file);
        });
    }
});
</script>

<!-- Image Resizer -->
<script src="/js/image-resizer.js"></script>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
