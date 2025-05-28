/**
 * MapIt - Main JavaScript
 * Handles interactive functionality for the main site
 */

// Global variables
let map;
let markers = [];
let infoWindow;
let quickCreateMarker = null;
let selectedPosition = null;
let googleMapsInitialized = false;

/**
 * Create enhanced SVG-based fallback markers instead of boring circles
 * @param {string} markerType - Type of marker (visited, wishlist, featured, etc.)
 * @returns {Object} Google Maps marker icon configuration
 */
function createEnhancedFallbackMarker(markerType) {
    const markerConfigs = {
        'visited': {
            color: '#28a745',
            gradient: 'linear-gradient(135deg, #28a745 0%, #1e7e34 100%)',
            icon: 'M9 16 L14 21 L23 11', // checkmark path
            iconType: 'path'
        },
        'wishlist': {
            color: '#ffc107',
            gradient: 'linear-gradient(135deg, #ffc107 0%, #ff8f00 100%)',
            icon: 'M16 26.5 C16 26.5 7 19 7 13 C7 10.5 9 8.5 11.5 8.5 C13.5 8.5 15.5 10 16 12 C16.5 10 18.5 8.5 20.5 8.5 C23 8.5 25 10.5 25 13 C25 19 16 26.5 16 26.5 Z', // heart path
            iconType: 'path'
        },
        'featured': {
            color: '#ff6b35',
            gradient: 'linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%)',
            icon: 'M16 6 L18.5 12.5 L25 12.5 L20 17 L22.5 24 L16 19.5 L9.5 24 L12 17 L7 12.5 L13.5 12.5 Z', // star path
            iconType: 'path'
        },
        'planned': {
            color: '#17a2b8',
            gradient: 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)',
            icon: 'calendar', // special case
            iconType: 'special'
        },
        'in_progress': {
            color: '#007bff',
            gradient: 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)',
            icon: 'M6 20 Q11 10 16 16 Q21 22 26 12', // route path
            iconType: 'path'
        },
        'public': {
            color: '#4285f4',
            gradient: 'linear-gradient(135deg, #4285f4 0%, #1a73e8 100%)',
            icon: 'globe', // special case
            iconType: 'special'
        }
    };

    const config = markerConfigs[markerType] || markerConfigs['public'];
    
    // Create SVG icon
    let iconSvg = '';
    if (config.iconType === 'path') {
        iconSvg = `
            <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="grad_${markerType}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:${config.color};stop-opacity:1" />
                        <stop offset="100%" style="stop-color:${darkenColor(config.color, 20)};stop-opacity:1" />
                    </linearGradient>
                    <filter id="shadow_${markerType}">
                        <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#000000" flood-opacity="0.3"/>
                    </filter>
                </defs>
                <circle cx="16" cy="16" r="14" fill="url(#grad_${markerType})" stroke="#ffffff" stroke-width="2" filter="url(#shadow_${markerType})"/>
                <path d="${config.icon}" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;
    } else if (config.iconType === 'special') {
        if (config.icon === 'calendar') {
            iconSvg = `
                <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="14" fill="${config.color}" stroke="#ffffff" stroke-width="2"/>
                    <rect x="10" y="9" width="12" height="14" rx="1" fill="none" stroke="#ffffff" stroke-width="1.5"/>
                    <line x1="13" y1="7" x2="13" y2="11" stroke="#ffffff" stroke-width="1.5"/>
                    <line x1="19" y1="7" x2="19" y2="11" stroke="#ffffff" stroke-width="1.5"/>
                    <line x1="10" y1="13" x2="22" y2="13" stroke="#ffffff" stroke-width="1.5"/>
                    <circle cx="14" cy="17" r="1" fill="#ffffff"/>
                    <circle cx="16" cy="17" r="1" fill="#ffffff"/>
                    <circle cx="18" cy="17" r="1" fill="#ffffff"/>
                </svg>
            `;
        } else if (config.icon === 'globe') {
            iconSvg = `
                <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="14" fill="${config.color}" stroke="#ffffff" stroke-width="2"/>
                    <circle cx="16" cy="16" r="8" fill="none" stroke="#ffffff" stroke-width="1.5"/>
                    <path d="M8 16 Q16 10 24 16" fill="none" stroke="#ffffff" stroke-width="1.5"/>
                    <path d="M8 16 Q16 22 24 16" fill="none" stroke="#ffffff" stroke-width="1.5"/>
                    <line x1="16" y1="8" x2="16" y2="24" stroke="#ffffff" stroke-width="1.5"/>
                </svg>
            `;
        }
    }

    // Convert SVG to data URL
    const svgDataUrl = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(iconSvg);
    
    return {
        url: svgDataUrl,
        scaledSize: new google.maps.Size(32, 32),
        anchor: new google.maps.Point(16, 16)
    };
}

/**
 * Helper function to darken a color
 * @param {string} color - Hex color string
 * @param {number} percent - Percentage to darken
 * @returns {string} Darkened hex color
 */
function darkenColor(color, percent) {
    const num = parseInt(color.replace("#", ""), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) - amt;
    const G = (num >> 8 & 0x00FF) - amt;
    const B = (num & 0x0000FF) - amt;
    return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
        (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
        (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
}

// Google Maps initialization callback - Called when the script loads
function initializeGoogleMaps() {
    // Enhanced initialization with better error handling
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.warn('Google Maps API not available in callback');
        return;
    }
    
    console.log('Google Maps API loaded, checking libraries...');
    
    // Wait for all required libraries to be available
    function waitForLibraries() {
        return new Promise((resolve, reject) => {
            const maxWait = 30; // 3 seconds max wait
            let attempts = 0;
            
            function checkLibraries() {
                const hasBasic = google.maps.Map && google.maps.InfoWindow && google.maps.Marker;
                const hasPlaces = google.maps.places && google.maps.places.SearchBox;
                
                if (hasBasic) {
                    console.log('Google Maps basic libraries ready');
                    googleMapsInitialized = true;
                    resolve();
                    return;
                }
                
                attempts++;
                if (attempts > maxWait) {
                    console.warn('Timeout waiting for Google Maps libraries');
                    googleMapsInitialized = true; // Proceed anyway
                    resolve();
                    return;
                }
                
                setTimeout(checkLibraries, 100);
            }
            
            checkLibraries();
        });
    }
    
    waitForLibraries().then(() => {
        // Initialize maps if DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeMapElements);
        } else {
            // Add a small delay to ensure DOM is fully ready
            setTimeout(initializeMapElements, 100);
        }
        
        // Trigger any callbacks waiting for Google Maps to load
        if (window.googleMapsCallbacks && Array.isArray(window.googleMapsCallbacks)) {
            window.googleMapsCallbacks.forEach(callback => {
                try {
                    console.log('Executing Google Maps callback...');
                    callback();
                } catch (error) {
                    console.warn('Error in Google Maps callback:', error);
                }
            });
            window.googleMapsCallbacks = [];
        }
        
        console.log('Google Maps initialized successfully');
    });
}

// Error handler for Google Maps API loading
function gm_authFailure() {
    console.error('Google Maps authentication failed');
    
    // Show comprehensive error message on all map containers
    const mapContainers = document.querySelectorAll('[id$="-map"]');
    mapContainers.forEach(container => {
        container.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-circle"></i> Map Authentication Failed</h5>
                <p>There was an issue loading the map. This could be due to:</p>
                <ul class="mb-2">
                    <li>Invalid Google Maps API key</li>
                    <li>API key restrictions</li>
                    <li>Billing account issues</li>
                    <li>Network connectivity problems</li>
                </ul>
                <button class="btn btn-sm btn-primary" onclick="window.location.reload()">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </div>
        `;
    });
}

// Additional global error handler for map loading issues
window.addEventListener('error', function(event) {
    if (event.message && event.message.includes('Google Maps')) {
        console.error('Google Maps error detected:', event);
        
        // Show a user-friendly error message
        const mapContainers = document.querySelectorAll('[id$="-map"]:empty');
        mapContainers.forEach(container => {
            if (!container.querySelector('.alert')) {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Map Loading Issue</h6>
                        <p>The map is having trouble loading. <a href="#" onclick="window.location.reload()">Please refresh the page</a> to try again.</p>
                    </div>
                `;
            }
        });
    }
});

// Initialize map elements after both DOM and Google Maps are ready
function initializeMapElements() {
    // Initialize dashboard maps
    const travelMap = document.getElementById('travel-map');
    if (travelMap) {
        initializeTravelMap(travelMap);
    }

    // Initialize destination creation/editing maps
    const destinationMap = document.getElementById('destination-map');
    if (destinationMap) {
        initializeDestinationMap(destinationMap);
    }    // Initialize trip planning map
    const tripMap = document.getElementById('trip-map');
    if (tripMap) {
        initializeTripMap(tripMap);
    }
}

// Initialize when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-close alerts after 5 seconds
    const autoCloseAlerts = document.querySelectorAll('.alert-dismissible');
    if (autoCloseAlerts.length > 0) {
        autoCloseAlerts.forEach(alert => {
            setTimeout(() => {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 5000);
        });
    }    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    if (forms.length > 0) {
        initializeFormValidation(forms);
    }

    // Initialize modal accessibility features
    initializeModalAccessibility();    // Initialize map elements if Google Maps is already loaded
    if (googleMapsInitialized) {
        initializeMapElements();
    }

    // Handle status toggle in destination form
    const statusSelect = document.getElementById('status');
    const visitDateContainer = document.querySelector('.visit-date-container');
    if (statusSelect && visitDateContainer) {
        statusSelect.addEventListener('change', function() {
            if (this.value === '1') { // Visited selected
                visitDateContainer.style.display = 'block';
            } else {
                visitDateContainer.style.display = 'none';
            }
        });
    }

    // Profile photo upload preview
    const profileImageInput = document.getElementById('profile_image');
    const profileImagePreview = document.getElementById('profile_image_preview');
    if (profileImageInput && profileImagePreview) {
        profileImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }    // Trip destination selection
    if (typeof initializeDestinationSelectors === 'function') {
        initializeDestinationSelectors();
    }

    // Handle badge tooltips
    const badgeElements = document.querySelectorAll('.badge-card');
    if (badgeElements.length > 0) {
        badgeElements.forEach(badge => {
            const description = badge.getAttribute('data-description');
            if (description) {
                new bootstrap.Tooltip(badge, {
                    title: description,
                    placement: 'top'
                });
            }
        });
    }

    // Initialize quick destination creation functionality
    initializeQuickDestinationCreate();

    // Initialize modal accessibility features
    initializeModalAccessibility();
});

/**
 * Utility function to get Google Maps API key from meta tag
 * This function is used by tests and other components that need to access the API key
 * @returns {string|null} The Google Maps API key or null if not found
 */
function getGoogleMapsApiKey() {
    const metaTag = document.querySelector('meta[name="google-maps-api-key"]');
    return metaTag ? metaTag.getAttribute('content') : null;
}

/**
 * Initialize form validation 
 * @param {NodeList} forms - Forms to validate
 */
function initializeFormValidation(forms) {
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Password strength validation
        const passwordField = form.querySelector('#password');
        if (passwordField) {
            passwordField.addEventListener('input', validatePassword);
        }

        // Username availability check
        const usernameField = form.querySelector('#username');
        if (usernameField) {
            usernameField.addEventListener('blur', checkUsernameAvailability);
        }

        // Email availability check
        const emailField = form.querySelector('#email');
        if (emailField) {
            emailField.addEventListener('blur', checkEmailAvailability);
        }
    });
}

/**
 * Validate password strength
 * @param {Event} event - Input event
 */
function validatePassword(event) {
    const password = event.target.value;
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    const isValid = regex.test(password);
    
    const passwordFeedback = document.getElementById('password-feedback');
    if (passwordFeedback) {
        if (isValid) {
            passwordFeedback.textContent = 'Password strength: Good';
            passwordFeedback.className = 'form-text text-success';
        } else {
            passwordFeedback.textContent = 'Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number';
            passwordFeedback.className = 'form-text text-danger';
        }
    }
}

/**
 * Check username availability via AJAX
 * @param {Event} event - Blur event
 */
function checkUsernameAvailability(event) {
    const username = event.target.value;
    if (username.length < 3) return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/auth/check-username', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            const feedback = document.getElementById('username-feedback');
            if (feedback) {
                if (response.available) {
                    feedback.textContent = 'Username is available';
                    feedback.className = 'form-text text-success';
                } else {
                    feedback.textContent = 'Username is already taken';
                    feedback.className = 'form-text text-danger';
                }
            }
        }
    };
    xhr.send('username=' + encodeURIComponent(username));
}

/**
 * Check email availability via AJAX
 * @param {Event} event - Blur event
 */
function checkEmailAvailability(event) {
    const email = event.target.value;
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) return;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/auth/check-email', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            const feedback = document.getElementById('email-feedback');
            if (feedback) {
                if (response.available) {
                    feedback.textContent = 'Email is available';
                    feedback.className = 'form-text text-success';
                } else {
                    feedback.textContent = 'Email is already registered';
                    feedback.className = 'form-text text-danger';
                }
            }
        }
    };
    xhr.send('email=' + encodeURIComponent(email));
}

/**
 * Initialize the travel map on the dashboard
 * @param {HTMLElement} mapElement - Map container element
 */
function initializeTravelMap(mapElement) {
    // Default center (0,0) - will adjust based on user's destinations
    const mapOptions = {
        zoom: 2,
        center: { lat: 20, lng: 0 },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: [
            { featureType: "water", elementType: "geometry", stylers: [{ color: "#e9e9e9" }, { lightness: 17 }] },
            { featureType: "landscape", elementType: "geometry", stylers: [{ color: "#f5f5f5" }, { lightness: 20 }] },
            { featureType: "road.highway", elementType: "geometry.fill", stylers: [{ color: "#ffffff" }, { lightness: 17 }] },
            { featureType: "road.highway", elementType: "geometry.stroke", stylers: [{ color: "#ffffff" }, { lightness: 29 }, { weight: 0.2 }] },
            { featureType: "road.arterial", elementType: "geometry", stylers: [{ color: "#ffffff" }, { lightness: 18 }] },
            { featureType: "road.local", elementType: "geometry", stylers: [{ color: "#ffffff" }, { lightness: 16 }] },
            { featureType: "poi", elementType: "geometry", stylers: [{ color: "#f5f5f5" }, { lightness: 21 }] },
            { featureType: "poi.park", elementType: "geometry", stylers: [{ color: "#dedede" }, { lightness: 21 }] },
            { elementType: "labels.text.stroke", stylers: [{ visibility: "on" }, { color: "#ffffff" }, { lightness: 16 }] },
            { elementType: "labels.text.fill", stylers: [{ saturation: 36 }, { color: "#333333" }, { lightness: 40 }] },
            { elementType: "labels.icon", stylers: [{ visibility: "off" }] },
            { featureType: "transit", elementType: "geometry", stylers: [{ color: "#f2f2f2" }, { lightness: 19 }] },
            { featureType: "administrative", elementType: "geometry.fill", stylers: [{ color: "#fefefe" }, { lightness: 20 }] },
            { featureType: "administrative", elementType: "geometry.stroke", stylers: [{ color: "#fefefe" }, { lightness: 17 }, { weight: 1.2 }] }
        ]
    };    // Create the map with mapId for Advanced Markers
    mapOptions.mapId = 'MAPIT_TRAVEL_MAP';
    window.travelMap = new google.maps.Map(mapElement, mapOptions);
    infoWindow = new google.maps.InfoWindow();

    // Enable interactive map clicking for adding destinations
    enableInteractiveMapClicking(window.travelMap);

    // Note: Destinations will be loaded by the dashboard page's own initialization
    // This function only sets up the map instance for use by other functions
}

/**
 * Enable interactive map clicking for destination creation
 * @param {google.maps.Map} map - The map instance
 */
function enableInteractiveMapClicking(map) {
    let markerClickOccurred = false;
    
    // Add click listener for map
    map.addListener('click', function(event) {
        // Use a small delay to allow marker clicks to be processed first
        setTimeout(() => {
            if (!markerClickOccurred) {
                handleMapClick(event.latLng, map);
            }
            markerClickOccurred = false;
        }, 10);
    });
    
    // Track when marker clicks occur to prevent map click handling
    window.onMarkerClick = function() {
        markerClickOccurred = true;
    };
}

/**
 * Handle map click for destination creation
 * @param {google.maps.LatLng} position - The clicked position
 * @param {google.maps.Map} map - The map instance
 */
function handleMapClick(position, map) {
    // Store the selected position
    selectedPosition = position;
    
    // Remove existing quick create marker
    if (quickCreateMarker) {
        quickCreateMarker.map = null;
    }
    
    // Create marker element with custom styling
    const markerElement = document.createElement('div');
    markerElement.innerHTML = `
        <div style="
            width: 32px; 
            height: 32px; 
            background: #007bff; 
            border: 2px solid white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-weight: bold; 
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        ">+</div>
    `;
    
    // Create new marker at clicked position using AdvancedMarkerElement
    try {
        // Try to use the new AdvancedMarkerElement if available
        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
            quickCreateMarker = new google.maps.marker.AdvancedMarkerElement({
                position: position,
                map: map,
                title: 'New destination location',
                content: markerElement
            });        } else {
            // Fallback to legacy Marker for compatibility
            quickCreateMarker = new google.maps.Marker({
                position: position,
                map: map,
                title: 'New destination location',
                icon: createEnhancedFallbackMarker('public'),
                animation: google.maps.Animation.DROP
            });
        }
    } catch (error) {
        // Fallback to legacy Marker
        quickCreateMarker = new google.maps.Marker({
            position: position,
            map: map,
            title: 'New destination location',
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="12" fill="#007bff" stroke="#ffffff" stroke-width="2"/>
                        <text x="16" y="21" text-anchor="middle" fill="white" font-size="16" font-weight="bold">+</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 32),
                anchor: new google.maps.Point(16, 16)
            },
            animation: google.maps.Animation.DROP
        });
    }
    // Update modal with coordinates
    updateQuickCreateModal(position);
    
    // Show the modal
    const modalElement = document.getElementById('quickAddDestinationModal');
    if (!modalElement) {
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

/**
 * Update the quick create modal with location data
 * @param {google.maps.LatLng} position - The selected position
 */
function updateQuickCreateModal(position) {
    // Update coordinate inputs
    document.getElementById('quickDestinationLat').value = position.lat().toFixed(6);
    document.getElementById('quickDestinationLng').value = position.lng().toFixed(6);
    
    // Update coordinate display
    document.getElementById('selectedCoordinates').textContent = 
        `${position.lat().toFixed(6)}, ${position.lng().toFixed(6)}`;
    
    // Try to reverse geocode to get location details
    reverseGeocodeForQuickCreate(position);
}

/**
 * Reverse geocode position for quick create modal
 * @param {google.maps.LatLng} position - The position to reverse geocode
 */
function reverseGeocodeForQuickCreate(position) {
    // Check if Geocoding API is available
    if (!google.maps.Geocoder) {
        console.warn('Geocoding API not available, skipping reverse geocoding');
        return;
    }
    
    const geocoder = new google.maps.Geocoder();
    
    geocoder.geocode({ location: position }, function(results, status) {
        if (status === google.maps.GeocoderStatus.OK && results && results.length > 0) {
            const result = results[0];
            const addressComponents = result.address_components;
            
            let city = '';
            let country = '';
            let countryCode = '';
            let placeName = '';
            
            // Extract address components
            for (let i = 0; i < addressComponents.length; i++) {
                const component = addressComponents[i];
                const types = component.types;
                
                if (types.includes('locality')) {
                    city = component.long_name;
                } else if (types.includes('administrative_area_level_1') && city === '') {
                    city = component.long_name;
                } else if (types.includes('country')) {
                    country = component.long_name;
                    countryCode = component.short_name;
                } else if (types.includes('point_of_interest') || types.includes('establishment')) {
                    placeName = component.long_name;
                }
            }
            
            // Auto-fill form fields
            const nameInput = document.getElementById('quickDestinationName');
            const cityInput = document.getElementById('quickDestinationCity');
            const countrySelect = document.getElementById('quickDestinationCountry');
            
            // Set name if we found a point of interest
            if (placeName && nameInput && !nameInput.value) {
                nameInput.value = placeName;
            }
            
            // Set city
            if (city && cityInput) {
                cityInput.value = city;
            }
            
            // Set country
            if (countryCode && countrySelect) {
                for (let i = 0; i < countrySelect.options.length; i++) {
                    if (countrySelect.options[i].value === countryCode) {
                        countrySelect.selectedIndex = i;
                        break;
                    }
                }
            }
        } else if (status === google.maps.GeocoderStatus.REQUEST_DENIED) {
            console.warn('Geocoding API request denied - API may not be enabled for this project');
            showNotification('Geocoding service not available. Please fill in location details manually.', 'warning');
        } else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
            console.warn('Geocoding API quota exceeded');
            showNotification('Geocoding quota exceeded. Please fill in location details manually.', 'warning');
        } else {
            console.warn('Geocoding failed:', status);
        }
    });
}

/**
 * Initialize quick destination creation functionality
 */
function initializeQuickDestinationCreate() {
    // Handle status change to show/hide visit date
    const statusSelect = document.getElementById('quickDestinationStatus');
    const visitDateContainer = document.querySelector('#quickAddDestinationModal .visit-date-container');
    
    if (statusSelect && visitDateContainer) {
        statusSelect.addEventListener('change', function() {
            visitDateContainer.style.display = this.value === '1' ? 'block' : 'none';
        });
    }
    
    // Handle save button
    const saveButton = document.getElementById('saveQuickDestination');
    if (saveButton) {
        saveButton.addEventListener('click', handleQuickDestinationSave);
    }
    
    // Handle modal close - remove marker
    const modal = document.getElementById('quickAddDestinationModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            resetQuickCreateModal();
        });
        
        // Enhanced accessibility: Focus management
        modal.addEventListener('shown.bs.modal', function() {
            // Focus on the first input field when modal opens
            const firstInput = modal.querySelector('input[type="text"], input[type="email"], textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        // Trap focus within modal
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                trapFocusInModal(e, modal);
            }
        });
    }
}

/**
 * Trap focus within a modal for accessibility
 * @param {KeyboardEvent} e - The keyboard event
 * @param {HTMLElement} modal - The modal element
 */
function trapFocusInModal(e, modal) {
    const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];
    
    if (e.shiftKey && document.activeElement === firstFocusable) {
        // Shift + Tab on first element, go to last
        e.preventDefault();
        lastFocusable.focus();
    } else if (!e.shiftKey && document.activeElement === lastFocusable) {
        // Tab on last element, go to first
        e.preventDefault();
        firstFocusable.focus();
    }
}

/**
 * Initialize modal accessibility features for all modals
 */
function initializeModalAccessibility() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        // Focus management
        modal.addEventListener('shown.bs.modal', function() {
            // Store the element that triggered the modal
            modal.previouslyFocusedElement = document.activeElement;
            
            // Focus on the close button or first focusable element
            const closeButton = modal.querySelector('.btn-close');
            const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            
            if (closeButton) {
                closeButton.focus();
            } else if (firstFocusable) {
                firstFocusable.focus();
            }
        });
        
        // Return focus when modal is hidden
        modal.addEventListener('hidden.bs.modal', function() {
            if (modal.previouslyFocusedElement) {
                modal.previouslyFocusedElement.focus();
                modal.previouslyFocusedElement = null;
            }
        });
        
        // Trap focus within modal
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                trapFocusInModal(e, modal);
            }
            
            // Close modal on Escape key
            if (e.key === 'Escape') {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        });
        
        // Ensure proper ARIA attributes
        if (!modal.hasAttribute('aria-labelledby')) {
            const modalTitle = modal.querySelector('.modal-title');
            if (modalTitle && modalTitle.id) {
                modal.setAttribute('aria-labelledby', modalTitle.id);
            }
        }
        
        if (!modal.hasAttribute('aria-describedby')) {
            const modalBody = modal.querySelector('.modal-body');
            if (modalBody && modalBody.id) {
                modal.setAttribute('aria-describedby', modalBody.id);
            }
        }
    });
}

/**
 * Handle saving the quick destination
 */
function handleQuickDestinationSave() {
    const form = document.getElementById('quickAddDestinationForm');
    const saveButton = document.getElementById('saveQuickDestination');
    
    if (!form || !selectedPosition) return;
    
    // Get form data
    const formData = new FormData(form);
    const data = {
        name: formData.get('name'),
        city: formData.get('city'),
        country: formData.get('country'),
        description: formData.get('description'),
        visited: formData.get('visited'),
        visit_date: formData.get('visit_date'),
        privacy: formData.get('privacy') || 'private',
        latitude: selectedPosition.lat(),
        longitude: selectedPosition.lng()
    };
    
    // Basic validation
    if (!data.name || !data.latitude || !data.longitude) {
        alert('Please fill in the destination name and make sure a location is selected.');
        return;
    }
    
    // Disable save button
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';
    // Send to API
    fetch('/api/destinations/quick-create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickAddDestinationModal'));
            modal.hide();
            
            // Show success message
            showNotification('Destination added successfully!', 'success');
            
            // Add the new destination to the map immediately
            addNewDestinationToMap(result.data, data);
              // Update page stats if available
            updateDestinationStats(data.visited);
            
        } else {
            throw new Error(result.message || 'Failed to create destination');
        }
    })
    .catch(error => {
        showNotification(error.message || 'Failed to create destination', 'error');
    })
    .finally(() => {
        // Re-enable save button
        saveButton.disabled = false;
        saveButton.textContent = 'Save Destination';
    });
}

/**
 * Reset the quick create modal
 */
function resetQuickCreateModal() {
    // Remove the marker
    if (quickCreateMarker) {
        quickCreateMarker.setMap(null);
        quickCreateMarker = null;
    }
    
    // Clear form
    const form = document.getElementById('quickAddDestinationForm');
    if (form) {
        form.reset();
    }
    
    // Reset coordinate display
    const coordsDisplay = document.getElementById('selectedCoordinates');
    if (coordsDisplay) {
        coordsDisplay.textContent = 'Click on the map to set location';
    }
    
    // Hide visit date container
    const visitDateContainer = document.querySelector('#quickAddDestinationModal .visit-date-container');
    if (visitDateContainer) {
        visitDateContainer.style.display = 'none';
    }
    
    // Clear selected position
    selectedPosition = null;
}

/**
 * Show notification to user
 * @param {string} message - The message to show
 * @param {string} type - The type of notification (success, error, info, warning)
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

/**
 * Add a newly created destination to the current map
 * @param {Object} destinationData - Data returned from API
 * @param {Object} formData - Original form data submitted
 */
function addNewDestinationToMap(destinationData, formData) {
    // Get the current map (could be travelMap or destinationsMap)
    const currentMap = window.travelMap || window.destinationsMap;
    if (!currentMap) {
        setTimeout(() => window.location.reload(), 1000);
        return;
    }
    // Remove the temporary marker
    if (quickCreateMarker) {
        if (quickCreateMarker.map !== undefined) {
            quickCreateMarker.map = null; // AdvancedMarkerElement
        } else {
            quickCreateMarker.setMap(null); // Legacy Marker
        }
        quickCreateMarker = null;
    }
    
    // Create the new destination marker
    const position = {
        lat: parseFloat(destinationData.latitude),
        lng: parseFloat(destinationData.longitude)
    };
    
    // Determine if this is a visited destination
    const isVisited = formData.visited === '1' || formData.visited === 1;
      // Create marker element for AdvancedMarkerElement
    const markerElement = document.createElement('div');
    markerElement.innerHTML = `
        <img src="/images/markers/${isVisited ? 'visited_enhanced' : 'wishlist_enhanced'}.svg" 
             style="width: 32px; height: 32px;" 
             alt="${isVisited ? 'Visited' : 'Wishlist'} destination"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <div style="
            display: none;
            width: 16px; 
            height: 16px; 
            background: ${isVisited ? '#28a745' : '#ffc107'}; 
            border: 2px solid white; 
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        "></div>
    `;
    
    // Create the marker using new API if available, fallback to legacy
    let marker;
    try {
        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
            marker = new google.maps.marker.AdvancedMarkerElement({
                position: position,
                map: currentMap,
                title: destinationData.name,
                content: markerElement
            });
        } else {
            // Fallback to legacy marker
            const visitedIcon = {
                url: '/images/markers/visited.png',
                scaledSize: new google.maps.Size(32, 32)
            };
            const wishlistIcon = {
                url: '/images/markers/wishlist.png',
                scaledSize: new google.maps.Size(32, 32)
            };
            
            marker = new google.maps.Marker({
                position: position,
                map: currentMap,
                title: destinationData.name,
                icon: isVisited ? visitedIcon : wishlistIcon
            });
              // Add error handling for marker icon loading
            marker.addListener('icon_changed', function() {
                const img = new Image();
                img.onerror = function() {
                    // Create attractive SVG fallback instead of boring circles
                    const fallbackIcon = createEnhancedFallbackMarker(isVisited ? 'visited' : 'wishlist');
                    marker.setIcon(fallbackIcon);
                };
                img.src = (isVisited ? visitedIcon : wishlistIcon).url;
            });
        }    } catch (error) {
        // Fallback to legacy marker
        const visitedIcon = {
            url: '/images/markers/visited_enhanced.svg',
            scaledSize: new google.maps.Size(32, 32)
        };
        const wishlistIcon = {
            url: '/images/markers/wishlist_enhanced.svg',
            scaledSize: new google.maps.Size(32, 32)
        };
        
        marker = new google.maps.Marker({
            position: position,
            map: currentMap,
            title: destinationData.name,
            icon: isVisited ? visitedIcon : wishlistIcon
        });
          // Add error handling for marker icon loading
        marker.addListener('icon_changed', function() {
            const img = new Image();
            img.onerror = function() {
                // Create attractive SVG fallback instead of boring circles
                const fallbackIcon = createEnhancedFallbackMarker(isVisited ? 'visited' : 'wishlist');
                marker.setIcon(fallbackIcon);
            };
            img.src = (isVisited ? visitedIcon : wishlistIcon).url;
        });
    }
    
    // Create info window
    const infoWindow = new google.maps.InfoWindow({
        content: `
            <div class="info-window">
                <h5>${destinationData.name}</h5>
                <p>${formData.city || 'Unknown'}, ${formData.country || 'Unknown'}</p>
                <a href="/destinations/${destinationData.id}" class="btn btn-sm btn-primary">View Details</a>
            </div>
        `
    });
    // Add click listener based on marker type
    if (google.maps.marker && google.maps.marker.AdvancedMarkerElement &&
        marker instanceof google.maps.marker.AdvancedMarkerElement) {
        marker.addEventListener('click', () => {
            infoWindow.open({
                anchor: marker,
                map: currentMap
            });
        });
    } else {
        marker.addListener('click', () => {
            infoWindow.open(currentMap, marker);
        });
    }
    
    // Optionally adjust map bounds to include new marker
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(position);
    
    // Get all existing markers and extend bounds
    // This is a simplified approach - in a real app you might track markers
    currentMap.panTo(position);
}

/**
 * Update destination statistics on the page
 */
function updateDestinationStats(visitedStatus) {
    // This function updates any stat counters on the page
    // Look for elements that display destination counts
    
    // Check if the new destination was marked as visited
    const isVisited = visitedStatus === '1' || visitedStatus === 1;
    
    if (isVisited) {
        // Update places visited count if present
        const placesVisitedElement = document.querySelector('[data-stat="places_visited"]');
        if (placesVisitedElement) {
            const currentCount = parseInt(placesVisitedElement.textContent) || 0;
            placesVisitedElement.textContent = currentCount + 1;
        }
    } else {
        // Update wishlist count if present
        const wishlistCountElement = document.querySelector('[data-stat="wishlist_count"]');
        if (wishlistCountElement) {
            const currentCount = parseInt(wishlistCountElement.textContent) || 0;
            wishlistCountElement.textContent = currentCount + 1;
        }
    }
    
    // Update total destinations count if present
    const totalDestinationsElement = document.querySelector('[data-stat="total_destinations"]');
    if (totalDestinationsElement) {
        const currentCount = parseInt(totalDestinationsElement.textContent) || 0;
        totalDestinationsElement.textContent = currentCount + 1;
    }
    
    // If on destinations page, we might want to add a new card to the grid
    if (window.location.pathname.includes('/destinations')) {
        // Show a message that the page will refresh to show the new destination
        showNotification('Refreshing page to show your new destination...', 'info');
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }
}

/**
 * Function to initialize destination selectors for trips
 */
function initializeDestinationSelectors() {
    const destinationSelects = document.querySelectorAll('select[name="destination_ids[]"]');
    
    if (destinationSelects.length > 0) {
        destinationSelects.forEach(select => {
            // Add event listeners for destination selection
            select.addEventListener('change', function() {
                const selectedDestination = this.value;
                if (selectedDestination) {
                    // Update any related UI elements
                    updateDestinationDisplay(selectedDestination);
                }
            });
        });
    }

    // Initialize destination search functionality
    const destinationSearch = document.getElementById('destination-search');
    if (destinationSearch) {
        destinationSearch.addEventListener('input', function() {
            debounceDestinationSearch(this.value);
        });
    }

    // Initialize add destination buttons
    const addDestinationBtns = document.querySelectorAll('.add-destination-btn');
    if (addDestinationBtns.length > 0) {
        addDestinationBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                addNewDestinationRow();
            });
        });
    }
}

/**
 * Helper function to update destination display
 */
function updateDestinationDisplay(destinationId) {
    // Find and update any related destination displays
    const displays = document.querySelectorAll(`[data-destination-id="${destinationId}"]`);
    displays.forEach(display => {
        // Update display as needed
        display.classList.add('selected');
    });
}

/**
 * Debounced search function
 */
let destinationSearchTimeout;
function debounceDestinationSearch(query) {
    clearTimeout(destinationSearchTimeout);
    destinationSearchTimeout = setTimeout(() => {
        if (query.length >= 2) {
            searchDestinations(query);
        }
    }, 300);
}

/**
 * Search destinations function
 */
function searchDestinations(query) {
    fetch(`/api/destinations/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                updateDestinationSearchResults(data.data);
            }
        })
        .catch(error => {
            console.warn('Destination search failed:', error);
        });
}

/**
 * Update search results
 */
function updateDestinationSearchResults(destinations) {
    const resultsContainer = document.getElementById('destination-search-results');
    if (!resultsContainer) return;

    resultsContainer.innerHTML = '';
    
    destinations.forEach(destination => {
        const resultItem = document.createElement('div');
        resultItem.className = 'destination-search-result';
        resultItem.innerHTML = `
            <div class="destination-item" data-destination-id="${destination.id}">
                <h6>${destination.name}</h6>
                <small class="text-muted">${destination.location || ''}</small>
            </div>
        `;
        
        resultItem.addEventListener('click', () => {
            selectDestination(destination);
        });
        
        resultsContainer.appendChild(resultItem);
    });
}

/**
 * Select a destination
 */
function selectDestination(destination) {
    const event = new CustomEvent('destinationSelected', {
        detail: destination
    });
    document.dispatchEvent(event);
}

/**
 * Add new destination row (for multi-destination trips)
 */
function addNewDestinationRow() {
    const container = document.getElementById('destinations-container');
    if (!container) return;

    const newRow = document.createElement('div');
    newRow.className = 'destination-row mb-3';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-10">
                <select name="destination_ids[]" class="form-select" required>
                    <option value="">Select a destination</option>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-destination-btn">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;

    // Add remove functionality
    const removeBtn = newRow.querySelector('.remove-destination-btn');
    removeBtn.addEventListener('click', () => {
        newRow.remove();
    });

    container.appendChild(newRow);
    
    // Initialize the new select
    const newSelect = newRow.querySelector('select');
    initializeDestinationSelect(newSelect);
}

/**
 * Initialize a single destination select
 */
function initializeDestinationSelect(selectElement) {
    // Populate with existing destinations
    fetch('/api/destinations')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                selectElement.innerHTML = '<option value="">Select a destination</option>';
                data.data.forEach(destination => {
                    const option = document.createElement('option');
                    option.value = destination.id;
                    option.textContent = destination.name;
                    selectElement.appendChild(option);
                });
            }        })
        .catch(error => {
            console.warn('Failed to load destinations:', error);
        });
}

/**
 * Utility function to get Google Maps API key from meta tag
 * This function is used by tests and other components that need to access the API key
 * @returns {string|null} The Google Maps API key or null if not found
 */
function getGoogleMapsApiKey() {
    const metaTag = document.querySelector('meta[name="google-maps-api-key"]');
    return metaTag ? metaTag.getAttribute('content') : null;
}
