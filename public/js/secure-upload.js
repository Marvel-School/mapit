/**
 * Secure Image Upload Validation
 * Client-side security validation for image uploads
 */

document.addEventListener('DOMContentLoaded', function() {
    // Configure validation settings
    const VALIDATION_CONFIG = {
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        maxDimensions: {
            avatars: { width: 800, height: 800 },
            destinations: { width: 1920, height: 1080 }
        }
    };

    // Get all file input elements
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(event) {
            validateImageUpload(event.target);
        });
    });

    function validateImageUpload(input) {
        const file = input.files[0];
        const errorContainer = getOrCreateErrorContainer(input);
        
        // Clear previous errors
        clearErrors(errorContainer);
        
        if (!file) {
            return;
        }

        const errors = [];

        // Validate file size
        if (file.size > VALIDATION_CONFIG.maxFileSize) {
            errors.push(`File size (${formatFileSize(file.size)}) exceeds maximum allowed size (${formatFileSize(VALIDATION_CONFIG.maxFileSize)})`);
        }

        // Validate file type
        if (!VALIDATION_CONFIG.allowedTypes.includes(file.type)) {
            errors.push(`File type "${file.type}" is not allowed. Allowed types: ${VALIDATION_CONFIG.allowedTypes.join(', ')}`);
        }

        // Validate file extension
        const extension = getFileExtension(file.name);
        if (!VALIDATION_CONFIG.allowedExtensions.includes(extension)) {
            errors.push(`File extension ".${extension}" is not allowed. Allowed extensions: ${VALIDATION_CONFIG.allowedExtensions.join(', ')}`);
        }

        // Validate filename for security
        if (!isSecureFilename(file.name)) {
            errors.push('Filename contains invalid characters. Please use only letters, numbers, hyphens, and underscores.');
        }

        // Check for suspicious file content (basic check)
        if (file.name.includes('.php') || file.name.includes('.js') || file.name.includes('.html')) {
            errors.push('File appears to contain executable code and cannot be uploaded.');
        }

        if (errors.length > 0) {
            displayErrors(errorContainer, errors);
            input.value = ''; // Clear the input
            return;
        }

        // Additional validation for image dimensions
        validateImageDimensions(file, input, errorContainer);
    }

    function validateImageDimensions(file, input, errorContainer) {
        // Determine upload type based on input name or form context
        const uploadType = determineUploadType(input);
        const maxDimensions = VALIDATION_CONFIG.maxDimensions[uploadType] || VALIDATION_CONFIG.maxDimensions.destinations;

        const img = new Image();
        const url = URL.createObjectURL(file);
        
        img.onload = function() {
            URL.revokeObjectURL(url); // Clean up
            
            if (img.width > maxDimensions.width || img.height > maxDimensions.height) {
                const errors = [
                    `Image dimensions (${img.width}x${img.height}) exceed maximum allowed size (${maxDimensions.width}x${maxDimensions.height})`
                ];
                displayErrors(errorContainer, errors);
                input.value = ''; // Clear the input
            } else {
                // Show success message
                displaySuccess(errorContainer, `Image validated successfully (${img.width}x${img.height}, ${formatFileSize(file.size)})`);
            }
        };
        
        img.onerror = function() {
            URL.revokeObjectURL(url);
            const errors = ['Invalid image file or corrupted image data'];
            displayErrors(errorContainer, errors);
            input.value = ''; // Clear the input
        };
        
        img.src = url;
    }

    function determineUploadType(input) {
        // Check form action or input context to determine type
        const form = input.closest('form');
        if (form && form.action.includes('profile')) {
            return 'avatars';
        }
        return 'destinations';
    }

    function getFileExtension(filename) {
        return filename.split('.').pop().toLowerCase();
    }

    function isSecureFilename(filename) {
        // Allow only safe characters: letters, numbers, hyphens, underscores, dots
        const securePattern = /^[a-zA-Z0-9._-]+$/;
        return securePattern.test(filename) && !filename.includes('..');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getOrCreateErrorContainer(input) {
        let container = input.parentElement.querySelector('.upload-validation-feedback');
        if (!container) {
            container = document.createElement('div');
            container.className = 'upload-validation-feedback mt-2';
            input.parentElement.appendChild(container);
        }
        return container;
    }

    function clearErrors(container) {
        container.innerHTML = '';
        container.className = 'upload-validation-feedback mt-2';
    }

    function displayErrors(container, errors) {
        container.className = 'upload-validation-feedback mt-2 text-danger';
        container.innerHTML = `
            <div class="alert alert-danger alert-sm p-2 mb-0">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Upload Error:</strong>
                <ul class="mb-0 mt-1 small">
                    ${errors.map(error => `<li>${escapeHtml(error)}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    function displaySuccess(container, message) {
        container.className = 'upload-validation-feedback mt-2 text-success';
        container.innerHTML = `
            <div class="alert alert-success alert-sm p-2 mb-0">
                <i class="fas fa-check-circle"></i>
                <small>${escapeHtml(message)}</small>
            </div>
        `;
    }

    function escapeHtml(text) {        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }    // Additional security: Monitor for paste events with preventDefault for security
    document.addEventListener('paste', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let item of items) {
            if (item.kind === 'file') {
                // Prevent paste of files for security: paste event preventDefault
                e.preventDefault();
                alert('Pasting files is not allowed for security reasons. Please use the file upload button.');
                return false;
            }
        }
    });

    // Disable drag and drop for file inputs to force use of secure upload
    fileInputs.forEach(input => {
        const parent = input.parentElement;
        ['dragover', 'dragenter', 'drop'].forEach(eventName => {
            parent.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });
    });
    
    // Export utility functions for direct use
    window.validateFileUpload = function(input, feedbackElementId) {
        const file = input.files[0];
        const feedbackElement = document.getElementById(feedbackElementId);
        
        if (!file) {
            if (feedbackElement) {
                feedbackElement.innerHTML = '';
                feedbackElement.className = 'upload-feedback';
            }
            return;
        }

        const errors = [];

        // Validate file size
        if (file.size > VALIDATION_CONFIG.maxFileSize) {
            errors.push(`File size exceeds maximum limit (${formatFileSize(VALIDATION_CONFIG.maxFileSize)})`);
        }

        // Validate file type
        if (!VALIDATION_CONFIG.allowedTypes.includes(file.type)) {
            errors.push(`Invalid file type. Allowed: ${VALIDATION_CONFIG.allowedExtensions.join(', ')}`);
        }

        // Validate filename security
        if (!isSecureFilename(file.name)) {
            errors.push('Filename contains invalid characters');
        }

        if (feedbackElement) {
            if (errors.length > 0) {
                feedbackElement.className = 'upload-feedback error';
                feedbackElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${errors.join(', ')}`;
                input.value = ''; // Clear invalid file
            } else {
                feedbackElement.className = 'upload-feedback success';
                feedbackElement.innerHTML = `<i class="fas fa-check-circle"></i> File validated successfully (${formatFileSize(file.size)})`;
            }
        }
    };
    
    window.showUploadError = function(feedbackElementId, message) {
        const feedbackElement = document.getElementById(feedbackElementId);
        if (feedbackElement) {
            feedbackElement.className = 'upload-feedback error';
            feedbackElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        }
    };
});
