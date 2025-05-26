/**
 * Docker-specific Google Maps API Loader
 * This script addresses common issues with Google Maps API in Docker environments
 * including connectivity issues, IP address detection, and CORS problems.
 */

(function() {
    // Settings for Docker environments
    const CONFIG = {
        // Maximum number of retry attempts
        MAX_RETRIES: 5,
        // Initial delay between retries in milliseconds
        INITIAL_RETRY_DELAY: 1000,        // Maximum delay between retries (with exponential backoff)        MAX_RETRY_DELAY: 10000,
        // Debug mode disabled
        DEBUG: false,
        // Alternative libraries to try loading if the main one fails
        FALLBACK_LIBRARIES: ['places', 'marker', 'places,marker', '']
    };

    // State tracking
    let state = {
        attempts: 0,
        libraryIndex: 0,
        apiKey: null,
        currentDelay: CONFIG.INITIAL_RETRY_DELAY,
        isLoading: false,
        hasLoaded: false,
        callbacks: []
    };    /**
     * Initialize the Google Maps loader with basic error detection
     */
    function init() {
        // Extract API key from meta tag
        const metaElement = document.querySelector('meta[name="google-maps-api-key"]');
        state.apiKey = metaElement ? metaElement.getAttribute('content') : '';
          if (!state.apiKey) {
            showError('Google Maps API key not found');
            return;
        }

        // Check if we're in a Docker environment 
        checkDockerEnvironment()            .then(isDocker => {
                if (isDocker) {
                    // Use Docker-specific loading strategy
                    loadWithRetry();
                } else {
                    // Just use the standard loading mechanism
                    loadMapsApi();
                }
            });

        // Add global error handler for script loading        window.gm_authFailure = function() {
            // If auth failure happens in Docker, try IP-based fallback
            handleLoadError('auth_failure');
        };
    }

    /**
     * Detect whether we're running in a Docker container
     */
    function checkDockerEnvironment() {
        return new Promise(resolve => {
            // Make a request to our diagnostic endpoint
            fetch('/api/debug/environment')
                .then(response => response.json())
                .then(data => {
                    resolve(data.isDocker === true);
                })                .catch(error => {
                    // Assume Docker if detection fails
                    resolve(true);
                });
        });
    }

    /**
     * Load the Google Maps API with retry and fallback mechanisms
     */
    function loadWithRetry() {
        if (state.isLoading) return;
          if (state.attempts >= CONFIG.MAX_RETRIES) {
            showError('Failed to load Google Maps after multiple attempts');
            return;
        }
        
        state.isLoading = true;
        state.attempts++;
          // Try different library combinations
        const libraryParam = CONFIG.FALLBACK_LIBRARIES[state.libraryIndex] || '';
        
        // Create and append the script
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${state.apiKey}&libraries=${libraryParam}&callback=dockerMapsCallback`;
        script.async = true;
        script.defer = true;
        
        script.onerror = function(error) {
            
            handleLoadError('script_error');
        };
        
        // Add the script to the page
        document.head.appendChild(script);
          // Set timeout for loading
        setTimeout(function() {
            if (!window.google || !window.google.maps) {
                handleLoadError('timeout');
            }
        }, 10000);
          // Global callback for when Maps API loads
        window.dockerMapsCallback = function() {
            state.isLoading = false;
            state.hasLoaded = true;
            
            // Call the original initialization function
            if (typeof initializeGoogleMaps === 'function') {
                initializeGoogleMaps();
            }
            
            // Run any other registered callbacks
            state.callbacks.forEach(callback => {
                try {
                    callback();
                } catch (e) {
                    
                }
            });
            
            // Remove all error messages
            document.querySelectorAll('.docker-maps-error').forEach(el => el.remove());
        };
    }    /**
     * Handle load errors with exponential backoff and fallback libraries
     */
    function handleLoadError(reason) {
        state.isLoading = false;
        
        // Try next library combination
        state.libraryIndex = (state.libraryIndex + 1) % CONFIG.FALLBACK_LIBRARIES.length;
        
        // Calculate backoff delay
        state.currentDelay = Math.min(state.currentDelay * 1.5, CONFIG.MAX_RETRY_DELAY);
        
        // Retry with backoff
        setTimeout(loadWithRetry, state.currentDelay);
    }

    /**
     * Standard Maps API loader (non-Docker environments)
     */
    function loadMapsApi() {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${state.apiKey}&libraries=places,marker&callback=initializeGoogleMaps`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    /**
     * Display error messages on map containers
     */
    function showError(message) {
        const mapContainers = document.querySelectorAll('[id$="-map"]');
        mapContainers.forEach(container => {
            // Only add error if container doesn't already have one
            if (!container.querySelector('.docker-maps-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-warning docker-maps-error';
                errorDiv.innerHTML = `
                    <h5><i class="fas fa-exclamation-triangle"></i> Map Loading Issue</h5>
                    <p>${message}</p>
                    <p>The system will try to recover automatically.</p>
                `;
                container.appendChild(errorDiv);
            }
        });
    }

    /**
     * Register a callback to be executed when Maps API loads
     */
    window.registerGoogleMapsCallback = function(callback) {
        if (state.hasLoaded && typeof google !== 'undefined' && google.maps) {
            // If already loaded, execute immediately
            callback();
        } else {
            // Otherwise add to queue
            state.callbacks.push(callback);
        }
    };

    // Start the loader
    document.addEventListener('DOMContentLoaded', init);
})();
