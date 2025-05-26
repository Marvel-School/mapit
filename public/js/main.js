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

// Google Maps initialization callback
function initializeGoogleMaps() {
    googleMapsInitialized = true;
    console.log('Google Maps API loaded successfully');
    
    // Initialize maps if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMapElements);
    } else {
        initializeMapElements();
    }
}

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
    }

    // Initialize trip planning map
    const tripMap = document.getElementById('trip-map');
    if (tripMap) {
        initializeTripMap(tripMap);
    }

    // Initialize destinations index map
    const destinationsMap = document.getElementById('destinations-map');
    if (destinationsMap) {
        initDestinationsMap();
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

    // Initialize map elements if Google Maps is already loaded
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
    }

    // Trip destination selection
    initializeDestinationSelectors();

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
});

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
    };

    // Create the map
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
    // Add click listener for map
    map.addListener('click', function(event) {
        handleMapClick(event.latLng, map);
    });
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
            });
        } else {
            // Fallback to legacy Marker for compatibility
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
    } catch (error) {
        console.warn('Error creating advanced marker, falling back to legacy marker:', error);
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
    const modal = new bootstrap.Modal(document.getElementById('quickAddDestinationModal'));
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
    }
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
            updateDestinationStats();
            
        } else {
            throw new Error(result.message || 'Failed to create destination');
        }
    })
    .catch(error => {
        console.error('Error creating destination:', error);
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
        console.log('No active map found, will reload page');
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
        <img src="/images/markers/${isVisited ? 'visited' : 'wishlist'}.png" 
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
                    const fallbackIcon = {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: isVisited ? '#28a745' : '#ffc107',
                        fillOpacity: 0.8,
                        scale: 8,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    };
                    marker.setIcon(fallbackIcon);
                };
                img.src = (isVisited ? visitedIcon : wishlistIcon).url;
            });
        }
    } catch (error) {
        console.warn('Error creating advanced marker, using legacy marker:', error);
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
                const fallbackIcon = {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: isVisited ? '#28a745' : '#ffc107',
                    fillOpacity: 0.8,
                    scale: 8,
                    strokeColor: '#ffffff',
                    strokeWeight: 2                };
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
    });    // Add click listener based on marker type
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
    
    console.log('New destination marker added to map:', destinationData.name);
}

/**
 * Update destination statistics on the page
 */
function updateDestinationStats() {
    // This function updates any stat counters on the page
    // Look for elements that display destination counts
    
    // Check if the new destination was marked as visited
    const statusSelect = document.getElementById('quickDestinationStatus');
    const isVisited = statusSelect && statusSelect.value === '1';
    
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
    
    console.log('Destination stats updated');
}
