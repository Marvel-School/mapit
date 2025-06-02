<?php
// Edit Trip view
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">Edit Trip</h4>
                </div>
                <div class="card-body">
                    <form action="/trips/<?= $trip['id']; ?>" method="POST">
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Trip Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($trip['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($trip['start_date'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($trip['end_date'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="planned" <?= isset($trip['status']) && $trip['status'] == 'planned' ? 'selected' : ''; ?>>Planned</option>
                                <option value="in_progress" <?= isset($trip['status']) && $trip['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?= isset($trip['status']) && $trip['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($trip['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Trip Destinations</h5>
                        <p class="text-muted small">Add or rearrange destinations in your trip itinerary.</p>
                        
                        <div class="mb-3">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1 me-2">
                                    <select class="form-select" id="destinationSelect">
                                        <option value="">Select a destination</option>
                                        <?php foreach ($userDestinations as $dest): ?>
                                            <option value="<?= $dest['id']; ?>" 
                                                    data-name="<?= htmlspecialchars($dest['name']); ?>"
                                                    data-city="<?= htmlspecialchars($dest['city']); ?>"
                                                    data-country="<?= htmlspecialchars($dest['country_name']); ?>"
                                                    data-lat="<?= $dest['latitude']; ?>"
                                                    data-lng="<?= $dest['longitude']; ?>">
                                                <?= htmlspecialchars($dest['name'] . ' (' . $dest['city'] . ', ' . $dest['country_name'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" id="addDestination">Add</button>
                            </div>
                            
                            <div id="tripDestinations" class="list-group mb-3">
                                <!-- Destinations will be added here dynamically -->
                                <div class="text-center py-3 empty-state" id="noDestinationsMessage" style="display: <?= !empty($tripDestinations) ? 'none' : 'block'; ?>">
                                    <p class="text-muted">No destinations added yet. Select a destination from the dropdown above.</p>
                                </div>
                            </div>
                            
                            <!-- Hidden input to store selected destinations -->
                            <input type="hidden" name="destinations" id="destinationsInput" value="<?= htmlspecialchars(json_encode(array_column($tripDestinations, 'id'))); ?>">
                        </div>
                        
                        <div id="trip-map" class="mb-4" style="height: 400px;"></div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/trips" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Trip</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date validation
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    function validateDates() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (startDate > endDate) {
            endDateInput.setCustomValidity('End date must be after start date');
        } else {
            endDateInput.setCustomValidity('');
        }
    }
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
    }
    
    // Trip destinations management
    const destinationSelect = document.getElementById('destinationSelect');
    const addDestinationButton = document.getElementById('addDestination');
    const tripDestinations = document.getElementById('tripDestinations');
    const destinationsInput = document.getElementById('destinationsInput');
    const noDestinationsMessage = document.getElementById('noDestinationsMessage');
    
    let selectedDestinations = <?= json_encode($tripDestinations); ?>;
    let map, markers = [];
    let directionsService, directionsRenderer;
    
    // Initialize the map
    function initMap() {
        const mapElement = document.getElementById('trip-map');
        if (!mapElement) return;
          map = new google.maps.Map(mapElement, {
            center: { lat: 20, lng: 0 },
            zoom: 2,
            mapTypeId: 'terrain',
            mapTypeControl: false,
            mapId: 'MAPIT_TRIP_EDIT_MAP'
        });
        
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true
        });
        
        // Initialize map with existing destinations
        if (selectedDestinations.length > 0) {
            updateDestinationsDisplay();
            updateMap();
        }
    }
    
    // Add destination to the trip
    function addDestination() {
        const destSelect = document.getElementById('destinationSelect');
        const selectedOption = destSelect.options[destSelect.selectedIndex];
        
        if (destSelect.value === '') return;
        
        const destId = destSelect.value;
        
        // Check if destination is already added
        if (selectedDestinations.some(d => d.id === destId)) {
            alert('This destination is already added to your trip.');
            return;
        }
        
        const destination = {
            id: destId,
            name: selectedOption.getAttribute('data-name'),
            city: selectedOption.getAttribute('data-city'),
            country: selectedOption.getAttribute('data-country'),
            latitude: parseFloat(selectedOption.getAttribute('data-lat')),
            longitude: parseFloat(selectedOption.getAttribute('data-lng')),
            order: selectedDestinations.length + 1
        };
        
        selectedDestinations.push(destination);
        updateDestinationsDisplay();
        updateDestinationsInput();
        updateMap();
        
        // Reset select
        destSelect.selectedIndex = 0;
    }
    
    // Update destinations display
    function updateDestinationsDisplay() {
        // Show/hide empty state message
        if (selectedDestinations.length > 0) {
            noDestinationsMessage.style.display = 'none';
        } else {
            noDestinationsMessage.style.display = 'block';
        }
        
        // Clear current list except for the empty state message
        const items = tripDestinations.querySelectorAll('.list-group-item');
        items.forEach(item => item.remove());
        
        // Add destinations to the list
        selectedDestinations.forEach((dest, index) => {
            const item = document.createElement('div');
            item.className = 'list-group-item d-flex justify-content-between align-items-center';
            item.innerHTML = `
                <div>
                    <div class="fw-medium">${index + 1}. ${dest.name}</div>
                    <div class="small text-muted">${dest.city}, ${dest.country}</div>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-up" ${index === 0 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-down" ${index === selectedDestinations.length - 1 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-dest">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            // Add event listeners
            const moveUpBtn = item.querySelector('.move-up');
            const moveDownBtn = item.querySelector('.move-down');
            const removeBtn = item.querySelector('.remove-dest');
            
            moveUpBtn.addEventListener('click', function() {
                moveDestination(index, 'up');
            });
            
            moveDownBtn.addEventListener('click', function() {
                moveDestination(index, 'down');
            });
            
            removeBtn.addEventListener('click', function() {
                removeDestination(index);
            });
            
            tripDestinations.appendChild(item);
        });
    }
    
    // Move destination up or down in the list
    function moveDestination(index, direction) {
        if (direction === 'up' && index > 0) {
            const temp = selectedDestinations[index];
            selectedDestinations[index] = selectedDestinations[index - 1];
            selectedDestinations[index - 1] = temp;
        } else if (direction === 'down' && index < selectedDestinations.length - 1) {
            const temp = selectedDestinations[index];
            selectedDestinations[index] = selectedDestinations[index + 1];
            selectedDestinations[index + 1] = temp;
        }
        
        updateDestinationsDisplay();
        updateDestinationsInput();
        updateMap();
    }
    
    // Remove destination from the trip
    function removeDestination(index) {
        selectedDestinations.splice(index, 1);
        updateDestinationsDisplay();
        updateDestinationsInput();
        updateMap();
    }
    
    // Update hidden input with selected destinations
    function updateDestinationsInput() {
        destinationsInput.value = JSON.stringify(selectedDestinations.map(d => d.id));
    }
    
    // Update map with destinations
    function updateMap() {
        // Clear existing markers
        markers.forEach(marker => marker.setMap(null));
        markers = [];
        
        if (selectedDestinations.length === 0) {
            directionsRenderer.setDirections({routes: []});
            return;
        }
          // Add markers for each destination
        selectedDestinations.forEach((dest, index) => {
            const position = {lat: parseFloat(dest.latitude), lng: parseFloat(dest.longitude)};
            
            // Create marker with AdvancedMarkerElement or fallback
            let marker;
            try {
                if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
                    const markerElement = document.createElement('div');
                    markerElement.innerHTML = (index + 1).toString();
                    markerElement.style.background = '#4285f4';
                    markerElement.style.color = 'white';
                    markerElement.style.borderRadius = '50%';
                    markerElement.style.width = '30px';
                    markerElement.style.height = '30px';
                    markerElement.style.display = 'flex';
                    markerElement.style.alignItems = 'center';
                    markerElement.style.justifyContent = 'center';
                    markerElement.style.fontWeight = 'bold';
                    markerElement.style.fontSize = '14px';
                    
                    marker = new google.maps.marker.AdvancedMarkerElement({
                        position: position,
                        map: map,
                        title: dest.name,
                        content: markerElement
                    });
                } else {
                    throw new Error('AdvancedMarkerElement not available');
                }
            } catch (error) {
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: dest.name,
                    label: (index + 1).toString()
                });
            }
            
            markers.push(marker);
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="info-window">
                        <h5>${dest.name}</h5>
                        <p>${dest.city}, ${dest.country}</p>
                    </div>
                `
            });
              // Add click listener based on marker type
            if (google.maps.marker && google.maps.marker.AdvancedMarkerElement &&
                marker instanceof google.maps.marker.AdvancedMarkerElement) {
                marker.addEventListener('click', () => {
                    infoWindow.open({
                        anchor: marker,
                        map: map
                    });
                });
            } else {
                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });
            }
        });
        
        // If there are at least 2 destinations, draw a route
        if (selectedDestinations.length > 1) {
            const waypoints = selectedDestinations.slice(1, -1).map(dest => {
                return {
                    location: new google.maps.LatLng(parseFloat(dest.latitude), parseFloat(dest.longitude)),
                    stopover: true
                };
            });
            
            const origin = new google.maps.LatLng(
                parseFloat(selectedDestinations[0].latitude),
                parseFloat(selectedDestinations[0].longitude)
            );
            
            const destination = new google.maps.LatLng(
                parseFloat(selectedDestinations[selectedDestinations.length - 1].latitude),
                parseFloat(selectedDestinations[selectedDestinations.length - 1].longitude)
            );
            
            directionsService.route({
                origin: origin,
                destination: destination,
                waypoints: waypoints,
                optimizeWaypoints: false,
                travelMode: google.maps.TravelMode.DRIVING
            }, function(response, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                }
            });
        } else {
            // If just one destination, center the map on it
            directionsRenderer.setDirections({routes: []});
            map.setCenter({lat: parseFloat(selectedDestinations[0].latitude), lng: parseFloat(selectedDestinations[0].longitude)});
            map.setZoom(10);
        }
    }
    
    // Initialize the map
    initMap();
    
    // Add event listeners
    if (addDestinationButton) {
        addDestinationButton.addEventListener('click', addDestination);
    }
});
</script>
