/**
 * MapIt - Destinations Map Handler
 * This module improves the handling of Google Maps on the destinations page,
 * particularly in Docker environments.
 */

(function() {
    // Store reference to original initialization function
    let originalInitDestinationsMap;

    // Enhanced initialization function with Docker support
    function enhancedInitDestinationsMap() {
        const mapContainer = document.getElementById('destinations-map');
        
        if (!mapContainer) {
            console.error('Destinations map container not found');
            return;
        }
        
        try {
            // Check if Google Maps is available
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.error('Google Maps API not loaded');
                mapContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Loading Map</h5>
                        <p>The map is currently loading. This might take a moment...</p>
                    </div>
                `;
                
                // Try to use our Docker-specific loader if available
                if (typeof registerGoogleMapsCallback === 'function') {
                    console.log('Using Docker Maps Loader for destinations page');
                    registerGoogleMapsCallback(function() {
                        console.log('Docker Maps Loader callback triggered for destinations');
                        enhancedInitDestinationsMap();
                    });
                } else {
                    // Fallback to the normal wait function
                    if (typeof waitForGoogleMaps === 'function') {
                        console.log('Attempting to load Google Maps API...');
                        waitForGoogleMaps();
                        // Try again after waiting
                        setTimeout(enhancedInitDestinationsMap, 2000);
                    } else {
                        // Last resort - show a clearer error
                        mapContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-map-marker-alt"></i> Map Loading Issue</h5>
                                <p>Unable to load Google Maps. This may be due to network connectivity or API key issues.</p>
                                <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">
                                    <i class="fas fa-redo"></i> Refresh Page
                                </button>
                            </div>
                        `;
                    }
                }
                return;
            }
            
            console.log('Creating destinations map');
            window.destinationsMap = new google.maps.Map(mapContainer, {
                zoom: 2,
                center: {lat: 20, lng: 0},
                mapTypeId: 'terrain',
                mapTypeControl: false,
                streetViewControl: false
            });
            
            // Call the original map initialization logic, if available
            if (typeof originalInitDestinationsMap === 'function') {
                try {
                    // Extract the part after the initial checks
                    const result = originalInitDestinationsMap();
                    if (result === false) {
                        console.log('Original map initialization returned false, continuing with enhanced version');
                    }
                } catch (error) {
                    console.error('Error in original map initialization:', error);
                }
            }
            
            // Ensure map data is loaded
            loadMapData();
            
            console.log('Destinations map initialization complete');
        } catch (error) {
            console.error('Error initializing destinations map:', error);
            mapContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle"></i> Map Error</h5>
                    <p>${error.message || 'Unknown error'}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i> Refresh Page
                    </button>
                </div>
            `;
            
            // Log the error to server
            if (typeof logToServer === 'function') {
                logToServer('error', 'Error initializing destinations map: ' + error.message);
            }
        }
        
        // Return true to indicate successful initialization
        return true;
    }
    
    // Helper function to load map data
    function loadMapData() {
        // Only proceed if map is initialized
        if (!window.destinationsMap) {
            console.error('Cannot load map data - map not initialized');
            return;
        }
        
        try {
            // Check if the data is already available in the page
            if (typeof userDestinations !== 'undefined' && typeof publicDestinations !== 'undefined') {
                console.log(`Using page data: ${userDestinations.length} user destinations and ${publicDestinations.length} public destinations`);
                
                // Combine user and public destinations for the map
                const allDestinations = [...userDestinations, ...publicDestinations];
                addDestinationsToMap(window.destinationsMap, allDestinations);
                
                // Enable interactive map clicking for adding destinations
                if (typeof enableInteractiveMapClicking === 'function') {
                    enableInteractiveMapClicking(window.destinationsMap);
                }
            } else {
                // If data isn't available, fetch it via AJAX
                console.log('Data not found in page, fetching via API');
                fetch('/api/destinations')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.destinations) {
                            addDestinationsToMap(window.destinationsMap, data.destinations);
                            
                            // Enable interactive map clicking for adding destinations
                            if (typeof enableInteractiveMapClicking === 'function') {
                                enableInteractiveMapClicking(window.destinationsMap);
                            }
                        } else {
                            console.error('Invalid data format from API');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching destinations:', error);
                    });
            }
        } catch (error) {
            console.error('Error loading map data:', error);
        }
    }
    
    // Hook our enhanced initialization when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Store reference to the original function if it exists
        if (typeof window.initDestinationsMap === 'function') {
            console.log('Enhancing original destinations map initialization');
            originalInitDestinationsMap = window.initDestinationsMap;
            
            // Replace with our enhanced version
            window.initDestinationsMap = enhancedInitDestinationsMap;
        } else {
            console.warn('Original initDestinationsMap not found, only using enhanced version');
            window.initDestinationsMap = enhancedInitDestinationsMap;
        }
    });
})();
