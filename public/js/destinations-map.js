/**
 * MapIt - Destinations Map Handler
 * Simplified initialization for destinations map
 */

(function() {
    // Simple destinations map initialization
    function initDestinationsMap() {
        const mapContainer = document.getElementById('destinations-map');
        if (!mapContainer) {
            return;
        }
        
        try {
            // Check if Google Maps is available
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.warn('Google Maps not available for destinations map');
                mapContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Loading Map</h5>
                        <p>The map is currently loading. Please wait...</p>
                    </div>
                `;
                return;
            }
            
            // Create the map
            window.destinationsMap = new google.maps.Map(mapContainer, {
                zoom: 2,
                center: {lat: 20, lng: 0},
                mapTypeId: 'terrain',
                mapTypeControl: false,
                streetViewControl: false,
                mapId: 'MAPIT_DESTINATIONS_MAP'
            });
            
            // Load map data
            loadMapData();
            
        } catch (error) {
            console.error('Error initializing destinations map:', error);
            mapContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle"></i> Map Error</h5>
                    <p>Unable to load the map. Please refresh the page.</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i> Refresh Page
                    </button>
                </div>
            `;
        }
        
        return true;
    }
    
    // Helper function to load map data
    function loadMapData() {
        if (!window.destinationsMap) {
            return;
        }
        
        try {            // Check if the data is already available in the page
            if (typeof window.publicDestinations !== 'undefined' || typeof window.userDestinations !== 'undefined') {
                const userDests = window.userDestinations || [];
                const publicDests = window.publicDestinations || [];
                const featuredDests = window.featuredDestinations || [];
                
                // Combine all destinations for the map
                const allDestinations = [...userDests, ...publicDests, ...featuredDests];
                
                console.log(`Loading ${allDestinations.length} destinations to map:`, {
                    user: userDests.length,
                    public: publicDests.length, 
                    featured: featuredDests.length
                });
                  // Add destinations to map if function is available
                if (typeof addDestinationsToMap === 'function') {
                    addDestinationsToMap(window.destinationsMap, allDestinations);
                } else {
                    console.warn('addDestinationsToMap function not found');
                }
                
                // Enable interactive map clicking
                if (typeof enableInteractiveMapClicking === 'function') {
                    enableInteractiveMapClicking(window.destinationsMap);
                } else {
                    // Fallback: add click listener directly only for authenticated users
                    if (!window.isPublicMap) {
                        window.destinationsMap.addListener('click', function(event) {
                            if (typeof handleMapClick === 'function') {
                                handleMapClick(event.latLng, window.destinationsMap);
                            }
                        });
                    }
                }} else {
                // Fetch data via AJAX if not available
                const apiEndpoint = window.isPublicMap ? '/api/public/destinations' : '/api/destinations';
                
                fetch(apiEndpoint)
                    .then(response => response.json())
                    .then(data => {
                        let destinations = [];
                        if (data && data.success && data.data) {
                            destinations = data.data;
                        } else if (data && data.destinations) {
                            destinations = data.destinations;
                        } else if (Array.isArray(data)) {
                            destinations = data;
                        }
                        
                        if (destinations.length > 0 && typeof addDestinationsToMap === 'function') {
                            addDestinationsToMap(window.destinationsMap, destinations);
                        }
                        
                        // Enable interactive map clicking only for authenticated users
                        if (!window.isPublicMap && typeof enableInteractiveMapClicking === 'function') {
                            enableInteractiveMapClicking(window.destinationsMap);
                        } else if (!window.isPublicMap) {
                            window.destinationsMap.addListener('click', function(event) {
                                if (typeof handleMapClick === 'function') {
                                    handleMapClick(event.latLng, window.destinationsMap);
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.warn('Failed to load destinations:', error);
                        
                        // Still enable clicking for authenticated users even if we can't load destinations
                        if (!window.isPublicMap && typeof enableInteractiveMapClicking === 'function') {
                            enableInteractiveMapClicking(window.destinationsMap);
                        } else if (!window.isPublicMap) {
                            window.destinationsMap.addListener('click', function(event) {
                                if (typeof handleMapClick === 'function') {
                                    handleMapClick(event.latLng, window.destinationsMap);
                                }
                            });
                        }
                    });
            }
        } catch (error) {
            console.error('Error loading map data:', error);
            
            // Still try to enable clicking
            if (typeof enableInteractiveMapClicking === 'function') {
                enableInteractiveMapClicking(window.destinationsMap);
            }
        }
    }
    
    // Set up the global function
    window.initDestinationsMap = initDestinationsMap;
})();
