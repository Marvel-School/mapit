/**
 * Image Resizer and Cropper for Profile Pictures
 * Allows users to resize and crop images to meet 800x800px requirements
 */

class ImageResizer {
    constructor(options = {}) {
        this.canvas = null;
        this.ctx = null;
        this.img = null;
        this.cropperModal = null;
        this.isInitialized = false;
        this.isProcessing = false; // Add processing state tracking
        this.processingStartTime = null; // Track when processing started
        this.minSize = 800; // Required size for avatars
        this.maxCanvasSize = 800; // Canvas display size
        this.originalFile = null;
        this.onSaveCallback = null;
        
        // Support for callback-based configuration
        this.targetWidth = options.targetWidth || 800;
        this.targetHeight = options.targetHeight || 800;
        this.outputFormat = options.outputFormat || 'jpeg';
        this.outputQuality = options.outputQuality || 0.9;
        this.onResize = options.onResize || null;
        this.onError = options.onError || null;
        
        this.init();
    }    init() {
        this.createCropperModal();
        this.attachEventListeners();
        this.startSafetyTimeout();
        this.isInitialized = true;
    }

    // Safety timeout to prevent indefinite processing
    startSafetyTimeout() {
        setInterval(() => {
            if (this.isProcessing && this.processingStartTime) {
                const elapsed = Date.now() - this.processingStartTime;
                if (elapsed > 30000) { // 30 seconds
                    console.warn('⚠️ Safety timeout triggered - forcing cleanup after 30 seconds');
                    this.forceCleanupModal();
                    if (this.onError) {
                        this.onError('Operation timed out after 30 seconds');
                    }
                }
            }
        }, 5000); // Check every 5 seconds
    }createCropperModal() {
        // Create modal HTML
        const modalHTML = `
            <div class="modal fade" id="imageCropperModal" tabindex="-1" aria-labelledby="imageCropperModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageCropperModalLabel">
                                <i class="fas fa-crop-alt me-2"></i>Resize Your Profile Picture
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="cropper-container">
                                        <canvas id="imageCanvas" class="img-fluid border"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="cropWidth" class="form-label">Width:</label>                                                <input type="range" class="form-range" id="cropWidth" min="${this.targetWidth}" max="2000" value="${this.targetWidth}">
                                                <span id="cropWidthValue">${this.targetWidth}px</span>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cropHeight" class="form-label">Height:</label>                                                <input type="range" class="form-range" id="cropHeight" min="${this.targetHeight}" max="2000" value="${this.targetHeight}">
                                                <span id="cropHeightValue">${this.targetHeight}px</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-outline-secondary" id="resetCrop">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" id="centerCrop">
                                                <i class="fas fa-crosshairs"></i> Center
                                            </button>
                                            <button type="button" class="btn btn-outline-success" id="autoFit">
                                                <i class="fas fa-expand-arrows-alt"></i> Auto Fit
                                            </button>
                                        </div>
                                    </div>
                                </div>                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h6>Preview (${this.targetWidth}x${this.targetHeight}px)</h6>
                                        <div class="preview-container">
                                            <canvas id="previewCanvas" width="200" height="200" class="border rounded-circle"></canvas>
                                        </div>
                                        <div class="mt-3">
                                            <div class="alert alert-info small">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Requirements:</strong><br>
                                                • Minimum size: ${this.targetWidth}x${this.targetHeight}px<br>
                                                • Square aspect ratio<br>
                                                • Will be optimized for web
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="image-info small text-muted">
                                                <div><strong>Original:</strong> <span id="originalDimensions">-</span></div>
                                                <div><strong>File size:</strong> <span id="originalFileSize">-</span></div>
                                                <div><strong>New size:</strong> <span id="newFileSize">-</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveResizedImage">
                                <i class="fas fa-save me-2"></i>Use This Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to page if it doesn't exist
        if (!document.getElementById('imageCropperModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        this.cropperModal = new bootstrap.Modal(document.getElementById('imageCropperModal'));
        this.canvas = document.getElementById('imageCanvas');
        this.ctx = this.canvas.getContext('2d');
        this.previewCanvas = document.getElementById('previewCanvas');
        this.previewCtx = this.previewCanvas.getContext('2d');
        
        // Add modal event listeners for proper cleanup
        const modalElement = document.getElementById('imageCropperModal');
        modalElement.addEventListener('hide.bs.modal', () => {
            this.forceCleanupModal();
        });
        
        modalElement.addEventListener('hidden.bs.modal', () => {
            this.forceCleanupModal();
        });
        
        // Initialize crop parameters
        this.cropParams = {
            x: 0,
            y: 0,
            width: this.targetWidth,
            height: this.targetHeight,
            scale: 1
        };
    }    attachEventListeners() {
        // Dimension sliders
        document.getElementById('cropWidth').addEventListener('input', (e) => {
            this.cropParams.width = parseInt(e.target.value);
            document.getElementById('cropWidthValue').textContent = e.target.value + 'px';
            this.updatePreview();
        });

        document.getElementById('cropHeight').addEventListener('input', (e) => {
            this.cropParams.height = parseInt(e.target.value);
            document.getElementById('cropHeightValue').textContent = e.target.value + 'px';
            this.updatePreview();
        });

        // Control buttons
        document.getElementById('resetCrop').addEventListener('click', () => this.resetCrop());
        document.getElementById('centerCrop').addEventListener('click', () => this.centerCrop());
        document.getElementById('autoFit').addEventListener('click', () => this.autoFit());
        document.getElementById('saveResizedImage').addEventListener('click', () => this.saveResizedImage());

        // Canvas mouse events for dragging
        this.canvas.addEventListener('mousedown', (e) => this.startDrag(e));
        this.canvas.addEventListener('mousemove', (e) => this.drag(e));
        this.canvas.addEventListener('mouseup', () => this.endDrag());
        this.canvas.addEventListener('mouseleave', () => this.endDrag());

        // Touch events for mobile
        this.canvas.addEventListener('touchstart', (e) => this.startDrag(e));
        this.canvas.addEventListener('touchmove', (e) => this.drag(e));
        this.canvas.addEventListener('touchend', () => this.endDrag());
        
        // Emergency cleanup handlers
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.cropperModal && document.getElementById('imageCropperModal').classList.contains('show')) {
                this.forceCleanupModal();
            }
        });
        
        window.addEventListener('beforeunload', () => {
            this.forceCleanupModal();
        });
    }

    // Force cleanup modal backdrop and state
    forceCleanupModal() {
        try {
            // Remove any lingering modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            
            // Reset body styles that Bootstrap may have set
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Reset processing state
            this.isProcessing = false;
            this.processingStartTime = null;
            
            console.log('Modal cleanup completed');
        } catch (error) {
            console.error('Error during modal cleanup:', error);
        }
    }    openCropper(file, onSave) {
        if (!file || !file.type || !file.type.startsWith('image/')) {
            console.error('Invalid file provided to openCropper');
            if (this.onError) this.onError('Invalid image file provided');
            return;
        }
        
        this.originalFile = file;
        this.onSaveCallback = onSave;
        this.isProcessing = true;
        this.processingStartTime = Date.now();

        const reader = new FileReader();
        reader.onload = (e) => {
            this.img = new Image();
            this.img.onload = () => {
                // Validate image dimensions
                if (!this.img.width || !this.img.height || this.img.width < 1 || this.img.height < 1) {
                    console.error('Invalid image dimensions:', this.img.width, this.img.height);
                    this.isProcessing = false;
                    if (this.onError) this.onError('Invalid image dimensions');
                    return;
                }
                
                console.log('Image loaded successfully:', this.img.width, 'x', this.img.height);
                this.setupCanvas();
                this.updateImageInfo();
                this.cropperModal.show();
                this.isProcessing = false;
            };
            
            this.img.onerror = () => {
                console.error('Failed to load image');
                this.isProcessing = false;
                if (this.onError) this.onError('Failed to load image file');
            };
            
            this.img.src = e.target.result;
        };
        
        reader.onerror = () => {
            console.error('Failed to read file');
            this.isProcessing = false;
            if (this.onError) this.onError('Failed to read image file');
        };
        
        reader.readAsDataURL(file);
    }

    // Alternative method name for compatibility
    openModal(file) {
        if (!file || !file.type || !file.type.startsWith('image/')) {
            console.error('Invalid file provided to openModal');
            if (this.onError) this.onError('Invalid image file provided');
            return;
        }
        
        this.originalFile = file;
        this.onSaveCallback = null; // Use callback configuration instead
        this.isProcessing = true;
        this.processingStartTime = Date.now();

        const reader = new FileReader();
        reader.onload = (e) => {
            this.img = new Image();
            this.img.onload = () => {
                // Validate image dimensions
                if (!this.img.width || !this.img.height || this.img.width < 1 || this.img.height < 1) {
                    console.error('Invalid image dimensions:', this.img.width, this.img.height);
                    this.isProcessing = false;
                    if (this.onError) this.onError('Invalid image dimensions');
                    return;
                }
                
                console.log('Image loaded successfully:', this.img.width, 'x', this.img.height);
                this.setupCanvas();
                this.updateImageInfo();
                this.cropperModal.show();
                this.isProcessing = false;
            };
            
            this.img.onerror = () => {
                console.error('Failed to load image');
                this.isProcessing = false;
                if (this.onError) this.onError('Failed to load image file');
            };
            
            this.img.src = e.target.result;
        };
        
        reader.onerror = () => {
            console.error('Failed to read file');
            this.isProcessing = false;
            if (this.onError) this.onError('Failed to read image file');
        };
        
        reader.readAsDataURL(file);
    }

    setupCanvas() {
        // Calculate scale to fit image in canvas while maintaining aspect ratio
        const maxCanvasWidth = 600;
        const maxCanvasHeight = 400;
        
        const imgAspect = this.img.width / this.img.height;
        const canvasAspect = maxCanvasWidth / maxCanvasHeight;
        
        let canvasWidth, canvasHeight;
        
        if (imgAspect > canvasAspect) {
            canvasWidth = maxCanvasWidth;
            canvasHeight = maxCanvasWidth / imgAspect;
        } else {
            canvasHeight = maxCanvasHeight;
            canvasWidth = maxCanvasHeight * imgAspect;
        }
        
        this.canvas.width = canvasWidth;
        this.canvas.height = canvasHeight;
        this.canvas.style.width = canvasWidth + 'px';
        this.canvas.style.height = canvasHeight + 'px';
        
        // Calculate scale factor
        this.cropParams.scale = Math.min(canvasWidth / this.img.width, canvasHeight / this.img.height);
          // Set initial crop size based on image and target dimensions
        const minCropSize = Math.min(this.img.width, this.img.height);
        this.cropParams.width = Math.max(this.targetWidth, minCropSize);
        this.cropParams.height = Math.max(this.targetHeight, minCropSize);
        
        // Update sliders
        document.getElementById('cropWidth').min = this.targetWidth;
        document.getElementById('cropWidth').max = this.img.width;
        document.getElementById('cropWidth').value = this.cropParams.width;
        document.getElementById('cropHeight').min = this.targetHeight;
        document.getElementById('cropHeight').max = this.img.height;
        document.getElementById('cropHeight').value = this.cropParams.height;
        
        this.centerCrop();
        this.drawCanvas();
        this.updatePreview();
    }    drawCanvas() {
        // Validate image and canvas before drawing
        if (!this.img || !this.img.complete || !this.img.naturalWidth || !this.canvas || !this.ctx) {
            console.warn('Cannot draw canvas: image or canvas not ready');
            return;
        }
        
        try {
            // Clear canvas
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Draw image
            this.ctx.drawImage(
                this.img,
                0, 0, 
                this.img.width, this.img.height,
                0, 0, 
                this.canvas.width, this.canvas.height
            );
            
            // Draw crop overlay
            this.drawCropOverlay();
        } catch (error) {
            console.error('Error drawing canvas:', error);
            if (this.onError) this.onError('Error drawing image: ' + error.message);
        }
    }

    drawCropOverlay() {
        // Validate image and canvas before drawing
        if (!this.img || !this.img.complete || !this.img.naturalWidth || !this.canvas || !this.ctx) {
            console.warn('Cannot draw crop overlay: image or canvas not ready');
            return;
        }
        
        try {
            const scaledX = this.cropParams.x * this.cropParams.scale;
            const scaledY = this.cropParams.y * this.cropParams.scale;
            const scaledWidth = this.cropParams.width * this.cropParams.scale;
            const scaledHeight = this.cropParams.height * this.cropParams.scale;
            
            // Validate crop parameters
            if (scaledX < 0 || scaledY < 0 || scaledWidth <= 0 || scaledHeight <= 0) {
                console.warn('Invalid crop parameters:', this.cropParams);
                return;
            }
            
            // Draw semi-transparent overlay
            this.ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Clear crop area
            this.ctx.clearRect(scaledX, scaledY, scaledWidth, scaledHeight);
            
            // Redraw image in crop area - validate crop bounds
            const cropX = Math.max(0, Math.min(this.cropParams.x, this.img.width - this.cropParams.width));
            const cropY = Math.max(0, Math.min(this.cropParams.y, this.img.height - this.cropParams.height));
            const cropWidth = Math.min(this.cropParams.width, this.img.width - cropX);
            const cropHeight = Math.min(this.cropParams.height, this.img.height - cropY);
            
            if (cropWidth > 0 && cropHeight > 0) {
                this.ctx.drawImage(
                    this.img,
                    cropX, cropY, cropWidth, cropHeight,
                    scaledX, scaledY, scaledWidth, scaledHeight
                );
            }
            
            // Draw crop border
            this.ctx.strokeStyle = '#007bff';
            this.ctx.lineWidth = 2;
            this.ctx.strokeRect(scaledX, scaledY, scaledWidth, scaledHeight);
            
            // Draw corner handles
            this.drawHandle(scaledX, scaledY);
            this.drawHandle(scaledX + scaledWidth, scaledY);
            this.drawHandle(scaledX, scaledY + scaledHeight);
            this.drawHandle(scaledX + scaledWidth, scaledY + scaledHeight);
        } catch (error) {
            console.error('Error drawing crop overlay:', error);
            if (this.onError) this.onError('Error drawing crop overlay: ' + error.message);
        }
    }

    drawHandle(x, y) {
        this.ctx.fillStyle = '#007bff';
        this.ctx.fillRect(x - 4, y - 4, 8, 8);
    }    updatePreview() {
        // Validate image and preview canvas before drawing
        if (!this.img || !this.img.complete || !this.img.naturalWidth || !this.previewCanvas || !this.previewCtx) {
            console.warn('Cannot update preview: image or preview canvas not ready');
            return;
        }
        
        try {
            // Clear preview canvas
            this.previewCtx.clearRect(0, 0, 200, 200);
            
            // Validate crop parameters
            const cropX = Math.max(0, Math.min(this.cropParams.x, this.img.width - this.cropParams.width));
            const cropY = Math.max(0, Math.min(this.cropParams.y, this.img.height - this.cropParams.height));
            const cropWidth = Math.min(this.cropParams.width, this.img.width - cropX);
            const cropHeight = Math.min(this.cropParams.height, this.img.height - cropY);
            
            if (cropWidth > 0 && cropHeight > 0) {
                // Draw cropped image to preview
                this.previewCtx.drawImage(
                    this.img,
                    cropX, cropY, cropWidth, cropHeight,
                    0, 0, 200, 200
                );
            }
            
            this.drawCanvas();
            this.updateFileSize();
        } catch (error) {
            console.error('Error updating preview:', error);
            if (this.onError) this.onError('Error updating preview: ' + error.message);
        }
    }

    centerCrop() {
        this.cropParams.x = Math.max(0, (this.img.width - this.cropParams.width) / 2);
        this.cropParams.y = Math.max(0, (this.img.height - this.cropParams.height) / 2);
        this.updatePreview();
    }    resetCrop() {
        this.cropParams.x = 0;
        this.cropParams.y = 0;
        this.cropParams.width = Math.min(this.img.width, this.targetWidth);
        this.cropParams.height = Math.min(this.img.height, this.targetHeight);
        
        document.getElementById('cropWidth').value = this.cropParams.width;
        document.getElementById('cropHeight').value = this.cropParams.height;
        document.getElementById('cropWidthValue').textContent = this.cropParams.width + 'px';
        document.getElementById('cropHeightValue').textContent = this.cropParams.height + 'px';
        
        this.updatePreview();
    }

    autoFit() {
        const minDimension = Math.min(this.img.width, this.img.height);
        this.cropParams.width = minDimension;
        this.cropParams.height = minDimension;
        
        document.getElementById('cropWidth').value = minDimension;
        document.getElementById('cropHeight').value = minDimension;
        document.getElementById('cropWidthValue').textContent = minDimension + 'px';
        document.getElementById('cropHeightValue').textContent = minDimension + 'px';
        
        this.centerCrop();
    }

    startDrag(e) {
        e.preventDefault();
        this.isDragging = true;
        const rect = this.canvas.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        
        this.dragStart = {
            x: (clientX - rect.left) / this.cropParams.scale,
            y: (clientY - rect.top) / this.cropParams.scale,
            cropX: this.cropParams.x,
            cropY: this.cropParams.y
        };
    }

    drag(e) {
        if (!this.isDragging) return;
        e.preventDefault();
        
        const rect = this.canvas.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        
        const currentX = (clientX - rect.left) / this.cropParams.scale;
        const currentY = (clientY - rect.top) / this.cropParams.scale;
        
        const deltaX = currentX - this.dragStart.x;
        const deltaY = currentY - this.dragStart.y;
        
        this.cropParams.x = Math.max(0, Math.min(
            this.img.width - this.cropParams.width,
            this.dragStart.cropX + deltaX
        ));
        
        this.cropParams.y = Math.max(0, Math.min(
            this.img.height - this.cropParams.height,
            this.dragStart.cropY + deltaY
        ));
        
        this.updatePreview();
    }

    endDrag() {
        this.isDragging = false;
    }

    updateImageInfo() {
        document.getElementById('originalDimensions').textContent = 
            `${this.img.width}x${this.img.height}px`;
        document.getElementById('originalFileSize').textContent = 
            this.formatFileSize(this.originalFile.size);
    }    updateFileSize() {
        // Estimate new file size (rough calculation)
        const compressionRatio = 0.8; // Typical JPEG compression
        const pixelRatio = (this.targetWidth * this.targetHeight) / (this.img.width * this.img.height);
        const estimatedSize = this.originalFile.size * pixelRatio * compressionRatio;
        
        document.getElementById('newFileSize').textContent = 
            this.formatFileSize(estimatedSize);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }    saveResizedImage() {
        // Validate image before processing
        if (!this.img || !this.img.complete || !this.img.naturalWidth) {
            console.error('Cannot save: image not ready');
            if (this.onError) this.onError('Image not ready for processing');
            return;
        }
        
        // Prevent multiple saves
        if (this.isProcessing) {
            console.log('Save already in progress, ignoring duplicate request');
            return;
        }
        
        this.isProcessing = true;
        this.processingStartTime = Date.now();
        
        try {
            // Create final canvas at target dimensions
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = this.targetWidth;
            finalCanvas.height = this.targetHeight;
            const finalCtx = finalCanvas.getContext('2d');
            
            // Validate crop parameters one more time
            const cropX = Math.max(0, Math.min(this.cropParams.x, this.img.width - this.cropParams.width));
            const cropY = Math.max(0, Math.min(this.cropParams.y, this.img.height - this.cropParams.height));
            const cropWidth = Math.min(this.cropParams.width, this.img.width - cropX);
            const cropHeight = Math.min(this.cropParams.height, this.img.height - cropY);
            
            if (cropWidth <= 0 || cropHeight <= 0) {
                throw new Error('Invalid crop dimensions');
            }
            
            // Draw cropped image to final canvas
            finalCtx.drawImage(
                this.img,
                cropX, cropY, cropWidth, cropHeight,
                0, 0, this.targetWidth, this.targetHeight
            );
            
            // Convert to blob
            finalCanvas.toBlob((blob) => {
                try {
                    if (!blob) {
                        throw new Error('Failed to create image blob');
                    }
                    
                    // Create new file object
                    const fileExtension = this.outputFormat === 'jpeg' ? 'jpg' : this.outputFormat;
                    const fileName = this.originalFile.name.replace(/\.[^/.]+$/, '') + '_resized.' + fileExtension;
                    const newFile = new File([blob], fileName, {
                        type: `image/${this.outputFormat}`,
                        lastModified: Date.now()
                    });
                    
                    // Get data URL for callback
                    const dataUrl = finalCanvas.toDataURL(`image/${this.outputFormat}`, this.outputQuality);
                    
                    // Call appropriate callback
                    if (this.onSaveCallback) {
                        // Legacy callback support
                        this.onSaveCallback(newFile);
                    } else if (this.onResize) {
                        // New callback configuration
                        this.onResize(newFile, dataUrl);
                    }
                    
                    // Close modal with proper cleanup
                    this.cropperModal.hide();
                    
                    // Force cleanup after a brief delay to ensure modal is closed
                    setTimeout(() => {
                        this.forceCleanupModal();
                    }, 100);
                    
                } catch (error) {
                    console.error('Error saving resized image:', error);
                    if (this.onError) {
                        this.onError('Failed to save resized image: ' + error.message);
                    }
                } finally {
                    this.isProcessing = false;
                    this.processingStartTime = null;
                }
            }, `image/${this.outputFormat}`, this.outputQuality);
            
        } catch (error) {
            console.error('Error in saveResizedImage:', error);
            if (this.onError) {
                this.onError('Failed to process image: ' + error.message);
            }
            this.isProcessing = false;
            this.processingStartTime = null;
        }
    }
}

// Initialize image resizer when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const imageResizer = new ImageResizer();
    
    // Override file input behavior for avatar uploads
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check if image meets requirements
            const img = new Image();
            img.onload = function() {
                if (img.width < 800 || img.height < 800) {
                    // Image is too small, show error
                    showUploadError('avatar-upload-feedback', 
                        `Image is too small (${img.width}x${img.height}px). Minimum size is 800x800px. Please choose a larger image.`);
                    e.target.value = '';
                    return;
                }
                
                if (img.width !== 800 || img.height !== 800) {
                    // Image needs resizing, open cropper
                    e.target.value = ''; // Clear the input
                    imageResizer.openCropper(file, function(resizedFile) {
                        // Replace the file in the input
                        const dt = new DataTransfer();
                        dt.items.add(resizedFile);
                        avatarInput.files = dt.files;
                        
                        // Trigger validation
                        validateFileUpload(avatarInput, 'avatar-upload-feedback');
                        
                        // Update preview
                        if (document.getElementById('profile_image_preview')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('profile_image_preview').src = e.target.result;
                            };
                            reader.readAsDataURL(resizedFile);
                        }
                    });
                } else {
                    // Image is perfect size, proceed with normal validation
                    validateFileUpload(e.target, 'avatar-upload-feedback');
                }
            };
            
            img.onerror = function() {
                showUploadError('avatar-upload-feedback', 'Invalid image file.');
                e.target.value = '';
            };
            
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
});
