/**
 * MapIt - Destinations Map Handler
 * This module improves the handling of Google Maps on the destinations page,
 * particularly in Docker environments.
 */

(function() {
    // Store reference to original initialization function
    let originalInitDestinationsMap;    // Enhanced initialization function with Docker support
    function enhancedInitDestinationsMap() {
        const mapContainer = document.getElementById('destinations-map');
          if (!mapContainer) {
            return;
        }
        
        try {            // Check if Google Maps is available
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                mapContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Loading Map</h5>
                        <p>The map is currently loading. This might take a moment...</p>
                    </div>
                `;
                  // Try to use our Docker-specific loader if available
                if (typeof registerGoogleMapsCallback === 'function') {
                    registerGoogleMapsCallback(function() {
                        enhancedInitDestinationsMap();
                    });
                } else {                    // Fallback to the normal wait function
                    if (typeof waitForGoogleMaps === 'function') {
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
                return;            }
            
            window.destinationsMap = new google.maps.Map(mapContainer, {
                zoom: 2,
                center: {lat: 20, lng: 0},
                mapTypeId: 'terrain',
                mapTypeControl: false,
                streetViewControl: false
            });
            
            // If we have the original initialization function, call it first to get data setup
            if (typeof originalInitDestinationsMap === 'function') {                try {
                    
                    // Create a temporary global reference to our map
                    const tempMap = window.destinationsMap;
                    
                    // Call the original function - it will handle data loading and interaction setup
                    const result = originalInitDestinationsMap();
                    
                    // Restore our map reference in case it was modified
                    window.destinationsMap = tempMap;
                      if (result === false) {
                        // Fallback to our enhanced version
                        loadMapData();                    } else {
                    }
                } catch (error) {
                    
                    // Fallback to our enhanced version
                    loadMapData();
                }            } else {
                // Use our enhanced version
                loadMapData();            }
            
        } catch (error) {
            
            mapContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle"></i> Map Error</h5>
                    <p>${error.message || 'Unknown error'}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i> Refresh Page
                    </button>
                </div>
            `;
              // Log the error (server logging removed for performance)
            
        }
        
        // Return true to indicate successful initialization
        return true;
    }
      // Helper function to load map data
    function loadMapData() {
        // Only proceed if map is initialized
        if (!window.destinationsMap) {
            
            return;
        }
        
        try {            // Check if the data is already available in the page
            if (typeof userDestinations !== 'undefined' && typeof publicDestinations !== 'undefined') {
                
                // Combine user and public destinations for the map
                const allDestinations = [...userDestinations, ...publicDestinations];
                
                // Call addDestinationsToMap if available
                if (typeof addDestinationsToMap === 'function') {
                    
                    addDestinationsToMap(window.destinationsMap, allDestinations);
                } else {
                    
                }
                
                // Enable interactive map clicking for adding destinations
                if (typeof enableInteractiveMapClicking === 'function') {
                    
                    enableInteractiveMapClicking(window.destinationsMap);
                } else {
                    
                    // Fallback: add click listener directly
                    window.destinationsMap.addListener('click', function(event) {
                        
                        if (typeof handleMapClick === 'function') {
                            handleMapClick(event.latLng, window.destinationsMap);
                        } else {
                            
                        }
                    });
                }
            } else {
                // If data isn't available, fetch it via AJAX
                
                fetch('/api/destinations')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.destinations) {
                            if (typeof addDestinationsToMap === 'function') {
                                addDestinationsToMap(window.destinationsMap, data.destinations);
                            }
                            
                            // Enable interactive map clicking for adding destinations
                            if (typeof enableInteractiveMapClicking === 'function') {
                                
                                enableInteractiveMapClicking(window.destinationsMap);
                            } else {
                                
                                // Fallback: add click listener directly
                                window.destinationsMap.addListener('click', function(event) {
                                    
                                    if (typeof handleMapClick === 'function') {
                                        handleMapClick(event.latLng, window.destinationsMap);
                                    } else {
                                        
                                    }
                                });
                            }
                        } else {
                            
                        }
                    })
                    .catch(error => {
                        
                        
                        // Still enable clicking even if we can't load destinations
                        if (typeof enableInteractiveMapClicking === 'function') {
                            
                            enableInteractiveMapClicking(window.destinationsMap);
                        } else {
                            // Fallback: add click listener directly
                            window.destinationsMap.addListener('click', function(event) {
                                
                                if (typeof handleMapClick === 'function') {
                                    handleMapClick(event.latLng, window.destinationsMap);
                                } else {
                                    
                                }
                            });
                        }
                    });
            }
        } catch (error) {
            
            
            // Still try to enable clicking
            if (typeof enableInteractiveMapClicking === 'function') {
                
                enableInteractiveMapClicking(window.destinationsMap);
            }
        }
    }
    
    // Hook our enhanced initialization when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Store reference to the original function if it exists
        if (typeof window.initDestinationsMap === 'function') {
            
            originalInitDestinationsMap = window.initDestinationsMap;
            
            // Replace with our enhanced version
            window.initDestinationsMap = enhancedInitDestinationsMap;
        } else {
            
            window.initDestinationsMap = enhancedInitDestinationsMap;
        }
    });
})();
