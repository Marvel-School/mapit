<?php
// Edit Destination view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">Edit Destination</h4>
                </div>                <div class="card-body">
                    <form action="/destinations/<?= $destination['id']; ?>" method="POST" enctype="multipart/form-data">
                        <?= \App\Core\View::csrfField(); ?>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Destination Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($destination['name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="visited">
                                    <option value="0" <?= isset($destination['visited']) && $destination['visited'] == 0 ? 'selected' : ''; ?>>Wishlist</option>
                                    <option value="1" <?= isset($destination['visited']) && $destination['visited'] == 1 ? 'selected' : ''; ?>>Visited</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="privacy" class="form-label">Privacy</label>
                                <select class="form-select" id="privacy" name="privacy">
                                    <option value="private" <?= isset($destination['privacy']) && $destination['privacy'] == 'private' ? 'selected' : ''; ?>>Private (only visible to me)</option>
                                    <option value="public" <?= isset($destination['privacy']) && $destination['privacy'] == 'public' ? 'selected' : ''; ?>>Public (visible on maps after approval)</option>
                                </select>
                                <div class="form-text">
                                    <?php if (isset($destination['privacy']) && $destination['privacy'] === 'public' && $destination['approval_status'] === 'pending'): ?>
                                        <span class="text-warning">⏳ Awaiting admin approval</span>
                                    <?php elseif (isset($destination['privacy']) && $destination['privacy'] === 'public' && $destination['approval_status'] === 'approved'): ?>
                                        <span class="text-success">✓ Approved and visible on maps</span>
                                    <?php elseif (isset($destination['privacy']) && $destination['privacy'] === 'public' && $destination['approval_status'] === 'rejected'): ?>
                                        <span class="text-danger">✗ Approval rejected</span>
                                    <?php else: ?>
                                        Public destinations require admin approval before appearing on maps
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($destination['city'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select a country</option>
                                    <?php foreach ($countries as $code => $name): ?>
                                        <option value="<?= $code; ?>" <?= isset($destination['country']) && $destination['country'] == $code ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($destination['latitude'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($destination['longitude'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($destination['description'] ?? ''); ?></textarea>
                        </div>                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">Destination Image</label>
                                <?php if (!empty($destination['image'])): ?>
                                    <div class="mb-2">
                                        <img id="current-image" src="/images/destinations/<?= htmlspecialchars($destination['image']); ?>" alt="Current image" class="img-thumbnail" style="max-height: 100px;">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                                            <label class="form-check-label small" for="delete_image">Delete current image</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                <input type="hidden" id="image_resized" name="image_resized" value="">
                                <div class="form-text">
                                    <strong>Image requirements:</strong><br>
                                    • Max file size: 5MB<br>
                                    • Allowed formats: JPG, PNG, GIF, WebP<br>
                                    • Large images will be automatically resized for optimal performance<br>
                                    • EXIF data will be removed for privacy
                                </div>
                                <div id="image-upload-feedback" class="upload-feedback mt-2" style="display: none;"></div>
                                <?php if (isset($errors['image'])): ?>
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errors['image']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="image-preview-container" style="display: none;">
                                    <label class="form-label">New Image Preview</label>
                                    <div class="border rounded p-2 text-center">
                                        <img id="image-preview" src="" alt="Image preview" class="img-fluid rounded" style="max-height: 200px;">
                                        <div class="mt-2 small text-muted">
                                            <span id="image-dimensions"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="visit-date-container" style="<?= isset($destination['visited']) && $destination['visited'] == 1 ? '' : 'display: none;' ?>">
                                    <label for="visit_date" class="form-label">Visit Date</label>
                                    <input type="date" class="form-control" id="visit_date" name="visit_date" value="<?= htmlspecialchars($destination['visit_date'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                            
                            <div class="col-md-6 visit-date-container" style="<?= isset($destination['visited']) && $destination['visited'] == 1 ? '' : 'display: none;' ?>">
                                <label for="visit_date" class="form-label">Visit Date</label>
                                <input type="date" class="form-control" id="visit_date" name="visit_date" value="<?= htmlspecialchars($destination['visit_date'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Location on Map</h5>
                        <p class="text-muted small">Search for a place or click on the map to update your destination location.</p>
                        
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="mapSearch" placeholder="Search for a place...">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="map" class="mb-4" style="height: 400px;"></div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/destinations" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Destination</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide visit date based on status
    const statusSelect = document.getElementById('status');
    const visitDateContainer = document.querySelector('.visit-date-container');
    
    if (statusSelect && visitDateContainer) {
        statusSelect.addEventListener('change', function() {
            visitDateContainer.style.display = this.value === '1' ? '' : 'none';
        });
    }
    
    // Initialize the map
    let map, marker;
    const mapElement = document.getElementById('map');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const searchInput = document.getElementById('mapSearch');
    const searchButton = document.getElementById('searchButton');
    
    function initMap() {
        // Default to a central position if no coordinates are set
        let initialLat = latInput.value ? parseFloat(latInput.value) : 20;
        let initialLng = lngInput.value ? parseFloat(lngInput.value) : 0;
          map = new google.maps.Map(mapElement, {
            center: { lat: initialLat, lng: initialLng },
            zoom: latInput.value ? 8 : 2,
            mapTypeId: 'terrain',
            mapId: 'MAPIT_DESTINATION_EDIT_MAP'
        });
        
        // Add marker if coordinates are already set
        if (latInput.value && lngInput.value) {
            addMarker({ lat: initialLat, lng: initialLng });
        }
        
        // Add click listener to map
        map.addListener('click', function(e) {
            const position = e.latLng.toJSON();
            addMarker(position);
            updateCoordinateInputs(position);
        });
        
        // Setup search functionality
        const searchBox = new google.maps.places.SearchBox(searchInput);
        
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            const places = searchBox.getPlaces();
            
            if (places.length === 0) {
                return;
            }
            
            const place = places[0];
              if (!place.geometry || !place.geometry.location) {
                return;
            }
            
            // Update map and inputs
            const position = place.geometry.location.toJSON();
            map.setCenter(position);
            map.setZoom(15);
            addMarker(position);
            updateCoordinateInputs(position);
            
            // We don't automatically update the name/city/country on edit form
            // to prevent overwriting existing data unless requested
        });
        
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                const places = new google.maps.places.PlacesService(map);
                places.findPlaceFromQuery({
                    query: searchInput.value,
                    fields: ['name', 'geometry', 'formatted_address']
                }, function(results, status) {
                    if (status === google.maps.places.PlacesServiceStatus.OK && results && results.length > 0) {
                        const place = results[0];
                        const position = place.geometry.location.toJSON();
                        
                        map.setCenter(position);
                        map.setZoom(15);
                        addMarker(position);
                        updateCoordinateInputs(position);
                    }
                });
            });
        }
    }
      function addMarker(position) {
        // Remove existing marker if any
        if (marker) {
            marker.setMap(null);
        }
        
        // Add new marker with AdvancedMarkerElement or fallback
        try {
            if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                const markerElement = document.createElement('div');
                markerElement.style.background = '#4285f4';
                markerElement.style.borderRadius = '50%';
                markerElement.style.width = '20px';
                markerElement.style.height = '20px';
                markerElement.style.border = '3px solid white';
                markerElement.style.boxShadow = '0 2px 4px rgba(0,0,0,0.3)';
                
                marker = new google.maps.marker.AdvancedMarkerElement({
                    position: position,
                    map: map,
                    content: markerElement,
                    gmpDraggable: true
                });
                
                // Add drag listener
                marker.addListener('dragend', function() {
                    const newPosition = marker.position;
                    updateCoordinateInputs({
                        lat: newPosition.lat,
                        lng: newPosition.lng
                    });
                });
            } else {
                throw new Error('AdvancedMarkerElement not available');
            }
        } catch (error) {
            marker = new google.maps.Marker({
                position: position,
                map: map,
                draggable: true
            });
              // Add drag listener to marker
            marker.addListener('dragend', function() {
                const position = marker.getPosition().toJSON();
                updateCoordinateInputs(position);
            });
        }
    }
    
    function updateCoordinateInputs(position) {
        latInput.value = position.lat.toFixed(6);
        lngInput.value = position.lng.toFixed(6);
    }
      if (mapElement) {
        // Initialize map when the page is loaded
        initMap();
    }
    
    // Initialize image resizer for destination images
    const imageUpload = document.getElementById('image');
    const imageResizedInput = document.getElementById('image_resized');
    const imagePreview = document.getElementById('image-preview');
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    const imageDimensions = document.getElementById('image-dimensions');
    const currentImage = document.getElementById('current-image');
    
    if (imageUpload) {
        // Initialize ImageResizer for destination images (landscape format, larger dimensions)
        const destinationImageResizer = new ImageResizer({
            targetWidth: 1200,
            targetHeight: 800,
            outputFormat: 'jpeg',
            outputQuality: 0.85,
            onResize: function(resizedFile, resizedDataUrl) {
                console.log('🎯 Destination image resized');
                
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
                    console.error('Error in destination image resize callback:', error);
                }
            },
            onError: function(error) {
                console.error('Destination image resize error:', error);
                
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
<!-- Secure Upload Validation -->
<script src="/js/secure-upload.js"></script>
